<section class="home-manifesto mb-6 md:mb-10" aria-labelledby="home-manifesto-quote">
    <div class="home-manifesto__inner">
        <div class="home-manifesto__quotes" aria-hidden="true">
            <span class="home-manifesto__quote-mark home-manifesto__quote-mark--open">&ldquo;</span>
            <span class="home-manifesto__quote-mark home-manifesto__quote-mark--close">&rdquo;</span>
        </div>

        <blockquote id="home-manifesto-quote" class="home-manifesto__quote">
            {!! nl2br(e(__('site.home.manifesto.quote'))) !!}
        </blockquote>

        <p class="home-manifesto__author">
            {{ __('site.home.manifesto.author') }}
        </p>

        <hr class="home-manifesto__divider" aria-hidden="true">

        <p class="home-manifesto__slogan">
            <span>{{ __('site.home.manifesto.slogan.welcome') }}</span>
            <span class="home-manifesto__slogan-dot" aria-hidden="true">&bull;</span>
            <strong>{{ __('site.home.manifesto.slogan.include') }}</strong>
            <span class="home-manifesto__slogan-dot" aria-hidden="true">&bull;</span>
            <span>{{ __('site.home.manifesto.slogan.change') }}</span>
            <span class="home-manifesto__slogan-dot" aria-hidden="true">&bull;</span>
            <strong>{{ __('site.home.manifesto.slogan.smile') }}</strong>
        </p>

        <div class="home-manifesto__emails">
            @foreach (__('site.home.manifesto.emails') as $email)
                <a class="home-manifesto__email" href="mailto:{{ $email }}">{{ $email }}</a>
                @if (! $loop->last)
                    <br>
                @endif
            @endforeach
        </div>

        <div class="home-manifesto__brand">
            <img
                src="{{ asset('images/logo.png') }}"
                alt=""
                class="home-manifesto__logo"
                width="56"
                height="56"
                decoding="async"
                aria-hidden="true"
            >
            <p class="home-manifesto__wordmark">{{ __('site.home.manifesto.brand') }}</p>
            <x-social-links />
        </div>
    </div>
</section>
