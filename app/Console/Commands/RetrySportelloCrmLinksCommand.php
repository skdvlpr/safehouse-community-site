<?php

namespace App\Console\Commands;

use App\Models\ContactSubmission;
use App\Services\EspoCrm\LinkSportelloContactSubmissionService;
use Illuminate\Console\Command;
use Throwable;

class RetrySportelloCrmLinksCommand extends Command
{
    protected $signature = 'crm:retry-sportello-links {--limit=25 : Maximum submissions to process}';

    protected $description = 'Create missing Leads and link pending sportello contact submissions to EspoCRM Cases.';

    public function handle(LinkSportelloContactSubmissionService $linker): int
    {
        $limit = max(1, (int) $this->option('limit'));

        $submissions = ContactSubmission::query()
            ->where('crm_link_status', 'pending')
            ->whereNotNull('correlation_token')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($submissions->isEmpty()) {
            $this->info('No pending sportello CRM links.');

            return self::SUCCESS;
        }

        $linked = 0;
        $waiting = 0;
        $failed = 0;

        foreach ($submissions as $submission) {
            try {
                $result = $linker->link($submission->fresh());

                match ($result) {
                    'linked' => $linked++,
                    'waiting' => $waiting++,
                    default => $failed++,
                };
            } catch (Throwable $exception) {
                $failed++;
                $this->warn("Submission #{$submission->id}: {$exception->getMessage()}");
            }
        }

        $this->info("Processed {$submissions->count()} submission(s): {$linked} linked, {$waiting} waiting for Case, {$failed} failed/skipped.");

        return self::SUCCESS;
    }
}
