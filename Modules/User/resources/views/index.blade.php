@extends('layouts.app')

@section('title', 'Usuários')

@section('page-button-action')
    <a href="{{ route('user.create') }}" class="btn btn-primary">Novo usuário</a>
@endsection

@section('content')
    @if (session('status'))
        <div class="alert alert-success" role="status">{{ session('status') }}</div>
    @endif
    <div class="card mb-3">
        <div class="card-body">
            <h4 class="header-title mb-3">Usuários</h4>
            @include('user::partials._table')
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
