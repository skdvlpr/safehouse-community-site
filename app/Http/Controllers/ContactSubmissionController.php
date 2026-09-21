<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactSubmissionRequest;
use App\Services\ContactSubmissionRateLimiter;
use App\Services\ContactSubmissionService;
use App\Services\MeasurementBootService;
use App\Services\PageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactSubmissionController extends Controller
{
    public function __construct(
        private readonly ContactSubmissionService $submissions,
        private readonly ContactSubmissionRateLimiter $rateLimiter,
        private readonly PageService $pages,
    ) {}

    public function store(StoreContactSubmissionRequest $request, string $locale): RedirectResponse
    {
        if ($request->filled('company')) {
            return $this->redirectWithSuccess($locale, desk: null);
        }

        $stored = $this->rateLimiter->attempt(
            $request,
            fn () => $this->submissions->store($request->validated(), $request),
        );

        if ($stored === false) {
            return $this->redirectWithRateLimitError($request, $locale);
        }

        return $this->redirectWithSuccess($locale, desk: $request->validated('desk'));
    }

    private function redirectWithSuccess(string $locale, ?string $desk): RedirectResponse
    {
        $url = $this->pages->urlForKey('contact', $locale) ?? route('home', ['locale' => $locale]);

        $redirect = redirect()
            ->to($url)
            ->with('contact_success', __('site.pages.contact_success'));

        $event = $this->conversionEventForDesk($desk);

        if ($event !== null) {
            $redirect->with(MeasurementBootService::SESSION_CONVERSION, $event);
        }

        return $redirect;
    }

    /**
     * GA4 custom event for a known sportello. Unknown desks are not measured.
     *
     * @see https://support.google.com/analytics/answer/12229021
     */
    private function conversionEventForDesk(?string $desk): ?string
    {
        return match ($desk) {
            'generic_desk' => 'contact_generic_success',
            'legal_desk' => 'contact_slegale_success',
            'digital_desk' => 'contact_sdigitale_success',
            default => null,
        };
    }

    private function redirectWithRateLimitError(Request $request, string $locale): RedirectResponse
    {
        $url = $this->pages->urlForKey('contact', $locale) ?? route('home', ['locale' => $locale]);

        return redirect()
            ->to($url)
            ->withInput($request->except('company', 'cf-turnstile-response'))
            ->withErrors([
                'contact_rate_limit' => __('site.pages.contact_rate_limited'),
            ]);
    }
}
