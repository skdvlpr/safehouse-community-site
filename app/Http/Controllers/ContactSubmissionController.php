<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactSubmissionRequest;
use App\Services\ContactSubmissionService;
use App\Services\PageService;
use Illuminate\Http\RedirectResponse;

class ContactSubmissionController extends Controller
{
    public function __construct(
        private readonly ContactSubmissionService $submissions,
        private readonly PageService $pages,
    ) {}

    public function store(StoreContactSubmissionRequest $request, string $locale): RedirectResponse
    {
        if ($request->filled('company')) {
            return $this->redirectWithSuccess($locale);
        }

        $this->submissions->store($request->validated(), $request);

        return $this->redirectWithSuccess($locale);
    }

    private function redirectWithSuccess(string $locale): RedirectResponse
    {
        $url = $this->pages->urlForKey('contact', $locale) ?? route('home', ['locale' => $locale]);

        return redirect()
            ->to($url)
            ->with('contact_success', __('site.pages.contact_success'));
    }
}
