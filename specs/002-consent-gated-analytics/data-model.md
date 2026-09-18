# Data model: Consent-gated audience measurement (002)

No new database tables. Measurement state is configuration + first-party consent storage + ephemeral dataLayer events.

## Measurement configuration (env / config)

| Field | Required | Rules |
| :--- | :--- | :--- |
| `enabled` | yes | Boolean from `MEASUREMENT_ENABLED`. Default **false**. |
| `container_id` | if enabled | String matching `GTM-[A-Z0-9]+`. Empty or mismatch → treat as disabled. Never logged to visitors. |

`is_bootable` = enabled AND valid container id AND not a preview/CMS request.

## Consent level (existing client + audit)

Client stores `essential` or `all` in `sh_cookie_consent` (cookie + localStorage), as today.

| Level | Analytics GTM | Necessary tools |
| :--- | :--- | :--- |
| none (first visit) | off | on |
| `essential` (including dismiss-without-accept) | off | on |
| `all` | on if `is_bootable` | on |

Server audit table `gdpr_consents` unchanged: `consent_type` `cookie_banner_essential` \| `cookie_banner_analytics`, `granted`, `ip_hash`, `user_agent_hash`, `consented_at`. No raw IP/UA columns.

## Consent mode keys (client, not stored in MariaDB)

| Key | Default | After analytics accept | After withdraw |
| :--- | :--- | :--- | :--- |
| `analytics_storage` | denied | granted | denied |
| `ad_storage` | denied | denied | denied |
| `ad_user_data` | denied | denied | denied |
| `ad_personalization` | denied | denied | denied |

## Measurement event (ephemeral)

Pushed to `dataLayer` only when consent is `all` and `is_bootable`.

| Name | When | Parameters |
| :--- | :--- | :--- |
| (GTM/GA4 page_view) | GA4 Configuration tag inside GTM, after inject | Owner-ops; no association PII |
| `donate_success` | One-time donate thank-you | none |
| `donate_recurring_success` | Recurring-campaign thank-you (first site signup) | none |
| `volunteer_success` | Volunteer **real** success flash | none |
| `contact_success` | Contact **real** success flash | none |

Forbidden on the event: name, email, phone, message, amount, payment intent id, query-string donor name.

Honeypot: visitor still sees success copy; **no** measurement event name in HTML.

## Operational policy pages (CMS)

Existing `pages` rows `key=privacy` and `key=cookie`, template `legal`, locales it+en (ru bodies in source unused publicly).

Blade status line (not a DB field): `configured` vs `not_loaded` from `is_bootable` so the published page cannot claim the opposite of the kill-switch.

## State

```text
first visit, no stored choice
  → banner visible; no GTM; necessary cookies only

dismiss / essentials
  → store essential; hide first-visit banner; no GTM

accept analytics (all) + bootable
  → store all; inject GTM; analytics_storage granted; ads keys denied

accept analytics + not bootable
  → store all; still no GTM; page does not error

withdraw to essential
  → store essential; consent keys denied; no further events

preview / CMS
  → never bootable
```
