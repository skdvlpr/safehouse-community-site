@php
    $brandName = __('cms.brand');
    $markPath = public_path('images/go-cms-mark.png');
    $logoUrl = asset('images/go-cms-mark.png').(is_file($markPath) ? '?v='.filemtime($markPath) : '');
@endphp

<span class="cms-brand">
    <img
        src="{{ $logoUrl }}"
        alt=""
        class="cms-brand__mark"
        width="40"
        height="40"
        decoding="async"
        aria-hidden="true"
    >
    <span class="cms-brand__label">{{ $brandName }}</span>
</span>

<style>
    .cms-brand {
        display: inline-flex;
        align-items: flex-end;
        gap: 0.625rem;
        min-height: 2.5rem;
    }

    .cms-brand__mark {
        display: block;
        width: 2.5rem;
        height: 2.5rem;
        object-fit: contain;
        flex-shrink: 0;
        filter: none;
    }

    .cms-brand__label {
        font-size: 1rem;
        font-weight: 600;
        line-height: 1;
        letter-spacing: -0.01em;
        white-space: nowrap;
        /* Optical: align with bottom of colored glyphs, not transparent padding */
        transform: translateY(-0.28rem);
        color: #1f2430;
    }

    .dark .cms-brand__label {
        color: #d5dae4;
    }

    @media (min-width: 640px) {
        .cms-brand__mark {
            width: 2.75rem;
            height: 2.75rem;
        }

        .cms-brand__label {
            font-size: 1.125rem;
            transform: translateY(-0.32rem);
        }
    }
</style>
