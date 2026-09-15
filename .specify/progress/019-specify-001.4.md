# 019 — Specified 001.4 volunteer mail + captcha layout

**Date:** 2026-09-15  
**Command:** `/speckit-specify`  
**Directory:** `specs/001.4-volunteer-mail-captcha`

## Why

Owner UAT after 001.3: volunteer bot-challenge works (managed green check is expected). Layout is right-aligned; theme is always dark. Volunteer table is unused — next pass emails Matteo + applicant acknowledgement, adds last name, all fields required. Live table drop only when the owner later asks to publish (no row review). CRM ingest is a later spec after CRM changes.

## Vendor docs (this specify)

- https://laravel.com/docs/13.x/mail
- https://laravel.com/docs/13.x/validation
- https://tailwindcss.com/docs/width
- https://developers.cloudflare.com/turnstile/get-started/client-side-rendering/
- https://developers.cloudflare.com/turnstile/concepts/widget/

## Follow-ups stored in the spec

- CRM volunteer candidacy (blocked on CRM) — not now, not `002`
- Production volunteer store: drop without inspecting rows at first live publish of 001.4 — not now

## Stop

`/speckit-plan` when the owner asks. Do not implement from chat. Do not start `002`. Do not drop production tables.
