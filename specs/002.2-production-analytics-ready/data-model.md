# Data model: Production analytics ready (002.2)

No new database tables.

## Contact conversion names

| Desk key (form `desk`) | Conversion name | Italian label (CMS default) |
| :--- | :--- | :--- |
| `generic_desk` | `contact_generic_success` | Richiesta generica |
| `legal_desk` | `contact_slegale_success` | Sportello legale |
| `digital_desk` | `contact_sdigitale_success` | Sportello digitale |

- Session key remains `MeasurementBootService::SESSION_CONVERSION`.
- Value MUST be exactly one of the three names above, or the key MUST be absent (honeypot / unknown desk).
- Visitor flash `contact_success` (thank-you sentence) is **unchanged** — that is UI copy, not the GA4 name.

## JS allowlist

`resources/js/measurement.js` `CONVERSION_EVENTS` MUST include the three names and MUST NOT include `contact_success`. Keep donate + volunteer names from 002.

## Edge allowlist (not stored in DB)

Public CSP string on apex + www. CMS path excluded. Hosts: see [contracts/caddy-csp.md](./contracts/caddy-csp.md).
