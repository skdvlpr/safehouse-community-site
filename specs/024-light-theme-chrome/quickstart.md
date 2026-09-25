# Quickstart: 024 Light-theme chrome

## Owner UAT (local DDEV)

1. Open `https://safehouse-community-site.ddev.site/it/about-us` — switch to **light**. Values panel (right) and closing quote keep a **red** outline. Intro glass stays ordinary grey/white glass.
2. Same page in **dark** — those two outlines still red.
3. Open Home in **light**. The 5×1000 strip mark is a **rectangle** with red outline and **5×**. Hover the banner: highlight is clearly stronger; **5×** stays readable (does not vanish).
4. Same hover in **dark** — **5×** still readable.
5. Open `/it/news` and `/it/articles` in **light**. **Categorie** matches the date fields (same light fill and border). Phone stack from 017.1 still shows **Data** and **Applica** in full.
6. Home with motion allowed: the three numbers count from 0 a bit slower than before (~3.5 s). With reduced motion: final numbers immediately.

## Verify

```sh
ddev exec php artisan test
ddev exec ./vendor/bin/pint --test
```

Frontend stale CSS: `bash bin/dev-rebuild-frontend.sh`
