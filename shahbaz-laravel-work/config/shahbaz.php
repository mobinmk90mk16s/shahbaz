<?php

return [
    'otp' => [
        'ttl_minutes' => (int) env('OTP_TTL_MINUTES', 5),
        'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),
    ],
    'sms' => [
        'driver' => env('SMS_DRIVER', 'http'),
        'endpoint' => env('SMS_HTTP_URL'),
        'token' => env('SMS_HTTP_TOKEN'),
        'sender_id' => env('SMS_SENDER_ID'),
    ],
];
