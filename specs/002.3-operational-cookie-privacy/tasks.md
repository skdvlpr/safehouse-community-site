# Tasks: 002.3 operational cookie and privacy

**Input**: [spec.md](./spec.md), [plan.md](./plan.md), [contracts/policy-copy.md](./contracts/policy-copy.md)

## Phase 1 — Copy

- [x] T001 [US1] Rewrite `LegalPagesContent` privacy IT+EN (+ unpublished RU aligned, not routed) per [policy-copy.md](./contracts/policy-copy.md). Cite [Filament resources](https://filamentphp.com/docs/4.x/resources/overview). (test: yes — CmsPagesTest; C3; model: inherit)
- [x] T002 [US2] Rewrite cookie IT+EN (+ unpublished RU) per the same contract, including banner UX and Turnstile row. Cite [GA4 cookies](https://developers.google.com/analytics/devguides/collection/ga4/cookie-usage) and [Turnstile widgets](https://developers.cloudflare.com/turnstile/concepts/widget/). (test: yes; C3; model: inherit)

## Phase 2 — Tests and local sync

- [x] T003 [US1/US2] Extend `CmsPagesTest` for Aruba Cloud, Google Workspace, Turnstile, no Aruba-mail, no `contact_success` analytics, no DPA/demo phrases. Keep existing Google API and `Card data does not pass through our servers` asserts. (test: yes; C2; model: inherit)
- [x] T004 [US3] Local only: `ddev exec php artisan site:sync-legal-pages --force`. Do not sync production. Cite [Laravel Artisan](https://laravel.com/docs/13.x/artisan). (test: no; C1; model: inherit)
- [x] T005 Run Pint + `ddev exec php artisan test`. (test: yes; C1; model: inherit)
