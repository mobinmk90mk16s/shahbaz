<?php

use App\Models\Role;
use App\Models\User;
use App\Support\PhoneNormalizer;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Command\Command;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('shahbaz:make-super-admin {mobile}', function (string $mobile) {
    $mobile = PhoneNormalizer::normalize($mobile);
    $user = User::where('mobile', $mobile)->first();
    if (!$user || !$user->mobile_verified_at || !$user->is_active) {
        $this->error('کاربر فعال و دارای شماره موبایل تأییدشده پیدا نشد. ابتدا حساب را بساز و شماره را تأیید کن.');
        return Command::FAILURE;
    }
    $role = Role::where('slug', 'super-admin')->firstOrFail();
    $role->users()->syncWithoutDetaching([$user->id]);
    $this->info('نقش مدیر کل به کاربر موردنظر اختصاص داده شد.');
    return Command::SUCCESS;
})->purpose('Assign the super-admin role to an existing verified account');
