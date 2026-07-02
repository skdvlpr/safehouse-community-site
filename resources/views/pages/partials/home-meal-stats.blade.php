@php
    /** @var \App\DataTransferObjects\HomeMealStatsSnapshot $mealStats */
    $panels = [
        [
            'title' => __('site.home.stats.meal_count_title'),
            'panel' => $mealStats->mealCount,
            'metric' => \App\DataTransferObjects\HomeMealStatsSnapshot::MEAL_COUNT_PRIMARY,
            'label' => __('site.home.stats.metrics.totalMeals'),
        ],
        [
            'title' => __('site.home.stats.network_meal_count_title'),
            'panel' => $mealStats->network,
            'metric' => \App\DataTransferObjects\HomeMealStatsSnapshot::NETWORK_PRIMARY,
            'label' => __('site.home.stats.metrics.portionCount'),
        ],
    ];

    $periods = [
        ['key' => 'year', 'label' => __('site.home.stats.period_year')],
        ['key' => 'month', 'label' => __('site.home.stats.period_month')],
        ['key' => 'today', 'label' => __('site.home.stats.period_today')],
    ];
@endphp

<div class="home-meal-stats">
    @foreach ($panels as $panelConfig)
        @php
            /** @var \App\DataTransferObjects\HomeMealStatsPanel $panel */
            $panel = $panelConfig['panel'];
            $metricKey = $panelConfig['metric'];
        @endphp

        <section class="home-meal-stats__panel safehouse-glass" aria-label="{{ $panelConfig['title'] }}">
            <h3 class="home-meal-stats__panel-title">{{ $panelConfig['title'] }}</h3>

            <div class="home-meal-stats__grid">
                @foreach ($periods as $periodConfig)
                    @php
                        $period = match ($periodConfig['key']) {
                            'year' => $panel->year,
                            'month' => $panel->month,
                            default => $panel->today,
                        };
                        $range = $mealStats->formatPeriodRange($period->from, $period->to);
                        $rawValue = $period->value($metricKey);
                        $displayValue = is_int($rawValue)
                            ? $mealStats->formatCount($rawValue)
                            : $mealStats->formatMetric($metricKey, $rawValue);
                    @endphp

                    <div class="home-meal-stats__period">
                        <p class="home-meal-stats__period-title">{{ $periodConfig['label'] }}</p>

                        @if ($range !== '')
                            <p class="home-meal-stats__period-range">{{ $range }}</p>
                        @endif

                        <p class="home-meal-stats__value">{{ $displayValue }}</p>
                        <p class="home-meal-stats__value-label">{{ $panelConfig['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach
</div>
