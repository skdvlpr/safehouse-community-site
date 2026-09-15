# 022 — Implemented 001.4 volunteer mail + captcha layout

**Date:** 2026-09-15  
**Command:** `/speckit-implement`  
**Directory:** `specs/001.4-volunteer-mail-captcha`  
**Tasks:** T001–T021 marked `[X]` in `tasks.md`  
**Models:** inherit (owner: всё ок по моделям; T005 kept)

## Official docs (this implement)

- [Laravel mail sending](https://laravel.com/docs/13.x/mail#sending-mail) — `Mail::send` two Mailables, Envelope To/Reply-To
- [Mail testing](https://laravel.com/docs/13.x/mail#testing-mailables) — `Mail::fake`, `assertSeeInText`, `hasTo` / `hasReplyTo`
- [Form Requests](https://laravel.com/docs/13.x/validation#form-request-validation) — required `last_name` / `phone` / `message`
- [Laravel Blade](https://laravel.com/docs/13.x/blade) — volunteer form fields + `@stack('scripts')`
- [Migrations drop](https://laravel.com/docs/13.x/migrations#dropping-tables) — `Schema::dropIfExists('volunteers')` preview only
- [Turnstile embed theme/size](https://developers.cloudflare.com/turnstile/get-started/client-side-rendering/#configuration-options) — `data-size="flexible"`; `light`|`dark` from `html[data-theme]`; not `auto`
- [Tailwind width](https://tailwindcss.com/docs/width) · [justify-content](https://tailwindcss.com/docs/justify-content) — widget wrapper `w-full` + `justify-center`
- [Vite](https://vite.dev/guide/) — `theme.js` copies theme; `api.js` after copy in scripts stack
- [Laravel testing](https://laravel.com/docs/13.x/testing) — `ddev exec php artisan test`
- [Laravel Pint](https://laravel.com/docs/13.x/pint) — `ddev exec ./vendor/bin/pint`
- [DDEV CLI](https://ddev.readthedocs.io/en/stable/users/usage/cli/) — preview `migrate` only

## What changed

- Volunteer POST sends `VolunteerStaffMail` (always Italian, To `matteo.grossi@safehouse.community`, subject `Nuova candidatura volontario`, Reply-To applicant) then `VolunteerApplicantMail` (locale of the form). No Eloquent store.
- SMTP missing or send throw: visitor error, no success flash (fail closed). Honeypot still fake-success with zero mail.
- New required `last_name`; `phone` and `message` required. Table `volunteers` dropped on **preview** (`2026_09_15_200000_drop_volunteers_table`). Model and factory removed.
- Contact + volunteer widgets: `data-size="flexible"`, no hardcoded dark, theme copied from `html[data-theme]` before `api.js`; CSS centres the box.
- Donate / cookie / CMS login: still no `cf-turnstile`.

## Verify

- PHPUnit: 336 passed, 2 skipped ([Laravel testing](https://laravel.com/docs/13.x/testing)).
- Pint: 361 files PASS ([Laravel Pint](https://laravel.com/docs/13.x/pint)).
- Preview migrate: `2026_09_15_200000_drop_volunteers_table` DONE. Production was **not** migrated.
- Frontend rebuilt (`bash bin/dev-rebuild-frontend.sh`) so widget CSS is in `public/build`.

## Production / publish (not this session)

First owner-asked publish of 001.4 MUST run the same drop migration on live and MUST NOT inspect or export `volunteers` rows ([owner-ops](../../specs/001.4-volunteer-mail-captcha/contracts/owner-ops.md)).

CRM volunteer ingest is a later spec after CRM is ready. Not `002`.

## Stop

Owner UAT (`checklists/owner-user-tests.md`) + Russian script in chat. Do **not** start `002`. Commit only if the owner asks. Cursor-browser only if the owner agrees this turn.
