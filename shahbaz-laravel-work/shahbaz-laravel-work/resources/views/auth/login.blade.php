@extends('layouts.storefront')
@section('title', 'ورود به حساب | شهباز')
@section('page-type', 'account')
@section('content')
<section class="container section-space auth-page"><div class="auth-card"><span class="section-kicker">خوش برگشتی</span><h1>ورود به شهباز</h1><p>با شماره موبایل و رمز عبور یا کد یک‌بارمصرف وارد شو.</p>
<form class="profile-form" action="{{ route('auth.login.password') }}" method="POST">@csrf<div class="form-grid"><label>شماره موبایل<input type="tel" name="mobile" value="{{ old('mobile',session('otp_mobile')) }}" required dir="ltr" inputmode="tel" autocomplete="tel" placeholder="0912xxxxxxx"></label><label>رمز عبور<input type="password" name="password" required autocomplete="current-password"></label></div><label class="remember-field"><input type="checkbox" name="remember" value="1"> مرا به خاطر بسپار</label><button class="button button-dark button-full" type="submit">ورود با رمز عبور</button></form>
<div class="auth-separator"><span>یا ورود با پیامک</span></div>
<form action="{{ route('auth.login.otp.request') }}" method="POST" class="profile-form">@csrf<label>شماره موبایل<input type="tel" name="mobile" value="{{ old('mobile',session('otp_mobile')) }}" required dir="ltr" inputmode="tel" placeholder="0912xxxxxxx"></label><button class="button button-light button-full" type="submit">درخواست کد ورود</button></form>
@if(session('otp_notice'))<div class="account-notice" role="status"><span><x-icon name="check" /></span><div><strong>{{ session('otp_notice') }}</strong></div></div><form action="{{ route('auth.login.otp.verify') }}" method="POST" class="profile-form">@csrf<input type="hidden" name="mobile" value="{{ session('otp_mobile') }}"><label>کد تأیید<input type="text" name="code" required inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9۰-۹٠-٩]{6}" dir="ltr"></label><button class="button button-dark button-full" type="submit">تأیید و ورود</button></form>@endif
<p class="auth-switch">حساب نداری؟ <a href="{{ route('register') }}">ثبت‌نام کن</a></p></div></section>
@endsection
