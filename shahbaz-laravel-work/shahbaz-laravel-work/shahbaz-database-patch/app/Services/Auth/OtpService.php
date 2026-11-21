<?php

namespace App\Services\Auth;

use App\Contracts\SmsGateway;
use App\Models\OtpChallenge;
use App\Support\PhoneNormalizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class OtpService
{
    public function __construct(private readonly SmsGateway $sms) {}

    public function issue(string $mobile, string $purpose, array $metadata = []): OtpChallenge
    {
        $mobile = PhoneNormalizer::normalize($mobile);
        $rateKey = 'shahbaz-otp-send:'.$mobile;
        if (RateLimiter::tooManyAttempts($rateKey, 3)) {
            throw ValidationException::withMessages(['mobile' => 'برای این شماره بیش از حد کد درخواست شده است. کمی بعد دوباره تلاش کن.']);
        }
        RateLimiter::hit($rateKey, 600);
        $code = (string) random_int(100000, 999999);

        $challenge = DB::transaction(function () use ($mobile, $purpose, $metadata, $code) {
            OtpChallenge::query()->where('mobile', $mobile)->where('purpose', $purpose)->whereNull('consumed_at')->update(['expires_at' => now()]);
            return OtpChallenge::create([
                'mobile' => $mobile,
                'purpose' => $purpose,
                'code_hash' => Hash::make($code),
                'expires_at' => now()->addMinutes((int) config('shahbaz.otp.ttl_minutes', 5)),
                'attempts' => 0,
                'max_attempts' => (int) config('shahbaz.otp.max_attempts', 5),
                'last_sent_at' => now(),
                'metadata' => $metadata,
            ]);
        });

        try {
            $this->sms->send($mobile, "کد تایید شهباز: {$code}\nاعتبار: ".config('shahbaz.otp.ttl_minutes', 5).' دقیقه');
        } catch (\Throwable $exception) {
            $challenge->delete();
            throw $exception;
        }

        return $challenge;
    }

    public function consume(string $mobile, string $purpose, string $code): OtpChallenge
    {
        $mobile = PhoneNormalizer::normalize($mobile);
        [$challenge, $error] = DB::transaction(function () use ($mobile, $purpose, $code) {
            $challenge = OtpChallenge::query()->where('mobile', $mobile)->where('purpose', $purpose)
                ->whereNull('consumed_at')->where('expires_at', '>', now())
                ->whereColumn('attempts', '<', 'max_attempts')->latest('id')->lockForUpdate()->first();

            if (!$challenge) return [null, 'کد تایید منقضی شده یا وجود ندارد.'];
            if (!Hash::check($code, $challenge->code_hash)) {
                $challenge->increment('attempts');
                return [null, 'کد تایید اشتباه است.'];
            }
            $challenge->forceFill(['consumed_at' => now()])->save();
            return [$challenge, null];
        });

        if ($error) throw ValidationException::withMessages(['code' => $error]);
        return $challenge;
    }
}
