@extends('layouts.app')

@section('title', __('site.volunteer.title'))

@section('content')
    <div class="template-page template-page--volunteer" data-page-template="volunteer">
        <section class="volunteer-page">
            <header class="volunteer-page__intro">
                <p class="template-eyebrow">{{ __('site.volunteer.eyebrow') }}</p>
                <h1 class="page-title">{{ __('site.volunteer.title') }}</h1>
                <p class="page-title__lead">{{ __('site.volunteer.lead') }}</p>
            </header>

            <aside class="volunteer-page__panel template-contact-aside safehouse-glass">
                <h2 class="volunteer-page__form-heading">{{ __('site.volunteer.form_heading') }}</h2>
                @include('pages.partials.volunteer-form-shell')
            </aside>
        </section>
    </div>
@endsection

@if (app(\App\Services\TurnstileVerifier::class)->enabled())
    @push('scripts')
        <script>
            (function () {
                var theme = document.documentElement.getAttribute('data-theme');
                if (theme !== 'light' && theme !== 'dark') {
                    theme = 'dark';
                }
                document.querySelectorAll('.cf-turnstile').forEach(function (el) {
                    el.setAttribute('data-theme', theme);
                });
            })();
        </script>
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endpush
@endif
