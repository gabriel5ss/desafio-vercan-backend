<?php

namespace App\Services;

use App\Mail\PasswordResetCode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class PasswordResetService
{
    public function sendCode(User $user): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => Hash::make($code), 'created_at' => now()],
        );

        Mail::to($user->email)->send(new PasswordResetCode($code));
    }

    public function resetPassword(string $email, string $code, string $password): void
    {
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (! $record || Carbon::parse($record->created_at)->addMinutes(30)->lessThanOrEqualTo(now()) || ! Hash::check($code, $record->token)) {
            throw ValidationException::withMessages([
                'code' => ['O código informado é inválido ou expirou.'],
            ]);
        }

        DB::transaction(function () use ($email, $password): void {
            $user = User::query()->where('email', $email)->firstOrFail();
            $user->password = $password;
            $user->save();
            $user->tokens()->delete();

            DB::table('password_reset_tokens')->where('email', $email)->delete();
        });
    }
}
