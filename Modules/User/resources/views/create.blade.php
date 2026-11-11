@extends('layouts.app')

@section('title', $user->exists ? 'Editar usuário' : 'Novo usuário')

@section('content')
    <form action="{{ $user->exists ? route('user.update', $user) : route('user.store') }}" method="POST">
        @csrf
        @if ($user->exists)
            @method('PUT')
        @endif

        <div class="card mb-3">
            <div class="card-body">
                <h4 class="header-title mb-3">{{ $user->exists ? 'Editar usuário' : 'Novo usuário' }}</h4>
                @include('user::partials._form')
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-3">
            <a href="{{ route('user.index') }}" class="btn btn-secondary">Voltar</a>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
    </form>
@endsection
