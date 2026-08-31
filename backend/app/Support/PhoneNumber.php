<?php

/**
 * SPDX-FileCopyrightText: 2026 Jonathan Läpple and VisitorPortal contributors
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace App\Support;

class PhoneNumber
{
    /**
     * Normalize a phone number to standard international (+254 or provided country code) format.
     */
    public static function normalize(?string $phone, string $defaultCountryCode = '254'): ?string
    {
        if (blank($phone)) {
            return null;
        }

        $raw = trim($phone);
        $cleanDigits = preg_replace('/[^\d]/', '', $raw);

        if (empty($cleanDigits)) {
            return null;
        }

        // Already has leading '+'
        if (str_starts_with($raw, '+')) {
            return '+' . $cleanDigits;
        }

        // Handles 00 prefix for international (e.g., 00254... or 001...)
        if (str_starts_with($cleanDigits, '00')) {
            return '+' . substr($cleanDigits, 2);
        }

        // Kenya specific: starts with 07 or 01 and is 10 digits (e.g., 0712345678, 0112345678)
        if (preg_match('/^(0[17]\d{8})$/', $cleanDigits)) {
            return '+' . $defaultCountryCode . substr($cleanDigits, 1);
        }

        // Kenya specific: starts with 7 or 1 and is 9 digits (e.g., 712345678, 112345678)
        if (preg_match('/^([17]\d{8})$/', $cleanDigits)) {
            return '+' . $defaultCountryCode . $cleanDigits;
        }

        // Kenya specific: starts with 254 and is 12 digits (e.g., 254712345678)
        if (str_starts_with($cleanDigits, $defaultCountryCode) && strlen($cleanDigits) === 12) {
            return '+' . $cleanDigits;
        }

        // Default fallback with plus if it has enough digits for international
        if (strlen($cleanDigits) >= 9) {
            return '+' . $cleanDigits;
        }

        return $raw;
    }

    /**
     * Validate if the given phone number string is a valid local or international number.
     */
    public static function isValid(?string $phone): bool
    {
        if (blank($phone)) {
            return true;
        }

        $trimmed = trim($phone);

        // Disallow invalid characters (only +, digits, whitespace, hyphens, brackets, dots allowed)
        if (! preg_match('/^\+?[\d\s\-\(\)\.]{7,25}$/', $trimmed)) {
            return false;
        }

        $digits = preg_replace('/[^\d]/', '', $trimmed);
        $digitCount = strlen($digits);

        // International phone numbers under E.164 must be between 7 and 15 digits
        if ($digitCount < 7 || $digitCount > 15) {
            return false;
        }

        // Kenyan local format check: 07xx, 01xx (10 digits) or 7xx, 1xx (9 digits)
        if (preg_match('/^(0[17]\d{8}|[17]\d{8}|254[17]\d{8})$/', $digits)) {
            return true;
        }

        // International format starting with +
        if (str_starts_with($trimmed, '+') && $digitCount >= 7 && $digitCount <= 15) {
            return true;
        }

        // Standard digits length between 9 and 15
        return $digitCount >= 9 && $digitCount <= 15;
    }
}
