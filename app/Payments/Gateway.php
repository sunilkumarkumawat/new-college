<?php

namespace App\Payments;

use App\Models\Payment;

/**
 * Every bank driver implements these 4 methods. Nothing else in the app knows bank details.
 * New bank = new class in app/Payments/Drivers/<Bank>/ + one entry in config/payments.php.
 * Driver constructor receives that account's config array (plus 'name' = account key).
 */
interface Gateway
{
    /** Create the payment at the bank and say where to send the customer. Throw if the bank refuses. */
    public function start(Payment $payment, string $callbackUrl): BankRedirect;

    /** Ask the bank for the real status of this payment. */
    public function status(Payment $payment): BankStatus;

    /** Refund (full or partial). Return the bank's response; throw if the bank rejects it. */
    public function refund(Payment $payment, string $amount, string $refundTxnNo): array;

    /**
     * Read anything the bank POSTs back (return URL or webhook).
     *
     * @return array{txn_no: ?string, valid: bool, settlement_id?: ?string, settlement_date?: ?string}
     */
    public function parseNotification(array $data): array;
}
