<?php

namespace Tests\Unit;

use App\Models\ContactSubmission;
use App\Services\ContactDeskSettings;
use App\Services\ContactSubmissionMailRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactSubmissionMailRendererTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(ContactDeskSettings::class)->save([
            [
                'key' => 'digital_desk',
                'label' => 'Sportello digitale',
                'inbox' => 'sportello.digitale@safehouse.community',
                'case_type' => 'SportelloDigitale',
            ],
        ]);
    }

    public function test_it_always_includes_crm_metadata_with_case_type_and_sportello_label(): void
    {
        $submission = ContactSubmission::query()->create([
            'name' => 'Sem Kok',
            'email' => 'visitor@example.com',
            'desk' => 'digital_desk',
            'message' => 'Test message',
            'status' => 'new',
            'correlation_token' => 'ecfe0207-fef5-42f7-8085-901bfc63d6cf',
        ]);

        $rendered = app(ContactSubmissionMailRenderer::class)->render($submission, 'it');

        $this->assertStringContainsString('Sportello: Sportello digitale', $rendered['text']);
        $this->assertStringContainsString('Tipo segnalazione: SportelloDigitale', $rendered['text']);
        $this->assertStringContainsString('[SH-ecfe0207-fef5-42f7-8085-901bfc63d6cf]', $rendered['text']);
        $this->assertStringContainsString('Sem Kok', $rendered['html']);
    }
}
