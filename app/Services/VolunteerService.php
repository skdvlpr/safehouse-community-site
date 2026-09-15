<?php

namespace App\Services;

use App\Exceptions\VolunteerMailFailedException;
use App\Mail\VolunteerApplicantMail;
use App\Mail\VolunteerStaffMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class VolunteerService
{
    public function __construct(
        private readonly OutboundMailConfigurator $mail,
    ) {}

    /**
     * @param  array{name: string, last_name: string, email: string, phone: string, message: string}  $data
     */
    public function send(array $data, string $locale): void
    {
        if (! $this->mail->canSendSportelloNotifications()) {
            Log::warning('Volunteer application mail skipped: SMTP not configured');

            throw new VolunteerMailFailedException('SMTP not configured');
        }

        $this->mail->applyForSportello();

        try {
            Mail::send(new VolunteerStaffMail(
                applicantName: $data['name'],
                lastName: $data['last_name'],
                applicantEmail: $data['email'],
                phone: $data['phone'],
                bodyMessage: $data['message'],
            ));

            Mail::send(new VolunteerApplicantMail(
                applicantEmail: $data['email'],
                mailLocale: $locale,
            ));
        } catch (Throwable $exception) {
            Log::warning('Volunteer application mail failed', [
                'error' => $exception->getMessage(),
            ]);

            throw new VolunteerMailFailedException('Mail send failed', previous: $exception);
        }
    }
}
