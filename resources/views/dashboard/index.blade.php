@extends('layouts.app')

@section('title', 'Painel')

@section('content')
    <section class="rounded-xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        <h1 class="mt-2 text-3xl font-semibold">Bem-vindo, {{ auth()->user()->name }}</h1>
        <p class="mt-3 text-slate-600">Gerencie os fornecedores e consulte localidades cadastradas.</p>
        <a href="{{ route('suppliers.index') }}" class="mt-6 inline-flex rounded-lg bg-slate-900 px-4 py-2.5 font-medium text-white hover:bg-slate-700">Ver fornecedores</a>
    </section>
@endsection
