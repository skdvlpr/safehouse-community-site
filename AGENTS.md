# Safehouse.community — Public Website Rulebook

**Framework:** Laravel 13.x | **Admin:** Filament v4 | **Executor:** AI agents (step-by-step)
**Last updated:** 2026-06-29
**Languages:** specs / code / Notion = **English** | User chat = **Russian** | Site UI = **Italian** (primary) + **Russian** + **English**

---

## MANDATORY PRE-TASK PROTOCOL

Before implementing **any** task, the executor MUST:

1. Re-read this file in full.
2. Fetch the current **Notion project page** and **target task page** from the canonical trackers (URLs below).
3. Read referenced files from the repository — never assume content from memory or old plans.
4. Implement **exactly one** Notion task per user request (one problem per step).
5. Run automated verification defined in the task → move status to **Testing**.
6. Post a **User QA checklist** in the task body; **never** move **Testing → Done** without explicit user confirmation.
7. **Notion progress log (mandatory, proactive):** After every implementation step, testing milestone, or planning decision — **without waiting for the user to remind you** — append executor notes to the **task page** and, when relevant, the **project page** in Notion. Never overwrite prior logs. Update task status (`In progress` → `Testing` → `Done`). Logs must be **handoff-ready**: current state, files changed, verification performed, blockers, exact next steps.
8. **Git push:** only when the user explicitly asks. Local commits: ask if unclear.
9. Do **not** mark tasks Done in the archived Gomercato tracker — it is read-only history.
10. **DDEV:** `ddev start` on the existing project is fine. **Never** without explicit user approval: `ddev stop`, `ddev poweroff`, `ddev delete`, creating a **new** DDEV project/config, or adding extra services/containers (e.g. new `ddev add-on`, new service in `config.yaml`).

### Notion logging (when Notion MCP is available)

**Do this proactively on every task** — the user must not need to ask. Before starting: fetch project + task pages. During/after work: append executor log, User QA checklist when entering **Testing**, and project-level summary for significant milestones. Mark **Done** only when acceptance criteria + user QA are satisfied. If Notion MCP fails: retry auth once, then report the blocker — do not skip logging silently.

---

## NOTION — PROJECT & TASK TRACKERS (canonical)

| Role | URL |
|------|-----|
| **Active project** | https://app.notion.com/p/38e8d469d405810980bbf8fbcf34ae94 |
| **Projects DB** | https://app.notion.com/p/2fb8d469d4058093a291fd990185824d |
| **Tasks DB** | https://app.notion.com/p/38d8d469d40580b8b87ee0681b9d929c |
| **Archive (old web plan)** | https://app.notion.com/p/3588d469d4058065bd38f56abfde821a |
| **Related CRM project** | https://app.notion.com/p/38d8d469d405817cbd23f6cfb3ce32af |

**Rules:**

- Every new task MUST link **Project** to the correct project page.
- Task names, acceptance criteria, and executor logs in Notion = **English only**.
- Status workflow: `Not started` → `In progress` → `Testing` → `Done` (or `Cancelled`).

---

## SECTION 1 — PROJECT OVERVIEW

### Mission

Public website for **Safehouse NGO** (Italy): assistance to people in crisis — food, clothing, employment support, addiction recovery, homelessness. The site must inform, recruit volunteers, accept donations, and publish news — with a **minimal, secure CMS** suitable for serious production.

### Domains & related systems

| System | URL | Repo |
|--------|-----|------|
| Public website | https://safehouse.community | https://github.com/skdvlpr/safehouse-community-site |
| Internal CRM | https://crm.safehouse.community | https://github.com/skdvlpr/noprofit-espocrm |

**Design goal:** Website and CRM must feel like one brand — shared logo, colors, polygon background, typography. CRM already ships **Safehouse Aurora** (dark) and **Safehouse Aurora Light** themes.

### Local development

```bash
cd safehouse-community-site
ddev start
# → https://safehouse-community-site.ddev.site
ddev composer install
ddev npm install && ddev npm run build
ddev exec php artisan migrate
```

PHP **8.3**, MariaDB **11.8**, webserver **nginx-fpm** (DDEV).

**DDEV policy (user rule):** Starting the stack with `ddev start` is allowed. Do **not** stop, remove, or reconfigure DDEV (new projects, add-ons, extra containers, `config.yaml` service changes) unless the user explicitly asks.

---

## SECTION 2 — TECH STACK (confirmed)

| Layer | Technology | Notes |
|-------|------------|-------|
| Framework | **Laravel 13** | User mandate — do not downgrade to 11 |
| Admin CMS | **Filament v4** | Panel path `/cms-safehouse` |
| Public UI | Blade + Tailwind + Vite | Align with Aurora design tokens |
| I18n | `spatie/laravel-translatable` + `/{locale}/` routes | Locales: `it`, `ru`, `en` |
| Donations | **Stripe Payment Element** + Filament campaigns → **EspoCRM** | See Section 8; no local payment DB |
| Media | `spatie/laravel-medialibrary` | Private disk + signed URLs in prod |
| RBAC | `spatie/laravel-permission` | CMS roles only |
| SEO | `spatie/laravel-sitemap` | Post-MVP |
| Cache / queue | Redis in production; DB driver OK in dev | Horizon deferred |
| HTTP edge | **Caddy v2** | CSP + HSTS at edge **only** |
| Runtime | PHP-FPM first | Octane deferred until perf baseline |
| CI/CD | GitHub Actions | pint, phpunit, composer audit |
| Fonts | **JetBrains Sans** (self-hosted, same as CRM) | Not Google Fonts |

### Explicitly OUT of MVP scope

- Mobile app, e-commerce shop
- PayPal (after Stripe donations stable)
- Laravel Octane / Horizon / Telescope in production
- Public user accounts / comments
- EspoCRM bi-directional sync (future epic)
- Fake “Done” tasks without code — always verify in repo

---

## SECTION 3 — BRAND & DESIGN SYSTEM (Safehouse Aurora alignment)

The public site must visually match **crm.safehouse.community** (see attached Prima Nota screenshot: dark polygon tessellation, red accent, glass panels, JetBrains Sans, Safehouse logo top-left).

### Canonical asset source (CRM repo)

Copy or symlink branding assets from the sibling repo:

**Path:** `/home/skoksharov/nonprofit-espocrm` (package name `noprofit-espocrm`)

| Asset | CRM path | Use on website |
|-------|----------|----------------|
| Logo (navbar) | `client/img/logo.svg` | Header, footer, OG image base |
| Favicon | `client/img/favicon.svg`, `favicon.ico`, `favicon-196.png`, `apple-touch-icon.png` | `public/` |
| Dark polygon BG | `client/custom/css/safehouse-aurora/bg.svg` | Full-page background |
| Light polygon BG | `client/custom/css/safehouse-aurora/bg-light.svg` | Optional light mode |
| Theme CSS reference | `client/custom/css/safehouse-aurora/safehouse-aurora.css` | Token extraction |
| Font | `client/fonts/jet-brains-sans/JetBrainsSans[wght].woff2` | `resources/fonts/` |

**CRM theme module:** `custom/Espo/Modules/SafehouseAuroraThemes/`

### Color tokens (dark theme — primary for public site)

Use CSS variables / Tailwind theme extension — **no raw hex in Blade/PHP**.

| Token | Hex | Usage |
|-------|-----|-------|
| Page background | `#050505` | Base under polygon SVG |
| Text primary | `#e8e8ec` | Body copy |
| Text muted | `#a1a1aa` | Secondary text |
| Brand primary | `#dc2626` | Buttons, links |
| Brand primary hover | `#b91c1c` | Hover states |
| Brand danger | `#ef4444` | Errors, expense-style figures |
| Brand success | `#65a30d` | Success, income-style figures |
| Brand warning | `#d97706` | Warnings |
| Link | `#f87171` → `#ef4444` | Anchors |
| Glass panel bg | `rgba(20, 20, 26, 0.42)` | Cards, content panels |
| Glass border | `rgba(255, 255, 255, 0.12)` | Panel borders |
| Modal surface | `#1c1c21` | Modals / overlays |

### Light theme tokens (optional public pages)

For accessibility or user toggle later — match **Safehouse Aurora Light**:

| Token | Hex |
|-------|-----|
| Background | `#dfe1e6` / `#fcfdfe` |
| Text | `#18181b` |
| Brand primary | `#ef4444` |
| Muted | `#52525b` |

### Background implementation pattern (from CRM)

```css
html::before {
  content: '';
  position: fixed;
  inset: 0;
  z-index: -1;
  pointer-events: none;
  background:
    linear-gradient(rgba(0, 0, 0, 0.48), rgba(0, 0, 0, 0.55)),
    url('/images/bg.svg') center / cover no-repeat;
  background-color: #050505;
}
```

Respect `prefers-reduced-transparency: reduce` — disable decorative background when set.

### Typography

```css
@font-face {
  font-family: 'JetBrains Sans';
  font-weight: 300 800;
  font-display: swap;
  src: url('/fonts/JetBrainsSans[wght].woff2') format('woff2');
}
--font-family: 'JetBrains Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
```

### UI patterns to mirror from CRM

- Frosted/glass content panels over polygon background
- Red primary CTAs (Donate, Volunteer)
- Green/red semantic amounts (if showing stats)
- Logo + “SAFEHOUSE COMMUNITY” wordmark in header
- High contrast, minimal chrome — reference sites: [beeozanam.com](https://beeozanam.com), [openarms.es](https://openarms.es)

### Deprecated tokens (old web plan — do not use)

The archived plan listed Cabinet Grotesk + Satoshi and `#121212` / `#E53E3E`. **Replace** with Aurora tokens above for CRM consistency.

---

## SECTION 4 — MULTILINGUAL ARCHITECTURE (mandatory)

### Locales

| Code | Role |
|------|------|
| `it` | **Primary** — default redirect from `/` |
| `ru` | Secondary |
| `en` | Secondary |

### Rules

1. **Zero hardcoded** user-facing strings in Blade/PHP — use `__()` / `trans()`.
2. DB content: JSON columns via `spatie/laravel-translatable` (`title`, `slug`, `body`, etc.).
3. URL structure: `/{locale}/path` — e.g. `/it/chi-siamo`, `/ru/o-nas`, `/en/about-us`.
4. Locale detection: URL segment → session → `Accept-Language` → default `it`.
5. Adding a locale = new `lang/{locale}/` files + DB seeds — **no code changes** to routing regex if `config('app.available_locales')` is updated.
6. Date/number formatting: `Carbon::setLocale()` + locale-aware helpers.
7. Invalid locale in URL → **404**.

### Route skeleton

```php
Route::get('/', fn () => redirect()->to('/' . config('app.default_locale', 'it')));

Route::prefix('{locale}')
    ->where(['locale' => implode('|', config('app.available_locales'))])
    ->middleware(['setlocale'])
    ->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('home');
        // ...
    });
```

---

## SECTION 5 — SITE MAP

```
/                                    → redirect /it
/{locale}/                           → Home
/{locale}/chi-siamo                  → About
/{locale}/servizi                    → Services
/{locale}/notizie                    → News list
/{locale}/notizie/{slug}             → Article
/{locale}/volontariato               → Volunteer form
/{locale}/donazioni                  → Campaign list
/{locale}/donazioni/{slug}           → Donation form (Stripe Payment Element)
/{locale}/donazioni/{slug}/privacy   → Campaign privacy notice
/{locale}/donazioni/{slug}/grazie    → Thank-you page
/{locale}/contatti                   → Contact
/{locale}/privacy                    → Privacy policy
/{locale}/cookie                     → Cookie policy

/cms-safehouse                       → Filament CMS (IP allowlist in Caddy)
/api/donations/intents/{slug}        → Create Stripe PaymentIntent
/api/webhooks/stripe                 → Stripe webhook (CSRF-exempt, signed)
```

Admin path is **`/cms-safehouse`** — never `/admin`. Env: `FILAMENT_PATH=cms-safehouse`.

---

## SECTION 6 — ARCHITECTURE PRINCIPLES

### Layering

```
HTTP Request → Middleware → Controller (thin) → Form Request → DTO → Service → Model
```

- **No business logic in controllers.**
- **No `new ClassName()` in controllers** — constructor injection only.
- **No `$guarded = []`** — explicit `$fillable` on every model.
- **No raw SQL** with user input.
- **No Repository boilerplate** unless swapping DB drivers — Eloquent + Services is sufficient.

### Directory structure (target)

```
app/
├── Contracts/Services/
├── DTOs/
├── Http/
│   ├── Controllers/
│   ├── Middleware/           # SecurityHeaders, SetLocale
│   └── Requests/
├── Models/
├── Observers/
├── Providers/
└── Services/
    ├── VolunteerService.php
    └── PageService.php
resources/
├── fonts/                    # JetBrains Sans (from CRM)
├── images/                   # logo.svg, bg.svg, favicons
├── lang/
│   ├── it/
│   ├── ru/
│   └── en/
└── views/
    ├── components/
    ├── layouts/
    └── pages/
deploy/
└── Caddyfile.example
docs/
└── SECURITY_CHECKLIST.md     # copied from archive when ready
```

### Design patterns

| Pattern | Location |
|---------|----------|
| Service Layer | `app/Services/` |
| Strategy | `app/Services/Payments/` |
| DTO | `app/DTOs/` from Form Requests |
| Observer | Cache invalidation, audit log |

---

## SECTION 7 — SECURITY (non-negotiable)

Full checklist: archived Notion page [02 · Security Checklist](https://app.notion.com/p/35e8d469d40581f1b9e6db5d8f11353e) — replicate to `docs/SECURITY_CHECKLIST.md` before production deploy.

### Header responsibility split

| Header | Where |
|--------|-------|
| `Content-Security-Policy` | **Caddy only** |
| `Strict-Transport-Security` | **Caddy only** |
| `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, COOP, CORP | `SecurityHeaders` middleware |

**Never set CSP in PHP** — breaks nonce strategy and duplicates headers.

### CSP notes for Stripe donations

Stripe Payment Element loads from `js.stripe.com`. Production CSP (in Caddy) must allow:

- `script-src` — `https://js.stripe.com`
- `frame-src` — `https://js.stripe.com`, `https://hooks.stripe.com`
- `connect-src` — `https://api.stripe.com`

Update CSP in **one place** (`deploy/Caddyfile.example`) when `/donazioni` ships to production. Test with [securityheaders.com](https://securityheaders.com).

### PII & forms

- Store `ip_hash` and `user_agent_hash` (SHA-256) on **volunteer/contact** submissions — never raw IP/UA in DB.
- **Donations:** no payment rows on this app. Stripe checkout → webhook `payment_intent.succeeded` → EspoCRM **PrimaNota** linked to **Finanziamento** (`Opportunity`). Card data processed by Stripe only.
- Webhooks that persist payments belong on the CRM/integration layer — verify signatures; idempotent handlers.

### Rate limits (AppServiceProvider)

| Limiter | Limit |
|---------|-------|
| `api` | 60/min per IP |
| `contact` | 5/hour per IP |
| `volunteers` | 3/hour per IP |
| `donations` | 30/hour per IP |

### Admin

- Filament at `/cms-safehouse`, IP allowlist in Caddy.
- 2FA for all admin users (post-MVP task).
- `session.secure`, `http_only`, `same_site=strict`, lifetime 120 min.
- `Password::defaults()` min 12 chars.

---

## SECTION 8 — DONATIONS

**Architectural decision (2026-06-30):** built-in fundraising on the public site via **Stripe Payment Element** + **Filament campaign CMS**. Payment records live in **EspoCRM** (`PrimaNota` + `Finanziamento`); **no** local payment history table. Card data never touches this app (PCI SAQ A via Stripe.js).

**Donorbox:** removed — paid API/webhooks not needed.

### 8.1 Flow

1. Editor creates **Donation Campaign** in Filament (`DonationCampaignResource`).
2. Public pages: `/{locale}/donazioni`, `/{locale}/donazioni/{slug}`, privacy at `.../privacy`.
3. Donor submits form → `POST /api/donations/intents/{slug}` creates Stripe **PaymentIntent** (metadata: donor name/type, comment, campaign).
4. Browser confirms payment via Stripe.js Payment Element.
5. Stripe webhook `payment_intent.succeeded` → `DonationIngestService` → EspoCRM **PrimaNota** linked to **Finanziamento**.

### 8.2 Stripe (standard account — no paid add-ons)

| Feature | Available on minimal Stripe account |
|---------|-----------------------------------|
| PaymentIntents + Payment Element | yes |
| Cards (EUR min €0.50) | yes |
| Webhooks | yes, free |
| Connect / Billing | not required for MVP |

**Env:** `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET` (see `.env.example`).

**Local webhook:** `stripe listen --forward-to https://safehouse-community-site.ddev.site/api/webhooks/stripe`

**Verify keys & account:** `php artisan stripe:verify` — see `docs/STRIPE-SETUP.md`

### 8.3 Campaign CMS (`donation_campaigns` table)

Config only — not payment history: slug, translatable title/description/privacy/form_notice, preset amounts (cents), allow custom amount, min amount, currency, Espo finanziamento name override, active flag.

### 8.4 EspoCRM mapping (Prima Nota split fields — 2026-06)

Each successful payment → one **PrimaNota** linked to existing **Opportunity** (`financingId`) by campaign name.

| Site / Stripe | CRM API field | Example |
|---------------|---------------|---------|
| Donor name (form) | `subjectName` (Soggetto pagamento) | `Mario Rossi` |
| Donor type (form) | `subjectParty` link | `individual` → **Contact**; `organization` → **Account** |
| Repeat donor | lookup by exact `name` | link `subjectPartyId` + `subjectPartyType` |
| New donor | CRM hook create flags | `createSubjectContact` or `createSubjectAccount` |
| Config default | `beneficiaryParty` link | **Account** named `Safe House` (lookup or `createBeneficiaryAccount`) |
| Campaign `finanziamentoTitle()` | Opportunity `name` lookup → `financingId` | must exist in CRM |
| PaymentIntent id | `description` idempotency (`contains`) | `Donazione Stripe ordine #pi_…` |

**Do not** send combined `"Payer - Beneficiary"` in `subjectName`. **Do not** auto-create Opportunity. **Always** link subject + beneficiary parties (Contact/Account) — no text-only Prima Nota rows.

**Env:** `ESPOCRM_*` including `ESPOCRM_ASSIGNED_USER_ID` from `GET /api/v1/App/user`.

**Local spec:** `nonprofit-espocrm/docs/integrations/DONATION-SITE-CRM-API.md` (gitignored in CRM repo).

### 8.5 Privacy / data minimization

- Form shows notice: card data processed by Stripe, not stored on site.
- Per-campaign privacy page (`privacy_notice` translatable field).
- Stored in CRM: name, amount, currency, comment, donor type — **not** card numbers.

### 8.6 Out of scope

| Item | Status |
|------|--------|
| Local `donations` payment table | removed |
| Donorbox embed/ingest | removed |
| Recurring subscriptions | future |

**Implementation:** `StripePaymentService`, `DonationIngestService`, `DonationCampaignResource`, `StripeWebhookController`.

---

## SECTION 9 — CMS (Filament)

### Scope (minimal)

| Resource | Purpose |
|----------|---------|
| Page | Static pages (About, Services, legal) |
| Article | News / blog |
| ArticleCategory | News grouping |
| SiteSetting | Key-value settings (optional) |
| User | Admin users only |

### Page

- Translatable fields: tabs per locale (IT / RU / EN).
- Rich text: sanitized HTML server-side before save.
- Media: `spatie/laravel-medialibrary`, private disk, signed URLs.

### Page templates (CMS → public layout)

Editors **must** pick a template when creating a page in Filament. Templates map to Blade views under `resources/views/pages/templates/` (config: `config/page_templates.php`).

| Template key | Use case |
|--------------|----------|
| `default` | Simple static page (title + body) |
| `about` | About / mission + values block (`meta.values`, `meta.closing`) |
| `services` | Service card grid (`meta.services[]`) |
| `article` | Long-form editorial layout |
| `news_index` | Static intro linking to `/notizie` |
| `landing` | Wide hero landing |
| `legal` | Privacy, cookie, policy pages |
| `contact` | Contact details + form placeholder (form in P4) |

**Stable `key`** on each page (e.g. `about`, `services`) powers navigation via `config/navigation.php` → `Navigation::url()`.

**Future tasks:** Filament template preview, structured meta editor per template, ArticleResource with `article` template parity, volunteer landing template.

### Editor

Align Filament panel colors with Aurora tokens (dark sidebar, red primary).

---

## SECTION 10 — FORMS (public)

### Volunteer (`/{locale}/volontariato`)

- Honeypot field, rate limit `volunteers`, GDPR explicit consent checkbox.
- `VolunteerService::store()` hashes IP/UA.

### Contact (`/{locale}/contatti`)

- Rate limit `contact`, optional Google Maps embed (CSP: `frame-src` for Google).
- Store in `contact_submissions`.

---

## SECTION 11 — TESTING PROTOCOL

### Per-task workflow

1. Implement single task scope only.
2. Run automated tests / commands listed in task.
3. Set Notion status → **Testing**.
4. Append **User QA checklist** to task (English).
5. Wait for user “OK” → **Done**.

### Automated baseline (every PR)

```bash
ddev exec ./vendor/bin/pint --test
ddev exec php artisan test
ddev composer audit
```

### User QA template (append to Notion task)

```markdown
## User QA — [Task ID]
**Environment:** ddev / staging / production
**Tester:** @user

- [ ] Step 1: ...
- [ ] Step 2: ...
- [ ] Regression: ...

**Result:** OK / FAIL (notes)
```

**Never** skip user QA for visual, donation, or security-sensitive tasks.

---

## SECTION 12 — TASK PHASES (backlog summary)

| Phase | Focus |
|-------|-------|
| **P0** | Git, AGENTS.md, README, security middleware, rate limits, session |
| **P1** | i18n routing, DB schema, translatable models |
| **P2** | Filament CMS + RBAC + media |
| **P3** | Aurora-themed frontend (layout, home, pages) |
| **P4** | Volunteer + contact forms |
| **P5** | Native Stripe donations + EspoCRM ingest (no local payment DB) |
| **P6** | GDPR banner, CI, Caddy template, deploy |

Pick the lowest-numbered `Not started` task unless user reprioritizes.

---

## SECTION 13 — ARCHIVED PLAN — WHAT TO TRUST

Source: [Web page safehouse.community (archive)](https://app.notion.com/p/3588d469d4058065bd38f56abfde821a)

| Trust | Item |
|-------|------|
| ✅ | Site map, security architecture, Service layer, Caddy/Filament path |
| ✅ | spatie packages list, webhook patterns, GDPR approach |
| ⚠️ | Task “Done” statuses — **ignore**; verify against git |
| ❌ | Laravel 11, Octane in MVP, 4-week timeline |
| ❌ | Cabinet Grotesk / Satoshi fonts, `#E53E3E` accent |
| ❌ | IT+EN only (now IT+RU+EN) |
| ❌ | PayPal in first release |

Child pages in archive still useful as reference:

- [01 · Project Bible](https://app.notion.com/p/35e8d469d40581b48ca0c945665b54a6)
- [02 · Security Checklist](https://app.notion.com/p/35e8d469d40581f1b9e6db5d8f11353e)

---

## SECTION 14 — GIT & DEPLOYMENT

| Item | Value |
|------|-------|
| Repository | https://github.com/skdvlpr/safehouse-community-site |
| Default branch | `main` |
| Production host | Aruba Cloud VPS (planned) |
| Edge | Caddy v2 + PHP-FPM |
| Deploy | GitHub Actions → SSH → `deploy.sh` (zero-downtime) |

**Never** commit `.env`, secrets, or user uploads.

---

## SECTION 15 — QUICK REFERENCE

- **One task = one problem** — no drive-by refactors
- **Laravel 13** — non-negotiable
- **Locales:** `it`, `ru`, `en` — `it` default
- **Donations:** Stripe Payment Element + Filament campaigns → EspoCRM PrimaNota/Finanziamento — no local payment DB
- **Design:** match CRM Safehouse Aurora — assets from `nonprofit-espocrm`
- **CSP:** Caddy only; include Stripe domains when `/donazioni` ships
- **CMS path:** `/cms-safehouse`
- **Testing:** auto tests → Testing → user QA → Done
- **Notion:** English tasks/logs; Russian chat with user

---

## SECTION 16 — Dual-agent model (Auto + Power)

Two executors share one repo. **Default = Auto** (this chat). **Power** = advanced model for Filament CMS + admin security only when user switches chats.

| Agent | Doc | Scope |
|-------|-----|-------|
| **Auto** | [`AGENTS.md`](AGENTS.md) | P0, P1, P3+, routine tasks, user QA coordination |
| **Power** | [`AGENTS.power.md`](AGENTS.power.md) | P2 CMS core (Filament, RBAC, translatable resources) |

**Handoff file:** [`docs/HANDOFF.md`](docs/HANDOFF.md) — Auto writes before Power; Power writes before return. On resume, read HANDOFF first, then Notion.

### Switch trigger (Auto → Power)

User opens a **new chat** with Power model **only after**:

1. P0-T06, T07, T08 = **Done** (+ user QA)
2. P1-T01 … P1-T12 = **Done** (+ user QA)
3. `docs/HANDOFF.md` → **Ready for Power Sprint 1 = Yes**
4. Auto tells user: «Переключайся на Power» + copy launch prompt from `AGENTS.power.md`

Power Sprint 1 tasks: **P2-T01 … P2-T04** (stretch P2-T05 if tokens allow).

### Switch trigger (Power → Auto)

Power ends session by updating HANDOFF + Notion. User returns here with:

`Продолжай по docs/HANDOFF.md`

### Power must not

- `git push`, `ddev stop` / delete, Notion **Done** without user QA
- Work on P3+ while P2 sprint incomplete (unless HANDOFF says otherwise)
- Duplicate full AGENTS.md — read it, follow it

### Token overflow

If Power runs out of tokens: HANDOFF lists **Gemini** for P2-T05/T06 or **Auto** for P3.

---

## EXECUTOR LOG

### 2026-06-29 — Re-baseline + AGENTS.md

**Agent:** Cursor Auto

- Assessed archived Gomercato tracker; created new project + 34 atomic tasks.
- Local repo: Laravel 13 skeleton + DDEV; pushed to GitHub `main`.
- Studied `nonprofit-espocrm` Safehouse Aurora themes for brand alignment.
- Added Donorbox as primary donation embed (`raccolta-dei-fondi-per-safe-house`).
- Created this `AGENTS.md` as single source of truth for all agents.

**Next:** P0-T01 user QA → P0-T03 README → P0-T04 SecurityHeaders

### 2026-06-29 — P0 security headers + dual-agent workflow

**Agent:** Cursor Auto

- **P0-T04/T05:** `SecurityHeaders` middleware + bootstrap registration; curl verified on DDEV.
- **Dual-agent:** added `AGENTS.power.md`, `docs/HANDOFF.md`, AGENTS.md §16.
- **Power switch:** after P1-T12 Done → Power Sprint 1 (P2-T01…T04 Filament CMS).
- **Next:** P0-T06 rate limiters.

### 2026-06-30 — Donations: Prima Nota split fields (CRM API live)

**Agent:** Cursor Auto

- **Updated** `DonationIngestService` for new Espo API: `subjectName` + `beneficiaryName`, no `createSubjectContact`.
- **Description:** `Donazione Stripe ordine #pi_…`; idempotency via `contains` on payment intent id.
- **Finanziamento:** lookup only — no auto-create; error on 0 or >1 matches.
- **Config:** `ESPOCRM_ASSIGNED_USER_ID`, `ESPOCRM_PRIMA_NOTA_DEFAULT_BENEFICIARY`.
- **Spec ref:** `nonprofit-espocrm/docs/integrations/DONATION-SITE-CRM-API.md` (local).
- **Tests:** 68 passed.

### 2026-06-30 — Donations: native Stripe (Donorbox removed)

**Agent:** Cursor Auto

- **Decision:** replace Donorbox with built-in Stripe Payment Element + Filament campaign CMS.
- **Removed:** Donorbox ingest API, CORS middleware, Donorbox env vars.
- **Added:** `DonationCampaign` model + Filament resource, public donation pages, `StripePaymentService`, webhook → `DonationIngestService` → EspoCRM (`[Stripe:pi_...]` idempotency).
- **No** local payment history table; card data never stored on site.
- **Tests:** 52 passed.

### 2026-06-29 — Donations: EspoCRM ingest via Donorbox tracking (revised)

**Agent:** Cursor Auto

- **Revised** stakeholder decision: Donorbox paid API/webhooks not available — use **After donation tracking code** → `POST /api/donations/donorbox`.
- **Spike verified** on `nonprofit-espocrm` DDEV: find/create `Opportunity` (Finanziamento), create `PrimaNota` with `financingId`, idempotency via `[Donorbox:{id}]` description prefix.
- **Implemented:** `DonationIngestService`, `EspoCrmClient`, `DonorboxDonationController`, rate limiter `donations`, CORS for Donorbox origins.
- **Still no** local `donations` table on public site.
- **Remaining:** P5-T03 embed page + Donorbox campaign tracking script; production secrets/ACL.

### 2026-06-29 — Donations: no local DB (superseded)

**Agent:** Cursor Auto

- Initial decision to rely on Donorbox API/webhooks only — superseded by ingest API approach above.
