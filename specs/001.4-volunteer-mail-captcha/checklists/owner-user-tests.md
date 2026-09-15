# Owner user tests: Volunteer mail + captcha layout (001.4)

**Purpose**: Owner accepts volunteer email-only workflow and public challenge layout/theme. PHPUnit does not close this handshake.  
**Created**: 2026-09-15  
**Feature**: [spec.md](../spec.md)

**Marker semantics**: `[x]` = Pass (owner). Agent notes below are not owner UAT.

**Closed**: 2026-09-15 — owner: all items Pass (`по всем пунктам - окей`). S01 amendments finished. `002` is unblocked (do not start until the owner asks).

Official docs for this implement: [Laravel mail](https://laravel.com/docs/13.x/mail#sending-mail) · [mail testing](https://laravel.com/docs/13.x/mail#testing-mailables) · [Form Requests](https://laravel.com/docs/13.x/validation#form-request-validation) · [migrations drop](https://laravel.com/docs/13.x/migrations#dropping-tables) · [Turnstile theme/size](https://developers.cloudflare.com/turnstile/get-started/client-side-rendering/#configuration-options) · [Tailwind width](https://tailwindcss.com/docs/width) · [justify-content](https://tailwindcss.com/docs/justify-content) · [Laravel testing](https://laravel.com/docs/13.x/testing) · [Pint](https://laravel.com/docs/13.x/pint) · [DDEV CLI](https://ddev.readthedocs.io/en/stable/users/usage/cli/)

## Volunteer mail (preview)

- [x] UAT001 `/it/volunteers`: last name field present; phone is **not** labelled optional; all visible fields required
- [x] UAT002 Complete Italian submit → on-page thank-you; Mailpit (or SMTP): staff mail **To** `matteo.grossi@safehouse.community`, **Subject** `Nuova candidatura volontario`, Italian body with Nome / Cognome / Indirizzo email / Tel. / Messaggio / `Website | Safe House`; Reply-To is the applicant
- [x] UAT003 Same submit: applicant inbox has Italian acknowledgement (received; we will contact you; do not reply). No Reply-To to Matteo on that message
- [x] UAT004 `/en/volunteers` complete submit → staff mail still Italian (same subject/body structure); applicant acknowledgement **English** (received; we will contact you; do not reply)
- [x] UAT005 Empty last name, empty phone, or empty message → errors; **no** staff mail; **no** applicant mail
- [x] UAT006 Preview has **no** volunteer-applications table/list after this feature (local migrate dropped `volunteers`)

## Challenge widget layout / theme (contact + volunteer)

Visual matrix ([captcha-ui.md](../contracts/captcha-ui.md)):

- [x] UAT007 Volunteer **it** + dark: widget centred in the field column **or** full field width — not flush right
- [x] UAT008 Volunteer **it** + light: same layout; widget looks light **or** owner accepts “theme unchanged by provider” **and** layout still passes UAT007
- [x] UAT009 Contact **it** + dark: same layout as UAT007
- [x] UAT010 Volunteer or contact **en** (either theme): spot-check layout
- [x] UAT011 Managed mode may show a green check without a picture puzzle — expected, not a defect
- [x] UAT012 Donate, cookie banner, and CMS login still have **no** challenge widget

## Production / publish (must stay untouched this session)

- [x] UAT013 This implement did **not** migrate production, dump live `volunteers`, reload live Caddy, or onboard Cloudflare DNS/zone
- [x] UAT014 When you later ask to **publish** this feature: run the same drop migration on live and **do not** inspect or export volunteer rows first ([owner-ops](../contracts/owner-ops.md))

## Optional leftover from 001.3 (skip if already accepted)

- [x] UAT015 Local CMS Captcha screen still works (on/off + keys); Sportelli has no captcha tab

## Notes from agent (2026-09-15)

- Staff inbox is `config/volunteer.php` (`matteo.grossi@safehouse.community`), not a CMS field.
- Preview `ddev exec php artisan migrate` drops local `volunteers` only.
- Live drop is **publish-time**, without inspecting rows.
- CRM volunteer ingest is a later spec after CRM is ready — not `002`.
