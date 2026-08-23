<?php

namespace App\Services;

use App\Models\Page;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class SiteAppearanceSettings
{
    public const MODE_STOCK = 'stock';

    public const MODE_AURORA = 'aurora';

    public const MODE_CUSTOM = 'custom';

    private const CACHE_KEY = 'site_appearance:state';

    /**
     * URL used for both themes when mode is stock or custom.
     * Null when Aurora (theme-specific CSS defaults apply).
     */
    public function activeBackgroundUrl(): ?string
    {
        $state = $this->state();

        return match ($state['mode']) {
            self::MODE_CUSTOM => $this->urlForStoredPath($state['path']),
            self::MODE_STOCK => $this->stockUrl(),
            default => null,
        };
    }

    public function backgroundUrlForPage(?Page $page = null): ?string
    {
        if ($page !== null) {
            $override = $this->pageOverrideUrl($page);

            if ($override !== null) {
                return $override;
            }
        }

        return $this->activeBackgroundUrl();
    }

    /**
     * @deprecated Use activeBackgroundUrl()
     */
    public function customBackgroundUrl(): ?string
    {
        return $this->activeBackgroundUrl();
    }

    public function backgroundPath(): ?string
    {
        $state = $this->state();

        return $state['mode'] === self::MODE_CUSTOM ? $state['path'] : null;
    }

    public function mode(): string
    {
        return $this->state()['mode'];
    }

    /**
     * @return array{mode: string, path: ?string}
     */
    public function state(): array
    {
        /** @var array{mode: string, path: ?string} */
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            return $this->readStateFromDatabase();
        });
    }

    /**
     * Path shown in the global FileUpload (stock library file when on stock).
     */
    public function formBackgroundPath(): ?string
    {
        $this->ensureStockInLibrary();

        return match ($this->mode()) {
            self::MODE_CUSTOM => $this->backgroundPath(),
            self::MODE_STOCK => $this->stockLibraryPath(),
            default => null,
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function nestedFormValues(): array
    {
        return [
            'appearance' => [
                'background' => $this->formBackgroundPath(),
            ],
        ];
    }

    /**
     * @return array<string, string> path => label
     */
    public function libraryOptions(): array
    {
        $this->ensureStockInLibrary();

        $disk = Storage::disk((string) config('site_appearance.disk', 'public'));
        $directory = trim((string) config('site_appearance.directory', 'site-appearance'), '/');
        $options = [];

        $stockPath = $this->stockLibraryPath();
        if ($disk->exists($stockPath)) {
            $options[$stockPath] = __('cms.fields.background_stock_option');
        }

        foreach ($disk->files($directory) as $file) {
            if ($file === $stockPath) {
                continue;
            }

            if (! $this->isAllowedBackgroundFile($file)) {
                continue;
            }

            $options[$file] = basename($file);
        }

        return $options;
    }

    /**
     * @return list<string>
     */
    public function acceptedMimeTypes(): array
    {
        $types = config('site_appearance.accepted_mimetypes', []);

        return is_array($types) ? array_values(array_filter($types, 'is_string')) : [];
    }

    /**
     * @param  array<string, mixed>  $formState
     */
    public function saveFromFormState(array $formState): void
    {
        $appearance = $formState['appearance'] ?? [];

        if (! is_array($appearance)) {
            $appearance = [];
        }

        $path = $this->normalizePath($appearance['background'] ?? null);

        if ($path === null) {
            $this->persist(['mode' => self::MODE_STOCK, 'path' => null]);

            return;
        }

        $this->ensureStockInLibrary();

        if ($path === $this->stockLibraryPath()) {
            $this->persist(['mode' => self::MODE_STOCK, 'path' => null]);

            return;
        }

        $this->persist(['mode' => self::MODE_CUSTOM, 'path' => $path]);
    }

    public function clearBackground(): void
    {
        $this->persist(['mode' => self::MODE_AURORA, 'path' => null]);
    }

    public function useStockBackground(): void
    {
        $this->persist(['mode' => self::MODE_STOCK, 'path' => null]);
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function ensureStockInLibrary(): void
    {
        $stockPublic = public_path(ltrim((string) config('site_appearance.stock', '/images/bg-photo.jpg'), '/'));
        $libraryPath = $this->stockLibraryPath();
        $disk = Storage::disk((string) config('site_appearance.disk', 'public'));

        if (! is_file($stockPublic)) {
            return;
        }

        if ($disk->exists($libraryPath)) {
            return;
        }

        $disk->put($libraryPath, (string) file_get_contents($stockPublic));
    }

    public function stockLibraryPath(): string
    {
        $directory = trim((string) config('site_appearance.directory', 'site-appearance'), '/');
        $filename = (string) config('site_appearance.stock_library_filename', 'stock-bg-photo.jpg');

        return $directory.'/'.$filename;
    }

    /**
     * @param  array<string, mixed>|null  $meta
     * @return array<string, mixed>|null
     */
    public function normalizePageBackgroundMeta(?array $meta): ?array
    {
        if ($meta === null) {
            return null;
        }

        $background = $meta['background'] ?? null;

        if (! is_array($background)) {
            unset($meta['background']);

            return $meta;
        }

        $enabled = (bool) ($background['enabled'] ?? false);
        $upload = $this->normalizePath($background['upload'] ?? null);
        $selected = $this->normalizePath($background['path'] ?? null);
        $path = $upload ?? $selected;

        if (! $enabled || $path === null || ! $this->pathExists($path)) {
            unset($meta['background']);

            return $meta;
        }

        $meta['background'] = [
            'enabled' => true,
            'path' => $path,
        ];

        return $meta;
    }

    private function pageOverrideUrl(Page $page): ?string
    {
        $meta = $page->meta;

        if (! is_array($meta)) {
            return null;
        }

        $background = $meta['background'] ?? null;

        if (! is_array($background) || ! ($background['enabled'] ?? false)) {
            return null;
        }

        $path = $this->normalizePath($background['path'] ?? null);

        return $this->urlForAnyPath($path);
    }

    private function pathExists(string $path): bool
    {
        if ($path === $this->stockLibraryPath()) {
            $this->ensureStockInLibrary();
        }

        $disk = Storage::disk((string) config('site_appearance.disk', 'public'));

        if ($disk->exists($path)) {
            return true;
        }

        return is_file(public_path($path));
    }

    private function urlForAnyPath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if ($path === $this->stockLibraryPath()) {
            return $this->stockUrl() ?? $this->urlForStoredPath($path);
        }

        return $this->urlForStoredPath($path) ?? (is_file(public_path($path)) ? asset($path) : null);
    }

    private function isAllowedBackgroundFile(string $path): bool
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true);
    }

    /**
     * @param  array{mode: string, path: ?string}  $next
     */
    private function persist(array $next): void
    {
        $previous = $this->readStateFromDatabase();

        if (! Schema::hasTable('site_settings')) {
            $this->forgetCache();

            return;
        }

        $setting = SiteSetting::query()->firstOrNew([
            'key' => (string) config('site_appearance.storage_key', 'appearance.background'),
        ]);

        if ($next['mode'] === self::MODE_STOCK) {
            $setting->storePlaintext(null, false);
            $setting->save();
            $this->deleteStoredFile($previous['path'] ?? null);
            $this->forgetCache();

            return;
        }

        if ($next['mode'] === self::MODE_AURORA) {
            $setting->storePlaintext(json_encode(['mode' => self::MODE_AURORA], JSON_UNESCAPED_UNICODE), false);
            $setting->save();
            $this->deleteStoredFile($previous['path'] ?? null);
            $this->forgetCache();

            return;
        }

        $path = $next['path'];

        if (! is_string($path) || $path === '') {
            $this->persist(['mode' => self::MODE_STOCK, 'path' => null]);

            return;
        }

        $setting->storePlaintext(json_encode([
            'mode' => self::MODE_CUSTOM,
            'path' => $path,
        ], JSON_UNESCAPED_UNICODE), false);
        $setting->save();

        $previousPath = $previous['path'] ?? null;

        if (is_string($previousPath) && $previousPath !== '' && $previousPath !== $path) {
            $this->deleteStoredFile($previousPath);
        }

        $this->forgetCache();
    }

    /**
     * @return array{mode: string, path: ?string}
     */
    private function readStateFromDatabase(): array
    {
        if (! Schema::hasTable('site_settings')) {
            return ['mode' => self::MODE_STOCK, 'path' => null];
        }

        $stored = SiteSetting::query()
            ->where('key', (string) config('site_appearance.storage_key', 'appearance.background'))
            ->first();
        $raw = $stored?->decryptedValue();

        if (! is_string($raw) || $raw === '') {
            return ['mode' => self::MODE_STOCK, 'path' => null];
        }

        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            $path = $this->normalizePath($raw);

            return $path !== null
                ? ['mode' => self::MODE_CUSTOM, 'path' => $path]
                : ['mode' => self::MODE_STOCK, 'path' => null];
        }

        $mode = (string) ($decoded['mode'] ?? '');

        if ($mode === self::MODE_AURORA) {
            return ['mode' => self::MODE_AURORA, 'path' => null];
        }

        $path = $this->normalizePath($decoded['path'] ?? null);

        if ($path !== null) {
            if ($path === $this->stockLibraryPath()) {
                return ['mode' => self::MODE_STOCK, 'path' => null];
            }

            return ['mode' => self::MODE_CUSTOM, 'path' => $path];
        }

        return ['mode' => self::MODE_STOCK, 'path' => null];
    }

    private function stockUrl(): ?string
    {
        $stock = (string) config('site_appearance.stock', '/images/bg-photo.jpg');
        $relative = ltrim($stock, '/');
        $absolute = public_path($relative);

        if (! is_file($absolute)) {
            return $this->urlForStoredPath($this->stockLibraryPath());
        }

        return asset($relative);
    }

    private function urlForStoredPath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $disk = Storage::disk((string) config('site_appearance.disk', 'public'));

        if (! $disk->exists($path)) {
            return null;
        }

        return $disk->url($path);
    }

    private function normalizePath(mixed $raw): ?string
    {
        if (is_array($raw)) {
            $raw = $raw[0] ?? null;
        }

        if (! is_string($raw)) {
            return null;
        }

        $path = ltrim(trim($raw), '/');

        return $path !== '' ? $path : null;
    }

    private function deleteStoredFile(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        if ($path === $this->stockLibraryPath()) {
            return;
        }

        $disk = Storage::disk((string) config('site_appearance.disk', 'public'));

        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }
}
