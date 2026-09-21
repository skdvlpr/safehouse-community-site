# Contract: Public CSP for consented measurement (002.2)

Cite: [Caddy `header`](https://caddyserver.com/docs/caddyfile/directives/header), [GTM with CSP](https://developers.google.com/tag-platform/security/guides/csp).

## Matcher

`@public_csp not path /cms-safehouse*`

CMS MUST NOT receive this Content-Security-Policy.

## Directives to merge into the existing public CSP (keep Stripe + Turnstile)

**GA4 without advertising features** (do not add doubleclick / googleadservices):

| Directive | Hosts |
| :--- | :--- |
| `script-src` | existing + `https://www.googletagmanager.com` (already in snippet) |
| `img-src` | existing + `https://www.googletagmanager.com` `https://*.google-analytics.com` |
| `connect-src` | existing + `https://www.googletagmanager.com` `https://www.google-analytics.com` `https://*.google-analytics.com` `https://*.analytics.google.com` `https://www.google.com` |

**Preview Mode** (needed for DebugView on live):

| Directive | Hosts |
| :--- | :--- |
| `script-src` | + `https://tagmanager.google.com` |
| `style-src` | + `https://www.googletagmanager.com` `https://tagmanager.google.com` `https://fonts.googleapis.com` |
| `img-src` | + `https://ssl.gstatic.com` `https://www.gstatic.com` |
| `font-src` | + `https://fonts.gstatic.com` `data:` (data already present) |
| `frame-src` | + `https://www.googletagmanager.com` (Preview/GTM frames; keep Stripe/Turnstile) |

`'unsafe-inline'` remains on `script-src` / `style-src` (002 injects GTM from first-party JS). Do not add `'unsafe-eval'` unless a later spec uses Custom JS variables.

Confirm the Google list at implement apply time against the same CSP guide URL.

## Apply

`sudo bash /var/www/safehouse-community-site/deploy/apply-caddy-site-once.sh`

Self-deletes after successful `caddy validate` + reload. Failure keeps the script. Git deploy restores it.
