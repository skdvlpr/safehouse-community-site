# Implementation Plan: Public SEO foundation

**Branch**: `003-public-seo-foundation` | **Date**: 2026-09-24 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/003-public-seo-foundation/spec.md`. Reconciliation: `.specify/progress/050-reconcile-003-public-seo.md`.

## Summary

Add locale-correct search and share metadata, a published URL index, and head language alternatives. Editors override title and description where a staff form already exists. Empty overrides fall back to the same locale's visible heading or summary, never to the other language. Previews stay noindex and out of the index. Donation thank-you stays out of the index. Implement only after `/speckit-tasks` and an explicit owner yes.

## Technical Context

**Language/Version**: PHP 8.4 / Laravel 13

**Primary Dependencies**: Blade `@stack('head')` ([Blade](https://laravel.com/docs/13.x/blade)), routes and XML/text responses ([routing](https://laravel.com/docs/13.x/routing), [responses](https://laravel.com/docs/13.x/responses)), Filament v4 `TextInput` ([text input](https://filamentphp.com/docs/4.x/forms/text-input)), existing `HasTranslations` ([laravel-translatable](https://spatie.be/docs/laravel-translatable/v6/introduction)), `PageService`, `LocalizedUrl`

**Storage**: Page search text in existing `pages.meta` JSON. New nullable `seo` JSON on `articles` and `donation_campaigns`. No new tables. Sitemap is generated on request, not stored.

**Testing**: PHPUnit Feature ([testing](https://laravel.com/docs/13.x/testing)) — head markup and sitemap XML, not screenshots

**Target Platform**: DDEV preview; production https://safehouse.community only after a later owner push

**Project Type**: web application

**Performance Goals**: Sitemap is one query set per request. No extra CDN or third-party SEO service.

**Constraints**: Public locales `it` and `en` only. No measurement scripts, no AdSense, no landing-copy rewrite, no Satispay banner, no CRM writes, no DPA. SEO text must not use the Italian fallback inside `PageService::localizedMeta`.

**Scale/Scope**: Head partial, one discovery helper, Filament fields on pages / articles / campaigns, `/sitemap.xml`, dynamic `/robots.txt`, tests

Vendor docs this plan turn: [Laravel routing](https://laravel.com/docs/13.x/routing); [Laravel responses](https://laravel.com/docs/13.x/responses); [Laravel Blade](https://laravel.com/docs/13.x/blade); [Laravel testing](https://laravel.com/docs/13.x/testing); [Filament text input](https://filamentphp.com/docs/4.x/forms/text-input); [laravel-translatable](https://spatie.be/docs/laravel-translatable/v6/introduction).

## Constitution Check

- One active spec: `003` after closed `002.5`. Pass.
- No CRM schema edits. Pass.
- No DPA. Pass.
- Implement from `tasks.md` only after owner yes. This command stops at design. Pass.
- No push in this command. Pass.
- Official docs opened this turn and cited. Pass.
- S-I18N it+en, Italian primary. Pass.
- Principle XI: tasks will name models; no implement launch here. Pass.

Post-design re-check: same gates. Thank-you exclusion and campaign-privacy inclusion are recorded in [research.md](./research.md) from the 050 reconciliation. They do not add a product the spec forbids.

## Project Structure

### Documentation (this feature)

```text
specs/003-public-seo-foundation/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── discovery-head.md
│   └── url-index.md
├── checklists/requirements.md
└── tasks.md              # /speckit-tasks — not created here
```

### Source Code (this feature)

```text
app/Support/DiscoveryText.php
app/Http/Controllers/SitemapController.php
app/Http/Controllers/RobotsController.php
resources/views/layouts/partials/discovery-head.blade.php
resources/views/layouts/app.blade.php
app/Filament/Resources/PageResource.php
app/Filament/Resources/ArticleResource.php
app/Filament/Resources/DonationCampaignResource.php
database/migrations/*_add_seo_to_articles_and_donation_campaigns.php
lang/it/cms.php
lang/en/cms.php
public/robots.txt                 # remove; route serves it
routes/web.php
tests/Feature/DiscoveryHeadTest.php
tests/Feature/SitemapTest.php
```

## Complexity Tracking

No constitution violations.
