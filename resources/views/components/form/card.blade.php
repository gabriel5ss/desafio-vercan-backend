@props([
    'title',
    'id',
    'description' => null,
    'collapsed' => true,
])

<div {{ $attributes->merge(['class' => 'card form-card mb-3']) }}>
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title m-0">
            {{ $title }}
        </h5>
        <div class="d-flex align-items-center gap-2">
            {{ $actions ?? '' }}
            <a
                class="card-collapse-toggle"
                data-bs-toggle="collapse"
                data-bs-target="#{{ $id }}"
                aria-expanded="{{ $collapsed ? 'false' : 'true' }}"
                aria-controls="{{ $id }}"
                aria-label="Alternar {{ $title }}"
            >
                <i class="mdi mdi-chevron-down card-collapse-arrow" aria-hidden="true"></i>
            </a>
        </div>
    </div>

    <hr class="form-card-divider">

    <div
        id="{{ $id }}"
        class="collapse {{  $collapsed ? '' : 'show' }}"
    >

        <div class="card-body">
            @if ($description)
                <p class="text-muted mb-3">{{ $description }}</p>
            @endif

            {{ $slot }}
        </div>

    </div>
</div>
