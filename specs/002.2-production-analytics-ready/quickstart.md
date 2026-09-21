# Quickstart: 002.2 production analytics ready

Cite: [Laravel testing](https://laravel.com/docs/13.x/testing), [Pint](https://laravel.com/docs/13.x/pint), [Realtime](https://support.google.com/analytics/answer/9271392).

## PHPUnit (no Caddy)

```bash
ddev exec php artisan test --filter=MeasurementConversionTest
ddev exec ./vendor/bin/pint --test
```

Expect: three contact desk markers; honeypot without marker; `contact_success` **absent** as `data-measurement-event`.

## Local preview (US2 only)

CMS Analytics on. Accetta tutti. Submit contact per desk; Tag Assistant Custom Event name matches the desk.

## Production (US1 + US3)

1. Owner published GTM (three tags).
2. `sudo bash /var/www/safehouse-community-site/deploy/apply-caddy-site-once.sh`
3. Combined checklist [owner-user-tests.md](./checklists/owner-user-tests.md)

Fail if: Accetta tutti on live `/it` and Network has no collect (CSP or toggle). Fail if Realtime empty after 5 minutes **and** collect never appeared.
