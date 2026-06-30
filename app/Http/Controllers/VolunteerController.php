<?php

namespace App\Http\Controllers;

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

        $this->volunteers->store($request->validated(), $request);

        return $this->redirectWithSuccess($locale);
    }

    private function redirectWithSuccess(string $locale): RedirectResponse
    {
        return redirect()
            ->route('volunteers.show', ['locale' => $locale])
            ->with('volunteer_success', __('site.volunteer.success'));
    }
}
