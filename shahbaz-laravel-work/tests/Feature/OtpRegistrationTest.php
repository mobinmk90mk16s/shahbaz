<?php

namespace Tests\Feature;

use App\Models\OtpChallenge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OtpRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_otp_is_hashed_and_one_time_use(): void
    {
        config(['shahbaz.sms.endpoint' => 'https://sms.example.test/send', 'shahbaz.sms.token' => 'test-token']);
        $sentMessage = null;
        Http::fake(function ($request) use (&$sentMessage) {
            $sentMessage = (string) $request['message'];
            return Http::response(['accepted' => true], 200);
        });

        $this->post(route('auth.register.otp.request'), [
            'name' => 'کاربر تازه',
            'mobile' => '09123456789',
            'email' => 'user@example.test',
            'password' => 'secure-pass-123',
            'password_confirmation' => 'secure-pass-123',
        ])->assertRedirect(route('register'));

        $this->assertNotNull($sentMessage);
        preg_match('/(\d{6})/u', $sentMessage, $matches);
        $this->assertArrayHasKey(1, $matches);
        $code = $matches[1];

        $challenge = OtpChallenge::where('mobile', '09123456789')->latest('id')->firstOrFail();
        $this->assertNotSame($code, $challenge->code_hash);
        $this->assertTrue(Hash::check($code, $challenge->code_hash));

        $this->post(route('auth.register.otp.verify'), ['mobile' => '09123456789', 'code' => $code])
            ->assertRedirect(route('account.index'));
        $this->assertAuthenticated();
        $user = User::where('mobile', '09123456789')->firstOrFail();
        $this->assertNotNull($user->mobile_verified_at);
        $this->assertTrue(Hash::check('secure-pass-123', $user->password));

        $this->post(route('logout'));
        $this->post(route('auth.register.otp.verify'), ['mobile' => '09123456789', 'code' => $code])
            ->assertSessionHasErrors('code');
    }
}
