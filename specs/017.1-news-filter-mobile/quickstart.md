# Quickstart: 017.1 phone filter bar

1. Open `https://safehouse-community-site.ddev.site/it/news` at ~390px. **Data** and **Applica** fully visible. No sideways page scroll from the bar.
2. Same on `/it/articles` (empty categories too).
3. Wide window: one-row bar as today.
4. Diventa socio unchanged.

```sh
ddev exec php artisan test --filter=NewsHeadingTest
ddev exec ./vendor/bin/pint --test
bash bin/dev-rebuild-frontend.sh
```
