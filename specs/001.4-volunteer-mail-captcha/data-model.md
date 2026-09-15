# Data model: Volunteer mail + captcha layout (001.4)

No volunteer-application table after this feature. Transient payload exists only in validated request state and outbound mail.

## Volunteer application (transient)

| Field | Required | Rules |
| :--- | :--- | :--- |
| `name` | yes | First name. String, max 255. Existing form name. |
| `last_name` | yes | New. String, max 255. |
| `email` | yes | Email, max 255. Staff Reply-To and applicant To. |
| `phone` | yes | String, max 50 (was optional). |
| `message` | yes | String, max 5000 (was optional). |
| `gdpr_consent` | yes | Accepted. **Not** persisted (no volunteer row; no new `gdpr_consents` row in this feature). |
| `company` | honeypot | If filled: success flash, **zero** mail. |
| `cf-turnstile-response` | if challenge on | Existing `TurnstileVerifier` gate. |

## Staff notification

| Attribute | Value |
| :--- | :--- |
| To | `matteo.grossi@safehouse.community` (`config/volunteer.php`) |
| Subject | `Nuova candidatura volontario` |
| Language | Italian always |
| Body | Spec template: *Abbiamo ricevuto…* Nome, Cognome, Indirizzo email, Tel., Messaggio, signature `Website \| Safe House` |
| Reply-To | Applicant email + display name `name last_name` |
| From | Existing website From (`OutboundMailConfigurator::applyForSportello`) |

## Applicant acknowledgement

| Attribute | Value |
| :--- | :--- |
| To | Applicant email |
| Language | Public locale of the POST (`it` or `en`) |
| Meaning | Received; we will contact you soon; do not reply |
| Reply-To | Must not point at Matteo (no-reply notice) |

## Removed store

Table `volunteers` (created `2026_06_29_000004_create_volunteers_table`) is dropped on environments that run the new migration. Columns `ip_hash` / `user_agent_hash` go away with it — volunteer PII is no longer stored locally.

Contact submissions and cookie consents are unchanged.

## Bot-challenge display (not a new entity)

Existing `turnstile.*` settings (001.3). This feature only changes widget **presentation**: size flexible, theme from `html[data-theme]`, centred/full-width in the form column.

## State

```text
invalid / honeypot / throttled / captcha fail
  → no mail, no success (except honeypot: fake success, no mail)
SMTP missing or send throws
  → no success flash; back with error
both mailables sent
  → success flash; no DB row
```
