<?php

namespace App\Payments\Drivers\Hdfc;

use App\Models\Payment;
use App\Payments\BankRedirect;
use App\Payments\BankStatus;
use App\Payments\Gateway;
use LogicException;

/**
 * HDFC Bank gateway: file, namespace and config entry are ready.
 * The bank calls are filled from HDFC's merchant integration kit (request format,
 * signing/encryption, UAT + live URLs, status API, refund API, test credentials).
 * Until then every method stops with a clear message; the rest of the app is already bank-independent.
 *
 * $this->cfg holds this account's entry from config/payments.php (+ 'name' = account key).
 * Put bank helpers (encryption, checksum) next to this file, like Icici/SecureHash.php.
 */
class HdfcGateway implements Gateway
{
    public function __construct(private array $cfg) {}

    public function start(Payment $payment, string $callbackUrl): BankRedirect
    {
        // Send $payment->txn_no, ->amount, ->customer_* and $callbackUrl to the bank, then return either
        //   new BankRedirect($bankPageUrl)                    when the bank gives a URL, or
        //   new BankRedirect($bankPostUrl, [...fields])       when the bank wants a POST form.
        throw $this->notReady();
    }

    public function status(Payment $payment): BankStatus
    {
        // Call the bank's status API for $payment->txn_no and map its answer to
        //   new BankStatus(BankStatus::SUCCESS|FAILED|PENDING, amount: ..., bankTxnId: ..., paymentMode: ..., raw: $res)
        throw $this->notReady();
    }

    public function refund(Payment $payment, string $amount, string $refundTxnNo): array
    {
        // Call the bank's refund API; return its response, throw if rejected.
        throw $this->notReady();
    }

    public function parseNotification(array $data): array
    {
        // From what the bank POSTs back: our txn number (decrypt first if the bank encrypts it)
        // and whether its checksum/signature is valid.
        //   return ['txn_no' => ..., 'valid' => ...];
        throw $this->notReady();
    }

    private function notReady(): LogicException
    {
        return new LogicException("HDFC gateway [{$this->cfg['name']}] is not implemented yet: fill ".static::class.' from the HDFC integration kit.');
    }
}
