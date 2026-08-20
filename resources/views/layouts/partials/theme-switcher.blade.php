@php
    $labels = [
        'dark' => __('site.theme.dark'),
        'light' => __('site.theme.light'),
        'system' => __('site.theme.system'),
    ];
@endphp

<nav class="theme-switcher" data-theme-switcher aria-label="{{ __('site.theme.label') }}">
    <button
        type="button"
        class="theme-switcher__option"
        data-theme-option="dark"
        aria-checked="false"
        aria-label="{{ $labels['dark'] }}"
        title="{{ $labels['dark'] }}"
    >
        <svg class="theme-switcher__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 14.3A8.1 8.1 0 0 1 9.7 3 7.1 7.1 0 1 0 21 14.3Z" />
        </svg>
    </button>
    <button
        type="button"
        class="theme-switcher__option"
        data-theme-option="light"
        aria-checked="false"
        aria-label="{{ $labels['light'] }}"
        title="{{ $labels['light'] }}"
    >
        <svg class="theme-switcher__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <circle cx="12" cy="12" r="3.6" />
            <path stroke-linecap="round" d="M12 3.2v1.6M12 19.2v1.6M4.8 12H3.2M20.8 12h-1.6M6.4 6.4l-1.1-1.1M18.7 18.7l-1.1-1.1M17.6 6.4l1.1-1.1M6.4 17.6l-1.1 1.1" />
        </svg>
    </button>
    <button
        type="button"
        class="theme-switcher__option"
        data-theme-option="system"
        aria-checked="false"
        aria-label="{{ $labels['system'] }}"
        title="{{ $labels['system'] }}"
    >
        <svg class="theme-switcher__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <rect x="3.5" y="4.5" width="17" height="12" rx="2" />
            <path stroke-linecap="round" d="M8 19.5h8M12 16.5v3" />
        </svg>
    </button>
</nav>
