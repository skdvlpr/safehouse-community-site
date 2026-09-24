<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $snippets = [
            'home' => [
                'title' => [
                    'it' => 'Safe House Community',
                    'en' => 'Safe House Community',
                ],
                'description' => [
                    'it' => 'Accoglienza, formazione e sostegno per chi costruisce un futuro migliore. Associazione di promozione sociale.',
                    'en' => 'Welcome, training and support for people building a better future. A social-promotion association.',
                ],
            ],
            'about' => [
                'title' => [
                    'it' => 'Chi siamo',
                    'en' => 'Who we are',
                ],
                'description' => [
                    'it' => 'Safe House ETS è una comunità di accoglienza e solidarietà. Sede legale ad Anzio, attività anche a Torino.',
                    'en' => 'Safe House ETS is a community of welcome and solidarity. Registered office in Anzio, with work in Turin too.',
                ],
            ],
            'services' => [
                'title' => [
                    'it' => 'Servizi',
                    'en' => 'Services',
                ],
                'description' => [
                    'it' => 'Interventi sociali, socio-sanitari, formazione e sostegno per persone e famiglie.',
                    'en' => 'Social care, health-related support, training and help for people and families.',
                ],
            ],
            'diventa-socio' => [
                'title' => [
                    'it' => 'Diventa socio',
                    'en' => 'Become a member',
                ],
                'description' => [
                    'it' => 'Chiedi l\'ammissione, leggi lo statuto e versa la quota annuale di 50 euro.',
                    'en' => 'Apply for membership, read the statute and pay the annual fee of €50.',
                ],
            ],
            'contact' => [
                'title' => [
                    'it' => 'Contatti',
                    'en' => 'Contact',
                ],
                'description' => [
                    'it' => 'Scrivi a Safe House o usa il modulo. Sede legale in via Delleani 26, Anzio.',
                    'en' => 'Write to Safe House or use the form. Registered office: Via Delleani 26, Anzio.',
                ],
            ],
            'trasparenza' => [
                'title' => [
                    'it' => 'Trasparenza',
                    'en' => 'Transparency',
                ],
                'description' => [
                    'it' => 'Bilancio sociale, governo associativo e dati pubblici dell\'ente.',
                    'en' => 'Social report, how the association is governed, and the public record of the organisation.',
                ],
            ],
        ];

        foreach ($snippets as $key => $snippet) {
            $page = Page::query()->where('key', $key)->first();
            if ($page === null) {
                continue;
            }

            $meta = is_array($page->meta) ? $page->meta : [];
            $meta['seo_title'] = $snippet['title'];
            $meta['seo_description'] = $snippet['description'];
            $page->meta = $meta;
            $page->save();
        }
    }

    public function down(): void
    {
        Page::query()->whereIn('key', [
            'home', 'about', 'services', 'diventa-socio', 'contact', 'trasparenza',
        ])->each(function (Page $page): void {
            $meta = is_array($page->meta) ? $page->meta : [];
            unset($meta['seo_title'], $meta['seo_description']);
            $page->meta = $meta;
            $page->save();
        });
    }
};
