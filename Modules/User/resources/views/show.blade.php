@extends('layouts.app')

@section('title', 'Visualizar usuário')

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <h4 class="header-title mb-3">Dados do usuário</h4>
            @include('user::partials._form', ['read_only' => true])
        </div>
    </div>

    @include('audit::partials._history', [
        'root_type' => $user->getMorphClass(),
        'root_id' => $user->id,
    ])

    <div class="d-flex justify-content-end gap-2 mb-3">
        <a href="{{ route('user.index') }}" class="btn btn-secondary">Voltar</a>
        <a href="{{ route('user.edit', $user) }}" class="btn btn-primary">Editar</a>
    </div>
@endsection
