<?php

namespace Tests\Feature;

use App\Services\SiteContentService;
use Database\Seeders\DeploySiteContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_primary_tagline_is_shared_between_footer_and_home(): void
    {
        $this->seed(DeploySiteContentSeeder::class);
        $this->seed(\Database\Seeders\PageSeeder::class);

        $tagline = app(SiteContentService::class)->primaryTagline('it');

        $this->assertSame('Comunità di accoglienza e solidarietà.', $tagline);

        $this->get('/it')
            ->assertOk()
            ->assertSee($tagline, false);
    }

    public function test_primary_tagline_persists_from_nested_filament_form_state(): void
    {
        $content = app(SiteContentService::class);

        $content->updateFromFormState([
            'content' => [
                'primary_tagline' => [
                    'it' => 'Nuovo slogan italiano.',
                    'en' => 'New English tagline.',
                ],
            ],
        ]);

        $content->forgetCache();

        $this->assertSame('Nuovo slogan italiano.', $content->primaryTagline('it'));
        $this->assertSame('New English tagline.', $content->primaryTagline('en'));
    }
}
