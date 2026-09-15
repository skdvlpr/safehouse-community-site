# Owner-ops: Volunteer mail + captcha layout (001.4)

Cite: [DDEV CLI](https://ddev.readthedocs.io/en/stable/users/usage/cli/) (`ddev exec`), [migrations drop](https://laravel.com/docs/13.x/migrations#dropping-tables).

## Agent MUST NOT (this specify/plan/tasks/implement)

- `git push`, production migrate, production `.env`, production CMS
- `DROP TABLE` on live `volunteers` now
- Inspect or export live volunteer rows
- Reload production Caddy / apply HSTS/CSP
- Onboard Cloudflare DNS/zone
- `ddev stop`, `php artisan config:cache` in DDEV
- Write `/home/skoksharov/safehouse/nonprofit-espocrm`
- Change captcha Settings keys or Stripe webhook URL

## Local preview (implement session)

- `ddev exec php artisan migrate` after the drop migration — **preview** table gone
- Prove volunteer POST → Mailpit / SMTP: Matteo + applicant
- Prove widget layout on contact + volunteer; theme toggle or reload
- Do not wipe local Turnstile keys

## When the owner later asks to publish this feature

1. Deploy the same git revision (owner-triggered push).
2. Run production migrate so `volunteers` is dropped.
3. **Do not** open, dump, or review that table first (owner instruction 2026-09-15).
4. Confirm live SMTP still sends; Matteo inbox is the configured address.

## Follow-up (not 001.4, not `002`)

- CRM volunteer ingest after CRM is ready (`S-CROSS-REPO` read then).
