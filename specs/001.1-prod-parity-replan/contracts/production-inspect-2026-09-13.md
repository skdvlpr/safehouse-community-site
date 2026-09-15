# Contract: production inspect 2026-09-13

Read-only. SSH: `deploy@77.81.234.138` with `~/.ssh/safehouse-deploy`. Public HTTPS from the inspector network. Production `.env` not read. `site_settings` not dumped.

**Refresh 2026-09-13 (implement):** `/it/diventa-socio` 200, `/it/landing-example` 404, `/ru` 404, `/cms-safehouse/login` 200 public, `X-Frame-Options: DENY`, HSTS absent — unchanged from the baseline table below.

Official: [Caddy header](https://caddyserver.com/docs/caddyfile/directives/header) · [Filament 4.x](https://filamentphp.com/docs/4.x) · [Stripe webhook signatures](https://docs.stripe.com/webhooks/signatures)

## Runtime

| Item | Value |
| :--- | :--- |
| Host | `safehouse` |
| SSH user | `deploy` (groups: `deploy`, `www-data`, `users`) |
| App path | `/var/www/safehouse-community-site` |
| PHP | 8.4.24 |
| Laravel | 13.17.0 |
| `php artisan env` | `production` |
| Git on server | none |
| `.env` | present, not read |
| `cms-last-error.txt` | present; 2026-09-01 Blade `touch(): Utime failed: Operation not permitted` |

## Community Caddy vhost (live)

```
safehouse.community, www.safehouse.community {
    root * /var/www/safehouse-community-site/public
    encode zstd gzip
    php_fastcgi unix//run/php/php8.4-fpm.sock
    try_files {path} {path}/ /index.php?{query}
    file_server
}
```

Does **not** match `deploy/Caddyfile.snippet` (no HSTS, CSP, CMS allowlist, X-Frame).

## Response headers on `GET https://safehouse.community/it`

| Header | Value |
| :--- | :--- |
| Via | `0.0 Caddy` |
| Strict-Transport-Security | absent |
| Content-Security-Policy | absent |
| X-Frame-Options | `DENY` (PHP) |
| X-Content-Type-Options | `nosniff` |
| Referrer-Policy | `strict-origin-when-cross-origin` |

## Public URL outcomes

| URL | Status |
| :--- | :--- |
| `/it` | 200 |
| `/en` | 200 |
| `/ru` | 404 |
| `/it/diventa-socio` | 200 |
| `/en/diventa-socio` | 200 |
| `/en/become-a-member` | 404 |
| `/it/landing-example` | 404 |
| `/en/landing-example` | 404 |
| `/it/about-us` | 200 |
| `/it/services` | 200 |
| `/it/contact` | 200 |
| `/it/cookie-policy` | 200 |
| `/it/privacy-policy` | 200 |
| `/it/transparency` | 200 |
| `/it/domande-frequenti-faq` | 200 |
| `/it/donations` | 200 |
| `/it/donations/5-per-thousand` | 200 |
| `/it/volunteers` | 200 |
| `/it/news` | 200 |
| `/it/articles` | 200 |
| `/cms-safehouse/login` | 200 (public) |
| `/admin` | 404 |
| `https://www.safehouse.community/it` | 200 |

Guessed Italian slugs `donazioni`, `diventa-volontario`, `privacy`, `chi-siamo`, `5x1000` are **404**; real slugs are English-ish as above.

## Published pages (CMS)

| id | key | template | published | slug it | title it |
| ---: | :--- | :--- | :--- | :--- | :--- |
| 1 | about | about | yes | about-us | Chi siamo |
| 2 | services | services | yes | services | Servizi |
| 3 | contact | contact | yes | contact | Contatti |
| 4 | privacy | legal | yes | privacy-policy | Privacy policy |
| 5 | cookie | legal | yes | cookie-policy | Cookie policy |
| 9 | trasparenza | legal | yes | transparency | Trasparenza |
| 10 | home | home | yes | (empty) | Safe House Community |
| 12 | faq | default | yes | domande-frequenti-faq | Domande Frequenti (F.A.Q.) |
| 13 | diventa-socio | landing | yes | diventa-socio | Diventa socio |

No `demo-landing` / `demo-article` rows. Several rows still store `ru` translations (not routed).

## Campaigns (no secrets)

| id | slug | active |
| ---: | :--- | :--- |
| 1 | donate-to-safe-house | yes |
| 10 | donazione-ricorrente | yes |
| 11 | recurring-donation | yes |

Articles: 5 total, 5 published.

## Public HTML hints

- 5×1000 appears in header (`5-per-thousand`) on sampled pages.
- No Satispay string on home.
- Volunteer and contact HTML: no `turnstile` / `challenges.cloudflare.com` / `data-sitekey` on this date.

## Logs (redacted counts)

| Needle | Count |
| :--- | ---: |
| SignatureVerificationException | 270 |
| No signatures found | 180 |
| lines with SignatureVerification and `.ERROR` | 90 |
| StripeWebhook + `.ERROR` | 0 |
| string `HTTP 500` | 0 |
| log file bytes | 6355262 |

## SSH identity note

`id_ed25519` offered and **rejected**. `safehouse-deploy` **accepted**. Treat the deploy key as production-capable.
