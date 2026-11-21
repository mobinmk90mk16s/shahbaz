<?php

namespace App\Http\Controllers;

use App\Http\Requests\MobileRequest;
use App\Http\Requests\PasswordLoginRequest;
use App\Http\Requests\RegisterOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Models\User;
use App\Models\OtpChallenge;
use App\Services\Auth\OtpService;
use App\Services\Cart\CartService;
use App\Services\Wishlist\WishlistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private readonly OtpService $otps, private readonly CartService $carts, private readonly WishlistService $wishlists) {}

    public function login(): View { return view('auth.login'); }
    public function register(): View { return view('auth.register'); }

    public function passwordLogin(PasswordLoginRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $key = 'login-password:'.$request->ip().':'.$data['mobile'];
        if (RateLimiter::tooManyAttempts($key, 5)) return back()->withErrors(['mobile'=>'تلاش‌های ورود بیش از حد مجاز است. کمی بعد دوباره امتحان کن.'])->onlyInput('mobile');
        if (!Auth::attempt(['mobile'=>$data['mobile'], 'password'=>$data['password'], 'is_active'=>true], $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            return back()->withErrors(['mobile'=>'شماره موبایل یا رمز عبور صحیح نیست.'])->onlyInput('mobile');
        }
        $user = Auth::user();
        if (!$user || !$user->mobile_verified_at) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            RateLimiter::hit($key, 60);
            return back()->withErrors(['mobile' => 'برای ورود با رمز عبور، ابتدا شماره موبایل باید تأیید شده باشد. از ورود پیامکی استفاده کن.'])->onlyInput('mobile');
        }
        RateLimiter::clear($key);
        $request->session()->regenerate();
        $this->mergeGuestData();
        return $this->safeIntendedRedirect();
    }

    public function requestLoginOtp(MobileRequest $request): RedirectResponse
    {
        $mobile = $request->validated('mobile');
        $user = User::where('mobile',$mobile)->where('is_active',true)->whereNotNull('mobile_verified_at')->first();
        if ($user) {
            try { $this->otps->issue($mobile, 'login'); }
            catch (\Illuminate\Validation\ValidationException $exception) { throw $exception; }
            catch (\Throwable $exception) { report($exception); return back()->withErrors(['mobile'=>'ارسال کد انجام نشد؛ تنظیمات سرویس پیامک را بررسی کن.'])->withInput(); }
        }
        return back()->with('otp_notice', 'در صورت وجود حساب فعال برای این شماره، کد تایید ارسال شده است.')->with('otp_mobile', $mobile);
    }

    public function verifyLoginOtp(VerifyOtpRequest $request): RedirectResponse
    {
        $data = $request->validated();
        try { $this->otps->consume($data['mobile'], 'login', $data['code']); }
        catch (\Throwable $exception) { if ($exception instanceof \Illuminate\Validation\ValidationException) throw $exception; report($exception); return back()->withErrors(['code'=>'تایید کد انجام نشد.']); }
        $user = User::where('mobile',$data['mobile'])->whereNotNull('mobile_verified_at')->first();
        if (!$user || !$user->is_active) return back()->withErrors(['mobile'=>'حساب فعالی برای این شماره پیدا نشد.']);
        Auth::login($user);
        $request->session()->regenerate();
        $this->mergeGuestData();
        return $this->safeIntendedRedirect();
    }

    public function requestRegisterOtp(RegisterOtpRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $metadata = ['name'=>$data['name'], 'email'=>$data['email'] ?? null, 'password_hash'=>Hash::make($data['password'])];
        try { $this->otps->issue($data['mobile'], 'register', $metadata); }
        catch (\Illuminate\Validation\ValidationException $exception) { throw $exception; }
        catch (\Throwable $exception) { report($exception); return back()->withErrors(['mobile'=>'ارسال کد انجام نشد؛ تنظیمات سرویس پیامک را بررسی کن.'])->withInput($request->except('password','password_confirmation')); }
        return redirect()->route('register')->with('otp_notice','کد تایید برای شماره واردشده ارسال شد.')->with('otp_mobile',$data['mobile']);
    }

    public function resendRegisterOtp(MobileRequest $request): RedirectResponse
    {
        $mobile = $request->validated('mobile');
        $challenge = OtpChallenge::query()
            ->where('mobile', $mobile)
            ->where('purpose', 'register')
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (!$challenge || empty($challenge->metadata['name']) || empty($challenge->metadata['password_hash'])) {
            return redirect()->route('register')->withErrors(['mobile' => 'فرایند ثبت‌نام منقضی شده است؛ فرم ثبت‌نام را دوباره تکمیل کن.']);
        }

        try {
            $this->otps->issue($mobile, 'register', $challenge->metadata);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            return redirect()->route('register')->withErrors(['mobile' => 'ارسال مجدد کد انجام نشد؛ تنظیمات سرویس پیامک را بررسی کن.']);
        }

        return redirect()->route('register')->with('otp_notice', 'کد تأیید جدید ارسال شد.')->with('otp_mobile', $mobile);
    }

    public function verifyRegisterOtp(VerifyOtpRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $challenge = $this->otps->consume($data['mobile'], 'register', $data['code']);
        $meta = $challenge->metadata ?? [];
        abort_unless(isset($meta['name'], $meta['password_hash']), 422, 'اطلاعات ثبت‌نام معتبر نیست.');
        if (User::where('mobile',$data['mobile'])->exists()) return redirect()->route('login')->withErrors(['mobile'=>'این شماره قبلاً ثبت شده است.']);
        if (!empty($meta['email']) && User::where('email',$meta['email'])->exists()) {
            return redirect()->route('register')->withErrors(['email'=>'این ایمیل قبلاً استفاده شده است.']);
        }
        $user = DB::transaction(function () use ($meta, $data): User {
            $user = new User();
            // These fields are server-controlled and intentionally excluded from the model's fillable list.
            $user->forceFill([
                'name' => $meta['name'],
                'mobile' => $data['mobile'],
                'email' => $meta['email'] ?? null,
                'password' => $meta['password_hash'],
                'mobile_verified_at' => now(),
                'is_active' => true,
            ]);
            $user->save();
            return $user;
        });
        Auth::login($user);
        $request->session()->regenerate();
        $this->mergeGuestData();
        return redirect()->route('account.index')->with('success','حساب کاربری با موفقیت ساخته شد.');
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('storefront.home')->with('success','از حساب کاربری خارج شدی.');
    }

    private function mergeGuestData(): void
    {
        DB::transaction(function (): void {
            $this->carts->mergeGuestIntoUser();
            $this->wishlists->mergeGuestIntoUser();
        });
    }

    private function safeIntendedRedirect(): RedirectResponse
    {
        $target = session()->pull('url.intended');
        if (!is_string($target) || $target === '' || str_contains($target, chr(13)) || str_contains($target, chr(10))) {
            return redirect()->route('account.index');
        }

        $parts = parse_url($target);
        if (!is_array($parts) || isset($parts['user']) || isset($parts['pass'])) {
            return redirect()->route('account.index');
        }

        // Relative paths are accepted only when they cannot be interpreted as network paths.
        if (!isset($parts['host']) && str_starts_with($target, '/') && !str_starts_with($target, '//') && !str_contains($target, '\\')) {
            return redirect($target);
        }

        // Absolute intended URLs must remain on the current host and use HTTP(S).
        if (isset($parts['host'])
            && strcasecmp($parts['host'], request()->getHost()) === 0
            && in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)) {
            return redirect($target);
        }

        return redirect()->route('account.index');
    }
}
