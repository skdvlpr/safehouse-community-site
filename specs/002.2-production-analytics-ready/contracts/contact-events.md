# Contract: Contact conversion names (002.2)

Cite: [GA4 custom events](https://support.google.com/analytics/answer/12229021), [Blade](https://laravel.com/docs/13.x/blade), [Laravel testing](https://laravel.com/docs/13.x/testing).

Extends [002 conversion-events](../../002-consent-gated-analytics/contracts/conversion-events.md). After this feature, **replace** the single `contact_success` row.

| Desk key | `data-measurement-event` / dataLayer `event` |
| :--- | :--- |
| `generic_desk` | `contact_generic_success` |
| `legal_desk` | `contact_slegale_success` |
| `digital_desk` | `contact_sdigitale_success` |

`ContactSubmissionController` sets `SESSION_CONVERSION` from the validated `desk` on **real** store only. Blade prints that name on the marker. JS allowlist must match.

Honeypot: success flash stays; no `SESSION_CONVERSION`.

MUST NOT emit `contact_success` as a measurement event.

PHPUnit: three desk posts → three markers; honeypot → no marker; marker HTML contains no visitor name/email.
