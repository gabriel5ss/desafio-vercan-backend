@extends('layouts.app')

@section('title', 'Logs de auditoria')

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <h4 class="header-title mb-3">Logs de auditoria</h4>
        <div class="table-responsive">
            @include('audit::partials._datatable', ['id' => 'audits-table'])
        </div>
    </div>
</div>
@endsection

@pushOnce('scripts', 'audit-assets')
    @vite('Modules/Audit/resources/assets/js/app.js')
@endPushOnce
