<?php

namespace App\Services;

use App\Exceptions\MembershipMailFailedException;
use App\Mail\MembershipApplicantMail;
use App\Mail\MembershipStaffMail;
use App\Services\EspoCrm\EspoCrmAssignedUserResolver;
use App\Services\EspoCrm\EspoCrmClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class MembershipApplicationService
{
    public function __construct(
        private readonly OutboundMailConfigurator $mail,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function submit(array $data, string $locale): void
    {
        if (! $this->mail->canSendSportelloNotifications()) {
            Log::warning('Membership application mail skipped: SMTP not configured');

            throw new MembershipMailFailedException('SMTP not configured');
        }

        $this->mail->applyForSportello();

        try {
            Mail::send(new MembershipStaffMail($data));
            Mail::send(new MembershipApplicantMail(
                applicantEmail: (string) $data['email'],
                mailLocale: $locale,
            ));
        } catch (Throwable $exception) {
            Log::warning('Membership application mail failed', [
                'error' => $exception->getMessage(),
            ]);

            throw new MembershipMailFailedException('Mail send failed', previous: $exception);
        }

        $this->createLeadBestEffort($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createLeadBestEffort(array $data): void
    {
        $client = EspoCrmClient::tryFromConfig();

        if ($client === null) {
            return;
        }

        try {
            $payload = [
                'firstName' => $data['name'],
                'lastName' => $data['last_name'],
                'emailAddress' => $data['email'],
                'phoneNumber' => $data['phone'],
                'addressStreet' => $data['address'],
                'addressCity' => $data['city'],
                'addressPostalCode' => $data['cap'],
                'addressState' => $data['province'],
                'addressCountry' => 'Italy',
                'taxCode' => $data['tax_code'],
                'birthDate' => $data['birth_date'],
                'birthPlace' => $data['birth_place'],
                'birthProvince' => $data['birth_province'],
                'contactType' => ['MemberContact'],
                'source' => 'Web Site',
                'status' => 'New',
                'description' => implode("\n", [
                    'Domanda di ammissione socio dal sito.',
                    'Statuto accettato: sì.',
                    'Mission accettata: sì.',
                    'Quota: impegno al versamento.',
                ]),
            ];

            $consent = $data['newsletter_consent'] ?? null;

            if ($consent === '1' || $consent === '0') {
                $payload['newsletterConsent'] = $consent === '1' ? 'Yes' : 'No';
            }

            $assignedUserId = app(EspoCrmAssignedUserResolver::class)->resolveUsing($client);

            if ($assignedUserId !== null) {
                $payload['assignedUserId'] = $assignedUserId;
            }

            // Existing Lead fields only. https://docs.espocrm.com/development/api/
            $client->create('Lead', $payload);
        } catch (Throwable $exception) {
            Log::warning('Membership Lead create skipped', [
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
