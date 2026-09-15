# CMS UI contract: captcha settings (001.3)

Cite: [Filament custom pages](https://filamentphp.com/docs/4.x/navigation/custom-pages), [text input password](https://filamentphp.com/docs/4.x/forms/text-input).

CMS path stays `/cms-safehouse` (`S-CMS-PATH`).

## Navigation

| Locale | Group | Item |
| :--- | :--- | :--- |
| it | Impostazioni | **Captcha** |
| en | Settings | **Captcha** |

Sort: after Sportelli / Social, before Integrazioni (`navigationSort` 98).

URL slug: `captcha` (full path `/cms-safehouse/captcha`).

Access: super-admin only. Guest → login. Non-super-admin → hidden + denied.

## Screen fields

| Control | it (existing keys where possible) | Behaviour |
| :--- | :--- | :--- |
| Toggle | Abilita captcha Cloudflare Turnstile (opzionale) | `turnstile.enabled` |
| Public key | Turnstile site key | visible; not encrypted |
| Secret | Turnstile secret key | password + revealable; empty after save; blank save keeps previous |

Helper under toggle (it): already `cms.helpers.turnstile_enabled` — keep: only enable if keys exist in Cloudflare.

Save notification (new): Italian *Impostazioni captcha salvate* / English *Captcha settings saved*.

## Sportelli

- No captcha tab, no captcha fields.
- Helper (it) must **not** say captcha is configured on Sportelli. Point to Impostazioni → Captcha.
- Desks, email templates, CRM case type: unchanged.

## Public HTML (unchanged contract from 001.2)

When complete: `cf-turnstile` + `challenges.cloudflare.com/turnstile/v0/api.js` + public site key on `/it|en/contact` and `/it|en/volunteers`.

Secret string MUST be absent from those documents.

Donate, cookie-consent, CMS login: no widget.
