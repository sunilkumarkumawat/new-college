<?php

namespace App\Payments\Drivers\Icici;

use App\Models\Payment;
use App\Payments\BankRedirect;
use App\Payments\BankStatus;
use App\Payments\Gateway;
use App\Payments\PaymentService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

/**
 * ICICI Bank Payment Gateway (redirect mode), as per the ICICI UAT kit.
 * One class serves any number of ICICI merchant accounts: each account passes its own config.
 */
class IciciGateway implements Gateway
{
    private const URLS = [
        'uat'  => 'https://pgpayuat.icicibank.com/tsp/pg/api',
        'prod' => 'https://pgpay.icicibank.com/pg/api',
    ];

    private const OK = ['0', '00', '000', '0000'];

    public function __construct(private array $cfg) {}

    public function start(Payment $payment, string $callbackUrl): BankRedirect
    {
        $res = $this->post('/v2/initiateSale', [
            'merchantTxnNo'    => $payment->txn_no,
            'amount'           => $payment->amount,
            'currencyCode'     => '356',
            'payType'          => 0,
            'transactionType'  => 'SALE',
            'txnDate'          => now('Asia/Kolkata')->format('YmdHis'),
            'customerName'     => $payment->customer_name,
            'customerEmailID'  => $payment->customer_email,
            'customerMobileNo' => $payment->customer_mobile,
            'addlParam1'       => $payment->order_ref,
            'returnURL'        => $callbackUrl,
        ]);

        if (($res['responseCode'] ?? '') !== 'R1000' || empty($res['redirectURI']) || empty($res['tranCtx'])) {
            throw new RuntimeException('ICICI Initiate Sale failed: '
                .($res['responseDescription'] ?? $res['respDescription'] ?? $res['responseCode'] ?? 'unknown'));
        }

        return new BankRedirect($res['redirectURI'].'?tranCtx='.urlencode((string) $res['tranCtx']));
    }

    public function status(Payment $payment): BankStatus
    {
        $res = $this->command('STATUS', $payment->txn_no);
        $ok = in_array((string) ($res['responseCode'] ?? ''), self::OK, true);

        return new BankStatus(
            status: match (true) {
                $ok && ($res['txnStatus'] ?? '') === 'SUC'                   => BankStatus::SUCCESS,
                $ok && in_array($res['txnStatus'] ?? '', ['REJ', 'ERR'], true) => BankStatus::FAILED,
                default                                                       => BankStatus::PENDING,
            },
            amount: isset($res['amount']) ? (string) $res['amount'] : null,
            bankTxnId: isset($res['txnID']) ? (string) $res['txnID'] : null,
            paymentMode: $res['paymentMode'] ?? null,
            raw: $res,
        );
    }

    public function refund(Payment $payment, string $amount, string $refundTxnNo): array
    {
        $res = $this->command('REFUND', $payment->txn_no, ['merchantTxnNo' => $refundTxnNo, 'amount' => $amount]);
        if (! in_array((string) ($res['responseCode'] ?? ''), ['R1000', '000', '0000'], true)) {
            throw new RuntimeException('ICICI refund rejected: '.($res['respDescription'] ?? json_encode($res)));
        }

        return $res;
    }

    public function parseNotification(array $data): array
    {
        return [
            'txn_no'          => isset($data['merchantTxnNo']) ? (string) $data['merchantTxnNo'] : null,
            'valid'           => SecureHash::verify($data, $this->cfg['secret_key']),
            'settlement_id'   => $data['settlementID'] ?? null,
            'settlement_date' => $data['settlementDate'] ?? null,
        ];
    }

    private function command(string $type, string $originalTxnNo, array $extra = []): array
    {
        return $this->post('/command?reqType=JSON', $extra + [
            'transactionType' => $type,
            'originalTxnNo'   => $originalTxnNo,
            'merchantTxnNo'   => $originalTxnNo,
        ]);
    }

    /** Auth fields, hashing, HTTP, logging and response-hash check in one place. */
    private function post(string $path, array $body): array
    {
        $base = self::URLS[$this->cfg['env'] ?? ''] ?? throw new InvalidArgumentException("{$this->cfg['name']}: env must be uat or prod");

        $body += ['merchantId' => $this->cfg['merchant_id'], 'aggregatorID' => $this->cfg['aggregator_id'] ?? null];
        if (isset($body['amount'])) {
            $body['amount'] = PaymentService::money($body['amount']);
        }
        $body = array_filter($body, fn ($v) => $v !== null && $v !== '');
        $body['secureHash'] = SecureHash::make($body, $this->cfg['secret_key']);

        $res = Http::timeout(30)->connectTimeout(10)->acceptJson()->post($base.$path, $body)->json();
        Log::info("ICICI [{$this->cfg['name']}] ".strtok($path, '?'), ['req' => $body, 'res' => $res]);

        if (! is_array($res)) {
            throw new RuntimeException("ICICI [{$this->cfg['name']}]: invalid response from bank");
        }
        if (isset($res['secureHash']) && ! SecureHash::verify($res, $this->cfg['secret_key'])) {
            Log::warning("ICICI [{$this->cfg['name']}] response hash mismatch", ['path' => $path]);
            if (! empty($this->cfg['strict_response_hash'])) {
                throw new RuntimeException("ICICI [{$this->cfg['name']}]: response secureHash mismatch");
            }
        }

        return $res;
    }
}
