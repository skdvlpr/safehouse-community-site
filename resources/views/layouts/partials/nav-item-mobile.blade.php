@php
    use App\Support\Navigation;

    $isActive = Navigation::isActive($item, $locale);
    $extraPages = ($item['type'] ?? null) === 'pages_dropdown'
        ? Navigation::extraMenuPages($locale)
        : collect();
@endphp

@if (($item['type'] ?? null) === 'pages_dropdown')
    @if ($extraPages->isNotEmpty())
        <div class="my-1 border-t border-white/10 pt-2">
            <p class="px-3 py-1 text-xs font-semibold uppercase tracking-wide text-safehouse-muted">
                {{ __($item['label']) }}
            </p>
            @foreach ($extraPages as $page)
                @php
                    $pageUrl = app(\App\Services\PageService::class)->publicUrl($page, $locale);
                @endphp
                @if ($pageUrl)
                    <a href="{{ $pageUrl }}"
                       class="block rounded-md px-3 py-2 text-sm text-safehouse-muted hover:bg-white/5 hover:text-safehouse-text">
                        {{ Navigation::pageTitle($page, $locale) }}
                    </a>
                @endif
            @endforeach
        </div>
    @endif
@else
    <a href="{{ Navigation::url($item, $locale) }}"
       class="block rounded-md px-3 py-2 text-sm text-safehouse-muted hover:bg-white/5 hover:text-safehouse-text">
        {{ __($item['label']) }}
    </a>
@endif
