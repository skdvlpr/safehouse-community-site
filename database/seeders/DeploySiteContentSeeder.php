<?php

namespace Database\Seeders;

use App\Services\SiteContentService;
use Illuminate\Database\Seeder;

class DeploySiteContentSeeder extends Seeder
{
    public function run(): void
    {
        app(SiteContentService::class)->updateMany([
            'content.primary_tagline.it' => 'Comunità di accoglienza e solidarietà.',
            'content.primary_tagline.en' => 'A community of welcome and solidarity.',
            'content.primary_tagline.ru' => 'Сообщество гостеприимства и солидарности.',
        ]);

        app(SiteContentService::class)->forgetCache();
    }
}
