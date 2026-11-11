@extends('supplier::create', ['readOnly' => true])

@section('content')
    @parent

    @include('audit::partials._history', [
        'root_type' => $supplier->getMorphClass(),
        'root_id' => $supplier->id,
    ])
@endsection
