<?php

namespace App\Services\Sms;

use App\Contracts\SmsGateway;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class HttpSmsGateway implements SmsGateway
{
    public function send(string $mobile, string $message): void
    {
        if (config('shahbaz.sms.driver') !== 'http') {
            throw new RuntimeException('درایور پیامک انتخاب‌شده پشتیبانی نمی‌شود.');
        }
        $endpoint = (string) config('shahbaz.sms.endpoint');
        $token = (string) config('shahbaz.sms.token');
        if ($endpoint === '' || $token === '') {
            throw new RuntimeException('سرویس پیامک تنظیم نشده است. مقدارهای SMS_HTTP_URL و SMS_HTTP_TOKEN را در فایل .env تنظیم کن.');
        }

        $response = Http::acceptJson()->withToken($token)->timeout(12)->post($endpoint, [
            'to' => $mobile,
            'message' => $message,
            'sender_id' => config('shahbaz.sms.sender_id'),
        ]);

        if (!$response->successful()) {
            throw new RuntimeException('ارسال پیامک توسط سرویس‌دهنده ناموفق بود.');
        }
    }
}
