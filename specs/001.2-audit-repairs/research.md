# Research: Audit repairs (001.2)

**Date**: 2026-09-15

## F-002 — Webhook signature fail-closed 4xx

- **Decision**: Catch Stripe signature/payload failures around `constructWebhookEvent` and return **400** with a generic body (`Invalid signature`). Keep `RuntimeException` → 400 for missing secret/client. After a verified event, CRM ingest failures stay **502**. Log via `report()` / `Log`; do not put library exception text in the HTTP body.
- **Rationale**: Stripe PHP `Webhook::constructEvent` throws `\Stripe\Exception\SignatureVerificationException` (bad signature) and `\UnexpectedValueException` (invalid payload), not `RuntimeException`. Uncaught throwables become 500; Stripe retries 5xx ([signatures](https://docs.stripe.com/webhooks/signatures)). Controller today only catches `RuntimeException`.
- **Alternatives considered**: Catch `Throwable` for the whole action (would turn CRM 502 into 400 and stop retries). Map only `SignatureVerificationException` and let invalid JSON 500 (still a retry storm). Rejected.
- **Local listen**: PHPUnit does **not** need `stripe listen`. If a human/sandbox test is required, the implementer MUST write in owner chat first (FR-003). Do not change the live Dashboard endpoint.

## F-003 / F-009 — Donate copy and safe checkout JSON

- **Decision**: Move Italian literals used as `__()` keys into `lang/it/site.php` and `lang/en/site.php` (`site.donations.*`). Cover `CreateDonationIntentRequest`, `donations/show.blade.php`, `donations/privacy.blade.php`. Checkout/mock `RuntimeException` responses use `__('site.donations.checkout_failed')` after `report($exception)`. Inactive campaign 404 uses a translated `site.donations.campaign_inactive`.
- **Rationale**: Constitution VII: zero hardcoded visitor strings. FR-005: donors must never see raw provider/CRM text. Existing `DonationCheckoutTest` asserts the raw English RuntimeException — update that assertion.
- **Alternatives considered**: Map each Stripe error to a distinct donor sentence (YAGNI; leaks categories). Checkout Sessions (forbidden, S-STRIPE).

## F-004 — Volunteer Turnstile

- **Decision**: Copy the contact Form Request pattern into `StoreVolunteerRequest` ([Form Requests](https://laravel.com/docs/13.x/validation#form-request-validation)): `cf-turnstile-response` required-if enabled, `TurnstileVerifier::verify` in `withValidator`. Widget + script on volunteer views when `TurnstileVerifier::enabled()`, same as contact. Reuse `site.pages.contact_captcha*` copy (same challenge). When disabled, honeypot + throttle unchanged.
- **Rationale**: One site-wide challenge toggle already exists in CMS. Volunteer currently has only honeypot + `throttle:volunteers`.
- **Alternatives considered**: Always-on Turnstile (breaks local UAT when keys absent). Separate volunteer-only flag (splits staff mental model).
- **Production inspect**: Public GET of live contact HTML for `cf-turnstile` / `challenges.cloudflare.com`. SSH only if HTML is insufficient, using [ssh-allowlist](../001.1-prod-parity-replan/contracts/ssh-allowlist.md). No writes, no `.env`.

## F-005 — X-Frame-Options

- **Decision**: Keep PHP `DENY`. Change `deploy/Caddyfile.snippet` from `SAMEORIGIN` to `DENY`. Do **not** run `apply-caddy-site-once.sh` or reload production Caddy. Do **not** add CSP/HSTS in PHP ([Caddy header](https://caddyserver.com/docs/caddyfile/directives/header); Caddy’s own example uses `DENY`).
- **Rationale**: One policy in the repo. `DENY` is the stricter clickjacking default and already shipped in `SecurityHeaders`. Live edge remains owner-ops (FR-008).
- **Alternatives considered**: Change PHP to `SAMEORIGIN` (weaker, only useful if we embed the public site in our own iframe — we do not). Apply the full snippet now (would also ship HSTS/CSP/CMS allowlist — out of scope).

## F-007 — HomeController injection

- **Decision**: Constructor-inject `PageService`. Keep `app()->getLocale()` (locale is request context, not a collaborator).
- **Rationale**: Constitution VII. Other HTTP controllers already inject.

## F-008 — Empty PrimaNota catch

- **Decision**: Replace empty `catch (\Throwable)` in `refreshFromPrimaNotaId` reload and settlement expand with `Log::warning` including PrimaNota id / PaymentIntent id and `$exception->getMessage()` — keep the same fallback behaviour (previous row / raw PI). Do not change CRM field names. Sibling `paymentStatus` enum already matches this app.
- **Rationale**: Constitution XII: expected failure must log; caller of webhook `applyFromStripeEvent` already treats handled events as 200.
- **Alternatives considered**: Re-throw (would 502 more often on transient CRM read blips). Touch `SiteSetting` decrypt catch (out of spec).

## F-010 — CSRF except path

- **Decision**: Use Laravel 13 `preventRequestForgery(except: ['api/webhooks/stripe'])` ([CSRF excluding URIs](https://laravel.com/docs/13.x/csrf)). If the installed framework only exposes `validateCsrfTokens`, use that method with the **same** except path. Signature verification remains the real control. API routes normally omit CSRF; the except is defense in depth if the route is later moved onto `web`.
- **Rationale**: Current except `webhooks/stripe` does not match `POST /api/webhooks/stripe`.
- **Alternatives considered**: Delete the except entirely (accurate today, silent trap if the route moves to `web.php`).

## F-011 — Consent hashes

- **Decision**: Add `gdpr_consents.user_agent_hash` nullable `string(64)`. `GdprConsentService` writes `ip_hash` and `user_agent_hash` via `ContactSubmissionService::hashIp` / `hashUserAgent`. Change those helpers to `hash_hmac('sha256', $value, (string) config('app.key'))` so volunteer, contact, and consent share HMAC. Hex length stays 64. Historical unsalted SHA-256 rows are not rewritten.
- **Rationale**: Constitution VI: volunteer/contact store both hashes. F-011 called out unsalted IP rainbow tables. Spec FR-012: HMAC if it is the house rule — this feature makes HMAC the house rule in one helper.
- **Alternatives considered**: HMAC only on consent (two algorithms). Dedicated hashing secret in CMS (new secret surface).

## F-012 — Trusted proxies

- **Decision**: Default `trustProxies` to loopback + RFC1918 CIDRs, not `*`. Optional `TRUSTED_PROXIES` env: `*` restores today’s behaviour; comma-separated list overrides. Document in `.env.example`. Headers stay Laravel defaults ([trusted proxies](https://laravel.com/docs/13.x/requests#configuring-trusted-proxies)).
- **Rationale**: PHP-FPM must not be public. Caddy unix/`127.0.0.1` and DDEV `172.16/12` are trusted. A client hitting PHP without a proxy cannot spoof `X-Forwarded-For` for throttles and IP hashes.
- **Alternatives considered**: Keep `*` and only document (weaker). Trust only `127.0.0.1` (breaks DDEV nginx→php on docker IPs).

## F-013 — CMS last-error file

- **Decision**: Write exception class, truncated message (redact `sk_live_`, `sk_test_`, `whsec_`, `Bearer `, `rk_`), file, and line. **No** `getTraceAsString()`. Keep `storage/logs/cms-last-error.txt` and `CmsHealthCommand` consumer.
- **Rationale**: Constitution VI: MUST NOT log secrets. Stacks often include request/config fragments.
- **Alternatives considered**: Delete the dump (breaks `site:cms-health`). Laravel log only (staff already use the file during CMS 500s).

## F-014 — Title suffix

- **Decision**: `resources/views/layouts/app.blade.php` uses `__('site.layout.title_suffix')` ([Blade](https://laravel.com/docs/13.x/blade)). Italian and English values: `— Safe House` (brand name is identical; the string is still translated).
- **Rationale**: FR-015. Do not change wordmark alt text in this feature (not in spec).
