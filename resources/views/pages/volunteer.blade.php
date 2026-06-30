@extends('layouts.app')

@section('title', __('site.volunteer.title'))

@section('content')
    <section class="mx-auto max-w-2xl">
        <p class="mb-3 text-sm font-medium uppercase tracking-wider text-safehouse-primary">
            {{ __('site.volunteer.eyebrow') }}
        </p>
        <h1 class="mb-3 text-3xl font-semibold tracking-tight md:text-4xl">{{ __('site.volunteer.title') }}</h1>
        <p class="mb-8 text-safehouse-muted">{{ __('site.volunteer.lead') }}</p>

        <aside class="template-contact-aside safehouse-glass">
            <h2 class="mb-4 text-lg font-semibold">{{ __('site.volunteer.form_heading') }}</h2>
            @include('pages.partials.volunteer-form-shell')
        </aside>
    </section>
@endsection
