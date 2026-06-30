# Agent Handoff — Safehouse.community

Living document. **Auto** writes before Power switch; **Power** writes before returning to Auto.

---

## Last updated

| Field | Value |
|-------|-------|
| **Agent** | Auto |
| **Date** | 2026-06-30 |
| **Sprint** | Phase P3 (frontend) — after Power Sprint 1 |

---

## Completed (Notion task → evidence)

| Task | Status | Evidence |
|------|--------|----------|
| P0-T01 … P0-T08 | Done | Phase P0 complete |
| P1-T01 … P1-T12 | Done | Phase P1 complete (P1-T10 cancelled) |
| P2-T01 … P2-T04 | **Testing** | Power Sprint 1 — see QA below |
| **P3-T01** | **Testing** | `resources/css/app.css` Aurora tokens + JetBrains Sans |
| **P3-T02** | **Done** | `7526926` — layout, header/footer, horizontal logo |
| **P3-T03** | **Done** | `7526926` — HomeController, polygon `bg.svg`, stats shell |
| **P3-T04** | **Testing** | CMS pages + template system (`PageService`, seeded chi-siamo/servizi/contatti/legal) |
| **Donations (Stripe)** | **Done (code)** | Filament campaigns + Stripe Payment Element → EspoCRM |

---

## Architectural decisions

### Filament v4 API (Power Sprint 1)

- `form()` uses `Filament\Schemas\Schema` not `Form`
- Layout: `Filament\Schemas\Components\`; fields: `Filament\Forms\Components\`
- Translatable CMS: native Tabs + dot notation (`title.it`, …) — no deprecated spatie Filament plugin

### Donations — native Stripe + Filament campaigns (2026-06-30)

**Removed:** Donorbox ingest API, CORS middleware, Donorbox env vars.

**Added:**
- `donation_campaigns` table + `DonationCampaign` model (campaign **config** only, not payments)
- Filament `DonationCampaignResource` (Fundraising group)
- Public: `/{locale}/donazioni`, `/{slug}`, `/{slug}/privacy`, `/{slug}/grazie`
- Stripe Payment Element embedded form
- `POST /api/donations/intents/{slug}` → PaymentIntent
- `POST /api/webhooks/stripe` → EspoCRM PrimaNota + Finanziamento
- Packages: `stripe/stripe-php`

**Flow:** CMS campaign → public form → Stripe.js confirm → webhook → EspoCRM. Card data never stored on site (PCI SAQ A).

**Env:** `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`, `ESPOCRM_BASE_URL`, `ESPOCRM_API_KEY`, `ESPOCRM_FINANZIAMENTO_CLOSE_DATE`.

**Default donor label:** `Donatore` when name empty (`ESPOCRM_PRIMA_NOTA_DEFAULT_SUBJECT`).

**Default beneficiary:** `Safe House` (`ESPOCRM_PRIMA_NOTA_DEFAULT_BENEFICIARY`).

**Prima Nota API (2026-06):** split `subjectName` + `beneficiaryName`; idempotency via `contains` on `pi_…` in description; Opportunity must pre-exist in CRM (no auto-create).

Cancelled on public site: P1-T10, P5-T01, P5-T02 (local payment DB). P5-T03 replaced by native Stripe pages.

---

## In Testing (awaiting user QA)

### P2 — Filament CMS (Power Sprint 1)

| Check | How |
|-------|-----|
| Panel URL | https://safehouse-community-site.ddev.site/cms-safehouse → login |
| No `/admin` | `/admin` → 404 |
| Login | `admin@safehouse.community` / `password` (super-admin) |
| PageResource | Create page with IT/RU/EN tabs |
| Roles | `editor`, `super-admin` seeded |

Automated: **45 tests passed** (includes `FilamentPanelTest`, `RbacTest`).

### P3-T01 — Design tokens

| Check | How |
|-------|-----|
| Dark page bg | `#050505` on `body` when Vite CSS loaded |
| Red accent | `.safehouse-btn-primary` or `bg-safehouse-primary` |
| Font | JetBrains Sans in `public/fonts/` |

Run `ddev npm install && ddev npm run build` if styles not updating.

### Donations — Stripe + EspoCRM (manual QA)

**Prerequisite:** both DDEV projects running (`safehouse-community-site`, `nonprofit-espocrm`).

1. `.env` — Stripe **test** keys (`STRIPE_KEY`, `STRIPE_SECRET`) + Espo API key
2. CMS → Fundraising → Campaigns — create active campaign with preset amounts
3. Terminal: `stripe listen --forward-to https://safehouse-community-site.ddev.site/api/webhooks/stripe` — copy webhook secret to `STRIPE_WEBHOOK_SECRET`
4. Open `/it/donazioni/{slug}` — fill name, amount, card `4242 4242 4242 4242`
5. Espo UI → **Prima nota** — description `Donazione Stripe ordine #pi_…`, `subjectName` = donor, `beneficiaryName` = Safe House, linked Finanziamento

---

## Blocked

| Item | Blocker |
|------|---------|
| Notion Tasks DB | Intermittent MCP access — log on project page |

---

## Files touched (recent — Auto)

- P3 layout: `layouts/app`, `HomeController`, `config/navigation.php`, `public/images/bg.svg`
- CMS pages: `PageService`, `PageController`, `config/page_templates.php`, `PageSeeder`, template Blade views
- `/notizie` — `ArticleController` (list + show shell)
- Donations pivot: `DonationCampaign*`, `StripePaymentService`, `DonationIngestService`

---

## Verification run

**Agents run this block themselves** after schema/seeder changes — not delegated to the user.

```bash
ddev exec php artisan migrate --seed
ddev exec php artisan test
ddev npm run build
```

---

## CMS page templates (roadmap)

Filament **Page** resource: editor picks template → public site renders matching Blade.

| Template | Status |
|----------|--------|
| default, about, services, legal, contact, landing, article, news_index | **Shipped** |
| Hero carousel (`meta.carousel`) on any template | **Shipped** |
| **P3-T05** — Prominent titles + about layout (split hero, section band, block spacing) | **Shipped** |
| **P3-T06** — Structured Filament fields per template (`about`: tagline, intro, values, closing) | **Shipped** (about); services repeater **next** |
| Template preview in CMS | **Shipped** — signed preview URL, IT/RU/EN, drafts OK |
| ArticleResource + news | **Shipped** |

### P3c — Schematic page block composer (planned)

**Goal:** Edit and create page layouts by arranging blocks — **not** Elementor-style WYSIWYG on the live page.

| Piece | Description |
|-------|-------------|
| Block registry | `heading`, `rich_text`, `image`, `carousel`, `columns`, `callout`, `section_band` |
| Template definition | Ordered slots + layout hints (e.g. 7/5 grid, full-width) stored in config/DB |
| Filament UI | Schematic canvas: labeled rectangles, drag-and-drop reorder, per-block field forms |
| Page content | Each block instance editable separately (fixes “3 blocks on site, 1 field in CMS”) |
| Migration | Current Blade templates become **presets** loaded into the composer |

Config seed: `config/page_template_fields.php` (block map per template — extend for composer).

### P4 — Contact + volunteer forms (next implementation phase)

Replace contact template placeholder; rate limits + Espo optional ingest.

**Shipped:** live contact form (`POST /{locale}/contact`), honeypot, GDPR checkbox, `ContactSubmissionService`, rate limit `contact` (5/hour).

**Shipped:** volunteer form at `/{locale}/volontariato` — `VolunteerService`, honeypot, GDPR, rate limit `volunteers` (3/hour).

---

## Next for Auto (immediate)

| Field | Value |
|-------|-------|
| **Notion task** | **P6** GDPR banner / CI / deploy prep |
| **Just shipped** | P4 volunteer form; Espo donation ingest fixes |
| **Prerequisite** | User QA on `/it/volontariato` and donation mock flow |
| **Production** | Stripe live keys, webhook endpoint, CSP for `js.stripe.com`, `ESPOCRM_BENEFICIARY_ACCOUNT_ID` |

---

## Notes for user

1. CMS → Content → Pages — pick **template** when creating new pages.
2. Confirm **P2 CMS** manual QA → reply «P2 ок».
3. Test donations flow with Stripe test keys + `stripe listen`.

(Migrations/seeds: **Auto agent runs `migrate --seed` automatically** — you should not need to.)
