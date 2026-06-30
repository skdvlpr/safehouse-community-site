@php
    $locale = app()->getLocale();
    $footerItems = config('navigation.footer', []);
@endphp

<footer class="mt-auto border-t border-white/10 bg-safehouse-modal/50">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <div class="flex flex-col gap-8 md:flex-row md:items-start md:justify-between">
            <div>
                @include('layouts.partials.brand-mark', ['locale' => $locale, 'logoClass' => 'h-8 w-auto'])
                <p class="mt-4 max-w-sm text-sm text-safehouse-muted">{{ __('site.footer.tagline') }}</p>
            </div>

            <nav class="flex flex-wrap gap-x-6 gap-y-2 text-sm" aria-label="Footer">
                @foreach ($footerItems as $item)
                    <a href="{{ route($item['route'], ['locale' => $locale]) }}"
                       class="text-safehouse-muted transition hover:text-safehouse-link">
                        {{ __($item['label']) }}
                    </a>
                @endforeach
            </nav>
        </div>

        <p class="mt-8 border-t border-white/10 pt-6 text-xs text-safehouse-muted">
            {{ __('site.footer.rights', ['year' => now()->year]) }}
        </p>
    </div>
</footer>
