<?php

namespace Tests\Feature\Services;

use App\Mail\PasswordResetCode;
use App\Models\User;
use App\Services\PasswordResetService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class PasswordResetServiceTest extends TestCase
{
    private PasswordResetService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->freezeSecond();
        $this->service = new PasswordResetService;
    }

    public function test_sends_six_digit_code_and_stores_its_hash(): void
    {
        $user = User::factory()->create();
        Mail::fake();

        $this->service->sendCode($user);

        $record = DB::table('password_reset_tokens')->where('email', $user->email)->first();
        $this->assertNotNull($record);
        $this->assertSame(now()->toDateTimeString(), $record->created_at);
        Mail::assertSent(PasswordResetCode::class, fn (PasswordResetCode $mail): bool => $mail->hasTo($user->email)
            && preg_match('/^[0-9]{6}\z/', $mail->code) === 1
            && $record->token !== $mail->code
            && Hash::check($mail->code, $record->token));
    }

    public function test_replaces_existing_code_and_refreshes_expiration_on_resend(): void
    {
        $user = User::factory()->create();
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => now()->subMinutes(30),
        ]);
        Mail::fake();

        $this->service->sendCode($user);

        $this->assertDatabaseCount('password_reset_tokens', 1);
        $record = DB::table('password_reset_tokens')->where('email', $user->email)->first();
        $this->assertNotNull($record);
        $this->assertSame(now()->toDateTimeString(), $record->created_at);
        Mail::assertSent(PasswordResetCode::class, fn (PasswordResetCode $mail): bool => $mail->hasTo($user->email)
            && Hash::check($mail->code, $record->token));
    }

    public function test_resets_password_and_removes_code_and_access_tokens_with_valid_code(): void
    {
        $user = User::factory()->create();
        $user->createToken('first-device');
        $user->createToken('second-device');
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => now()->subMinutes(29)->subSeconds(59),
        ]);

        $this->service->resetPassword($user->email, '123456', '654321');

        $this->assertTrue(Hash::check('654321', $user->fresh()->password));
        $this->assertSame(0, $user->tokens()->count());
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }

    #[TestWith(['654321', 0], 'incorrect code')]
    #[TestWith(['123456', 30], 'code expires at exactly thirty minutes')]
    public function test_rejects_invalid_code_without_changing_password_or_tokens(string $code, int $minutesAgo): void
    {
        $user = User::factory()->create();
        $originalPassword = $user->password;
        $accessToken = $user->createToken('device')->accessToken;
        $record = [
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => now()->subMinutes($minutesAgo)->toDateTimeString(),
        ];
        DB::table('password_reset_tokens')->insert($record);

        try {
            $this->service->resetPassword($user->email, $code, '654321');
            $this->fail('Expected the reset code to be rejected.');
        } catch (ValidationException $exception) {
            $this->assertSame([
                'code' => ['O código informado é inválido ou expirou.'],
            ], $exception->errors());
        }

        $this->assertSame($originalPassword, $user->fresh()->password);
        $this->assertModelExists($accessToken);
        $this->assertDatabaseHas('password_reset_tokens', $record);
    }
}
