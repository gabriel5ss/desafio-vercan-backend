<!-- start page title -->
<div class="mt-2 mb-2">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex justify-content-between align-items-center">
                <div class="page-title-left">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Desafio Vercan</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ $subtitle ?? 'Painel' }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $title ?? 'Painel' }}</li>
                    </ol>
                </div>
                <div class="page-title-right">
                    @yield('page-button-action')
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end page title -->
