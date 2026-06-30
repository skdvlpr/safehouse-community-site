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

```bash
ddev exec php artisan migrate --seed   # seeds chi-siamo, servizi, contatti, legal pages
ddev exec php artisan test             # 96 passed
ddev npm run build
```

---

## CMS page templates (roadmap)

Filament **Page** resource: editor picks template → public site renders matching Blade.

| Template | Status |
|----------|--------|
| default, about, services, legal, contact, landing, article, news_index | **Shipped** |
| Filament meta editor per template (structured fields) | **Next** |
| ArticleResource + news template parity | **Next** |
| Template preview in CMS | Backlog |

---

## Next for Auto (immediate)

| Field | Value |
|-------|-------|
| **Notion task** | P4 contact/volunteer forms OR Filament ArticleResource |
| **Prerequisite** | User QA on `/it/chi-siamo`, `/it/servizi`, nav links |
| **Production** | Stripe live keys, webhook endpoint, CSP for `js.stripe.com` |

---

## Notes for user

1. Run `ddev exec php artisan migrate --seed` to load CMS pages (chi-siamo, servizi, …).
2. CMS → Content → Pages — pick **template** when creating new pages.
3. Confirm **P2 CMS** manual QA → reply «P2 ок».
4. Test donations flow with Stripe test keys + `stripe listen`.
