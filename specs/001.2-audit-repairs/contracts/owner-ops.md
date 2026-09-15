# Owner-ops and safety: Audit repairs (001.2)

## Stripe listen / sandbox (FR-003)

Before any local test that needs a **real** signed Stripe event (`stripe listen --forward-to …` or Dashboard sandbox):

1. Write in owner chat that listen/sandbox is about to be used.
2. Do not assume the CLI is already logged in.
3. Do not change the **live** webhook URL or signing secret.

PHPUnit bad-signature tests MUST use `Webhook::constructEvent` (or the real `StripePaymentService` with a test `whsec_`) and MUST NOT require the CLI.

## Production Caddy (FR-008)

- Git: `deploy/Caddyfile.snippet` `X-Frame-Options` = `DENY`.
- Agent MUST NOT reload Caddy, MUST NOT run `deploy/apply-caddy-site-once.sh`, MUST NOT enable HSTS/CSP on the live vhost in this feature.
- Owner applies the snippet later under root.

## Production contact widget (FR-007)

Inspect-only. Prefer public HTTPS GET of `https://safehouse.community/it/contact` (or the live contact slug) and record whether `cf-turnstile` / `challenges.cloudflare.com` appears.

If SSH is needed: [ssh-allowlist](../../001.1-prod-parity-replan/contracts/ssh-allowlist.md). Identity `~/.ssh/safehouse-deploy`. No `.env`, no writes.

## Out of scope (must be absent from the change set)

- Checkout Sessions / Payment Element replacement
- Production webhook Dashboard edits
- HSTS / CSP / CMS IP allowlist apply
- Specs `002`–`008`
- Git history rewrite
- Campaign sync preview ↔ live
