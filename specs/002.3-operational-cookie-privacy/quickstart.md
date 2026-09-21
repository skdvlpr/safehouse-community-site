# Quickstart: 002.3 operational cookie and privacy

1. Edit `database/seeders/Data/LegalPagesContent.php`.
2. `ddev exec php artisan site:sync-legal-pages --force`
3. Open https://safehouse-community-site.ddev.site/it/privacy-policy and `/it/cookie-policy` (then `/en/...`).
4. `ddev exec php artisan test --filter=CmsPagesTest`
5. Do **not** run the sync on production until the owner asks.
