# Quickstart: CMS measurement settings (002.1)

1. `ddev exec php artisan test --filter=Measurement`
2. Log in local CMS as super-admin → Impostazioni → Integrazioni → analytics tab.
3. Leave off: public `/it` HTML has `data-measurement-enabled="false"`.
4. Save on + `GTM-TEST1` (tests) or the real container (UAT): public boot node enabled; still no `googletagmanager.com` in first HTML; inject only after accept all ([GTM web](https://developers.google.com/tag-platform/tag-manager/web)).
5. Invalid id: site stays up, measurement off.
