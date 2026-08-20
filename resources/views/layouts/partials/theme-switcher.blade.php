@php
    $labels = [
        'dark' => __('site.theme.dark'),
        'light' => __('site.theme.light'),
        'system' => __('site.theme.system'),
    ];
@endphp

<details class="theme-switcher" data-theme-switcher>
    <summary
        class="theme-switcher__trigger"
        aria-label="{{ __('site.theme.label') }}"
        title="{{ __('site.theme.label') }}"
    >
        <svg class="theme-switcher__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1.5M12 19.5V21M4.5 12H3m18 0h-1.5M6.1 6.1 5 5m13 13-1.1-1.1M18 6.1 19.1 5M6.1 17.9 5 19M12 8.25A3.75 3.75 0 1 1 8.25 12 3.75 3.75 0 0 1 12 8.25Z" />
        </svg>
        <span class="sr-only" data-theme-label
              data-label-dark="{{ $labels['dark'] }}"
              data-label-light="{{ $labels['light'] }}"
              data-label-system="{{ $labels['system'] }}">{{ $labels['dark'] }}</span>
    </summary>
    <div class="theme-switcher__menu" role="listbox" aria-label="{{ __('site.theme.label') }}">
        <button type="button" class="theme-switcher__option" role="option" data-theme-option="dark" aria-checked="false">
            {{ $labels['dark'] }}
        </button>
        <button type="button" class="theme-switcher__option" role="option" data-theme-option="light" aria-checked="false">
            {{ $labels['light'] }}
        </button>
        <button type="button" class="theme-switcher__option" role="option" data-theme-option="system" aria-checked="false">
            {{ $labels['system'] }}
        </button>
    </div>
</details>
