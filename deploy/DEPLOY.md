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

## Content without demo images

- CMS pages + articles are seeded on each deploy (`PageSeeder`, `ArticleSeeder`).
- Demo carousel JPGs are **excluded** from rsync (`deploy/rsync-excludes.txt`).
- Static logo ships in `public/images/logo.png`.

## After deploy

1. Open `https://safehouse.community` (after DNS).
2. CMS: `https://safehouse.community/cms-safehouse` (allowlist your IP in Caddy snippet).
3. Create admin: `php artisan make:filament-user` on server.
