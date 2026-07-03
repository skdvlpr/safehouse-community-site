<?php

namespace App\Console\Commands;

use App\Services\OutboundMailConfigurator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class VerifyMailCommand extends Command
{
    protected $signature = 'mail:verify {--to= : Optional recipient override for the test message}';

    protected $description = 'Verify SMTP settings and send a test email';

    public function handle(OutboundMailConfigurator $mail): int
    {
        if (! $mail->isConfigured()) {
            $this->components->error('SMTP host is not configured. Set it in CMS → Integrazioni → Email or MAIL_HOST in .env.');

            return self::FAILURE;
        }

        $recipient = (string) ($this->option('to') ?: $mail->websiteFromAddress());

        if (filter_var($recipient, FILTER_VALIDATE_EMAIL) === false) {
            $this->components->error('No valid recipient. Pass --to=email@example.com or configure the website sender in CMS.');

            return self::FAILURE;
        }

        $mail->apply();

        try {
            Mail::raw('Test email from Safe House Community (php artisan mail:verify).', function ($message) use ($recipient): void {
                $message->to($recipient)->subject('Safe House — test SMTP');
            });
        } catch (Throwable $exception) {
            $this->components->error('SMTP send failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Test email sent to {$recipient}.");

        return self::SUCCESS;
    }
}
