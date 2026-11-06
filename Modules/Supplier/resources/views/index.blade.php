@extends('layouts.app')

@section('title', 'Fornecedores')

@section('content')
<div class="card mb-3">
    <div class="card-body">
        @include('supplier::partials._datatable', ['id' => 'suppliers-table'])
    </div>
</div>
@endsection

@section('page-button-action')
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
        Novo fornecedor
    </a>
@endsection

@push('scripts')
    @vite('Modules/Supplier/resources/assets/js/app.js')
@endpush
