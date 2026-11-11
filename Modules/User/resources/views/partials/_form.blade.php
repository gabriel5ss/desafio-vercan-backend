<fieldset @disabled($read_only)>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="name" class="form-label">Nome</label>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" maxlength="255" autocomplete="name" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" maxlength="255" autocomplete="email" required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        @unless ($read_only)
            <div class="col-md-6">
                <label for="password" class="form-label">Senha</label>
                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" minlength="8" autocomplete="new-password" @required(! $user->exists) aria-describedby="password-help">
                <div id="password-help" class="form-text">
                    {{ $user->exists ? 'Deixe em branco para manter a senha atual.' : 'Informe uma senha com pelo menos 8 caracteres.' }}
                </div>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label for="password_confirmation" class="form-label">Confirmar senha</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" minlength="8" autocomplete="new-password" @required(! $user->exists)>
            </div>
        @endunless
        @include('user::partials._observation')
    </div>
</fieldset>
