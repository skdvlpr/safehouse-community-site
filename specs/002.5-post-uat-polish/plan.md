# Implementation Plan: Post-UAT visual and legal polish

**Branch**: `002.5-post-uat-polish` | **Date**: 2026-09-21 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/002.5-post-uat-polish/spec.md`

## Summary

UAT repair of shipped `002.4`: drop the ticker pause control; phone-only swipe-down hint; desktop-only Contattaci filler (square socials + Statuto/FAQ/donate/volunteer); even translucent legal column; footer locale switch instead of cookie reopen; banner IT/EN; one-shot production `site:sync-legal-pages --force`. Implement only after owner yes.

## Technical Context

**Language/Version**: PHP 8.4 / Laravel 13

**Primary Dependencies**: Blade, Tailwind v4, existing `LocalizedUrl`, `SocialLinksSettings`, `LandingContent`, cookie-consent JS, `LegalPagesContent`, Artisan `site:sync-legal-pages`

**Storage**: Existing `pages` JSON; no new tables. One-shot marker via self-deleting `deploy/sync-legal-pages-once.sh`

**Testing**: PHPUnit Feature ([Laravel testing](https://laravel.com/docs/13.x/testing)) — markup assertions, not screenshots

**Target Platform**: DDEV preview; production https://safehouse.community after authorised push

**Project Type**: web application

**Performance Goals**: No new third-party font/CDN. Ticker still CSS-driven.

**Constraints**: No public `/ru`; no DPA; no CRM repo writes; consent gate unchanged; phone Contattaci composition locked; legal sync once not every deploy

**Scale/Scope**: Landing contact band, legal template chrome, footer, cookie banner, legal HTML sentence, one-shot deploy script

Vendor docs this plan turn: [Laravel Blade](https://laravel.com/docs/13.x/blade); [Laravel localization](https://laravel.com/docs/13.x/localization); [Tailwind max-width](https://tailwindcss.com/docs/max-width); [Laravel Artisan](https://laravel.com/docs/13.x/artisan); [Laravel testing](https://laravel.com/docs/13.x/testing); [WCAG 2.2.2](https://www.w3.org/WAI/WCAG22/Understanding/pause-stop-hide.html).

## Constitution Check

- One active spec: `002.5` amendment before `003`. Pass.
- No CRM schema edits. Pass.
- No DPA. Pass.
- Implement from `tasks.md` only after owner yes. Pass.
- Git: owner asked commit after spec+plan; no push until implement is authorised. Pass.
- Official docs opened this turn and cited. Pass.
- S-I18N it+en, IT primary. Pass.
- Principle XI: advanced models proposed in tasks, Launch vs Replace required. Pass.

Post-design re-check: same gates. One-shot production legal sync is owner-ordered (FR-010), not a silent CMS overwrite loop.

## Project Structure

### Documentation (this feature)

```text
specs/002.5-post-uat-polish/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/locale-switch.md
├── contracts/legal-sync.md
└── tasks.md
```

### Source Code

```text
resources/views/pages/templates/landing.blade.php
resources/views/pages/templates/legal.blade.php
resources/views/layouts/partials/footer.blade.php
resources/views/layouts/partials/cookie-banner.blade.php
resources/views/components/social-links.blade.php
resources/css/app.css
resources/js/landing-motion.js
lang/it/site.php
lang/en/site.php
database/seeders/Data/LegalPagesContent.php
deploy/sync-legal-pages-once.sh
deploy/post-deploy.sh
tests/Feature/MembershipFormTest.php
tests/Feature/CookieConsentTest.php
tests/Feature/CmsPagesTest.php
```

**Structure Decision**: Single Laravel app; touch existing templates/CSS/JS/lang/deploy. No new PHP services unless Statuto URL needs a one-line PageService lookup.

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| Ticker has no on-page pause (WCAG 2.2.2 residual) | Owner UAT: remove Pause from the marquee | Keep pause — owner said the control should go. Reduced-motion still pauses. |
