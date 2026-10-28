<?php

namespace App\Payments\Drivers\Icici;

/**
 * ICICI PG secureHash (HMAC-SHA256), as per the kit's "Secure Hash Functions Reference":
 *   1. drop secureHash, sort TOP-LEVEL keys (byte order)
 *   2. concat values: nested arrays -> JSON, bool -> true/false, null/'' -> skipped
 *   3. hash_hmac sha256 with the merchant secret key -> lowercase hex
 * Verified against the hash samples in the kit.
 */
class SecureHash
{
    public static function make(array $data, string $key): string
    {
        unset($data['secureHash']);
        $keys = array_keys($data);
        sort($keys, SORT_STRING);

        $text = '';
        foreach ($keys as $k) {
            $v = $data[$k];
            $text .= match (true) {
                is_array($v) => json_encode($v, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                is_bool($v)  => $v ? 'true' : 'false',
                default      => (string) $v,
            };
        }

        return hash_hmac('sha256', $text, $key);
    }

    public static function verify(array $data, string $key): bool
    {
        return is_string($data['secureHash'] ?? null)
            && hash_equals(self::make($data, $key), strtolower($data['secureHash']));
    }
}
