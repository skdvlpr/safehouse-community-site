# Production deploy — safehouse.community (same VPS as CRM)

Target server: **77.81.234.138** (`crm.safehouse.community`).

Docroot: `/var/www/safehouse-community-site/public`  
CRM docroot (reference): `/var/www/safehouse-crm/public`

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
sudo caddy validate --config /etc/caddy/Caddyfile
sudo systemctl reload caddy
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

## Content sync

Deploy seeds (idempotent, safe to re-run):

| Seeder | Content |
|--------|---------|
| `PageSeeder` | Core pages (about, services, privacy, …) |
| `DeployArticleSeeder` | Articles from `database/seeders/data/deploy-articles.php` |
| `DonationCampaignSeeder` | Demo donation campaigns |
| `DeployIntegrationSeeder` | Stripe **test** keys from `database/seeders/data/deploy-integrations.php` |

Re-export from local CMS before deploy:

```bash
ddev exec php artisan site:export-deploy-data
git add database/seeders/data/
git commit -m "Update deploy content export"
```

CRM API key is **not** in the export — set `ESPOCRM_API_KEY` in server `.env` or CMS → Integrations.

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

Deploy seeds already load **test** publishable/secret keys. Only webhook secret must be set manually (it is unique per endpoint).

## After deploy

1. Open `https://safehouse.community` (after DNS).
2. CMS: `https://safehouse.community/cms-safehouse` (allowlist your IP in Caddy snippet).
3. Create admin: `php artisan make:filament-user` on server.
