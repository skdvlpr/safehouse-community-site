# Owner-ops: Production analytics ready (002.2)

Cite: [Caddy `header`](https://caddyserver.com/docs/caddyfile/directives/header), [GTM CSP](https://developers.google.com/tag-platform/security/guides/csp), [Realtime](https://support.google.com/analytics/answer/9271392), [DebugView](https://support.google.com/analytics/answer/7201382), [Preview](https://support.google.com/tagmanager/answer/6107056).

## Before implement UAT

1. Owner: GTM tags per [gtm-owner.md](./gtm-owner.md) → **Publish**.
2. Owner: live CMS Analytics toggle **on** + valid `GTM-…` (already said done).
3. Implementer: merge CSP into `deploy/Caddyfile.snippet`, commit only if asked, **then** apply.

## Live apply (root)

After git on the server has the new snippet:

```bash
sudo bash /var/www/safehouse-community-site/deploy/apply-caddy-site-once.sh
```

Expect: backup `/etc/caddy/Caddyfile.bak-*`, validate, reload, script **deleted**.

If implement SSH cannot sudo, owner runs that one line as root. Do not `ddev stop`. Do not `php artisan config:cache`.

## Optional legal sync (measurement on)

```bash
cd /var/www/safehouse-community-site
php artisan site:sync-legal-pages --force
```

Operational cookie/privacy only. Not a DPA.

## Combined UAT (one round)

Ad blockers off. Chrome. Live origin.

1. GTM Preview → Connect `https://safehouse.community/it` (or skip Preview and use Realtime only).
2. Accetta tutti → Network `gtm.js` + collect; Realtime page_view within 5 minutes.
3. One-time donate thank-you → `donate_success`.
4. Recurring thank-you → `donate_recurring_success`.
5. `/it/volunteers` real → `volunteer_success`.
6. Contact three desks → three new names.
7. Essentials / X → no `gtm.js`.
8. `/cms-safehouse/login` still usable.

Home zeros **pass**. Cursor-browser: offer, wait.

## After UAT

Return to 002 remaining Google Admin ticks (Signals off, second admin) if still open. Do not start `003` until owner says.
