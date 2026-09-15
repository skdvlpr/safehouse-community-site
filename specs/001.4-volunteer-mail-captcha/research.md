# Research: Volunteer mail + captcha layout (001.4)

**Date**: 2026-09-15

## Volunteer persist vs mail-only

- **Decision**: Stop writing `volunteers` rows. `VolunteerService` sends two Laravel Mailables via `Mail::send` (not queued; `S-NO-OCTANE`). Reuse `OutboundMailConfigurator::applyForSportello()` / `canSendSportelloNotifications()` for the existing website From + SMTP ([Laravel mail](https://laravel.com/docs/13.x/mail#sending-mail)). Staff To is `config/volunteer.php` → `matteo.grossi@safehouse.community` (not a CMS field). Drop the table with a new migration `Schema::dropIfExists('volunteers')` ([dropping tables](https://laravel.com/docs/13.x/migrations#dropping-tables)). Remove `Volunteer` model, factory, and persistence tests.
- **Rationale**: FR-001–FR-003. Owner called the table unused. Contact/sportelli already prove SMTP + From.
- **Alternatives considered**: Keep the table and also email (YAGNI, still PII at rest). CMS inbox field (out of spec). Queue the mail (Horizon forbidden).

## Two mailables, locales, fail closed

- **Decision**: `VolunteerStaffMail` — always Italian subject/body from spec (force locale `it` for that mailable). Reply-To = applicant. `VolunteerApplicantMail` — `__()` in the **request** locale (`it`/`en`). No Reply-To to Matteo on the acknowledgement. Send staff first, then applicant. If SMTP is not configured or `Mail::send` throws: log a warning **without** secrets or full message bodies ([constitution XII](../../.specify/memory/constitution.md)); redirect **back** with a translated error; **do not** flash `volunteer_success`. Unlike sportelli (which logs and still stored the contact row), volunteer has nowhere to store — fail visible.
- **Rationale**: FR-002, FR-005, edge “SMTP down”. Duplicate staff mail on visitor retry after a partial failure is acceptable.
- **Alternatives considered**: One email with CC applicant (owner asked for a separate no-reply acknowledgement). Silent skip like sportello when SMTP missing (would look like success).

## Form fields and validation

- **Decision**: Keep `name` as first name. Add required `last_name`. `phone` and `message` become required. Consent + Turnstile-when-enabled unchanged. Honeypot `company` still short-circuits with success and **zero** mail. Throttle `volunteers` stays. Form Request: [validation](https://laravel.com/docs/13.x/validation#form-request-validation).
- **Rationale**: FR-004. Phone placeholder “Opzionale” must go.
- **Alternatives considered**: Split into `first_name` (rename) — extra churn for one field.

## Captcha layout and theme

- **Decision**: Same widget on contact + volunteer. Set `data-size="flexible"` and wrap with full-width flex + `justify-center` ([Turnstile sizes/theme](https://developers.cloudflare.com/turnstile/get-started/client-side-rendering/#configuration-options), [Tailwind width](https://tailwindcss.com/docs/width), [justify-content](https://tailwindcss.com/docs/justify-content)). Do **not** use `data-theme="auto"` (that follows OS, not `html[data-theme]`). Copy `html[data-theme]` (`light`|`dark`) onto `.cf-turnstile` **before** `api.js` runs (move the script from `@push('head')` to `@stack('scripts')` after a copy step). On theme toggle, update the attribute; `turnstile.reset` if the global exists. First paint must be correct; live toggle is best-effort (spec SHOULD).
- **Rationale**: FR-006 mandatory; FR-007 should. Hardcoded `data-theme="dark"` is the current bug.
- **Alternatives considered**: Full explicit `turnstile.render` SPA (more JS than needed). Leave dark widget (owner asked to try).

## Tests

- **Decision**: Rewrite `tests/Feature/VolunteerFormTest.php` with `Mail::fake()` like `ContactFormMailTest` ([mail testing](https://laravel.com/docs/13.x/mail#testing-mailables), [Laravel testing](https://laravel.com/docs/13.x/testing)): happy path asserts staff To + Italian subject/body fragments + applicant To + locale copy; `Schema::hasTable('volunteers')` is false **or** no Volunteer model writes; missing last_name/phone/message → errors and `assertNothingSent`; honeypot and Turnstile-on-without-token → nothing sent. Delete or shrink `VolunteerModelTest` / factory (table gone). No CSS/HTML snapshots (owner UAT + visual matrix in contracts). No coverage %.
- **Rationale**: Constitution V — mail routing, locale split, and “no store” are real bugs. Layout is visual.
- **Alternatives considered**: Keep asserting `volunteers` row count (contradicts spec). Dusk for widget alignment (not in stack).

## Production drop

- **Decision**: Local implement runs `ddev exec php artisan migrate` ([DDEV CLI](https://ddev.readthedocs.io/en/stable/users/usage/cli/)). That drops **preview** `volunteers` only. Production is not migrated until the owner later asks to publish; [owner-ops](./contracts/owner-ops.md) says drop live without inspecting rows (the same migration, no dump).
- **Rationale**: FR-008 / FR-009.
- **Alternatives considered**: Manual `DROP TABLE` on live now (forbidden). Export rows first (owner said no).

## Out of scope (confirmed)

- CRM volunteer ingest (follow-up; no CRM repo writes this feature)
- Captcha Settings keys / CMS inbox editor
- Donate, cookie, CMS login widgets
- Specs `002`–`008`
