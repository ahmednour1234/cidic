<?php

namespace App\Support\CvPanel;

use Illuminate\Support\Facades\Config;

/**
 * Deterministic HMAC used to look up values that are encrypted at rest.
 *
 * Encrypted casts produce a different ciphertext every save, so an equality
 * search is impossible against the encrypted column. Each such column is
 * paired with a *_hash column carrying this digest instead.
 */
final class SearchableHash
{
    public static function make(?string $value): ?string
    {
        $value = is_string($value) ? trim($value) : null;

        if ($value === null || $value === '') {
            return null;
        }

        return hash_hmac('sha256', mb_strtolower($value), self::key());
    }

    private static function key(): string
    {
        $key = (string) Config::get('app.key');

        // The app key is base64-prefixed in a standard Laravel install.
        if (str_starts_with($key, 'base64:')) {
            $key = (string) base64_decode(substr($key, 7), true);
        }

        return $key;
    }
}
