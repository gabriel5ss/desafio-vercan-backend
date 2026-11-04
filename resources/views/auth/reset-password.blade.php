@extends('layouts.auth')

@section('title', 'Redefinir senha')

@section('content')
    <h1 class="h4 mb-3">Redefinir senha</h1>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input class="form-control" id="email" type="email" name="email" value="{{ old('email') }}" required>
        </div>

        <div class="mb-3">
            <label for="code" class="form-label">Código recebido por e-mail</label>
            <input class="form-control" id="code" type="text" name="code" inputmode="numeric" autocomplete="one-time-code" value="{{ old('code') }}" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Nova senha</label>
            <input class="form-control" id="password" type="password" name="password" autocomplete="new-password" required>
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirme a nova senha</label>
            <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
        </div>

        <div class="text-center d-grid">
            <button class="btn btn-primary" type="submit">Redefinir senha</button>
        </div>
    </form>
@endsection

@section('return-link')
    <div class="row mt-3">
        <div class="col-12 text-center">
            <p class="text-white-50">Back to <a href="{{ route('login') }}" class="text-white ms-1"><b>Log in</b></a></p>
        </div>
    </div>
@endsection
