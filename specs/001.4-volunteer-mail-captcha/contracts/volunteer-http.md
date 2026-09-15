# HTTP + mail contract: volunteer form (001.4)

Cite: [Form Requests](https://laravel.com/docs/13.x/validation#form-request-validation), [sending mail](https://laravel.com/docs/13.x/mail#sending-mail).

CMS path unchanged (`S-CMS-PATH`). Public volunteer:

| Method | Path | Result |
| :--- | :--- | :--- |
| GET | `/{locale}/volunteers` | Form including last name; challenge widget if 001.3 complete |
| POST | `/{locale}/volunteers` | Validate → two emails or errors |

Locales: `it`, `en` only.

## POST success

- Redirect to volunteer show with `volunteer_success`.
- Mail 1: To `matteo.grossi@safehouse.community`, subject `Nuova candidatura volontario`, Italian body per [data-model.md](../data-model.md).
- Mail 2: To applicant, acknowledgement in POST locale.
- `volunteers` table absent (after migrate).

## POST validation fail

Redirect back with errors. `Mail::assertNothingSent` in tests.

Required: `name`, `last_name`, `email`, `phone`, `message`, `gdpr_consent`. Turnstile token required-if verifier enabled.

## Honeypot

`company` filled → redirect + success flash, nothing sent.

## SMTP / transport failure

Redirect back, translated error (not a stack). No success flash.

## Unchanged

Throttle name `volunteers`. Contact POST/sportelli CRM link. Captcha Settings keys.
