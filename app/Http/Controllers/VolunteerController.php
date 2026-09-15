<?php

namespace App\Http\Controllers;

use App\Exceptions\VolunteerMailFailedException;
use App\Http\Requests\StoreVolunteerRequest;
use App\Services\VolunteerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VolunteerController extends Controller
{
    public function __construct(
        private readonly VolunteerService $volunteers,
    ) {}

    public function show(string $locale): View
    {
        return view('pages.volunteer');
    }

    public function store(StoreVolunteerRequest $request, string $locale): RedirectResponse
    {
        if ($request->filled('company')) {
            return $this->redirectWithSuccess($locale);
        }

        try {
            $this->volunteers->send($request->validated(), $locale);
        } catch (VolunteerMailFailedException) {
            return redirect()
                ->back()
                ->withInput($request->except('company', 'cf-turnstile-response'))
                ->withErrors([
                    'volunteer_mail' => __('site.volunteer.mail_failed'),
                ]);
        }

        return $this->redirectWithSuccess($locale);
    }

    private function redirectWithSuccess(string $locale): RedirectResponse
    {
        return redirect()
            ->route('volunteers.show', ['locale' => $locale])
            ->with('volunteer_success', __('site.volunteer.success'));
    }
}
