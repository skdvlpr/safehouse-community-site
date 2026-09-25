@php
    /** @var \App\DataTransferObjects\ArticleListingFilters $filters */
    /** @var \Illuminate\Support\Collection<int, \App\Models\ArticleCategory> $categories */
    $articlesService = app(\App\Services\ArticleService::class);
    $indexRoute = $indexRoute ?? 'articles.index';
    $categoryOptions = [];

    foreach ($categories as $category) {
        $categorySlug = $articlesService->categorySlug($category, $locale);
        $categoryName = $articlesService->categoryName($category, $locale);

        if ($categorySlug === null || $categoryName === '') {
            continue;
        }

        $toggleFilters = $filters->withCategorySlugs($filters->toggledCategorySlugs($categorySlug));
        $categoryOptions[] = [
            'name' => $categoryName,
            'active' => in_array($categorySlug, $filters->categorySlugs, true),
            'href' => route($indexRoute, $toggleFilters->routeParameters($locale)),
        ];
    }

    $selectedCategories = array_values(array_filter(
        $categoryOptions,
        static fn (array $option): bool => $option['active'],
    ));
    $selectedCount = count($selectedCategories);
    $visibleCategories = $selectedCount > 3
        ? array_slice($selectedCategories, 0, 2)
        : $selectedCategories;
    $extraCategories = $selectedCount > 3 ? $selectedCount - 2 : 0;
@endphp

<section class="news-toolbar safehouse-glass" aria-label="{{ __($filtersLabel ?? 'site.pages.news_filters_label') }}">
    <div class="news-toolbar__row">
        <div class="news-toolbar__group news-toolbar__group--categories">
            <span class="news-toolbar__label">{{ __('site.pages.news_categories_label') }}</span>
            @if ($categoryOptions === [])
                <p class="news-toolbar__hint">{{ __($categoriesEmptyLabel ?? 'site.pages.news_categories_empty') }}</p>
            @else
                <details class="news-cat-menu">
                    <summary class="news-cat-menu__summary">
                        @if ($selectedCount === 0)
                            <span class="news-cat-menu__face">{{ __('site.pages.news_categories_label') }}</span>
                        @else
                            @foreach ($visibleCategories as $selected)
                                <span class="news-cat-menu__face">{{ $selected['name'] }}</span>
                            @endforeach
                            @if ($extraCategories > 0)
                                <span class="news-cat-menu__face">+{{ $extraCategories }}</span>
                            @endif
                        @endif
                    </summary>
                    <div class="news-cat-menu__panel" role="group" aria-label="{{ __('site.pages.news_categories_label') }}">
                        @foreach ($categoryOptions as $option)
                            <a href="{{ $option['href'] }}"
                               @class(['news-cat-menu__option', 'is-active' => $option['active']])
                               aria-pressed="{{ $option['active'] ? 'true' : 'false' }}">
                                {{ $option['name'] }}
                            </a>
                        @endforeach
                    </div>
                </details>
            @endif
        </div>

        <div class="news-toolbar__group news-toolbar__group--dates">
            <span class="news-toolbar__label">{{ __('site.pages.news_date_label') }}</span>
            <form method="get" action="{{ route($indexRoute, ['locale' => $locale]) }}" class="news-date-filters">
                @foreach ($filters->categorySlugs as $categorySlug)
                    <input type="hidden" name="categories[]" value="{{ $categorySlug }}">
                @endforeach
                @if ($filters->layout === 'list')
                    <input type="hidden" name="layout" value="list">
                @endif
                <label class="news-date-filters__field">
                    <span class="sr-only">{{ __('site.pages.news_date_from') }}</span>
                    <input type="date"
                           name="from"
                           value="{{ $filters->publishedFrom }}"
                           aria-label="{{ __('site.pages.news_date_from') }}">
                </label>
                <span class="news-date-filters__separator" aria-hidden="true">—</span>
                <label class="news-date-filters__field">
                    <span class="sr-only">{{ __('site.pages.news_date_to') }}</span>
                    <input type="date"
                           name="to"
                           value="{{ $filters->publishedTo }}"
                           aria-label="{{ __('site.pages.news_date_to') }}">
                </label>
                <button type="submit" class="news-date-filters__submit safehouse-btn-secondary">
                    {{ __('site.pages.news_filter_apply') }}
                </button>
            </form>
        </div>

        <div class="news-toolbar__group news-toolbar__group--layout">
            <span class="news-toolbar__label">{{ __('site.pages.news_view_label') }}</span>
            <div class="news-view-toggle" role="group" aria-label="{{ __('site.pages.news_view_label') }}">
                <a href="{{ route($indexRoute, $filters->withLayout('feed')->routeParameters($locale)) }}"
                   @class(['news-view-toggle__btn', 'is-active' => $filters->layout === 'feed'])>
                    {{ __('site.pages.news_view_feed') }}
                </a>
                <a href="{{ route($indexRoute, $filters->withLayout('list')->routeParameters($locale)) }}"
                   @class(['news-view-toggle__btn', 'is-active' => $filters->layout === 'list'])>
                    {{ __('site.pages.news_view_list') }}
                </a>
            </div>
        </div>
    </div>
</section>
