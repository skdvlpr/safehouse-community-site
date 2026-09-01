# Production deploy — safehouse.community (same VPS as CRM)

Target server: **77.81.234.138** (`crm.safehouse.community`).

Docroot: `/var/www/safehouse-community-site/public`  
CRM docroot (reference): `/var/www/safehouse-crm/public`

## Deploy scripts on the server

All `deploy/*.sh` scripts **self-delete after a successful run** — they must not remain on the VPS.
The next git deploy (rsync) restores them from the repo when needed.

| Script | Needed? | When |
|--------|---------|------|
| `post-deploy.sh` | **Yes** — CI calls this every deploy | Auto: migrate, cache clear, roles, storage link |
| `apply-php-upload-limits-once.sh` | Once | Root: raise `upload_max_filesize` above 2M |
| `fix-storage-permissions-once.sh` | Once (or after permission incident) | Root: full storage / livewire-tmp ownership |
| `apply-caddy-site-once.sh` | Once | Root: Caddy site block |
| `restore-caddy-backup.sh` | Emergency only | Root: rollback bad Caddy edit |
| `server-bootstrap.sh` | Empty server only | Root: create DB + app directory |

`deploy-local.sh` is **local WSL only** (excluded from rsync).

`post-deploy.sh` does **not** repeat PHP/Caddy/permission one-time setup. It only runs Laravel
commands safe to repeat (migrations, cache clears, `RoleSeeder` with `firstOrCreate`).

**Not in post-deploy (run manually when needed):**

```bash
php artisan cms:regenerate-url-slugs   # can change public URLs — do not auto-run on deploy
php artisan db:bootstrap-production    # first install only
php artisan site:sync-legal-pages --force
```

If a script fails, it is **kept** on disk so you can fix the error and retry.

## DNS

Point the apex record to the CRM server:

| Host | Type | Value |
|------|------|-------|
| `@` | A | `77.81.234.138` |
| `www` | A | `77.81.234.138` |
| `crm` | A | `77.81.234.138` (already) |

Until DNS propagates, test by IP:

```bash
curl -k -H "Host: safehouse.community" https://77.81.234.138/
```

## One-time server setup (SSH as root or deploy)

```bash
# As root — create MariaDB + user
mysql -e "CREATE DATABASE IF NOT EXISTS safehouse_community CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS 'safehouse_site'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD';"
mysql -e "GRANT ALL ON safehouse_community.* TO 'safehouse_site'@'localhost'; FLUSH PRIVILEGES;"

# App directory
sudo mkdir -p /var/www/safehouse-community-site
sudo chown -R deploy:deploy /var/www/safehouse-community-site

# First deploy copies code — then on server:
cd /var/www/safehouse-community-site
cp deploy/env.production.example .env
# edit .env — APP_KEY, DB_*, ESPOCRM_*, STRIPE_*
php artisan key:generate

sudo chown -R deploy:www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache

# Caddy — append deploy/Caddyfile.snippet to /etc/caddy/Caddyfile, then:
sudo bash /var/www/safehouse-community-site/deploy/apply-caddy-site-once.sh
# (syncs the site block + Turnstile CSP, validates, reloads, self-deletes)
```

## GitHub Actions secrets

Repository → Settings → Secrets → Actions:

| Secret | Value |
|--------|-------|
| `DEPLOY_HOST` | `77.81.234.138` |
| `DEPLOY_USER` | `deploy` |
| `DEPLOY_PATH` | `/var/www/safehouse-community-site` |
| `DEPLOY_SSH_KEY` | private key (same as CRM repo if same user) |
| `DEPLOY_SSH_PORT` | `22` (optional) |

Push to `main` → automatic deploy.

## PHP runtime (shared VPS with CRM)

Production runs **one** PHP version (**8.4**) for CRM and community site. Upgrade once
as root (script lives in the CRM tree):

```bash
sudo bash /var/www/safehouse-crm/deploy/upgrade-php84.sh
```

Both repos' CI deploy jobs verify `php -v` is 8.4 before post-deploy.

## Manual deploy (from WSL)

```bash
chmod +x deploy/deploy-local.sh
export DEPLOY_HOST=77.81.234.138
./deploy/deploy-local.sh
```

Enter SSH password when prompted (or use your SSH key).

## Carousel on production

- **Hero carousel is enabled** on all page templates (Filament → Pages → Hero carousel).
- **Local demo JPGs** (`public/images/carousel-demo/`) are **not** rsync'd to the server.
- **Production seed** does not reference those demo paths (`SEED_DEMO_CAROUSEL=false`).
- Upload real slides in CMS — files land in `storage/app/public/page-carousels/` and persist across deploys.

## CMS content and deploy

**Deploy does not touch CMS content.** `post-deploy.sh` runs migrations and `RoleSeeder` only.

Content seeders (`PageSeeder`, `DeployArticleSeeder`, `DonationCampaignSeeder`, `DeploySiteContentSeeder`) use `updateOrCreate` / `updateMany` and **must not** run on every deploy — they would recreate deleted donation campaigns, reset page copy, overwrite articles, and reset the site tagline.

Edit content in production CMS (`/cms-safehouse`). Changes persist across deploys.

### First install on an empty database

After migrations, run once on the server:

```bash
cd /var/www/safehouse-community-site
php artisan db:bootstrap-production
php artisan make:filament-user
```

This adds core pages, demo campaigns, articles, and the default tagline **only when those tables/settings are still empty**.

### Refresh privacy / cookie page copy

When `database/seeders/Data/LegalPagesContent.php` changes and production pages already exist:

```bash
cd /var/www/safehouse-community-site
php artisan site:sync-legal-pages --force
```

Overwrites the `privacy` and `cookie` CMS page bodies from the repo source (does **not** self-delete files).

### Export articles for local dev / git (optional)

To refresh the local deploy export file from CMS:

```bash
ddev exec php artisan site:export-deploy-data
git add database/seeders/data/
git commit -m "Update deploy content export"
```

CRM API key is **not** in the export — set `ESPOCRM_API_KEY` in server `.env` or CMS → Integrations.

## CMS photo uploads (Filament / Livewire)

Filament batch upload needs **PHP-FPM limits ≥ 32M** and writable storage for `www-data`.

Default Ubuntu/CRM PHP is often **`upload_max_filesize = 2M`** — photos around 3–4 MB reach **100% in the UI then hang** (bytes arrive, PHP rejects the file).

One-time fix on the VPS (**as root**):

```bash
sudo bash /var/www/safehouse-community-site/deploy/apply-php-upload-limits-once.sh
sudo bash /var/www/safehouse-community-site/deploy/fix-storage-permissions-once.sh
```

Verify:

```bash
php -r 'echo ini_get("upload_max_filesize"), " ", ini_get("post_max_size"), PHP_EOL;'
# expect: 32M 64M
```

Carousel files live in `storage/app/public/article-carousels/` (persists across deploys; rsync excludes this tree).

## Stripe test mode on production

After first deploy:

1. **Webhook (required for donations)** — Stripe Dashboard → [Webhooks](https://dashboard.stripe.com/test/webhooks) → Add endpoint:
   - URL: `https://safehouse.community/api/webhooks/stripe`
   - Events: `payment_intent.succeeded`
   - Copy signing secret (`whsec_…`) → CMS → **Integrations** → Stripe webhook secret (or `STRIPE_WEBHOOK_SECRET` in `.env`)

2. **Verify** on server:
   ```bash
   cd /var/www/safehouse-community-site
   php artisan stripe:verify
   php artisan config:clear && php artisan cache:clear
   ```

3. **Test payment** — open `/it/donazioni/safe-house`, use card `4242 4242 4242 4242`, any future expiry/CVC.

4. **Switch to live** later — replace keys in CMS → Integrations and register a **live** webhook with the same URL.

### Caddy (Turnstile CSP)

After deploy, run once on the server as root:

```bash
sudo bash /var/www/safehouse-community-site/deploy/apply-caddy-site-once.sh
```

The script replaces or appends the `safehouse.community` block from `deploy/Caddyfile.snippet`, validates Caddy, reloads, then **deletes itself**. Re-deploy restores the script if you need to run it again.

Deploy seeds already load **test** publishable/secret keys. Only webhook secret must be set manually (it is unique per endpoint).

### CMS returns 500 after deploy

`php artisan cms:health` as root/deploy can return **200** while the browser still gets **500** — PHP-FPM runs as `www-data` and needs its own writable caches.

Run on the server (SSH as `deploy`):

```bash
cd /var/www/safehouse-community-site
php artisan optimize:clear
php artisan filament:optimize
php artisan route:clear
php artisan config:clear
sudo chown -R deploy:www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
sudo systemctl reload php8.4-fpm
sudo -u www-data php artisan cms:health
```

Do **not** run `php artisan config:cache` on production — it breaks PHP-FPM when caches are built as the deploy user.

If the browser still shows 500 after the steps above:

1. Open `https://safehouse.community/cms-safehouse/login` once.
2. Read the captured exception:
   ```bash
   cat storage/logs/cms-last-error.txt
   tail -80 storage/logs/laravel.log
   ```
3. Check PHP-FPM extensions (Filament needs **intl**):
   ```bash
   sudo -u www-data php -m | grep intl
   ```

Do **not** run `php artisan view:cache` — it breaks Filament/Livewire for PHP-FPM.

## After deploy

1. Open `https://safehouse.community` (after DNS).
2. CMS: `https://safehouse.community/cms-safehouse` (allowlist your IP in Caddy snippet).
3. Create admin: `php artisan make:filament-user` on server.
