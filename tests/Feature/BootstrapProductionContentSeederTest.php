<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\DonationCampaign;
use App\Models\Page;
use App\Models\SiteSetting;
use Database\Seeders\BootstrapProductionContentSeeder;
use Database\Seeders\DonationCampaignSeeder;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BootstrapProductionContentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_bootstrap_seeds_empty_database(): void
    {
        $this->seed(BootstrapProductionContentSeeder::class);

        $this->assertTrue(Page::query()->exists());
        $this->assertTrue(DonationCampaign::query()->exists());
        $this->assertTrue(Article::query()->exists());
        $this->assertTrue(
            SiteSetting::query()->where('key', 'content.primary_tagline')->exists(),
        );
    }

    public function test_bootstrap_does_not_recreate_deleted_campaigns(): void
    {
        $this->seed(PageSeeder::class);
        $this->seed(DonationCampaignSeeder::class);

        DonationCampaign::query()->where('slug', 'operazione-inverno')->delete();
        DonationCampaign::query()->where('slug', 'mensa-solidale')->delete();

        $this->assertSame(1, DonationCampaign::query()->count());

        $this->seed(BootstrapProductionContentSeeder::class);

        $this->assertSame(1, DonationCampaign::query()->count());
        $this->assertTrue(
            DonationCampaign::query()->where('slug', 'safe-house')->exists(),
        );
    }

    public function test_bootstrap_does_not_overwrite_existing_pages(): void
    {
        $this->seed(PageSeeder::class);

        $home = Page::query()->where('key', 'home')->firstOrFail();
        $home->setTranslation('title', 'it', 'Custom title');
        $home->setTranslation('title', 'en', 'Custom title');
        $home->setTranslation('title', 'ru', 'Custom title');
        $home->save();

        $this->seed(BootstrapProductionContentSeeder::class);

        $home->refresh();

        $this->assertSame('Custom title', $home->getTranslation('title', 'it'));
    }
}
