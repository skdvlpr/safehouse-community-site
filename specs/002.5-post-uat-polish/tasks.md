# Tasks: 002.5 post-UAT polish

**Input**: [spec.md](./spec.md), [plan.md](./plan.md), [research.md](./research.md), [data-model.md](./data-model.md), [contracts/](./contracts/)

**Tests**: Markup assertions only (no screenshots). Owner UAT is the visual gate.

**Implement**: owner said yes 2026-09-21. T006 visual on this chat (Grok 4.7 Extra High is not an allowed subagent slug). Footer EN pages use «Versione italiana».

## Phase 1 — Foundational copy

- [X] T001 [P] Add IT/EN strings for swipe hint, contact links, footer locale, banner IT/EN in `lang/it/site.php` and `lang/en/site.php`. Cite [Laravel localization](https://laravel.com/docs/13.x/localization). (test: no; C2; model: inherit)

## Phase 2 — User Story 1 (phone hint, no pause)

- [X] T002 [US1] Remove ticker pause control from `resources/views/pages/templates/landing.blade.php`, `resources/js/landing-motion.js`, `resources/css/app.css`. Keep reduced-motion still ticker. Cite [WCAG 2.2.2](https://www.w3.org/WAI/WCAG22/Understanding/pause-stop-hide.html). (test: yes — MembershipFormTest no `data-marquee-toggle`; C3; model: inherit)
- [X] T003 [US1] Add `md:hidden` swipe-down hint between hero and cards in `resources/views/pages/templates/landing.blade.php` + `resources/css/app.css`. Cite [Laravel Blade](https://laravel.com/docs/13.x/blade). (test: yes — MembershipFormTest sees hint class; C3; model: inherit)

## Phase 3 — User Story 2 (desktop Contattaci filler)

- [X] T004 [US2] Tests in `tests/Feature/MembershipFormTest.php`: desktop filler markup (social squares class, Statuto/FAQ/donate/volunteer hrefs); extras wrapper is the one hidden below `md`. (test: yes; C3; model: inherit)
- [X] T005 [US2] Wire destinations in `resources/views/pages/templates/landing.blade.php` (FAQ via `urlForKey('faq')` as Statuto fallback, donate, volunteer, `<x-social-links />`). Cite [Blade](https://laravel.com/docs/13.x/blade). (test: yes — T004; C4; model: inherit)
- [X] T006 [US2] Desktop-only visual: larger square socials + link row; phone Contattaci stays compact. `resources/css/app.css`, landing contact markup. Cite [Tailwind max-width](https://tailwindcss.com/docs/max-width). (test: no screenshots; C6; model: inherit — Grok 4.7 EH requested but not in allowed subagent list)

## Phase 4 — User Story 3 (legal column)

- [X] T007 [P] [US3] Even column + matching translucency for hero and paper in `resources/views/pages/templates/legal.blade.php` and `resources/css/app.css`. Cite [Tailwind max-width](https://tailwindcss.com/docs/max-width). (test: yes — CmsPagesTest class/width contract; C4; model: inherit)

## Phase 5 — User Story 4 (locale + footer)

- [X] T008 [P] [US4] Footer: remove `data-cookie-reopen`; add other-locale link via `LocalizedUrl::forLocale` in `resources/views/layouts/partials/footer.blade.php`. (test: yes — CookieConsentTest; C3; model: inherit)
- [X] T009 [P] [US4] Cookie + privacy hero locale control in `resources/views/pages/templates/legal.blade.php`. (test: yes — CmsPagesTest; C3; model: inherit)
- [X] T010 [US4] Obvious IT/EN buttons on `resources/views/layouts/partials/cookie-banner.blade.php` (must not clear `sh_cookie_consent`). Cite [localization](https://laravel.com/docs/13.x/localization). (test: yes — CookieConsentTest; C4; model: inherit)

## Phase 6 — User Story 5 (legal document + one-shot prod sync)

- [X] T011 [US5] Drop footer-preferences sentence in `database/seeders/Data/LegalPagesContent.php`; keep cookie-page button. Local `ddev exec php artisan site:sync-legal-pages --force`. Cite [Artisan](https://laravel.com/docs/13.x/artisan). (test: yes — string assertion in CmsPagesTest or a unit on LegalPagesContent; C3; model: inherit)
- [X] T012 [US5] Add `deploy/sync-legal-pages-once.sh` (storage marker so later deploys do not clobber CMS) and call it from `deploy/post-deploy.sh` if present ([legal-sync](./contracts/legal-sync.md)). (test: no — script presence; C4; model: inherit)

## Phase 6.5 — User Story 6 (Home)

- [X] T013 [US6] Home hero: Diventa socio button to the membership landing in `resources/views/pages/templates/home.blade.php`. Cite [Blade](https://laravel.com/docs/13.x/blade). (test: yes — HomePageTest; C3; model: inherit)
- [X] T014 [US6] Hover animation on the home manifesto quote in `resources/css/app.css`, gated by `prefers-reduced-motion`. Cite [Tailwind prefers-reduced-motion](https://tailwindcss.com/docs/hover-focus-and-other-states#prefers-reduced-motion). (test: no screenshots; C3; model: inherit)

## Phase 7 — Verify

- [X] T015 Pint + `ddev exec php artisan test` + `bash bin/dev-rebuild-frontend.sh`. Cite [testing](https://laravel.com/docs/13.x/testing), [Pint](https://laravel.com/docs/13.x/pint). (test: yes; C2; model: inherit)

## Dependencies

T001 before US1–US4 copy. T004 before T005/T006. T006 must not restyle the phone band. T011 before T012. T015 last. No production push in these tasks — that is a later owner command after implement UAT, except T012 prepares the once-script for that push.

## Parallel

T001 || (after T001) T002+T003. T008 || T009. T007 can overlap US4. T013 || T014.

## MVP

T001–T003 already improve the phone landing. Full owner ask is T001–T015.
