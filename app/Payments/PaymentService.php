<?php

namespace App\Payments;

use App\Events\PaymentSucceeded;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * The payment flow, identical for every bank. Controller, webhook and your cron only call this class.
 *
 * Status: INITIATED -> PENDING -> SUCCESS | FAILED | AMOUNT_MISMATCH -> REFUND_INITIATED
 * The final status always comes from the bank's status API, never from the browser.
 */
class PaymentService
{
    private const LOCKED = ['SUCCESS', 'REFUND_INITIATED'];   // sync() never changes these

    private array $gateways = [];

    /** The driver for one configured account, e.g. gateway('icici_1'). */
    public function gateway(string $account): Gateway
    {
        return $this->gateways[$account] ??= $this->makeGateway($account);
    }

    /** Create a payment on $account and return where to send the customer. */
    public function start(string $account, array $data): BankRedirect
    {
        $gateway = $this->gateway($account);
        $payment = Payment::create($data + [
            'gateway' => $account,
            'txn_no'  => self::newTxnNo(config("payments.accounts.$account.txn_prefix", 'PAY')),
            'status'  => 'INITIATED',
        ]);

        try {
            $redirect = $gateway->start($payment, route('payment.callback', $account));
        } catch (Throwable $e) {
            $this->save($payment->txn_no, 'FAILED', ['error' => $e->getMessage()]);
            throw $e;
        }
        $this->save($payment->txn_no, 'PENDING', ['redirect' => $redirect->url]);

        return $redirect;
    }

    /** Anything the bank POSTs to /payment/{account}/webhook (payment or settlement notice). */
    public function fromBank(string $account, array $data): ?Payment
    {
        $note = $this->notification($account, $data);

        if (! empty($note['settlement_id'])) {
            if ($note['valid'] && $note['txn_no']) {   // settlement data is trusted only with a valid signature
                Payment::where('txn_no', $note['txn_no'])->update([
                    'settlement_id'   => $note['settlement_id'],
                    'settlement_date' => $note['settlement_date'] ?? null,
                ]);
            }

            return null;
        }

        return $note['txn_no'] ? $this->sync($note['txn_no']) : null;
    }

    /** Let the account's driver read (and log) what the bank posted: our txn number + signature check. */
    public function notification(string $account, array $data): array
    {
        $note = $this->gateway($account)->parseNotification($data);
        Log::info('Payment notification', ['account' => $account, 'valid' => $note['valid'], 'data' => $data]);

        return $note;
    }

    /** Ask the payment's own bank for the real status and update the DB. */
    public function sync(string $txnNo): ?Payment
    {
        $payment = Payment::firstWhere('txn_no', $txnNo);
        if (! $payment || in_array($payment->status, self::LOCKED, true)) {
            return $payment;
        }

        $bank = $this->gateway($payment->gateway)->status($payment);
        $status = $bank->status;
        if ($status === BankStatus::SUCCESS && self::money($bank->amount) !== self::money($payment->amount)) {
            $status = 'AMOUNT_MISMATCH';   // paid amount differs from order amount: review manually
        }

        $changed = $this->save($txnNo, $status, $bank->raw, $bank->bankTxnId, $bank->paymentMode);
        $payment->refresh();
        if ($changed && $status === BankStatus::SUCCESS) {
            PaymentSucceeded::dispatch($payment);   // fires exactly once per payment
        }

        return $payment;
    }

    /** Full or partial refund of a successful payment, on whichever bank it was paid. */
    public function refund(string $txnNo, ?string $amount = null): array
    {
        $payment = Payment::where('txn_no', $txnNo)->firstOrFail();
        if (! in_array($payment->status, self::LOCKED, true)) {
            throw new RuntimeException('Only successful payments can be refunded');
        }

        $left = (float) $payment->amount - (float) $payment->refunded_amount;
        $amount = self::money($amount ?? $left);
        if ((float) $amount <= 0 || (float) $amount > $left + 0.001) {
            throw new InvalidArgumentException('Refundable amount left: '.self::money($left));
        }

        $res = $this->gateway($payment->gateway)->refund($payment, $amount, self::newTxnNo('R'));
        $payment->update([
            'status'          => 'REFUND_INITIATED',
            'refunded_amount' => (float) $payment->refunded_amount + (float) $amount,
            'last_response'   => $res,
        ]);

        return $res;
    }

    /**
     * Call from your every-minute cron. Covers all accounts/banks. Safe every minute:
     * each unconfirmed payment is re-checked at most once per 15 min, max $limit per run,
     * and the run stops early if a bank is unreachable.
     */
    public function reconcile(int $limit = 20): array
    {
        $txnNos = Payment::whereIn('status', ['INITIATED', 'PENDING'])
            ->where('created_at', '>', now()->subDays(3))
            ->where('updated_at', '<', now()->subMinutes(15))
            ->oldest('updated_at')
            ->limit($limit)
            ->pluck('txn_no');

        $result = [];
        foreach ($txnNos as $txnNo) {
            try {
                $result[$txnNo] = $this->sync($txnNo)?->status;
            } catch (Throwable $e) {
                report($e);
                $result[$txnNo] = 'ERROR';
                break;   // bank unreachable: try again next minute
            }
        }

        return $result;
    }

    /** Shareable pay link for an account with a fixed amount (signed URL, amount cannot be edited). */
    public static function payLink(string $account, string|int|float $amount, ?string $orderRef = null): string
    {
        return URL::signedRoute('payment.create', array_filter(['account' => $account, 'amount' => $amount, 'order_ref' => $orderRef]));
    }

    /** Amount format banks expect: 2 decimals, no commas. */
    public static function money(string|int|float|null $v): string
    {
        return number_format((float) $v, 2, '.', '');
    }

    private function makeGateway(string $account): Gateway
    {
        $cfg = config("payments.accounts.$account") ?? throw new InvalidArgumentException("Unknown payment account [$account]");
        $gateway = new $cfg['driver']($cfg + ['name' => $account]);

        return $gateway instanceof Gateway ? $gateway : throw new InvalidArgumentException("{$cfg['driver']} must implement ".Gateway::class);
    }

    /** True only if the row really changed: stops double processing when return URL and webhook race. */
    private function save(string $txnNo, string $status, array $raw, ?string $bankTxnId = null, ?string $mode = null): bool
    {
        return Payment::where('txn_no', $txnNo)
            ->whereNotIn('status', self::LOCKED)
            ->update(array_filter(['status' => $status, 'bank_txn_id' => $bankTxnId, 'payment_mode' => $mode])
                + ['last_response' => json_encode($raw)]) === 1;
    }

    /** Alphanumeric, max 20 chars (safe for all banks): PREFIX(<=4) + yymmddHHMMSS + 4 random hex. */
    private static function newTxnNo(string $prefix): string
    {
        return substr(preg_replace('/[^A-Za-z0-9]/', '', $prefix), 0, 4)
            .now('Asia/Kolkata')->format('ymdHis')
            .strtoupper(bin2hex(random_bytes(2)));
    }
}
