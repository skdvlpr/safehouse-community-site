<?php

namespace Tests\Feature;

use App\Services\DonationSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonationFivePerMilleTest extends TestCase
{
    use RefreshDatabase;

    public function test_five_per_mille_page_renders_configured_content(): void
    {
        app(DonationSettingsService::class)->saveFromFormState([
            'donations' => [
                'five_per_mille' => [
                    'enabled' => true,
                    'codice_fiscale' => '98765432109',
                    'menu_label' => ['it' => '5 x 1000'],
                    'heading' => ['it' => 'Dona il 5 x 1000'],
                    'lead' => ['it' => 'Senza costi aggiuntivi.'],
                    'body' => ['it' => '<p>Testo introduttivo.</p>'],
                    'instructions' => ['it' => '<p>Istruzioni.</p>'],
                    'codice_label' => ['it' => 'Codice fiscale'],
                ],
                'bank_transfer' => [
                    'enabled' => false,
                    'iban' => '',
                    'beneficiary' => '',
                    'heading' => ['it' => 'Bonifico'],
                    'body' => ['it' => ''],
                    'iban_label' => ['it' => 'IBAN'],
                    'beneficiary_label' => ['it' => 'Intestatario'],
                ],
            ],
        ]);

        app(DonationSettingsService::class)->forgetCache();

        $this->get('/it/donazioni/5-per-mille')
            ->assertOk()
            ->assertSee('Dona il 5 x 1000', false)
            ->assertSee('98765432109', false)
            ->assertSee('Senza costi aggiuntivi.', false);
    }

    public function test_donations_index_shows_five_per_mille_and_bank_transfer_sections(): void
    {
        app(DonationSettingsService::class)->saveFromFormState([
            'donations' => [
                'five_per_mille' => [
                    'enabled' => true,
                    'codice_fiscale' => '98765432109',
                    'menu_label' => ['it' => '5 x 1000'],
                    'heading' => ['it' => 'Dona il 5 x 1000'],
                    'lead' => ['it' => 'Lead'],
                    'body' => ['it' => ''],
                    'instructions' => ['it' => ''],
                    'codice_label' => ['it' => 'Codice fiscale'],
                ],
                'bank_transfer' => [
                    'enabled' => true,
                    'iban' => 'IT60X0542811101000000123456',
                    'beneficiary' => 'Safe House ETS',
                    'heading' => ['it' => 'Bonifico bancario'],
                    'body' => ['it' => '<p>Bonifico sul conto.</p>'],
                    'iban_label' => ['it' => 'IBAN'],
                    'beneficiary_label' => ['it' => 'Intestatario'],
                ],
            ],
        ]);

        app(DonationSettingsService::class)->forgetCache();

        $this->get('/it/donazioni')
            ->assertOk()
            ->assertSee('Dona il 5 x 1000', false)
            ->assertSee('Bonifico bancario', false)
            ->assertSee('IT60X0542811101000000123456', false);
    }
}
