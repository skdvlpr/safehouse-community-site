<?php

namespace App\Services;

use Illuminate\Http\Request;

/**
 * Whether the public layout may expose a GTM container id to first-party JS.
 * CMS Integrations rows win over env. Preview/CMS must never boot.
 * Invalid/empty ids are off, not an error.
 */
class MeasurementBootService
{
    public const SESSION_CONVERSION = 'measurement_conversion';

    public function __construct(
        private readonly Request $request,
        private readonly SiteSettingsService $settings,
    ) {}

    public function isBootable(): bool
    {
        if ($this->isPreviewRequest()) {
            return false;
        }

        if (! $this->measurementEnabled()) {
            return false;
        }

        return $this->normalizedContainerId() !== null;
    }

    public function containerId(): ?string
    {
        if (! $this->isBootable()) {
            return null;
        }

        return $this->normalizedContainerId();
    }

    private function measurementEnabled(): bool
    {
        if ($this->settings->has('measurement.enabled')) {
            return $this->settings->isTruthy('measurement.enabled');
        }

        return (bool) config('measurement.enabled');
    }

    private function normalizedContainerId(): ?string
    {
        if ($this->settings->has('measurement.container_id')) {
            $id = strtoupper(trim((string) ($this->settings->getRaw('measurement.container_id') ?? '')));
        } else {
            $id = strtoupper(trim((string) config('measurement.container_id', '')));
        }

        if (preg_match('/^GTM-[A-Z0-9]+$/', $id) !== 1) {
            return null;
        }

        return $id;
    }

    private function isPreviewRequest(): bool
    {
        return $this->request->routeIs(
            'pages.preview',
            'articles.preview',
            'editorial-articles.preview',
        );
    }
}
