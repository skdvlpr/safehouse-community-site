@php
    /** @var string|null $customerPortalLoginUrl */
    $portalUrl = trim((string) ($customerPortalLoginUrl ?? ''));
    $cta = $ctaLabel ?? __('site.donations.cancel_portal_cta');
@endphp

@if ($portalUrl !== '')
    <p class="mt-3">
        <a href="{{ $portalUrl }}"
           target="_blank"
           rel="noopener noreferrer"
           class="inline-flex text-sm font-semibold text-safehouse-primary underline underline-offset-2 hover:text-safehouse-link-hover">
            {{ $cta }}
        </a>
    </p>
@endif
