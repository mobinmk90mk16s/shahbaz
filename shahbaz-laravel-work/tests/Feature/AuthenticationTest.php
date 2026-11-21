<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_log_in_with_mobile_and_password_and_session_is_regenerated(): void
    {
        $user = new User();
        $user->forceFill(['name' => 'کاربر آزمایشی', 'mobile' => '09123456789', 'email' => null, 'mobile_verified_at' => now(), 'password' => Hash::make('secret-pass-123')])->save();
        $oldSessionId = $this->app['session']->getId();
        $this->post(route('auth.login.password'), ['mobile' => '۰۹۱۲۳۴۵۶۷۸۹', 'password' => 'secret-pass-123'])
            ->assertRedirect(route('account.index'));
        $this->assertAuthenticated();
        $this->assertNotSame($oldSessionId, $this->app['session']->getId());
    }

    public function test_inactive_account_cannot_log_in(): void
    {
        $user = new User();
        $user->forceFill(['name' => 'کاربر غیرفعال', 'mobile' => '09123456789', 'mobile_verified_at' => now(), 'password' => Hash::make('secret-pass-123'), 'is_active' => false])->save();
        $this->post(route('auth.login.password'), ['mobile' => '09123456789', 'password' => 'secret-pass-123'])
            ->assertSessionHasErrors('mobile');
        $this->assertGuest();
    }

    public function test_password_login_rejects_a_mobile_number_that_has_not_been_verified(): void
    {
        $user = new User();
        $user->forceFill(['name' => 'کاربر تأییدنشده', 'mobile' => '09123456789', 'password' => Hash::make('secret-pass-123')])->save();

        $this->post(route('auth.login.password'), ['mobile' => '09123456789', 'password' => 'secret-pass-123'])
            ->assertSessionHasErrors('mobile');
        $this->assertGuest();
    }

    public function test_logout_invalidates_session_and_regenerates_csrf_token(): void
    {
        $user = User::create(['name' => 'کاربر خروج', 'mobile' => '09123456789', 'password' => Hash::make('secret-pass-123')]);
        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('storefront.home'));
        $this->assertGuest();
    }
}
