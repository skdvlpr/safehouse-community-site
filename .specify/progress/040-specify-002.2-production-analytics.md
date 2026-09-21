# 040 — Specify/plan/tasks 002.2 production analytics ready

**Date**: 2026-09-21

**Active spec:** `specs/002.2-production-analytics-ready`

Owner interrupt of 002 UAT: live CSP so Realtime can work; three contact conversion names; combined UAT. Cycle **stopped after tasks**. No implement, no Caddy reload, no git commit unless asked.

Official: [Caddy header](https://caddyserver.com/docs/caddyfile/directives/header), [GTM CSP](https://developers.google.com/tag-platform/security/guides/csp), [Realtime](https://support.google.com/analytics/answer/9271392), [custom events](https://support.google.com/analytics/answer/12229021), [GTM publish](https://support.google.com/tagmanager/answer/6107163), [testing](https://laravel.com/docs/13.x/testing), [Blade](https://laravel.com/docs/13.x/blade), [Pint](https://laravel.com/docs/13.x/pint).

## Artifacts

- `spec.md`, `plan.md`, `research.md`, `data-model.md`, `quickstart.md`
- `contracts/caddy-csp.md`, `contact-events.md`, `gtm-owner.md`, `owner-ops.md`
- `checklists/requirements.md` (quality PASS), `owner-user-tests.md`
- `tasks.md` T001–T010

## Owner before implement

GTM: three Event tags + triggers; retire `contact_success`; Publish (`contracts/gtm-owner.md`).
