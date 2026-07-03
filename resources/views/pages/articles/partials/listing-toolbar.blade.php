@php
    /** @var \App\DataTransferObjects\ArticleListingFilters $filters */
    /** @var \Illuminate\Support\Collection<int, \App\Models\ArticleCategory> $categories */
    $articlesService = app(\App\Services\ArticleService::class);
    $indexRoute = $indexRoute ?? 'articles.index';
@endphp

<section class="news-toolbar safehouse-glass" aria-label="{{ __($filtersLabel ?? 'site.pages.news_filters_label') }}">
    <div class="news-toolbar__row">
        <div class="news-toolbar__group">
            <span class="news-toolbar__label">{{ __('site.pages.news_categories_label') }}</span>
            <div class="news-category-chips" role="group" aria-label="{{ __('site.pages.news_categories_label') }}">
                @forelse ($categories as $category)
                    @php
                        $categorySlug = $articlesService->categorySlug($category, $locale);
                        $categoryName = $articlesService->categoryName($category, $locale);
                        $isActive = $categorySlug !== null && in_array($categorySlug, $filters->categorySlugs, true);
                        $toggleFilters = $filters->withCategorySlugs(
                            $categorySlug !== null ? $filters->toggledCategorySlugs($categorySlug) : $filters->categorySlugs,
                        );
                    @endphp
                    @if ($categorySlug !== null && $categoryName !== '')
                        <a href="{{ route($indexRoute, $toggleFilters->routeParameters($locale)) }}"
                           @class(['news-category-chip', 'is-active' => $isActive])
                           aria-pressed="{{ $isActive ? 'true' : 'false' }}">
                            {{ $categoryName }}
                        </a>
                    @endif
                @empty
                    <p class="news-toolbar__hint">{{ __($categoriesEmptyLabel ?? 'site.pages.news_categories_empty') }}</p>
                @endforelse
            </div>
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

    @if ($filters->hasActiveFilters())
        <div class="news-toolbar__footer">
            <a href="{{ route($indexRoute, ['locale' => $locale]) }}" class="news-toolbar__clear">
                {{ __('site.pages.news_clear_filters') }}
            </a>
        </div>
    @endif
</section>
