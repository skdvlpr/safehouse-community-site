# Quickstart: 002.5 post-UAT polish

Preview: `https://safehouse-community-site.ddev.site`

1. `ddev exec php artisan site:sync-legal-pages --force` after legal copy edits ([Artisan](https://laravel.com/docs/13.x/artisan)).
2. `bash bin/dev-rebuild-frontend.sh` after CSS/JS.
3. Phone ~390px `/it/diventa-socio`: swipe hint between hero and 01; no Pausa; Contattaci still compact.
4. Laptop ~1400px: Contattaci has large square socials + Statuto/FAQ/Donazioni/Volontariato; cookie/privacy one even translucent column (not a T).
5. Footer: English version, no Preferenze cookie. Cookie page still has Apri le preferenze cookie. Banner shows IT and EN.
6. `ddev exec php artisan test` ([Laravel testing](https://laravel.com/docs/13.x/testing)); `ddev exec ./vendor/bin/pint --dirty`.
7. Production after authorised push: cookie/privacy show September operational text (Anzio). Confirm `deploy/sync-legal-pages-once.sh` is gone on the VPS so the next deploy will not sync again.
