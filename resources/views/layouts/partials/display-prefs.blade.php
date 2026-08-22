@php
    use App\Support\LocalizedUrl;

    $currentLocale = app()->getLocale();
    $localeLabels = ['it' => 'IT', 'en' => 'EN'];
    $themeLabels = [
        'dark' => __('site.theme.dark'),
        'light' => __('site.theme.light'),
        'system' => __('site.theme.system'),
    ];
@endphp

<details class="display-prefs" data-display-prefs>
    <summary
        class="display-prefs__trigger"
        aria-label="{{ __('site.display_prefs.label') }}"
        title="{{ __('site.display_prefs.label') }}"
    >
        <svg class="display-prefs__trigger-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.25a3.25 3.25 0 1 0 0-6.5 3.25 3.25 0 0 0 0 6.5Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-1.08-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09a1.65 1.65 0 0 0 1.51-1.08 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1.08 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9c.26.6.9 1.01 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z" />
        </svg>
    </summary>

    <div class="display-prefs__panel" role="group" aria-label="{{ __('site.display_prefs.label') }}">
        <div class="display-prefs__grid" role="presentation">
            <button
                type="button"
                class="display-prefs__cell"
                data-theme-option="dark"
                aria-checked="false"
                aria-label="{{ $themeLabels['dark'] }}"
                title="{{ $themeLabels['dark'] }}"
            >
                <svg class="display-prefs__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 14.3A8.1 8.1 0 0 1 9.7 3 7.1 7.1 0 1 0 21 14.3Z" />
                </svg>
            </button>
            <button
                type="button"
                class="display-prefs__cell"
                data-theme-option="light"
                aria-checked="false"
                aria-label="{{ $themeLabels['light'] }}"
                title="{{ $themeLabels['light'] }}"
            >
                <svg class="display-prefs__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <circle cx="12" cy="12" r="3.6" />
                    <path stroke-linecap="round" d="M12 3.2v1.6M12 19.2v1.6M4.8 12H3.2M20.8 12h-1.6M6.4 6.4l-1.1-1.1M18.7 18.7l-1.1-1.1M17.6 6.4l1.1-1.1M6.4 17.6l-1.1 1.1" />
                </svg>
            </button>
            <button
                type="button"
                class="display-prefs__cell"
                data-theme-option="system"
                aria-checked="false"
                aria-label="{{ $themeLabels['system'] }}"
                title="{{ $themeLabels['system'] }}"
            >
                <svg class="display-prefs__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <rect x="3.5" y="4.5" width="17" height="12" rx="2" />
                    <path stroke-linecap="round" d="M8 19.5h8M12 16.5v3" />
                </svg>
            </button>

            @foreach (config('locales.available', []) as $code)
                @if ($code === $currentLocale)
                    <span
                        class="display-prefs__cell display-prefs__cell--active"
                        aria-current="true"
                        title="{{ $localeLabels[$code] ?? strtoupper($code) }}"
                    >{{ $localeLabels[$code] ?? strtoupper($code) }}</span>
                @else
                    <a
                        href="{{ LocalizedUrl::forLocale($code) }}"
                        class="display-prefs__cell"
                        title="{{ $localeLabels[$code] ?? strtoupper($code) }}"
                        aria-label="{{ $localeLabels[$code] ?? strtoupper($code) }}"
                    >{{ $localeLabels[$code] ?? strtoupper($code) }}</a>
                @endif
            @endforeach
        </div>
    </div>
</details>
