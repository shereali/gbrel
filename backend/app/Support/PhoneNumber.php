<?php

namespace App\Support;

class PhoneNumber
{
    public const PATTERN = '/^(?:\+8801[3-9]\d{8}|\+(?!880)[1-9]\d{7,14})$/';

    /**
     * Turns 01712345678, 8801712345678, Bangla digits or 00-prefixed numbers into +8801712345678 / +<country>.
     */
    public static function normalize(?string $raw): string
    {
        $phone = trim((string) $raw);
        // Meta CSV/Excel exports prefix phone numbers with "p:" (e.g. "p:+8801712345678").
        $phone = preg_replace('/^p:\s*/i', '', $phone) ?? $phone;

        $phone = strtr($phone, array_combine(
            preg_split('//u', '০১২৩৪৫৬৭৮৯', -1, PREG_SPLIT_NO_EMPTY), array_map('strval', range(0, 9))
        ));
        $phone = preg_replace('/[\s().-]/u', '', $phone) ?? '';

        // Excel numeric cells often strip the leading 0 from Bangladesh mobile numbers (1712345678 -> 01712345678).
        if (preg_match('/^1[3-9]\d{8}$/', $phone)) {
            return '+880'.$phone;
        }
        if (preg_match('/^01[3-9]\d{8}$/', $phone)) {
            return '+88'.$phone;
        }
        if (preg_match('/^8801[3-9]\d{8}$/', $phone)) {
            return '+'.$phone;
        }
        if (str_starts_with($phone, '00')) {
            return '+'.substr($phone, 2);
        }

        return $phone;
    }

    public static function isValid(?string $phone): bool
    {
        return (bool) preg_match(self::PATTERN, (string) $phone);
    }
}
