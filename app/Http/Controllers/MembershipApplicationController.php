<?php

namespace App\Http\Controllers;

use App\Exceptions\MembershipMailFailedException;
use App\Http\Requests\StoreMembershipApplicationRequest;
use App\Services\MembershipApplicationService;
use App\Services\PageService;
use Illuminate\Http\RedirectResponse;

class MembershipApplicationController extends Controller
{
    public function __construct(
        private readonly MembershipApplicationService $applications,
    ) {}

    public function store(StoreMembershipApplicationRequest $request, string $locale): RedirectResponse
    {
        if ($request->filled('company')) {
            return $this->redirectSuccess($locale);
        }

        try {
            $this->applications->submit($request->validated(), $locale);
        } catch (MembershipMailFailedException) {
            return redirect()
                ->back()
                ->withInput($request->except('company', 'cf-turnstile-response'))
                ->withErrors([
                    'membership_mail' => __('site.membership.mail_failed'),
                ]);
        }

        return $this->redirectSuccess($locale);
    }

    private function redirectSuccess(string $locale): RedirectResponse
    {
        $url = app(PageService::class)->urlForKey('diventa-socio', $locale)
            ?? '/'.$locale.'/diventa-socio';

        return redirect()
            ->to($url)
            ->with('membership_success', __('site.membership.success'));
    }
}
