# Data model: CMS captcha settings (001.3)

No new table. No migration. Reuse `site_settings` keys already defined in `config/site_settings.php`.

## Bot-challenge setting (per environment)

Staff record in the preview or live CMS database (not shared).

| Key | Encrypted | Rules |
| :--- | :--- | :--- |
| `turnstile.enabled` | no | Stored `'1'` / `'0'`. Public widget requires this **and** both keys non-empty (`TurnstileVerifier::enabled()`). |
| `turnstile.site_key` | no | Public identifier; may appear in public HTML as widget `data-sitekey`. |
| `turnstile.secret_key` | **yes** | Server-only. Never in public HTML. Blank CMS save does not overwrite. |

`.env` `TURNSTILE_SITE_KEY` / `TURNSTILE_SECRET_KEY` remain fallbacks when DB values are empty. CMS rows win when present.

## State

```text
incomplete (toggle off OR missing site key OR missing secret)
  → no public widget; contact/volunteer honeypot + rate limit only
complete (toggle on AND both keys)
  → widget on contact + volunteer; submit without token stores 0 rows
```

Toggle off after complete → next public GET has no widget (verifier reads CMS).

## Relationships

- **Public forms** (contact, volunteer) read via `TurnstileVerifier` only. No new columns on `volunteers` / `contact_submissions`.
- **Sportelli desks / mail** stay on their own settings. They MUST NOT write the three keys above after this feature.

## Validation (CMS save)

- Site key: optional string, max 255.
- Secret: optional on save; if filled, stored encrypted.
- Enabled: boolean. Enabling with empty keys is allowed in CMS; public side treats it as incomplete (not a hard form error — avoids locking visitors).
