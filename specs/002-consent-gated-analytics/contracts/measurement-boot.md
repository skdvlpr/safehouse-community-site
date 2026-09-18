# Contract: Measurement boot (002)

Public layout only ([Blade](https://laravel.com/docs/13.x/blade)). Filament CMS layout MUST NOT include this boot.

## HTML

When `is_bootable` is false:

- Document MUST NOT contain `googletagmanager.com` or a `GTM-` container id.
- Optional boot node may exist with `data-measurement-enabled="false"` and empty container.

When `is_bootable` is true:

- A JSON/boot node MAY expose the container id to **our** bundled JS (not a third-party snippet in `head`).
- The official GTM snippet is injected by JS **after** analytics consent, not by Blade on first paint without consent.

Preview routes (`pages.preview`, `articles.preview`, `editorial-articles.preview`): never bootable.

## JS inject (after consent `all`)

1. Ensure `window.dataLayer` exists.
2. Consent default denied for `analytics_storage`, `ad_storage`, `ad_user_data`, `ad_personalization` ([Consent mode](https://developers.google.com/tag-platform/security/guides/consent)).
3. Update `analytics_storage` to granted only.
4. Load `https://www.googletagmanager.com/gtm.js?id=GTM-…` using the official web container pattern ([GTM web](https://developers.google.com/tag-platform/tag-manager/web)).
5. MUST NOT inject the noscript iframe (would bypass the JS consent path).

On withdraw to `essential`: set all four keys denied; do not push further events.

## Page location

After inject, strip query keys `donor_name`, `phone`, `email` from the URL used for measurement (`history.replaceState` and/or GTM) so `page_location` cannot carry thank-you PII ([conversion-events](./conversion-events.md), [GA4 cookies](https://developers.google.com/analytics/devguides/collection/ga4/cookie-usage)).

## Config

`config/measurement.php` via `MEASUREMENT_ENABLED` and `GTM_CONTAINER_ID` ([configuration](https://laravel.com/docs/13.x/configuration)). Missing/invalid id ⇒ not bootable, no exception on the public page.

## Tests (propose)

- Default testing env: home HTML has no `googletagmanager.com`.
- `Config::set` enabled + `GTM-TEST1`: boot node present; still no GTM script tag until JS (assert snippet not in Blade).
- Enabled + `not-a-container`: treat as off.
