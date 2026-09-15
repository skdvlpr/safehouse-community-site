# Quickstart validation: CMS captcha settings (001.3)

Local only: `https://safehouse-community-site.ddev.site`. Do not `ddev stop`. Do not `php artisan config:cache`. Do not write production ([owner-ops](./contracts/owner-ops.md)).

## Prerequisites

- DDEV running; CMS login as super-admin
- Local Turnstile keys already in `site_settings` (2026-09-15) unless testing the off path
- Official checks: [Laravel testing](https://laravel.com/docs/13.x/testing), [Pint](https://laravel.com/docs/13.x/pint)

## Automated

```bash
ddev exec php artisan test
ddev exec ./vendor/bin/pint --test
```

Expect (if the proposed test is kept at tasks): blank secret save does not clear `turnstile.secret_key`; incomplete keys ⇒ verifier disabled. Existing volunteer Turnstile tests still pass.

## Manual (staff screen)

See [cms-ui.md](./contracts/cms-ui.md).

1. `/cms-safehouse` → Impostazioni → **Captcha** (not inside Sportelli).
2. Toggle + site key + secret as today; save with secret blank → public volunteer still challenges.
3. Sportelli has no captcha tab; desks/mail still save.
4. `/it/contact`, `/en/contact`, `/it/volunteers`, `/en/volunteers`: widget present when complete; absent when toggle off.
5. Volunteer/contact submit without widget token stores no row when complete.
6. View-source: public site key may appear; secret must not.
7. Confirm no production CMS/Caddy/DNS change.

Owner combined UAT checklist is written at implement (`checklists/owner-user-tests.md`) with a Russian numbered script in chat.
