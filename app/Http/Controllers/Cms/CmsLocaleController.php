<?php

namespace App\Http\Controllers\Cms;

use App\Services\CmsUiLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CmsLocaleController
{
    public function __invoke(Request $request, string $locale, CmsUiLocale $cmsLocale): RedirectResponse
    {
        abort_unless($cmsLocale->set($locale), 404);

        return redirect()
            ->back(fallback: filament()->getUrl())
            ->with('status', __('cms.notifications.cms_locale_updated'));
    }
}
