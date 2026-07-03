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
    @php
        $highlight = (bool) ($item['highlight'] ?? false);
        $label = __($item['label']);

        if (($item['route'] ?? null) === 'donations.five-per-mille') {
            $customLabel = app(\App\Services\DonationSettingsService::class)
                ->localized(app(\App\Services\DonationSettingsService::class)->fivePerMille(), 'menu_label', $locale);

            if ($customLabel !== '') {
                $label = $customLabel;
            }
        }
    @endphp
    <a href="{{ Navigation::url($item, $locale) }}"
       @class([
           'block rounded-md px-3 py-2 text-sm hover:bg-white/5 hover:text-safehouse-text',
           'nav-link--highlight' => $highlight,
           'text-safehouse-primary bg-white/5' => $isActive && ! $highlight,
           'text-safehouse-muted' => ! $isActive && ! $highlight,
       ])>
        {{ $label }}
    </a>
@endif
