@extends('layouts.app')

@props([
    'readOnly' => false
])

@section('title', 'Fornecedores')

@section('content')
    <form
        action="{{ isset($supplier)
            ? route('suppliers.update', $supplier)
            : route('suppliers.store') }}"
        method="POST"
        id="supplier-form"
        novalidate
    >
        @csrf

        @isset($supplier)
            @method('PUT')
        @endisset

        <fieldset @disabled($readOnly)>
            <x-form.card title="Dados do Fornecedor" id="supplier-identification" :collapsed="false">
                @include('supplier::partials.form._identification')
            </x-form.card>

            <x-form.card title="Contatos" id="supplier-contacts" :collapsed="false">
                @include('supplier::partials.form._contacts')
            </x-form.card>

            <x-form.card title="Contatos Adicionais" id="supplier-aditional-contacts" :collapsed="false">
                <x-slot:actions>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-add-additional-contact>
                        <i class="mdi mdi-plus" aria-hidden="true"></i> Adicionar contato
                    </button>
                </x-slot:actions>
                @include('supplier::partials.form._additional_contacts')
            </x-form.card>

            <x-form.card title="Dados de Endereço" id="supplier-address" :collapsed="false">
                @include('supplier::partials.form._address')
            </x-form.card>

            <x-form.card title="Observações" id="supplier-observations" :collapsed="false">
                @include('supplier::partials.form._observations')
            </x-form.card>

        </fieldset>

        <div class="row mb-2">
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Voltar</a>
                @unless ($readOnly)
                    <button type="submit" class="btn btn-primary">Salvar</button>
                @endunless
            </div>
        </div>

    </form>
@endsection

@push('scripts')
    @vite('Modules/Supplier/resources/assets/js/app.js')
@endpush
