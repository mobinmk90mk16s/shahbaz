<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

class PhoneNormalizer
{
    public static function normalize(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', strtr(trim($phone), [
            '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
            '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
        ]));
        if (str_starts_with($digits, '0098')) $digits = '0'.substr($digits, 4);
        elseif (str_starts_with($digits, '98') && strlen($digits) === 12) $digits = '0'.substr($digits, 2);
        elseif (strlen($digits) === 10 && str_starts_with($digits, '9')) $digits = '0'.$digits;
        if (!preg_match('/^09\d{9}$/', $digits)) {
            throw ValidationException::withMessages(['mobile' => 'شماره موبایل معتبر وارد کن.']);
        }
        return $digits;
    }
}
