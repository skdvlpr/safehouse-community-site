# Contract: public locale switch

**Helper**: `App\Support\LocalizedUrl::forLocale` (existing).

**Behaviour**: Replace the first URL segment with `it` or `en`. Keep the remaining path and the query string. No `/ru`.

**Surfaces this feature**:

1. Footer — other locale only (not the current one). Label: “English version” / “Versione italiana”.
2. Cookie and privacy heroes — same link.
3. Cookie banner — two buttons **IT** and **EN**. Current locale is marked (non-link or `aria-current`). Other locale is an `href` to `LocalizedUrl::forLocale`.

**Must not**:

- Open cookie preferences from the footer.
- Clear `sh_cookie_consent` when the locale changes.
- Hide IT/EN inside the header gear as the only banner language control.

Cite: [Laravel localization](https://laravel.com/docs/13.x/localization), [Blade](https://laravel.com/docs/13.x/blade).
