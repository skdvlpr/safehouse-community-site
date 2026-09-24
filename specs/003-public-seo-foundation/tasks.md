# Tasks: Public SEO foundation

**Input**: Design documents from `/specs/003-public-seo-foundation/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/, quickstart.md

**Tests**: Feature tests for discovery head and sitemap only. They lock locale fallback and index exclusions. No screenshot tests.

**Organization**: Tasks are grouped by user story. Implement only after the owner says yes to this list. `tasks.md` is not a launch order.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: US1, US2, US3

## Phase 1: Setup

**Purpose**: Staff labels for the new fields

- [x] T001 [P] Add Italian and English CMS labels for search title (max 70 characters) and search description (max 160 characters) in `lang/it/cms.php` and `lang/en/cms.php`. Cite [Filament text input](https://filamentphp.com/docs/4.x/forms/text-input). (test: no; C2; model: inherit)

---

## Phase 2: Foundational (Blocking)

**Purpose**: Storage and the same-locale reader every story uses

**⚠️ CRITICAL**: No user story work until this phase is complete

- [x] T002 Add a nullable JSON `seo` column on `articles` and `donation_campaigns` in `database/migrations/`, and cast `seo` to array on `app/Models/Article.php` and `app/Models/DonationCampaign.php`. Shape: `{"title":{"it":"","en":""},"description":{"it":"","en":""}}`. Cite [laravel-translatable](https://spatie.be/docs/laravel-translatable/v6/introduction). (test: no — exercised by T004/T009; C3; model: inherit)
- [x] T003 Implement `app/Support/DiscoveryText.php`. Read only the requested locale. Title: override, else visible title, else the route's existing lang string. Description: override, else excerpt or first plain-text body paragraph, else the existing lead; strip tags; cut at about 160 characters on a word boundary. Never call `PageService::localizedMeta` and never use the other locale. Page overrides live in `meta.seo_title.{locale}` and `meta.seo_description.{locale}` (max 70 / max 160). Article and campaign overrides live in `seo`. (test: yes — T004; C6; model: inherit)

**Checkpoint**: Helper returns same-locale text for a page, an article, and a campaign

---

## Phase 3: User Story 1 - Unique title and description (Priority: P1) MVP

**Goal**: Each public URL has a locale-appropriate `<title>` and `<meta name="description">`. Editors can override CMS pages, news, editorial articles, and campaigns.

**Independent Test**: `/it` and `/en` titles and descriptions differ and match their language. An Italian override does not appear on the English URL. Two published URLs do not share a description unless the editor copied it.

### Tests for User Story 1

- [x] T004 [US1] Write failing feature tests in `tests/Feature/DiscoveryHeadTest.php`: editor override is rendered; empty English description does not contain the Italian override; home and one CMS page do not share the same description. Cite [Laravel testing](https://laravel.com/docs/13.x/testing). (test: yes; C4; model: inherit)

### Implementation for User Story 1

- [x] T005 [P] [US1] Add Filament `TextInput` fields `meta.seo_title.{locale}` `maxLength(70)` and `meta.seo_description.{locale}` `maxLength(160)` in `app/Filament/Resources/PageResource.php`. Cite [Filament text input](https://filamentphp.com/docs/4.x/forms/text-input). (test: yes — T004; C3; model: inherit)
- [x] T006 [P] [US1] Add the same limits on `seo.title.{locale}` and `seo.description.{locale}` in `app/Filament/Resources/ArticleResource.php` and `app/Filament/Resources/EditorialArticleResource.php`. (test: yes — T004; C3; model: inherit)
- [x] T007 [P] [US1] Add the same limits on `seo.title.{locale}` and `seo.description.{locale}` in `app/Filament/Resources/DonationCampaignResource.php`. (test: yes — T004; C3; model: inherit)
- [x] T008 [US1] Render the discovery title (plus the existing `— Safe House` suffix) and `<meta name="description">` from `resources/views/layouts/partials/discovery-head.blade.php`, included by `resources/views/layouts/app.blade.php` through `@stack('head')`. Cite [Laravel Blade](https://laravel.com/docs/13.x/blade). (test: yes — T004; C4; model: inherit)

**Checkpoint**: US1 passes `DiscoveryHeadTest` without alternates or sitemap

---

## Phase 4: User Story 2 - Published URL index (Priority: P1)

**Goal**: Crawlers can fetch every published public URL and none of the private ones.

**Independent Test**: `/sitemap.xml` contains donate, volunteer, 5×1000, about, and contact. An unpublished page, a preview URL, and a donation thank-you URL are absent. `/robots.txt` names the sitemap.

### Tests for User Story 2

- [x] T009 [US2] Write failing feature tests in `tests/Feature/SitemapTest.php` for those inclusions and exclusions, including campaign privacy only while the campaign is active. Cite [Laravel testing](https://laravel.com/docs/13.x/testing). (test: yes; C4; model: inherit)

### Implementation for User Story 2

- [x] T010 [US2] Serve `GET /sitemap.xml` from `app/Http/Controllers/SitemapController.php` and `routes/web.php` (outside the locale prefix). `Content-Type: application/xml; charset=UTF-8`. Include published CMS pages that have that locale's title (membership, cookie, privacy included when published), both locales of home, donations index, 5×1000, volunteer, news index, editorial index, published news and editorial articles, and active campaign plus privacy URLs. `lastmod` from `updated_at` as `Y-m-d` when present. Cite [Laravel routing](https://laravel.com/docs/13.x/routing) and [Laravel responses](https://laravel.com/docs/13.x/responses). (test: yes — T009; C5; model: inherit)
- [x] T011 [US2] Replace `public/robots.txt` with `GET /robots.txt` in `app/Http/Controllers/RobotsController.php` and `routes/web.php`. Body allows crawling and prints `Sitemap:` plus the absolute sitemap URL. Cite [Laravel routing](https://laravel.com/docs/13.x/routing). (test: yes — T009; C3; model: inherit)

**Checkpoint**: US2 passes `SitemapTest` without Open Graph

---

## Phase 5: User Story 3 - Locales linked, previews private (Priority: P2)

**Goal**: Italian and English declare each other only when both exist. Previews and thank-you stay non-indexable. Share tags use the URL locale.

**Independent Test**: A translated page has `rel="alternate"` for `it` and `en` and no `ru`. A preview has `noindex` and is absent from the sitemap. `og:locale` matches the URL.

### Tests for User Story 3

- [x] T012 [US3] Extend `tests/Feature/DiscoveryHeadTest.php` for canonical, `hreflang` it/en only, no `ru`, `og:title` / `og:description` / `og:locale`, preview `<meta name="robots" content="noindex, nofollow">`, and thank-you noindex. (test: yes; C4; model: inherit)

### Implementation for User Story 3

- [x] T013 [US3] Add canonical, alternate links, and Open Graph tags to `resources/views/layouts/partials/discovery-head.blade.php`. Alternates only when that locale has its own title. `og:image` is the first existing carousel or background image, otherwise `images/logo.png`. `og:locale` is `it_IT` or `en_US`. Cite [Laravel Blade](https://laravel.com/docs/13.x/blade). (test: yes — T012; C4; model: inherit)
- [x] T014 [US3] Keep `X-Robots-Tag: noindex, nofollow` and ensure the meta robots tag is present for previews in `app/Http/Controllers/PageController.php`, `app/Http/Controllers/ArticleController.php`, and `app/Http/Controllers/EditorialArticleController.php`. (test: yes — T012; C3; model: inherit)
- [x] T015 [US3] Mark donation thank-you `noindex, nofollow` in `app/Http/Controllers/DonationCampaignController.php` and keep it out of the sitemap built in `app/Http/Controllers/SitemapController.php`. (test: yes — T009 and T012; C3; model: inherit)

**Checkpoint**: US3 passes the extended head test. Sitemap exclusions from US2 still pass.

---

## Phase 6: Polish

- [x] T016 Run `ddev exec ./vendor/bin/pint --dirty` and `ddev exec php artisan test`. Cite [Laravel Pint](https://laravel.com/docs/13.x/pint) and [Laravel testing](https://laravel.com/docs/13.x/testing). (test: yes; C2; model: inherit)

---

## Dependencies & Execution Order

### Phase Dependencies

- Setup (T001) has no dependency.
- Foundational (T002, T003) blocks every user story. T003 can start after T002.
- US1 after foundational. T004 before T008. T005, T006, T007 can run together after T001 and T002.
- US2 after foundational. T009 before T010 and T011. T011 after the route file is free, so after T010.
- US3 after US1's head partial exists (T008). T012 before T013. T014 can run beside T013. T015 touches the sitemap, so after T010.
- Polish last.

### User Story Dependencies

- **US1 (P1)**: after foundational. No dependency on US2 or US3.
- **US2 (P1)**: after foundational. Independently testable. Does not require Open Graph.
- **US3 (P2)**: after US1 head partial and US2 sitemap controller.

### Parallel Opportunities

- T005, T006, and T007 touch different Filament resources.
- T014 touches preview controllers while T013 edits the head partial.

### Parallel Example: User Story 1

```text
T005 PageResource SEO fields
T006 ArticleResource and EditorialArticleResource SEO fields
T007 DonationCampaignResource SEO fields
```

---

## Implementation Strategy

### MVP First (User Story 1)

1. T001–T003
2. T004–T008
3. Stop and check titles and descriptions on `/it` and `/en` before the sitemap.

### Incremental Delivery

1. US1: snippets exist and editors can override them.
2. US2: crawlers get a clean URL list.
3. US3: language alternatives and private previews.
4. Owner UAT from `quickstart.md`, then a separate push decision.

### Out of scope

No measurement tags, no donate/volunteer/about body rewrite, no Satispay banner, no `/ru`, no CRM writes, no DPA.

## Notes

- [P] tasks use different files.
- Tests in T004, T009, and T012 are written to fail before their implementation tasks.
- Do not implement from this file until the owner says yes.
