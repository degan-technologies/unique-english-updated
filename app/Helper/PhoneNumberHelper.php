<?php

namespace App\Helper;

class PhoneNumberHelper
{
    /**
     * Format phone number to international format (e.g., +251912345678).
     *
     * @param string $phone Phone number
     * @return string Formatted phone number
     */
    public static function formatPhoneNumber($phone)
    {
        if (!preg_match('/^\+/', $phone)) {
            $phone = '+251' . ltrim($phone, '0');  // Assuming Ethiopia as an example
        }
        return $phone;
    }
}
