# Findings register — Safehouse.community S01

- Date: 2026-09-13
- Local URL: `https://safehouse-community-site.ddev.site`
- Production: inspected 2026-09-13 (read-only SSH + public HTTPS). Snapshot: [`../001.1-prod-parity-replan/contracts/production-inspect-2026-09-13.md`](../001.1-prod-parity-replan/contracts/production-inspect-2026-09-13.md)
- Production permission that turn: owner asked SSH inspect (001.1)
- CRM repo read: yes (`/home/skoksharov/safehouse/nonprofit-espocrm`)
- App tree: S01 implement did not modify the app. Remap of F-001/F-006/F-017/F-018/F-020 and secrets-repo: 001.1 (2026-09-13)

## Severity


| Value     | Meaning                                                                                                                                                                      |
| --------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `blocker` | Secrets in git, fail-open payments, raw PII stored, staff CMS on `/admin`                                                                                                    |
| `high`    | Authz hole, unsigned webhook accepted / signature mismatch as 500, unhashed IP/UA stored, mass-assignment of privileged fields, constitution locale missing from public URLs |
| `medium`  | Broken public journey, empty-catch hiding donor/CRM failure, mixed locale on a primary page, demo content looking official                                                   |
| `low`     | Hardcoded string, deprecated CSRF helper with equivalent protection still on, docs drift                                                                                     |
| `note`    | Locked-decision reminder (not a repair)                                                                                                                                      |




## Buckets

`001.K`  `002`  `003`  `004`  `005`  `006`  `owner-ops`  `other-repo`

---



## F-001 — Russian public locale is constitution-locked but not routed

- Severity: high
- Area: i18n
- Env: both
- Bucket: `001.1` then **closed**
- Bucket why: Owner 2026-09-13: drop Russian from the public site and from product law. Do **not** add `/ru`.
- Constitution: VII / S-I18N (amended 001.1 to it + en, IT primary)
- Docs: n/a (product law)
- Status: remapped 2026-09-13 in 001.1

**Evidence**

`config/locales.php` `available` is only `it`, `en`. HTTP: `/it` 200, `/en` 200, `/ru` 404 (local and production). Production still stores leftover `ru` JSON on some pages; it is not routed.

**Impact**

Closed as a product-law change, not a missing locale to build. Leftover `ru` strings in CMS JSON are not a public locale.

---



## F-002 — Invalid Stripe webhook signatures are not guaranteed 4xx

- Severity: high
- Area: security-payments
- Env: local
- Bucket: 001.K
- Bucket why: Fail-closed signatures are security, not analytics/SEO.
- Constitution: XII
- Docs: [https://docs.stripe.com/webhooks/signatures](https://docs.stripe.com/webhooks/signatures)

**Evidence**

`app/Services/Payments/StripePaymentService.php` `constructWebhookEvent` returns `Webhook::constructEvent(...)`. Stripe’s official libraries throw on bad signatures (not a `RuntimeException`). `app/Http/Controllers/StripeWebhookController.php` only catches `RuntimeException` for that call and returns 400. Other throwables become an unhandled 500. Stripe retries 5xx. Existing `tests/Feature/StripeWebhookDonationTest.php` mocks `constructWebhookEvent` and never asserts a real bad signature → 400.

Missing/empty secret or client does throw `RuntimeException` → 400 (good). The gap is signature/payload parse failures.

**Impact**

A forged or malformed webhook may 500-retry instead of fail closed. After a verified event, CRM ingest 502 is appropriate (retryable).

---



## F-003 — Donor-facing checkout strings are Italian `__()` keys (all locales)

- Severity: high
- Area: i18n
- Env: local
- Bucket: 001.K
- Bucket why: Hardcoded visitor strings on donate flow.
- Constitution: VII (zero hardcoded user-facing strings)
- Docs: n/a

**Evidence**

`app/Http/Requests/CreateDonationIntentRequest.php` and `resources/views/donations/show.blade.php` use `__('Inserisci un\'email...')` and similar Italian literals as translation keys. `lang/it/site.php` / `lang/en/site.php` have no matching entries, so English URLs still show Italian.

**Impact**

English (and any future Russian) donors see Italian validation/help on the payment page.

---



## F-004 — Volunteer form has no bot challenge (contact does)

- Severity: medium
- Area: personal-forms
- Env: local
- Bucket: 001.K
- Bucket why: Form abuse control, not a later product spec.
- Constitution: VI
- Docs: [https://laravel.com/docs/13.x/validation#form-request-validation](https://laravel.com/docs/13.x/validation#form-request-validation)

**Evidence**

`StoreContactSubmissionRequest` requires Cloudflare Turnstile when enabled. `StoreVolunteerRequest` does not. Volunteer has honeypot `company` and `throttle:volunteers` (3/hour) in `VolunteerController` / `AppServiceProvider`.

**Impact**

Automated volunteer spam is easier than contact spam when Turnstile is on for contact only.

---



## F-005 — Conflicting X-Frame-Options (PHP DENY vs Caddy SAMEORIGIN)

- Severity: medium
- Area: security-payments
- Env: local (code + `deploy/Caddyfile.snippet`; production Caddy not fetched)
- Bucket: 001.K
- Bucket why: Header conflict is in this repo’s PHP + deploy snippet.
- Constitution: S-CSP (edge owns transport headers; duplicates confuse)
- Docs: [https://caddyserver.com/docs/caddyfile/directives/header](https://caddyserver.com/docs/caddyfile/directives/header)

**Evidence**

`app/Http/Middleware/SecurityHeaders.php` sets `X-Frame-Options: DENY` (`tests/Feature/SecurityHeadersTest.php`). `deploy/Caddyfile.snippet` sets `X-Frame-Options SAMEORIGIN` plus HSTS/CSP. Duplicate/conflicting frame options depend on proxy merge order.

CSP and HSTS are correctly **not** set in PHP (S-CSP pass for that part).

**Impact**

Clickjacking policy is undefined in production if both layers emit the header.

---



## F-006 — Published demo landing looks like a real public page

- Severity: medium
- Area: public-journeys
- Env: both
- Bucket: `001.1`
- Bucket why: Production has no demo-landing row (`/it/landing-example` 404). Local must match published keys; PageSeeder must not republish demo.
- Constitution: n/a (honest public journeys)
- Docs: n/a
- Status: remapped 2026-09-13 in 001.1 (local parity)

**Evidence**

Production inspect: no `demo-landing` / `demo-article` rows; `/it/landing-example` **404**. Local seeder previously published `landing-example`. Owner copied production pages into local on purpose; align **published** flags.

**Impact**

004 must not sitelink demo URLs. Local preview after 001.1 must not treat demo landing as official.

---



## F-007 — HomeController uses service locator instead of constructor injection

- Severity: medium
- Area: architecture
- Env: local
- Bucket: 001.K
- Bucket why: Constitution VII controller shape.
- Constitution: VII
- Docs: n/a

**Evidence**

`app/Http/Controllers/HomeController.php` calls `app(PageService::class)` inside `index()`. Other HTTP controllers inject in the constructor. No `new ClassName()` in controllers (pass for that rule). Mutations use Form Requests (`StoreVolunteerRequest`, `StoreContactSubmissionRequest`, `StoreGdprConsentRequest`, `CreateDonationIntentRequest`).

**Impact**

Harder to test/substitute PageService; pattern drift for later features.

---



## F-008 — Empty `catch (Throwable)` in PrimaNota Stripe sync swallows CRM/Stripe errors

- Severity: medium
- Area: architecture
- Env: local
- Bucket: 001.K
- Bucket why: Constitution XII; donor status can stay stale silently.
- Constitution: XII
- Docs: n/a

**Evidence**

`app/Services/Donations/PrimaNotaPaymentStatusService.php` contains `catch (\Throwable)` with empty body / “Keep previous $row” (e.g. around the select of payment status fields). Failures are not typed to the caller.

`app/Models/SiteSetting.php` empty catch on decrypt returns null (corrupt payload) — related **low** pattern, same area.

**Impact**

Payment status in CRM may not update; operators get no explicit failure.

---



## F-009 — Checkout JSON returns raw `RuntimeException` messages to the browser

- Severity: medium
- Area: security-payments
- Env: local
- Bucket: 001.K
- Bucket why: Information leak to donors; translate/map errors.
- Constitution: XII (no leaking internals; user-facing translations)
- Docs: n/a

**Evidence**

`DonationCheckoutController::store` and `MockDonationCompleteController` `return response()->json(['message' => $exception->getMessage()], 422)`. Stripe/CRM wording can surface in the donate UI.

**Impact**

English/Italian donors may see provider internals; slight recon of configuration.

---



## F-010 — CSRF except path `webhooks/stripe` does not match `/api/webhooks/stripe`

- Severity: low
- Area: security-payments
- Env: local
- Bucket: 001.K
- Bucket why: Laravel 13 API + CSRF config hygiene.
- Constitution: III
- Docs: [https://laravel.com/docs/13.x/csrf](https://laravel.com/docs/13.x/csrf)

**Evidence**

`bootstrap/app.php` uses deprecated `validateCsrfTokens(except: ['webhooks/stripe'])`. Route is `POST /api/webhooks/stripe` in `routes/api.php`. Laravel 13 documents `preventRequestForgery(except: ...)`. API routes typically omit CSRF; the except is likely dead. Signature verification remains the real control (see F-002).

**Impact**

Confusion for maintainers; not by itself a bypass if the `api` group has no CSRF.

---



## F-011 — Cookie consent audit stores IP hash only (no user-agent hash)

- Severity: low
- Area: cookie-consent
- Env: local
- Bucket: 001.K
- Bucket why: Align consent rows with volunteer/contact hashing.
- Constitution: VI (volunteer/contact require both hashes; consent is adjacent)
- Docs: n/a

**Evidence**

`GdprConsentService::recordCookieBanner` writes `ip_hash` only. `GdprConsent` fillable has no `user_agent_hash`. Volunteer/contact store both hashes via `ContactSubmissionService`. IP hash is SHA-256 without app-key HMAC (`hashIp`).

**Impact**

Weaker audit trail for cookie choice; unsalted IP hashes are more rainbow-table friendly than HMAC.

---



## F-012 — `trustProxies(at: '*')` trusts all X-Forwarded-For

- Severity: low
- Area: architecture
- Env: local
- Bucket: 001.K
- Bucket why: Rate-limit and IP hash depend on client IP.
- Constitution: VI
- Docs: n/a (Laravel trusted proxies)

**Evidence**

`bootstrap/app.php` `$middleware->trustProxies(at: '*');` Fine behind a known Caddy, dangerous if the app is ever reachable without that proxy.

**Impact**

Attackers can spoof IP for throttles (`volunteers`, `gdpr`, `donations`) and consent `ip_hash`.

---



## F-013 — CMS exception dump writes full traces to a local file

- Severity: low
- Area: staff-cms
- Env: local
- Bucket: 001.K
- Bucket why: May contain tokens in messages; `storage/` is gitignored.
- Constitution: VI (MUST NOT log secrets)
- Docs: n/a

**Evidence**

`bootstrap/app.php` `report` callback for `cms-safehouse*` writes message, file, line, and **full stack** to `storage/logs/cms-last-error.txt`.

**Impact**

Anyone with disk access to the app user can read CMS traces.

---



## F-014 — Document title suffix “Safe House” is hardcoded in the layout

- Severity: low
- Area: i18n
- Env: local
- Bucket: 001.K
- Bucket why: Visitor-facing layout string.
- Constitution: VII
- Docs: n/a

**Evidence**

`resources/views/layouts/app.blade.php`: `@yield('title', config('app.name')) — Safe House`. No `__()`. Acceptable as brand, still violates “zero hardcoded user-facing strings”.

**Impact**

Minor; all locales share the English/Italian brand spelling (intentional).

---



## F-015 — No audience measurement (known gap)

- Severity: note
- Area: cookie-consent
- Env: local
- Bucket: 002
- Bucket why: Specified as S02a; do not glue into 001.K.
- Constitution: S-SITE-QUEUE
- Docs: n/a

**Evidence**

`resources/js/cookie-consent.js` never loads a third-party tracker. Cookie copy still says analytics are inactive (`lang/it/site.php`, `LegalPagesContent.php`). HTTP site works without measurement.

**Impact**

Staff cannot see visits until 002.

---



## F-016 — No search titles, descriptions, or URL index (known gap)

- Severity: note
- Area: public-journeys
- Env: local
- Bucket: 003
- Bucket why: S02b SEO foundation.
- Constitution: S-SITE-QUEUE
- Docs: n/a

**Evidence**

`resources/views/layouts/app.blade.php` has viewport/csrf only; no description, canonical, or hreflang. No sitemap/robots in repo.

**Impact**

Organic/share snippets are generic until 003.

---



## F-017 — No membership landing for Ad Grants sitelink “Diventa socio” (known gap)

- Severity: note
- Area: public-journeys
- Env: production **exists**
- Bucket: 004 (verify/harden, **do not create**)
- Bucket why: Production `https://safehouse.community/it/diventa-socio` is published (key `diventa-socio`, template `landing`). Seeder gap is local-only.
- Constitution: S-NO-PUBLIC-ACCOUNTS (do not invent logins)
- Docs: [https://support.google.com/nonprofits/answer/9314402](https://support.google.com/nonprofits/answer/9314402)
- Status: remapped 2026-09-13 in 001.1

**Evidence**

Production inspect: page id 13, published, slug `diventa-socio` (it + en). Local PageSeeder still omits socio until 001.1 import.

**Impact**

004 is ads-safe review of the **existing** URL, not a new membership product.

---



## F-018 — No Satispay / 5×1000 campaign creatives (known gap)

- Severity: note
- Area: public-journeys
- Env: both
- Bucket: 005
- Bucket why: Owner 2026-09-13: Satispay = QR in HTML/CSS banner only. 5×1000 stays the **header link** — no extra banner. Page `/it/donations/5-per-thousand` already 200.
- Constitution: S-STRIPE (do not add a second processor here)
- Docs: n/a
- Status: remapped 2026-09-13 in 001.1

**Evidence**

Production home has 5×1000 in the header, no Satispay string. No campaign-creative manager.

**Impact**

005 must not build a 5×1000 banner. Satispay is a QR banner in page markup.

---



## F-019 — Cookie/privacy texts still describe analytics as inactive (known gap)

- Severity: note
- Area: cookie-consent
- Env: local
- Bucket: 006
- Bucket why: Full operational rewrite is S04; 002 may patch the false sentence when a tool goes live.
- Constitution: S-GDPR
- Docs: [https://www.garanteprivacy.it/home/docweb/-/docweb-display/docweb/9677876](https://www.garanteprivacy.it/home/docweb/-/docweb-display/docweb/9677876)

**Evidence**

Legal pages and banner notes state analytics are not loaded. That matches today’s behaviour (F-015). After 002 they must change (006 owns the full text).

**Impact**

None until measurement exists; then stale copy becomes a defect.

---



## F-020 — Stripe currently prefers Checkout Sessions; constitution keeps Payment Element

- Severity: note
- Area: security-payments
- Env: local
- Bucket: `later-checkout` (not 001.1, not 001.2)
- Bucket why: Owner chose a later Checkout Sessions migration. `S-STRIPE` stays Payment Element until that spec amends the locked row — otherwise current checkout becomes non-compliant overnight.
- Constitution: S-STRIPE / FR-006
- Docs: [https://docs.stripe.com/payments/payment-element](https://docs.stripe.com/payments/payment-element) · [https://docs.stripe.com/payments/checkout](https://docs.stripe.com/payments/checkout)
- Status: remapped 2026-09-13 in 001.1

**Evidence**

Checkout uses `stripe.elements` + `elements.create('payment')` in `resources/views/donations/show.blade.php` and `StripePaymentService` PaymentIntents/subscriptions. Official PE page (2026-09-13) recommends Checkout Sessions over Payment Intents.

**Impact**

None unless the owner later amends S-STRIPE.

---



## Area coverage


| Area              | Outcome                                                                                        | Checked    | Env   |
| ----------------- | ---------------------------------------------------------------------------------------------- | ---------- | ----- |
| security-payments | findings F-002, F-005, F-009, F-010, F-020                                                     | 2026-09-13 | local |
| personal-forms    | findings F-004                                                                                 | 2026-09-13 | local |
| cookie-consent    | findings F-011, F-015, F-019                                                                   | 2026-09-13 | local |
| staff-cms         | findings F-013; path `/cms-safehouse` 200, `/admin` 404                                        | 2026-09-13 | local |
| public-journeys   | findings F-006, F-016, F-017, F-018; donate/volunteer/contact/about/news/5×1000 200            | 2026-09-13 | local |
| i18n              | findings F-001, F-003, F-014                                                                   | 2026-09-13 | local |
| architecture      | findings F-007, F-008, F-012                                                                   | 2026-09-13 | local |
| secrets-repo      | F-seeder: Stripe **test** keys in `database/seeders/data/local-integrations.php` (001.1 strips working tree; history kept) | 2026-09-13 | local |
| crm-integration   | **none found** (PrimaNota field names used by ingest exist in CRM `entityDefs/PrimaNota.json`) | 2026-09-13 | local |




### secrets-repo — Stripe test keys in seeder (corrected 001.1)

- Env: local
- Evidence: Working tree file `database/seeders/data/local-integrations.php` committed in `777c237` contained `pk_test_` / `sk_test_` (GitHub secret scanning). Not live. Owner: remove from working tree, do not rewrite history. `.env` remains gitignored. CMS secrets use Laravel encryption ([encryption](https://laravel.com/docs/13.x/encryption)).



### crm-integration — none found

- Env: local
- Evidence: Read `nonprofit-espocrm` `PrimaNota.json`: `donationPaymentReference`, `donationDonorCategory`, `donationPaymentProvider`, `commissionAmount`, `internalClassification` exist. Site ingest uses those names (`DonationIngestService`). No invented aliases. CRM **writes** would be `other-repo` — none required for this audit.



### staff-cms — additional pass notes

Filament `AdminPanelProvider` `path(env('FILAMENT_PATH', 'cms-safehouse'))`. `tests/Feature/FilamentPanelTest` and `RbacTest` encode path + roles (`HasRoles`, [https://spatie.be/docs/laravel-permission/v8/introduction](https://spatie.be/docs/laravel-permission/v8/introduction)). Login 200 on `/cms-safehouse/login`.

### personal-forms — additional pass notes

Volunteer/contact: Form Requests, hashed IP+UA, honeypot `company`, throttles. Consent POST `throttle:gdpr`. Card data not stored (PE + Stripe). Mock complete endpoint 404 when mock off.

### cookie-consent — additional pass notes

Banner + preferences exist; `essential` vs `all`; no tracker script. `CookieConsentTest` covers store + throttle.

---



## HTTP probe (DDEV, 2026-09-13)

Production not fetched (owner did not approve Cursor-browser/production this turn).


| Path                                                                                                                                                              | Code       |
| ----------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------- |
| `/it` `/en`                                                                                                                                                       | 200        |
| `/ru`                                                                                                                                                             | 404        |
| `/it/donations` `/it/donations/5-per-thousand` `/it/volunteers` `/en/volunteers` `/it/about-us` `/it/news` `/it/contact` `/it/privacy-policy` `/it/cookie-policy` | 200        |
| `/it/landing-example`                                                                                                                                             | 200 (demo) |
| `/cms-safehouse/login`                                                                                                                                            | 200        |
| `/admin`                                                                                                                                                          | 404        |


