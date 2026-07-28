<?php

namespace Tests\Unit;

use App\Enums\ArticleSection;
use App\Models\ArticleCategory;
use App\Models\DonationCampaign;
use App\Support\UrlSlug;
use App\Support\UrlSlugSynchronizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UrlSlugTest extends TestCase
{
    use RefreshDatabase;

    public function test_from_strips_special_characters_and_lowercases(): void
    {
        $this->assertSame('test-category', UrlSlug::from('Test Category!'));
        $this->assertSame('caffe-latte', UrlSlug::from('Caffè Latte', 'it'));
        $this->assertSame('test-kategorii', UrlSlug::from('Тест категории', 'ru'));
        $this->assertSame('hello-world-co', UrlSlug::from('Hello / World & Co.', 'en'));
        $this->assertSame('', UrlSlug::from('!!!'));
    }

    public function test_unique_appends_suffix(): void
    {
        $taken = ['test' => true, 'test-2' => true];

        $this->assertSame('test-3', UrlSlug::unique(
            'test',
            fn (string $candidate): bool => isset($taken[$candidate]),
        ));
    }

    public function test_category_save_auto_fills_slug_from_name(): void
    {
        $category = ArticleCategory::query()->create([
            'section' => ArticleSection::Editorial,
            'name' => ['it' => 'Test Category!'],
            'description' => ['it' => ''],
        ]);

        $this->assertSame('test-category', $category->fresh()->getTranslation('slug', 'it'));
    }

    public function test_donation_campaign_keeps_explicit_valid_slug_on_create(): void
    {
        $campaign = DonationCampaign::factory()->create([
            'slug' => 'keep-me',
            'title' => ['it' => 'Different Title'],
        ]);

        $this->assertSame('keep-me', $campaign->fresh()->slug);
    }

    public function test_force_regenerate_overwrites_campaign_slug_from_title(): void
    {
        $campaign = DonationCampaign::factory()->create([
            'slug' => 'keep-me',
            'title' => ['it' => 'New Title Here'],
            'allows_recurring' => false,
        ]);

        $changed = app(UrlSlugSynchronizer::class)->sync($campaign, force: true);
        $this->assertTrue($changed);
        $this->assertSame('new-title-here', $campaign->slug);
    }

    public function test_recurring_campaign_slug_is_protected(): void
    {
        $slug = (string) config('donations.recurring_campaign_slug', 'donazione-ricorrente');

        $campaign = DonationCampaign::factory()->create([
            'slug' => $slug,
            'title' => ['it' => 'Should Not Change'],
            'allows_recurring' => true,
        ]);

        $changed = app(UrlSlugSynchronizer::class)->sync($campaign, force: true);
        $this->assertFalse($changed);
        $this->assertSame($slug, $campaign->fresh()->slug);
    }
}
