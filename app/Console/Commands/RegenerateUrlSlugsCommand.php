<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\DonationCampaign;
use App\Models\Page;
use App\Support\UrlSlugSynchronizer;
use Illuminate\Console\Command;

class RegenerateUrlSlugsCommand extends Command
{
    protected $signature = 'cms:regenerate-url-slugs {--dry-run : Show changes without saving}';

    protected $description = 'Regenerate CMS URL slugs from name/title (excludes slogan/tagline and recurring donation slug)';

    public function handle(UrlSlugSynchronizer $synchronizer): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $updated = 0;

        foreach ([
            ArticleCategory::class,
            Article::class,
            Page::class,
            DonationCampaign::class,
        ] as $modelClass) {
            $this->info($modelClass);

            $modelClass::query()->orderBy('id')->each(function ($model) use ($synchronizer, $dryRun, &$updated): void {
                $before = $this->snapshot($model);
                $changed = $synchronizer->sync($model, force: true);

                if (! $changed) {
                    return;
                }

                $after = $this->snapshot($model);
                $this->line("  #{$model->getKey()}: ".json_encode($before).' → '.json_encode($after));

                if (! $dryRun) {
                    // Avoid re-entering saving hook side effects twice: sync already applied.
                    $model->saveQuietly();
                }

                $updated++;
            });
        }

        $this->info(($dryRun ? 'Dry-run would update ' : 'Updated ').$updated.' record(s).');

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>|string|null
     */
    private function snapshot(object $model): array|string|null
    {
        if ($model instanceof DonationCampaign) {
            return $model->slug;
        }

        if (method_exists($model, 'getTranslations')) {
            return $model->getTranslations('slug');
        }

        return null;
    }
}
