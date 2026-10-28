<?php

namespace App\Listeners;

use App\Events\PaymentSucceeded;

/**
 * Runs exactly once when the bank confirms a payment (Laravel finds this file automatically).
 * Put your project logic here: mark the order/fee as paid, send a receipt, etc.
 */
class HandlePaymentSucceeded
{
    public function handle(PaymentSucceeded $event): void
    {
        $payment = $event->payment;   // txn_no, amount, order_ref, user_id, customer_email ...

        // Example:
        // \App\Models\Order::where('order_no', $payment->order_ref)->update(['status' => 'paid']);
    }
}
