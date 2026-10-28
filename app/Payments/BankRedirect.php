<?php

namespace App\Payments;

/** Where to send the customer: a plain URL (GET), or a URL + form fields when the bank wants a POST form. */
final class BankRedirect
{
    public function __construct(
        public readonly string $url,
        public readonly array $fields = [],
    ) {}
}
