# Tasks: 002.4 urgent visual and legal

**Input**: [spec.md](./spec.md), [plan.md](./plan.md)

## Phase 1 — Chrome

- [x] T001 [US2] Self-host Nunito Sans and set `--font-sans`. Cite [Tailwind font-family](https://tailwindcss.com/docs/font-family) and [Nunito Sans](https://fonts.google.com/specimen/Nunito+Sans). (test: yes — DesignTokensTest; C2; model: inherit)
- [x] T002 [US1] Restore horizontal opaque cookie banner (`max-w-5xl`, actions row). Cite [WCAG contrast](https://www.w3.org/WAI/WCAG22/Understanding/contrast-minimum.html). (test: yes; C3; model: inherit)
- [x] T003 [US5] Larger logo/wordmark, bolder nav, taller header. (test: no; C2; model: inherit)
- [x] T004 [US4] Solid legal hero + metadata chips; FAQ button; Anzio seat in lang + LegalPagesContent. (test: yes — CmsPagesTest; C3; model: inherit)

## Phase 2 — Landing + membership

- [x] T005 [US3] Landing two-column hero + `<hr>` cards; hide empty visual. Cite [Laravel Blade](https://laravel.com/docs/13.x/blade). (test: yes; C4; model: inherit)
- [x] T006 [US6] Membership dialog + POST + mail + rate limit + Turnstile. Cite [validation](https://laravel.com/docs/13.x/validation), [mail](https://laravel.com/docs/13.x/mail), [routing rate limiting](https://laravel.com/docs/13.x/routing#rate-limiting), [dialog](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/dialog), [Turnstile](https://developers.cloudflare.com/turnstile/get-started/client-side-rendering/). (test: yes — MembershipFormTest; C6; model: inherit)
- [x] T007 [US6] Best-effort Espo Lead `MemberContact` using existing fields. Cite [Espo API](https://docs.espocrm.com/development/api/). CRM layout/PDF = [crm-agent brief](./contracts/crm-agent-lead-associato.md). (test: no — HTTP skipped when CRM unset; C5; model: inherit)

## Phase 3 — Local verify

- [x] T008 Local `site:sync-legal-pages --force`. No production. Cite [Artisan](https://laravel.com/docs/13.x/artisan). (test: no; C1; model: inherit)
- [x] T009 Pint + `ddev exec php artisan test` + `bash bin/dev-rebuild-frontend.sh`. (test: yes; C1; model: inherit)

## Phase 4 — Contemporary landing + CMS blocks

- [x] T010 [US7] Split landing CMS into hero / values ticker / max 6 cards / contact heading. Cite [Filament repeater](https://filamentphp.com/docs/4.x/forms/repeater). Fallback parse legacy `<hr>` HTML. (test: yes — LandingContentTest; C6; model: inherit)
- [x] T011 [US7] Visual template + motion: ticker, off-screen card reveal, phone stack, pause control. Cite [Intersection Observer](https://developer.mozilla.org/en-US/docs/Web/API/Intersection_Observer_API), [prefers-reduced-motion](https://developer.mozilla.org/en-US/docs/Web/CSS/@media/prefers-reduced-motion), [WCAG 2.2.2](https://www.w3.org/WAI/WCAG22/Understanding/pause-stop-hide.html). (test: no screenshots; C7; model: Fable 5.1 subagent)
- [x] T012 [US7] No Landing eyebrow; sede legale + Compila domanda in contact band without email. (test: yes — MembershipFormTest; C3; model: inherit)
- [x] T013 Owner-authorised this cycle: Pint, full test, frontend rebuild, commit, push `main` (GitHub Actions deploy). No production `site:sync-legal-pages`. (test: yes; C2; model: inherit)
