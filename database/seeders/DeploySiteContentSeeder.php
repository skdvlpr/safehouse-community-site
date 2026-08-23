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
            'content.home_independence_title.it' => 'Indipendenza',
            'content.home_independence_title.en' => 'Independence',
            'content.home_independence_body.it' => 'non riceviamo finanziamenti vincolati e non veicoliamo pubblicità. Le nostre attività sono sostenute da soci, donatori, bandi pubblici e collaborazioni con altre realtà del terzo settore.',
            'content.home_independence_body.en' => 'we do not receive tied funding and we do not carry advertising. Our work is supported by members, donors, public grants, and partnerships with other third-sector organisations.',
        ]);

        app(SiteContentService::class)->forgetCache();
    }
}
