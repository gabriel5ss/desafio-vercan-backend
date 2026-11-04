@extends('layouts.app')

@section('title', 'Meu perfil')

@section('content')
    <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h1 class="text-2xl font-semibold">Meu perfil</h1>
        <dl class="mt-5 grid gap-4 sm:grid-cols-2">
            <div><dt class="text-sm text-slate-500">Nome</dt><dd class="font-medium">{{ $user->name }}</dd></div>
            <div><dt class="text-sm text-slate-500">E-mail</dt><dd class="font-medium">{{ $user->email }}</dd></div>
        </dl>
    </section>
@endsection
