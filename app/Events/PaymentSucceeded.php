<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired exactly once when a payment is confirmed by the bank.
 * Handled in app/Listeners/HandlePaymentSucceeded.php.
 */
class PaymentSucceeded
{
    use Dispatchable, SerializesModels;

    public function __construct(public Payment $payment) {}
}
