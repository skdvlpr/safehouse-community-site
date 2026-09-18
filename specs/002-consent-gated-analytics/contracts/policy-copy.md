# Contract: Operational cookie and privacy copy (002)

This is **operational** copy for visitors and for later counsel review. It is **not** a DPA, SCC, or DPIA. Agent MUST NOT add lawyer-grade processor-agreement text (`S-GDPR`).

Implement this contract **after** the measurement gate can inject (spec US5).

## Banner (`lang/it/site.php`, `lang/en/site.php`)

When measurement is **not** bootable: short text still says analytics scripts are not loaded.

When bootable: short text says necessary tools run automatically; Google Analytics 4 (via Tag Manager) runs **only** after accepting analytics; refuse/essentials keeps the site usable; link to cookie + privacy pages.

Analytics preference note: aggregated statistics and conversion events, not advertising/remarketing.

## Cookie policy CMS body (it+en)

Must include:

- First-visit actions: accept all, essentials, preferences, dismiss = essentials
- Reopen from the footer control
- Necessary cookies already listed (session, CSRF, `sh_cookie_consent`, Stripe on checkout)
- Analytics row: Google Analytics 4 via Google Tag Manager; cookies `_ga` and `_ga_*` ([GA4 cookies](https://developers.google.com/analytics/devguides/collection/ga4/cookie-usage)); purpose aggregated statistics + conversion events; load only after analytics consent; not used for remarketing in this version
- Marketing/Ads row remains “not in use”
- Association does not store raw visitor IP for this measurement
- Audit of the banner choice uses hashed identifiers

## Privacy policy CMS body (it+en)

Must add/adjust a public-site measurement subsection:

- Product names: Google Analytics 4, Google Tag Manager
- Purpose: aggregated audience statistics and conversion events (`donate_success`, `donate_recurring_success`, `volunteer_success`, `contact_success`)
- Legal basis described operationally as consent for this non-technical tool (not legitimate interest for trackers)
- Recipients: Google Ireland / Google LLC for this purpose
- Transfer: mention the EU–US Data Privacy Framework / adequacy decision as the **operational** transfer note Google already publishes; **no** pasted DPA
- Google’s EU IP statement may be summarised (IP used for coarse geo then discarded per [Google](https://support.google.com/analytics/answer/12017362)) **and** restated that Safe House still does not keep raw IP for measurement
- Staff Calendar/Drive OAuth section stays; do not confuse it with public GA4
- Card data still does not land on association servers (Stripe) — do not regress that sentence

## Live status line

Legal cookie and privacy templates include a translated status line from config: configured/consent-gated vs globally not loaded. This is how SC-004 stays true if CMS HTML is slightly generic.

## Locales

Italian primary, English second. Do not add public `/ru` pages. Existing unused ru bodies in `LegalPagesContent` MUST NOT be published.

## Sync

Preview: `ddev exec php artisan site:sync-legal-pages --force` after editing `LegalPagesContent`. Production CMS overwrite waits for owner publish. Filament page edit remains possible ([resources](https://filamentphp.com/docs/4.x/resources/overview)).

## Tests (propose)

- Seed/sync then `GET /it/cookie-policy` and `/en/privacy-policy` see `Google Analytics 4` / Tag Manager (or Italian equivalent) and do not see the old “Non in uso” / “not currently active” **analytics** claim when the test enables measurement.
- Pages still must not contain `EspoCRM` / `crm.safehouse.community`.
- Assert absence of phrases like `Data Processing Agreement` / `Data Processing Addendum` added by this feature.

## Counsel

After owner UAT, the owner may send these IT+EN pages to lawyers. Implement does not wait on counsel. Their redlines become `002.K` or `006`.
