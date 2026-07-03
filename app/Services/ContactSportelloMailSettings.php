<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class ContactSportelloMailSettings
{
    private const CACHE_KEY = 'contact_sportello_mail:payload';

    /**
     * @return array{subject: array<string, string>, body: array<string, string>}
     */
    public function payload(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            $stored = $this->readFromDatabase();

            return $this->mergeWithDefaults($stored);
        });
    }

    public function subjectForLocale(string $locale): string
    {
        $payload = $this->payload();
        $subject = trim($payload['subject'][$locale] ?? '');

        if ($subject !== '') {
            return $subject;
        }

        $subject = trim($payload['subject']['it'] ?? '');

        if ($subject !== '') {
            return $subject;
        }

        return trim((string) config('contact_sportello_mail.default_subject.it', ''));
    }

    public function bodyHtmlForLocale(string $locale): string
    {
        $payload = $this->payload();
        $body = trim($payload['body'][$locale] ?? '');

        if ($body !== '') {
            return $body;
        }

        $body = trim($payload['body']['it'] ?? '');

        if ($body !== '') {
            return $body;
        }

        return trim((string) config('contact_sportello_mail.default_body.it', ''));
    }

    /**
     * @param  array<string, mixed>  $formState  sportello_mail.subject.it, sportello_mail.body.it, …
     */
    public function saveFromFormState(array $formState): void
    {
        $mail = is_array($formState['sportello_mail'] ?? null) ? $formState['sportello_mail'] : [];
        $subject = is_array($mail['subject'] ?? null) ? $mail['subject'] : [];
        $body = is_array($mail['body'] ?? null) ? $mail['body'] : [];

        $normalized = [
            'subject' => $this->normalizeLocaleMap($subject, maxLength: 255),
            'body' => $this->normalizeLocaleMap($body, maxLength: 65535, sanitizeHtml: true),
        ];

        if (! Schema::hasTable('site_settings')) {
            Cache::forever(self::CACHE_KEY, $this->mergeWithDefaults($normalized));

            return;
        }

        $setting = SiteSetting::query()->firstOrNew(['key' => (string) config('contact_sportello_mail.storage_key')]);
        $setting->storePlaintext(json_encode($normalized, JSON_THROW_ON_ERROR), false);
        $setting->save();

        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array{sportello_mail: array{subject: array<string, string>, body: array<string, string>}}
     */
    public function nestedFormValues(): array
    {
        $payload = $this->payload();

        return [
            'sportello_mail' => [
                'subject' => $payload['subject'],
                'body' => $payload['body'],
            ],
        ];
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array{subject: array<string, string>, body: array<string, string>}
     */
    private function readFromDatabase(): array
    {
        if (! Schema::hasTable('site_settings')) {
            return ['subject' => [], 'body' => []];
        }

        $stored = SiteSetting::query()
            ->where('key', (string) config('contact_sportello_mail.storage_key'))
            ->first();

        $raw = $stored?->decryptedValue();

        if ($raw === null || trim($raw) === '') {
            return ['subject' => [], 'body' => []];
        }

        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            return ['subject' => [], 'body' => []];
        }

        return [
            'subject' => is_array($decoded['subject'] ?? null) ? $decoded['subject'] : [],
            'body' => is_array($decoded['body'] ?? null) ? $decoded['body'] : [],
        ];
    }

    /**
     * @param  array{subject: array<string, string>, body: array<string, string>}  $stored
     * @return array{subject: array<string, string>, body: array<string, string>}
     */
    private function mergeWithDefaults(array $stored): array
    {
        /** @var array<string, string> $defaultSubject */
        $defaultSubject = config('contact_sportello_mail.default_subject', []);
        /** @var array<string, string> $defaultBody */
        $defaultBody = config('contact_sportello_mail.default_body', []);

        return [
            'subject' => array_merge($defaultSubject, $stored['subject'] ?? []),
            'body' => array_merge($defaultBody, $stored['body'] ?? []),
        ];
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, string>
     */
    private function normalizeLocaleMap(array $values, int $maxLength, bool $sanitizeHtml = false): array
    {
        $normalized = [];

        foreach ($values as $locale => $value) {
            if (! is_string($locale) || ! is_string($value)) {
                continue;
            }

            $value = trim($value);

            if ($value === '') {
                continue;
            }

            if ($sanitizeHtml) {
                $value = $this->sanitizeHtml($value);
            }

            $normalized[$locale] = mb_substr($value, 0, $maxLength);
        }

        return $normalized;
    }

    private function sanitizeHtml(string $html): string
    {
        $config = (new HtmlSanitizerConfig)
            ->allowSafeElements()
            ->allowRelativeLinks()
            ->allowRelativeMedias();

        return (new HtmlSanitizer($config))->sanitize($html);
    }
}
