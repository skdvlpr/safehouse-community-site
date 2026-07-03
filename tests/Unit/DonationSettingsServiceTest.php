<?php

namespace Tests\Unit;

use App\Services\DonationSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonationSettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_default_five_per_mille_content(): void
    {
        $settings = app(DonationSettingsService::class);

        $this->assertTrue($settings->fivePerMilleEnabled());
        $this->assertSame('Dona il 5 per mille', $settings->localized($settings->fivePerMille(), 'heading', 'it'));
    }

    public function test_persists_codice_fiscale_and_iban(): void
    {
        app(DonationSettingsService::class)->saveFromFormState([
            'donations' => [
                'five_per_mille' => [
                    'enabled' => true,
                    'codice_fiscale' => '12345678901',
                    'menu_label' => ['it' => '5 per mille'],
                    'heading' => ['it' => 'Dona il 5 per mille'],
                    'lead' => ['it' => 'Lead'],
                    'body' => ['it' => '<p>Body</p>'],
                    'instructions' => ['it' => '<ol><li>Step</li></ol>'],
                    'codice_label' => ['it' => 'Codice fiscale'],
                ],
                'bank_transfer' => [
                    'enabled' => true,
                    'iban' => 'IT60 X054 2811 1010 0000 0123 456',
                    'beneficiary' => 'Safe House ETS',
                    'heading' => ['it' => 'Bonifico'],
                    'body' => ['it' => '<p>Bonifico</p>'],
                    'iban_label' => ['it' => 'IBAN'],
                    'beneficiary_label' => ['it' => 'Intestatario'],
                ],
            ],
        ]);

        app(DonationSettingsService::class)->forgetCache();

        $settings = app(DonationSettingsService::class);

        $this->assertSame('12345678901', $settings->codiceFiscale());
        $this->assertSame('IT60X0542811101000000123456', $settings->iban());
        $this->assertSame('Safe House ETS', $settings->bankTransfer()['beneficiary']);
    }
}
