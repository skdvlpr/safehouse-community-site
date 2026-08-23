<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class CmsUiLocale
{
    public const SESSION_KEY = 'cms_ui_locale';

    /**
     * @return list<string>
     */
    public function available(): array
    {
        $configured = config('cms.available_locales');

        if (is_array($configured) && $configured !== []) {
            return array_values(array_filter($configured, 'is_string'));
        }

        return array_values(config('locales.available', ['it', 'en']));
    }

    public function default(): string
    {
        $default = (string) config('cms.locale', 'it');

        return $this->isSupported($default) ? $default : ($this->available()[0] ?? 'it');
    }

    public function current(): string
    {
        $sessionLocale = Session::get(self::SESSION_KEY);

        if (is_string($sessionLocale) && $this->isSupported($sessionLocale)) {
            return $sessionLocale;
        }

        return $this->default();
    }

    public function set(string $locale): bool
    {
        if (! $this->isSupported($locale)) {
            return false;
        }

        Session::put(self::SESSION_KEY, $locale);

        return true;
    }

    public function isSupported(string $locale): bool
    {
        return in_array($locale, $this->available(), true);
    }

    public function publicSiteUrl(?string $locale = null): string
    {
        $locale ??= $this->current();

        if (! $this->isSupported($locale)) {
            $locale = $this->default();
        }

        return url('/'.$locale);
    }
}
