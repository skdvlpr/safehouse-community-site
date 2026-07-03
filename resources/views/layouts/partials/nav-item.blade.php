@php
    use App\Support\Navigation;

    $isActive = Navigation::isActive($item, $locale);
    $extraPages = ($item['type'] ?? null) === 'pages_dropdown'
        ? Navigation::extraMenuPages($locale)
        : collect();
@endphp

@if (($item['type'] ?? null) === 'pages_dropdown')
    @if ($extraPages->isNotEmpty())
        <div @class([
            'nav-dropdown group',
            'is-active' => $isActive,
        ])>
            <span @class([
                'nav-dropdown__trigger',
                'text-safehouse-primary' => $isActive,
                'text-safehouse-muted group-hover:text-safehouse-text' => ! $isActive,
            ])>
                {{ __($item['label']) }}
                <svg class="nav-dropdown__chevron" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.25a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z" clip-rule="evenodd" />
                </svg>
            </span>

            <div class="nav-dropdown__panel" role="menu">
                @foreach ($extraPages as $page)
                    @php
                        $pageUrl = app(\App\Services\PageService::class)->publicUrl($page, $locale);
                        $pageSlug = Navigation::pageSlugForLocale($page, $locale);
                        $pageActive = request()->routeIs('pages.show') && request()->route('pageSlug') === $pageSlug;
                    @endphp
                    @if ($pageUrl)
                        <a href="{{ $pageUrl }}"
                           role="menuitem"
                           @class([
                               'nav-dropdown__link',
                               'nav-dropdown__link--active' => $pageActive,
                           ])>
                            {{ Navigation::pageTitle($page, $locale) }}
                        </a>
                    @endif
                @endforeach
            </div>
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
           'text-sm font-medium transition',
           'nav-link--highlight' => $highlight,
           'text-safehouse-primary' => $isActive && ! $highlight,
           'text-safehouse-muted hover:text-safehouse-text' => ! $isActive && ! $highlight,
       ])>
        {{ $label }}
    </a>
@endif
