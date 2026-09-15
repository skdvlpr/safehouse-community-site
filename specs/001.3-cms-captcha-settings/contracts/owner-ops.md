# Owner-ops: CMS captcha settings (001.3)

Cite: [Turnstile get started](https://developers.cloudflare.com/turnstile/get-started/) (widget does **not** require proxying the domain through Cloudflare).

## Agent MUST NOT

- Onboard `safehouse.community` as a Cloudflare DNS/zone (Connect your domain / nameservers)
- Reload production Caddy or apply HSTS/CSP
- Write production CMS / production `.env`
- `ddev stop`, `php artisan config:cache` in DDEV
- Print or commit the Turnstile secret
- Change the live Stripe Dashboard webhook URL (not this feature)

## Local preview

- Prove the new Settings → Captcha screen on `https://safehouse-community-site.ddev.site/cms-safehouse`
- Existing local keys (owner-ops 2026-09-15) MUST still work after implement unless the owner clears them
- Combined owner UAT of 001.2 public forms + this screen after implement (Russian script in chat; Cursor-browser only if the owner agrees that turn)

## Production (later, owner)

Staff paste the same (or a live-only) widget keys on live CMS themselves. Inspect-only during this feature: record whether `https://safehouse.community/it/contact` still lacks the widget until they do.
