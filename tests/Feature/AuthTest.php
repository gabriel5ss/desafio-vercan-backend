<?php

namespace Tests\Feature;

use App\Mail\PasswordResetCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_receive_a_bearer_token(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonMissingPath('data.user.password');
        $this->assertDatabaseHas('personal_access_tokens', ['tokenable_id' => $user->id]);
    }

    public function test_login_requires_valid_credentials(): void
    {
        $this->postJson('/api/login', [])->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);

        $this->postJson('/api/login', ['email' => 'nobody@example.com', 'password' => 'wrong'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_authenticated_user_can_read_profile_and_revoke_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonMissingPath('data.password');

        $this->withToken($token)->postJson('/api/logout')->assertOk();
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id]);
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/user')->assertUnauthorized();
    }

    public function test_profile_and_logout_require_authentication(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
        $this->postJson('/api/logout')->assertUnauthorized();
    }

    public function test_password_reset_request_sends_a_code_and_stores_only_its_hash(): void
    {
        Mail::fake();
        $user = User::factory()->create();

        $this->postJson('/api/forgot-password', ['email' => 'invalid'])->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        $this->postJson('/api/forgot-password', ['email' => $user->email])->assertOk()
            ->assertJsonPath('message', 'Se o e-mail estiver cadastrado, você receberá um código para redefinir sua senha.');

        $code = null;
        Mail::assertSent(PasswordResetCode::class, function (PasswordResetCode $mail) use (&$code, $user): bool {
            $code = $mail->code;

            return $mail->hasTo($user->email);
        });

        $storedHash = DB::table('password_reset_tokens')->where('email', $user->email)->value('token');
        $this->assertIsString($code);
        $this->assertNotSame($code, $storedHash);
        $this->assertTrue(Hash::check($code, $storedHash));

        $emailContent = (new PasswordResetCode($code))->render();
        $this->assertStringContainsString('Desafio', $emailContent);
        $this->assertStringContainsString('222d32', $emailContent);
        $this->assertStringContainsString($code, $emailContent);
    }

    public function test_password_reset_code_updates_password_and_revokes_existing_bearer_tokens(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $plainToken = $user->createToken('existing-session')->plainTextToken;
        $this->postJson('/api/forgot-password', ['email' => $user->email])->assertOk();

        $code = null;
        Mail::assertSent(PasswordResetCode::class, function (PasswordResetCode $mail) use (&$code): bool {
            $code = $mail->code;

            return true;
        });

        $wrongCode = $code === '999999' ? '999998' : '999999';

        $this->postJson('/api/reset-password', [
            'email' => $user->email,
            'code' => $wrongCode,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertUnprocessable()->assertJsonValidationErrors(['code']);

        $this->postJson('/api/reset-password', [
            'email' => $user->email,
            'code' => $code,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertOk()->assertJsonPath('message', 'Senha redefinida com sucesso.');

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->withToken($plainToken)->getJson('/api/user')->assertUnauthorized();
    }

    public function test_password_reset_code_expires_after_thirty_minutes(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $this->postJson('/api/forgot-password', ['email' => $user->email])->assertOk();

        $code = null;
        Mail::assertSent(PasswordResetCode::class, function (PasswordResetCode $mail) use (&$code): bool {
            $code = $mail->code;

            return true;
        });

        $this->travel(30)->minutes();
        $this->postJson('/api/reset-password', [
            'email' => $user->email,
            'code' => $code,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertUnprocessable()->assertJsonValidationErrors(['code']);

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_unknown_email_receives_the_same_password_reset_response_without_sending_mail(): void
    {
        Mail::fake();

        $this->postJson('/api/forgot-password', ['email' => 'unknown@example.com'])->assertOk()
            ->assertJsonPath('message', 'Se o e-mail estiver cadastrado, você receberá um código para redefinir sua senha.');

        Mail::assertNothingOutgoing();
    }
}
