# Agent Handoff — Safehouse.community

Living document. **Auto** writes before Power switch; **Power** writes before returning to Auto.

---

## Last updated

| Field | Value |
|-------|-------|
| **Agent** | Auto |
| **Date** | 2026-06-29 |
| **Sprint** | Phase P1 (i18n + DB) |

---

## Completed (Notion task → evidence)

| Task | Status | Evidence |
|------|--------|----------|
| P0-T01 … P0-T08 | Done | Phase P0 complete |
| Dual-agent docs | Done | `AGENTS.power.md`, `docs/HANDOFF.md` |
| P1-T01 | Done | `config/locales.php` — it/ru/en |
| P1-T02 | Done | `app/Http/Middleware/SetLocale.php` |
| P1-T03 | Done | `bootstrap/app.php` alias `setlocale` |
| P1-T04 | Done | `routes/web.php` locale group + `home` route |
| P1-T05 | Done | Root `/` → redirect `/it` |
| P1-T06 | Done | `spatie/laravel-translatable` v6.14.1 |
| P1-T07 | Done | `pages` table + `Page` model |
| P1-T08 | Done | `article_categories` + `articles` |
| P1-T09 | Done | `volunteers` table + model |
| P1-T10 | **Cancelled** | No local donation DB — see § Donations architecture below |

---

## Architectural decisions

### Donations — no DB on public website (2026-06-29)

**Stakeholder:** payment history on the public Laravel app is **out of scope**.

| What | Where |
|------|--------|
| Donor checkout UI | Donorbox embed on `/{locale}/donazioni` (P5-T03) |
| Payment records | **EspoCRM** via Donorbox API + webhooks |
| Removed from repo | `donations` table, `Donation` model, `donations` rate limiter |
| Cancelled tasks | P1-T10, P5-T01, P5-T02 |

---

## In Testing (awaiting user QA)

| Task | Notes |
|------|-------|
| — | (none) |

---

## Blocked

| Item | Blocker |
|------|---------|
| Notion MCP | Intermittent `Unauthorized` — logs duplicated here; user may paste to Notion manually if needed |

---

## Files touched (recent)

- `AGENTS.md`, `AGENTS.power.md`, `docs/HANDOFF.md`
- `app/Http/Middleware/SecurityHeaders.php`
- `bootstrap/app.php`
- `tests/Feature/SecurityHeadersTest.php`
- `app/Providers/AppServiceProvider.php`
- `tests/Feature/RateLimiterRegistrationTest.php`
- `config/locales.php`, `tests/Feature/LocalesConfigTest.php`
- `app/Http/Middleware/SetLocale.php`, `tests/Feature/SetLocaleMiddlewareTest.php`
- `bootstrap/app.php` (setlocale alias), `tests/Feature/SetLocaleMiddlewareRegistrationTest.php`
- `routes/web.php` (locale group), `tests/Feature/LocaleRoutesTest.php`
- `tests/Feature/RootRedirectTest.php`
- `composer.json` + `spatie/laravel-translatable`, `tests/Feature/TranslatablePackageTest.php`
- `database/migrations/2026_06_29_000001_create_pages_table.php`, `app/Models/Page.php`, `tests/Feature/PageModelTest.php`
- `database/migrations/2026_06_29_000002_*`, `2026_06_29_000003_*`, Article models, `tests/Feature/ArticleSchemaTest.php`
- `database/migrations/2026_06_29_000004_create_volunteers_table.php`, `app/Models/Volunteer.php`, `tests/Feature/VolunteerModelTest.php`
- Removed donations artifacts; `2026_06_29_000006_drop_donations_table.php`; updated `AGENTS.md` §8

---

## Verification run

```bash
ddev exec php artisan test --filter=RateLimiter   # 1 passed
ddev exec php artisan test --filter=SecurityHeaders   # 3 passed (last known)
curl -sI https://safehouse-community-site.ddev.site/ | grep -iE 'x-frame|x-content|referrer'
```

---

## Ready for Power Sprint 1?

**No** — complete first:

- [x] P0-T06 rate limiters → Done
- [x] P0-T07 session config → Done
- [x] P0-T08 password defaults → Done
- [ ] P1-T01 … P1-T12 → Done + user QA (i18n + DB schema)
- [ ] `ddev exec php artisan test` — all green
- [ ] Auto message to user: «Переключайся на Power» + launch prompt from `AGENTS.power.md`

When all checked, set **Ready for Power Sprint 1** = **Yes** below.

| Ready for Power Sprint 1 | **No** |
|--------------------------|--------|

---

## Next for Auto (immediate)

| Field | Value |
|-------|-------|
| **Notion task** | P1-T11 — gdpr_consents table migration |
| **Notion URL** | https://app.notion.com/p/38e8d469d40581198a89e031b1b21cc7 |
| **First action** | Migration + GdprConsent model |
| **Verify** | migrate + model test (hashes only) |

---

## Next for Power (after P1-T12 Done)

Power Sprint 1: **P2-T01 … P2-T04** — see [`AGENTS.power.md`](../AGENTS.power.md).

Launch prompt at bottom of `AGENTS.power.md`.

---

## Notes for user

Auto agent continues one Notion task per step through P0 and P1. Switch to Power (Claude/Gemini) only when Auto announces and this file shows **Ready for Power Sprint 1 = Yes**.
