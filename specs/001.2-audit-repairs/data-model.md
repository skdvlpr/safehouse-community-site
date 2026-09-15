# Data model: Audit repairs (001.2)

No new aggregate. One column and one hashing rule.

## Consent audit row (`gdpr_consents`)

Existing table. This feature adds:

| Field | Type | Rules |
| :--- | :--- | :--- |
| `user_agent_hash` | `string(64)`, nullable | HMAC-SHA256 hex of `User-Agent`, or null when UA empty. Never store raw UA. |

Existing `ip_hash` (`string(64)`, required): continue required; value produced by the same HMAC helper (not unsalted SHA-256). Fallback `hash_hmac` of `'unknown'` remains if IP is missing.

Fillable: `consent_type`, `granted`, `ip_hash`, `user_agent_hash`, `consented_at`. Still no `ip` / `user_agent` columns.

## Shared fingerprint helpers

`ContactSubmissionService::hashIp` / `hashUserAgent`:

- Input null/empty → null
- Else `hash_hmac('sha256', $value, (string) config('app.key'))` (64 hex chars)
- Used by volunteer, contact, and cookie consent

Volunteer and contact tables already have `ip_hash` and `user_agent_hash`. No migration there. Old rows stay unsalted SHA-256 until naturally replaced.

## Payment notification (not stored)

Inbound `POST /api/webhooks/stripe`. Trust boundary is Stripe signature verification. Invalid/missing signature: no donation ingest, no PrimaNota write.

## CRM (read-only sibling)

`PrimaNota.paymentStatus` enum is unchanged: Planned, Inviato, Cancelled, Refunded, Disputed, Problematic. This feature does not add CRM fields.
