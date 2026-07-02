@php
    /** @var \App\DataTransferObjects\HomeMealStatsSnapshot $mealStats */
    $panels = [
        [
            'title' => __('site.home.stats.meal_count_title'),
            'panel' => $mealStats->mealCount,
        ],
        [
            'title' => __('site.home.stats.network_meal_count_title'),
            'panel' => $mealStats->network,
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
                    @endphp

                    <div class="home-meal-stats__period">
                        <p class="home-meal-stats__period-title">{{ $periodConfig['label'] }}</p>

                        @if ($range !== '')
                            <p class="home-meal-stats__period-range">{{ $range }}</p>
                        @endif

                        <div class="home-meal-stats__metrics">
                            @foreach ($panel->metricList as $metricKey)
                                <div class="home-meal-stats__metric">
                                    <span class="home-meal-stats__metric-value">
                                        {{ $mealStats->formatMetric($metricKey, $period->value($metricKey)) }}
                                    </span>
                                    <span class="home-meal-stats__metric-label">
                                        {{ $mealStats->metricLabel($metricKey) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach
</div>
