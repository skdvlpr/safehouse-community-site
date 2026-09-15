# Contract: queue remap after owner UAT of S01

Owner answers 2026-09-13. Implement of 001.1 rewrites the target specs to match this table. Code repairs wait for `001.2` except content/governance in 001.1.

| Finding | Owner | Bucket | Spec change |
| :--- | :--- | :--- | :--- |
| F-001 Russian locale | Remove Russian from site and constitution | `001.1` then closed | S-I18N it+en; 002–006 drop ru |
| F-002 webhook 4xx | Fix carefully; CRM statuses work; test local first; say when to raise Stripe listen | `001.2` | Not 001.1 |
| F-003 donor strings | Make translation-ready | `001.2` | it+en only |
| F-004 volunteer Turnstile | Fix | `001.2` | Also re-check production contact (widget absent 2026-09-13) |
| F-005 X-Frame mismatch | Fix | `001.2` + `owner-ops` | PHP/Caddy consistency; applying live snippet needs root |
| F-006 demo landing | Local copies OK; prod demo unpublished; socio at `/it/diventa-socio` | `001.1` | Local unpublish demo; import socio/faq |
| F-007 HomeController DI | Fix | `001.2` | |
| F-008 empty catch | Fix; inspect prod logs | `001.2` | Prod log note in inspect contract |
| F-009–F-014 | Fix | `001.2` | |
| F-015 no analytics | Fix in analytics spec | `002` | Drop ru from cookie sentences |
| F-016 no SEO | Fix in SEO spec | `003` | it+en |
| F-017 membership | Verify prod socio URL | `004` | Do **not** create a new page; harden existing |
| F-018 banners | Satispay = QR HTML/CSS banner; 5×1000 header link only | `005` | No 5×1000 banner |
| F-019 cookie texts | Fix with GDPR spec | `006` | After 002 |
| F-020 Checkout vs PE | Migrate to Checkout Sessions later | `later-checkout` | Keep `S-STRIPE` until that spec; [Checkout](https://docs.stripe.com/payments/checkout) |
| Secrets in seeder | Remove from working tree; no history rewrite | `001.1` | `local-integrations.php` |
| Live Caddy gap / public CMS login | Recorded 2026-09-13 | `owner-ops` | Community vhost has no HSTS/CSP/CMS allowlist ([Caddy header](https://caddyserver.com/docs/caddyfile/directives/header)); `/cms-safehouse/login` is 200 from the public internet. Applying `deploy/Caddyfile.snippet` needs root — not 001.1 |
| Webhook signature ERROR counts | Recorded | `001.2` | `SignatureVerificationException` ×270, «No signatures found» ×180, 90 ERROR lines; 0 `StripeWebhook` ERROR lines. Repair fail-closed 4xx locally first ([Stripe signatures](https://docs.stripe.com/webhooks/signatures)) |

## Serial order after 001.1 UAT

1. `/speckit-constitution` S-I18N (done inside 001.1 implement)
2. `001.2` audit repairs (specify if only an outline exists)
3. `002` consent-gated analytics
4. `003` public SEO
5. `004` Ad Grants landings (existing socio)
6. `005` Satispay QR banner
7. `006` operational GDPR
8. New spec: Stripe Checkout Sessions + S-STRIPE amend

## Explicit non-goals of 001.1 implement

- Webhook signature handling
- Payment Element → Checkout Sessions
- Production Caddy reload
- `ddev stop` / extra DDEV services
