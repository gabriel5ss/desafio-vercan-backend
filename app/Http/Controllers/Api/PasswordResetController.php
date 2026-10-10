<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordResetRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use App\Services\PasswordResetService;
use Illuminate\Http\JsonResponse;

class PasswordResetController extends Controller
{
    public function sendCode(PasswordResetRequest $request, PasswordResetService $service): JsonResponse
    {
        $email = (string) $request->validated('email');
        $user = User::query()->where('email', $email)->first();

        if ($user) {
            $service->sendCode($user);
        }

        return response()->json([
            'message' => 'Se o e-mail estiver cadastrado, você receberá um código para redefinir sua senha.',
        ]);
    }

    public function reset(ResetPasswordRequest $request, PasswordResetService $service): JsonResponse
    {
        $service->resetPassword((string) $request->validated('email'), (string) $request->validated('code'), (string) $request->validated('password'));

        return response()->json(['message' => 'Senha redefinida com sucesso.']);
    }
}
