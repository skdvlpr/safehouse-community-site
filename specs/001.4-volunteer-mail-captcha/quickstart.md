# Quickstart validation: Volunteer mail + captcha layout (001.4)

Local only: `https://safehouse-community-site.ddev.site`. Do not `ddev stop`. Do not `php artisan config:cache`. Do not write production ([owner-ops](./contracts/owner-ops.md)).

## Prerequisites

- DDEV running; SMTP already used for sportelli (Mailpit is fine)
- Local Turnstile keys still in CMS if testing the challenge-on path
- Official checks: [Laravel testing](https://laravel.com/docs/13.x/testing), [Pint](https://laravel.com/docs/13.x/pint)

## Automated

```bash
ddev exec php artisan test
ddev exec ./vendor/bin/pint --test
```

Expect (if proposed tests kept at tasks): volunteer POST with `Mail::fake` sends staff + applicant mailables; missing last name/phone/message sends nothing; honeypot and Turnstile-fail send nothing; `volunteers` table gone. Contact mail tests still pass.

## Manual

See [volunteer-http.md](./contracts/volunteer-http.md), [copy.md](./contracts/copy.md), [captcha-ui.md](./contracts/captcha-ui.md).

1. `/it/volunteers`: last name present; phone not labelled optional; all fields required.
2. Submit complete form → success flash; Mailpit (or SMTP): Italian staff mail to Matteo, Italian acknowledgement to you; **no** CMS/DB volunteer list.
3. `/en/volunteers` complete submit → staff mail still Italian; acknowledgement English.
4. Empty last name / phone / message → errors, no mail.
5. Challenge on: submit without widget → no mail. Widget centred or full width on volunteer **and** contact; not right-aligned.
6. Theme light vs dark: widget follows or layout still OK after reload.
7. Donate / cookie / CMS login: no widget.
8. Confirm no production migrate/Caddy/DNS in this session.

Owner UAT checklist is written at implement (`checklists/owner-user-tests.md`) with a Russian numbered script in chat. Cursor-browser only if the owner agrees that turn.
