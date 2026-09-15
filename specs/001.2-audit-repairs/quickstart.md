# Quickstart validation: Audit repairs (001.2)

Local only: `https://safehouse-community-site.ddev.site`. Do not `ddev stop`. Do not `php artisan config:cache`.

## Prerequisites

- DDEV project running
- `ddev exec php artisan migrate` after the consent-hash migration
- Official checks: [Laravel testing](https://laravel.com/docs/13.x/testing), [Pint](https://laravel.com/docs/13.x/pint)

## Automated

```bash
ddev exec php artisan test
ddev exec ./vendor/bin/pint --test
```

Expect: bad-signature webhook tests **400**; donate English validation keys resolve; checkout 422 message is the translated generic string; volunteer without Turnstile token is rejected when enabled; cookie consent stores `user_agent_hash`.

## Manual (owner UAT)

See `checklists/owner-user-tests.md` (written at implement) and the Russian numbered script in chat.

1. `/en/…` donate campaign: empty contact → English help/validation.
2. `/it/…` donate: Italian.
3. Force checkout failure in mock mode → generic translated error, not Stripe/CRM English internals.
4. CMS Turnstile on → volunteer submit without widget token does not store a row. Off → volunteer still works.
5. Any public page title ends with the translated suffix.
6. Confirm the agent did not ask you to reload production Caddy.

## Stripe CLI (optional)

Skip until credentials exist. If used: implementer must have announced it in chat first ([owner-ops](./contracts/owner-ops.md)). Forward to local `https://safehouse-community-site.ddev.site/api/webhooks/stripe`. Unsigned POST → 400. Signed test event → 200 as today.

## Production contact (inspect)

`curl -sS https://safehouse.community/it/contact | grep -E 'cf-turnstile|challenges.cloudflare.com'` — record yes/no. No writes.
