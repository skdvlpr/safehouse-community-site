# Research: 001 stack-compliance-audit

All Technical Context unknowns resolved. Official pages opened 2026-09-13.

## Decision: Deliverable is markdown, not a CMS resource

- **Decision**: Ship `findings.md` + coverage table in this spec directory. Do not add Filament pages, models, or migrations.
- **Rationale**: FR-004 forbids behaviour change. YAGNI (constitution VII). Owner already reads `specs/`.
- **Alternatives considered**: Filament “Audit” resource (behaviour change); JSON in DB (migration); Notion (retired, S-NO-NOTION).

## Decision: Environments

- **Decision**: Always inspect **local DDEV**. Inspect **production** only if the owner agrees that turn (constitution VIII Cursor-browser wait). Label every finding `env: local | production | both`. Seed/demo pages are not production facts unless confirmed.
- **Rationale**: Spec edge case on seed vs live content.
- **Alternatives considered**: Production-only (misses local secrets/config); silent production crawl (forbidden).

## Decision: No new automated tests

- **Decision**: `test: no` for this feature. After implement, run existing `ddev exec php artisan test` and `ddev exec ./vendor/bin/pint --test` as a **regression that the app tree was not modified**.
- **Rationale**: Principle V — no tests without new logic. Spec SC-003 is zero behavioural change.
- **Alternatives considered**: Snapshot tests of the register (brittle); “HTTP 200” probes as PHPUnit (stupid tests).

## Decision: Official-docs bar (not OWASP-as-law)

- **Decision**: Each security/architecture finding MUST cite constitution principle/locked ID **and/or** an official URL opened in the implement session. Do not invent a second security standard.
- **Rationale**: Principle III. Docs opened for this plan:
  - Laravel CSRF / `PreventRequestForgery`: https://laravel.com/docs/13.x/csrf
  - Laravel validation / Form Requests: https://laravel.com/docs/13.x/validation
  - Laravel Eloquent mass assignment: https://laravel.com/docs/13.x/eloquent#mass-assignment
  - Laravel testing: https://laravel.com/docs/13.x/testing
  - Laravel Pint: https://laravel.com/docs/13.x/pint
  - Laravel 13 upgrade note (CSRF rename): https://laravel.com/docs/13.x/upgrade
  - Filament v4 panel path: https://filamentphp.com/docs/4.x/panel-configuration
  - Spatie Permission **v8** (composer `^8.1`): https://spatie.be/docs/laravel-permission/v8/introduction
  - Stripe Payment Element: https://docs.stripe.com/payments/payment-element
  - Stripe webhook signatures: https://docs.stripe.com/webhooks/signatures
  - Caddy `header` (HSTS/CSP belong at edge): https://caddyserver.com/docs/caddyfile/directives/header
- **Alternatives considered**: OWASP ASVS as mandatory checklist (conflicts with “official stack docs supremacy”).

## Decision: Stripe Checkout Sessions vs locked Payment Element

- **Decision**: Stripe’s current Payment Element page **recommends Checkout Sessions** over Payment Intents. Constitution **S-STRIPE** locks the existing native Payment Element integration. Treat a “migrate to Checkout Sessions” remark as **severity note**, bucket `owner-ops` or omit as a repair. MUST NOT open `001.K` to re-platform checkout.
- **Rationale**: Principle III conflict protocol + FR-006 (do not reopen locked decisions). Owner was not asked to change S-STRIPE.
- **Alternatives considered**: AskQuestion now (unnecessary — constitution already closed S-STRIPE); treat as blocker (would fork the stack).

## Decision: CSRF helper vs Laravel 13 API

- **Decision**: Audit `bootstrap/app.php` use of `validateCsrfTokens(except: ['webhooks/stripe'])` against https://laravel.com/docs/13.x/csrf (`preventRequestForgery`) and the Stripe CSRF-exclusion example. Record drift as a finding if material. The webhook lives on `routes/api.php` (`POST /api/webhooks/stripe`) and verifies via `Webhook::constructEvent` — signature failure MUST remain fail-closed (4xx), not 500.
- **Rationale**: Existing `StripeWebhookController` already returns 400 on `RuntimeException` from construct; 502 only after verified event when CRM ingest fails (retryable). Audit must distinguish those.
- **Alternatives considered**: Fix CSRF API in this feature (violates FR-004).

## Decision: CSP / extra headers

- **Decision**: Confirm CSP and HSTS are **not** set in PHP (`SecurityHeaders` currently sets frame/nosniff/referrer/permissions/COOP/CORP only; comment already defers CSP/HSTS to Caddy). Findings about missing CSP go to **edge/Caddy** (`owner-ops` if Caddyfile is outside this git) or `001.K` only if this repo’s Caddyfile is wrong — do not add CSP in Laravel middleware.
- **Rationale**: S-CSP; Caddy `header` docs.
- **Alternatives considered**: Add CSP in `SecurityHeaders` (forbidden).

## Decision: Staff CMS path

- **Decision**: Pass if panel `path` is `cms-safehouse` (not `/admin`). Filament 4 default is `/admin`; this project already overrides via `AdminPanelProvider`. Cite https://filamentphp.com/docs/4.x/panel-configuration (Changing the path).
- **Rationale**: S-CMS-PATH.
- **Alternatives considered**: None.

## Decision: CRM

- **Decision**: When reviewing donation ingest / sportello contact, **read** `/home/skoksharov/safehouse/nonprofit-espocrm` field names. Mismatch → finding bucket `other-repo` (owner decision). MUST NOT invent site-only field aliases as a “fix”.
- **Rationale**: Principle X; FR-007.
- **Alternatives considered**: Skip CRM (would miss ingest mapping holes).

## Decision: Bucket map for known gaps

| Known absence | Bucket | Not `001.K` |
| :--- | :--- | :--- |
| No audience measurement | `002` | yes |
| No search title/description/index | `003` | yes |
| Weak/missing membership landing / sitelink pack | `004` | yes |
| No Satispay/5×1000 campaign creatives | `005` | yes |
| Policy text vs live tools / withdraw UX | `006` | yes |
| Security holes, PII, webhook fail-open, `/admin`, unguarded models, empty-catch, hardcoded UI strings, broken journeys | `001.K` | — |
| Google Ads account, DPA, live credentials | `owner-ops` | yes |

Target SC-004: ≥90% of S02–S04 overlaps use the table above.

## Decision: Secrets protocol

- **Decision**: If `.env`, keys, or dumps appear in git: notify owner in chat **without pasting the secret**; finding says “secret material in path X”; severity blocker. Do not quote values in `findings.md`.
- **Rationale**: Principle VI.
- **Alternatives considered**: Redacted snippets (still risky).

## Decision: Architecture pass checklist

Inspect against constitution VII/XII and Laravel Form Request docs:

- Controllers: no `new ClassName()`, no business rules (constructor injection only).
- Mutations: Form Request + validated() (https://laravel.com/docs/13.x/validation#form-request-validation).
- Models: explicit `$fillable`; no `$guarded = []` / `#[Unguarded]` on request-facing models (https://laravel.com/docs/13.x/eloquent#mass-assignment).
- Catch blocks: no empty `catch (Throwable)` that swallows; no secret logging.
- i18n: visitor strings via `__()` / CMS, not Blade literals (except existing design tokens / brand names already content).
- RBAC: Spatie roles/permissions used for CMS, not home-grown (https://spatie.be/docs/laravel-permission/v8/introduction). Existing `tests/Feature/RbacTest.php` is evidence.

## Decision: Public-journey pass

For it/en/ru: home, donate hub, 5×1000, volunteer, contact, about, news. Record mixed locale, empty locale, demo pages looking official, broken in-page links. Prefer reproducing from the browser **after owner agrees**; otherwise static route/view/lang review labelled `env: local (static)`.

## Decision: Evidence from existing tests

Use as **pass evidence** when they already encode the control (do not re-test in new PHPUnit):

- `StripeWebhookDonationTest`, `DonationCheckoutTest`, `CookieConsentTest`, `VolunteerFormTest`, `ContactFormTest`, `FilamentPanelTest`, `SecurityHeadersTest`, `RbacTest`, `LocaleRoutesTest`.

Gaps in those tests become findings (missing coverage of a control), bucket `001.K` or note — still no tests written in 001 implement.
