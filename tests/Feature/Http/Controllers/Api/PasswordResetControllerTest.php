<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Mail\PasswordResetCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Modules\User\Models\User;
use Tests\TestCase;

/** Verifica a recuperação de senha após a mudança da model. */
class PasswordResetControllerTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['email' => 'alfa@teste.com']);
    }

    /** Confere o envio do código para um usuário do módulo. */
    public function test_send_code(): void
    {
        Mail::fake();

        $response = $this->post(route('password.email'), ['email' => 'alfa@teste.com']);

        $response->assertRedirect();
        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'alfa@teste.com']);
        Mail::assertSent(PasswordResetCode::class, fn (PasswordResetCode $mail): bool => $mail->hasTo('alfa@teste.com'));
        $this->assertGuest();
    }

    /** Confere que um e-mail desconhecido não recebe código. */
    public function test_send_code_failed(): void
    {
        Mail::fake();

        $response = $this->post(route('password.email'), ['email' => 'beta@teste.com']);

        $response->assertRedirect();
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'beta@teste.com']);
        Mail::assertNothingSent();
        $this->assertGuest();
    }

    /** Confere a redefinição da senha e a revogação dos tokens. */
    public function test_reset(): void
    {
        $this->user->createToken('Alfa');
        DB::table('password_reset_tokens')->insert(['email' => 'alfa@teste.com', 'token' => Hash::make('123456'), 'created_at' => now()]);

        $response = $this->post(route('password.update'), ['email' => 'alfa@teste.com', 'code' => '123456', 'password' => 'omega123', 'password_confirmation' => 'omega123']);

        $response->assertRedirectToRoute('login');
        $this->assertTrue(Hash::check('omega123', $this->user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'alfa@teste.com']);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertGuest();
    }

    /** Confere que um código incorreto preserva a senha. */
    public function test_reset_failed(): void
    {
        $password = $this->user->password;
        DB::table('password_reset_tokens')->insert(['email' => 'alfa@teste.com', 'token' => Hash::make('123456'), 'created_at' => now()]);

        $response = $this->postJson(route('password.update'), ['email' => 'alfa@teste.com', 'code' => '654321', 'password' => 'omega123', 'password_confirmation' => 'omega123']);

        $response->assertUnprocessable();
        $response->assertInvalid(['code']);
        $this->assertSame($password, $this->user->fresh()->password);
        $this->assertGuest();
    }
}
