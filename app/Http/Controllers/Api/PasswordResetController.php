<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordResetRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Modules\User\Models\User;

final class PasswordResetController extends Controller
{
    public function sendCode(PasswordResetRequest $request, PasswordResetService $service): RedirectResponse
    {
        $email = (string) $request->validated('email');
        $user = User::query()->where('email', $email)->first();

        if ($user) {
            $service->sendCode($user);
        }

        return back()->with('status', 'Se o e-mail estiver cadastrado, você receberá um código para redefinir sua senha.');
    }

    public function reset(ResetPasswordRequest $request, PasswordResetService $service): RedirectResponse
    {
        $service->resetPassword((string) $request->validated('email'), (string) $request->validated('code'), (string) $request->validated('password'));

        return redirect()->route('login')->with('status', 'Senha redefinida com sucesso.');
    }
}
