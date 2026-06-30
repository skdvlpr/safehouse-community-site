@extends('layouts.app')

@section('title', __('site.home.title'))

@section('content')
    <section class="safehouse-glass mb-10 rounded-2xl p-8 md:p-12">
        <p class="mb-3 text-sm font-medium uppercase tracking-wider text-safehouse-primary">
            {{ __('site.home.eyebrow') }}
        </p>
        <h1 class="mb-4 max-w-3xl text-3xl font-semibold tracking-tight md:text-4xl lg:text-5xl">
            {{ __('site.home.title') }}
        </h1>
        <p class="mb-8 max-w-2xl text-lg text-safehouse-muted md:text-xl">
            {{ __('site.home.lead') }}
        </p>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('donations.index', ['locale' => app()->getLocale()]) }}" class="safehouse-btn-primary">
                {{ __('site.home.cta_donate') }}
            </a>
            <a href="{{ route('volunteers.show', ['locale' => app()->getLocale()]) }}" class="safehouse-btn-secondary">
                {{ __('site.home.cta_volunteer') }}
            </a>
        </div>
    </section>

    <section aria-labelledby="home-stats-heading">
        <div class="mb-6 flex items-end justify-between gap-4">
            <div>
                <h2 id="home-stats-heading" class="text-xl font-semibold tracking-tight md:text-2xl">
                    {{ __('site.home.stats.heading') }}
                </h2>
                <p class="mt-1 text-sm text-safehouse-muted">{{ __('site.home.stats.lead') }}</p>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            @foreach ($stats as $stat)
                <article class="safehouse-glass rounded-xl p-6 text-center">
                    <p class="text-3xl font-semibold tabular-nums text-safehouse-primary md:text-4xl">
                        {{ $stat['value'] }}
                    </p>
                    <p class="mt-2 text-sm text-safehouse-muted">{{ __($stat['label']) }}</p>
                </article>
            @endforeach
        </div>
    </section>
@endsection
