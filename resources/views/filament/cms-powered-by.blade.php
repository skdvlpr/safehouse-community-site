@php
    $markPath = public_path('images/go-cms-mark.png');
    $logoUrl = asset('images/go-cms-mark.png').(is_file($markPath) ? '?v='.filemtime($markPath) : '');
    $compact = $compact ?? false;
@endphp

<div @class(['cms-powered', 'cms-powered--compact' => $compact])>
    <img
        src="{{ $logoUrl }}"
        alt=""
        class="cms-powered__mark"
        width="36"
        height="36"
        decoding="async"
        aria-hidden="true"
    >
    <div class="cms-powered__copy">
        <p class="cms-powered__line">
            <span class="cms-powered__lead">{{ __('cms.powered.lead') }}</span>
            <a
                class="cms-powered__link"
                href="https://gomercato.it"
                target="_blank"
                rel="noopener noreferrer"
            >GoMercato.it</a>
            <span class="cms-powered__rocket" aria-hidden="true">🚀</span>
        </p>
        @unless ($compact)
            <p class="cms-powered__tags">
                {{ __('cms.powered.tags') }}
            </p>
        @endunless
    </div>
</div>
