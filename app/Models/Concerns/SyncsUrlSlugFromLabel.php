<?php

namespace App\Models\Concerns;

use App\Support\UrlSlugSynchronizer;

trait SyncsUrlSlugFromLabel
{
    public static function bootSyncsUrlSlugFromLabel(): void
    {
        static::saving(function ($model): void {
            app(UrlSlugSynchronizer::class)->sync($model);
        });
    }
}
