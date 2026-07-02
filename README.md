# Safehouse.community — Public Website

Production website for **Safe House ETS** (Italy): humanitarian aid, human rights protection, and social inclusion.

| | |
|---|---|
| **Stack** | Laravel 13 · PHP 8.3 · Filament v4 (CMS) · Tailwind · Vite |
| **Site** | https://safehouse.community (production) |
| **CRM** | https://crm.safehouse.community |
| **Repo** | https://github.com/skdvlpr/safehouse-community-site |
| **Agent rules** | [`AGENTS.md`](AGENTS.md) |

Primary site language: **Italian**. Also **Russian** and **English**.

---

## Requirements

- [DDEV](https://ddev.com/) (Docker-based local environment)
- Docker running on your machine

No native PHP, Composer, or Node install required for day-to-day work — use `ddev` wrappers.

---

## Quick start (DDEV)

```bash
git clone git@github.com:skdvlpr/safehouse-community-site.git
cd safehouse-community-site

cp .env.example .env
# Edit .env if needed — DDEV sets DB credentials automatically when using ddev

ddev start
ddev composer install
ddev npm install
ddev npm run build
ddev artisan key:generate   # first time only, if APP_KEY is empty
ddev artisan migrate --seed
```

If pages or campaigns disappear after a fresh DB volume, run:

```bash
ddev artisan db:seed-if-empty
```

(or `ddev artisan db:seed` for a full re-seed).

**Cursor agents** run `migrate` / `migrate --seed` automatically after migration or seeder changes (see [`AGENTS.md`](AGENTS.md)).

Open the site:

**https://safehouse-community-site.ddev.site**

HTTP redirect to HTTPS is handled by DDEV.

---

## Common commands

| Task | Command |
|------|---------|
| Start stack | `ddev start` |
| Stop stack | `ddev stop` *(run manually — agents must not stop DDEV without asking)* |
| Artisan | `ddev artisan …` |
| Composer | `ddev composer …` |
| NPM | `ddev npm …` |
| PHPUnit | `ddev exec php artisan test` |
| Pint (style) | `ddev exec ./vendor/bin/pint` |
| Shell | `ddev ssh` |
| DB CLI | `ddev mysql` |

**Do not run `php artisan config:cache` in DDEV** — cached config forces tests onto MariaDB and `RefreshDatabase` wipes your local CMS data.

### Frontend dev (hot reload)

```bash
ddev npm run dev
```

Run Vite inside DDEV; the app serves assets from the dev server when `public/hot` exists.

---

## Environment

| Setting | Value |
|---------|--------|
| PHP | 8.3 |
| Database | MariaDB 11.8 |
| Web server | nginx-fpm |
| Project name | `safehouse-community-site` |
| Docroot | `public/` |

Database host inside the app container: `db` (DDEV default). After `ddev start`, run `ddev describe` for URLs and credentials.

---

## Production (Caddy + PHP-FPM)

Production target matches **crm.safehouse.community**: **Caddy v2** as reverse proxy → **PHP-FPM 8.3** → Laravel `public/` docroot.

| Concern | Where handled |
|---------|----------------|
| HTTPS, HTTP/3, auto-certs | Caddy |
| `Content-Security-Policy`, HSTS | **Caddy only** (never in PHP) |
| Other security headers | Laravel `SecurityHeaders` middleware (P0-T04+) |
| Health check | Laravel `/up` (for load balancer / monitoring) |
| Admin CMS | `/cms-safehouse` — IP allowlist in Caddy |
| Static assets | `public/` via Caddy `file_server`; Vite build in `public/build/` |

Local dev uses **DDEV nginx-fpm** — that is expected; production is **Caddy**, not nginx.

Full rules: [`AGENTS.md`](AGENTS.md) (Section 7 — Security). Caddyfile template: task **P6-T03** → `deploy/Caddyfile.example` (not in repo yet). Reference deploy pattern: [noprofit-espocrm/deploy/DEPLOY.md](https://github.com/skdvlpr/noprofit-espocrm/blob/main/deploy/DEPLOY.md).

**Planned stack on Aruba VPS:** Caddy → php8.3-fpm → `/var/www/safehouse-community-site/public`, Redis, MariaDB, GitHub Actions SSH deploy (same family as CRM).

---

## Project tracking

Implementation tasks and acceptance criteria live in Notion:

- [Project: Safehouse.community — Public Website](https://app.notion.com/p/38e8d469d405810980bbf8fbcf34ae94)
- [Tasks database](https://app.notion.com/p/38d8d469d40580b8b87ee0681b9d929c)

AI agents: read [`AGENTS.md`](AGENTS.md) before any code change.

---

## License

Application code: MIT (Laravel skeleton). Content and branding © Safe House ETS.
