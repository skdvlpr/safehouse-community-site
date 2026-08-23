<?php

namespace App\Services;

use App\Services\EspoCrm\EspoCrmAssignedUserOptions;
use App\Services\EspoCrm\EspoCrmCaseTypeOptions;
use App\Services\EspoCrm\HomeImpactStatsService;
use App\Services\EspoCrm\HomeMealStatsService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

/**
 * Admin "Clear cache" — mirrors EspoCRM Repair → Clear Cache for the Laravel site.
 */
class ApplicationCacheClearer
{
    public function __construct(
        private SiteSettingsService $siteSettings,
        private DonationSettingsService $donationSettings,
        private SiteContentService $siteContent,
        private ContactDeskSettings $contactDesk,
        private ContactSportelloMailSettings $sportelloMail,
        private SocialLinksSettings $socialLinks,
        private SiteAppearanceSettings $siteAppearance,
        private HomeImpactStatsService $homeImpactStats,
        private HomeMealStatsService $homeMealStats,
        private EspoCrmAssignedUserOptions $assignedUserOptions,
        private EspoCrmCaseTypeOptions $caseTypeOptions,
    ) {}

    public function clearAll(): void
    {
        $this->siteSettings->forgetCache();
        $this->donationSettings->forgetCache();
        $this->siteContent->forgetCache();
        $this->contactDesk->forgetCache();
        $this->sportelloMail->forgetCache();
        $this->socialLinks->forgetCache();
        $this->siteAppearance->forgetCache();
        $this->homeImpactStats->forgetCache();
        $this->homeMealStats->forgetCache();
        $this->assignedUserOptions->forgetCache();
        $this->caseTypeOptions->forgetCache();

        Cache::flush();

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('event:clear');
    }
}
