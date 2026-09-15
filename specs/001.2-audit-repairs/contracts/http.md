# HTTP contracts: Audit repairs (001.2)

Cite: [Stripe signatures](https://docs.stripe.com/webhooks/signatures), [Laravel CSRF](https://laravel.com/docs/13.x/csrf), [Laravel testing](https://laravel.com/docs/13.x/testing).

## `POST /api/webhooks/stripe`

| Case | Status | Body | Side effects |
| :--- | :--- | :--- | :--- |
| Missing/empty signature, missing secret, mock mode, invalid payload, bad HMAC | **400** | Generic `Invalid signature` (or existing mock “disabled” string mapped to 400 — must not be 5xx) | No CRM write |
| Verified event, ingest/status applied | **200** | `OK` / `Ignored` / skip messages as today | Existing bookkeeping |
| Verified event, CRM ingest `RuntimeException` | **502** | `CRM ingest failed` | Retryable |

Do not return `$exception->getMessage()` from Stripe’s signature classes.

CSRF: except URI `api/webhooks/stripe` via `preventRequestForgery` (or `validateCsrfTokens` if that is the installed alias).

## `POST /api/donations/intents/{slug}`

Failure to create an intent (`RuntimeException` after `report()`): **422** JSON `{ "message": "<translated site.donations.checkout_failed>" }`. Message MUST NOT equal the exception text.

Inactive campaign: **404** JSON `{ "message": "<translated site.donations.campaign_inactive>" }`.

Validation missing email and phone: **422** with `donor_email` error = translated `site.donations.contact_required` for the request locale (`Accept-Language` / URL locale as the donate form already sends).

## `POST /{locale}/volunteers`

When Turnstile is enabled: missing/invalid `cf-turnstile-response` → redirect back with errors; **zero** `volunteers` rows.

When disabled: existing honeypot + throttle behaviour.

## `POST /{locale}/cookie-consent`

JSON 200 as today. Stored row MUST include `ip_hash` and `user_agent_hash` matching `ContactSubmissionService` helpers for that request.

## Public document title

`GET /it/…` and `GET /en/…` `<title>` suffix is `__('site.layout.title_suffix')`, not a hardcoded `— Safe House` in the layout.
