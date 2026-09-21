# 041 — Implement 002.2 production analytics ready

**Date**: 2026-09-21

**Active spec:** `specs/002.2-production-analytics-ready`

Owner GTM published (three contact Event tags; `contact_success` retired) before this implement.

Official this implement: [Caddy header](https://caddyserver.com/docs/caddyfile/directives/header), [GTM CSP](https://developers.google.com/tag-platform/security/guides/csp), [custom events](https://support.google.com/analytics/answer/12229021), [Blade](https://laravel.com/docs/13.x/blade), [testing](https://laravel.com/docs/13.x/testing), [Pint](https://laravel.com/docs/13.x/pint), [Realtime](https://support.google.com/analytics/answer/9271392), [Preview](https://support.google.com/tagmanager/answer/6107056), [DebugView](https://support.google.com/analytics/answer/7201382).

## Done in git working tree (not committed unless owner asks)

- Contact desks → `contact_generic_success` / `contact_slegale_success` / `contact_sdigitale_success`
- `contact_success` no longer a measurement event (UI flash key unchanged)
- `deploy/Caddyfile.snippet` Preview + GA4-without-Ads hosts; no doubleclick
- PHPUnit 361 passed, 2 skipped; Pint dirty PHP PASS

## Not done from this session

- **T007 live apply**: SSH `deploy@77.81.234.138` → Permission denied (publickey). Snippet not on VPS until git deploy.
- **T008** legal sync: skipped (optional; no SSH; seeder still names `contact_success` in cookie copy)
- No git commit/push unless owner asks
- No `003`

## Remaining root (after CI deploy of this snippet)

```bash
sudo bash /var/www/safehouse-community-site/deploy/apply-caddy-site-once.sh
```

Do not edit CMS allowlist IPs. Script self-deletes on success.
