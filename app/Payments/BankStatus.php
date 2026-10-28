<?php

namespace App\Payments;

/** A bank's status answer, in the same shape for every bank. */
final class BankStatus
{
    public const SUCCESS = 'SUCCESS';
    public const FAILED = 'FAILED';
    public const PENDING = 'PENDING';   // not paid yet / not found / bank still processing

    public function __construct(
        public readonly string $status,
        public readonly ?string $amount = null,
        public readonly ?string $bankTxnId = null,
        public readonly ?string $paymentMode = null,
        public readonly array $raw = [],
    ) {}
}
