<footer class="site-donated-by" aria-label="{{ __('site.donated_by.label') }}">
    <div class="site-content site-donated-by__inner">
        <a
            class="site-donated-by__banner"
            href="https://gomercato.it"
            target="_blank"
            rel="noopener noreferrer"
        >
            <img
                src="{{ asset('images/go-cms-mark.png') }}{{ is_file(public_path('images/go-cms-mark.png')) ? '?v='.filemtime(public_path('images/go-cms-mark.png')) : '' }}"
                alt=""
                class="site-donated-by__mark"
                width="28"
                height="28"
                decoding="async"
                aria-hidden="true"
            >
            <span class="site-donated-by__text">
                <span>{{ __('site.donated_by.lead') }}</span>
                <span class="site-donated-by__brand">GoMercato.it</span>
                <span class="site-donated-by__sep" aria-hidden="true">|</span>
                <span>{{ __('site.donated_by.tags') }}</span>
            </span>
        </a>
    </div>
</footer>
