# Frozen Notion extract (pre-SDD)

**Captured:** 2026-09-13  
**Purpose:** Read-only reconstruction if a new session needs pre-Spec-Kit history.  
**Not** a living tracker. Do not update this file as if it were Notion.  
**Governance:** `.specify/memory/constitution.md` Principle II.

Source project (archived for agents):  
https://app.notion.com/p/38e8d469d405810980bbf8fbcf34ae94  
Tasks DB (historical):  
https://app.notion.com/p/38d8d469d40580b8b87ee0681b9d929c  
Gomercato archive (untrusted “Done” flags):  
https://app.notion.com/p/3588d469d4058065bd38f56abfde821a

---

## Product

Public site for **Safe House ETS** (Italy): crisis assistance, volunteers,
donations, news. Production: https://safehouse.community  
CMS: `/cms-safehouse`. CRM sibling: https://crm.safehouse.community  
Repo: https://github.com/skdvlpr/safehouse-community-site

Locales: `it` (default), `ru`, `en`. Brand: Safehouse Aurora (CRM-aligned),
JetBrains Sans, polygon background. Old Cabinet Grotesk / `#E53E3E` plan is
void.

## Stack decisions that shipped

- Laravel 13 + PHP 8.4 + DDEV + Caddy (not Laravel 11, not Octane in MVP).
- Filament v4; translatable CMS without deprecated Spatie Filament plugin.
- Donations: **Stripe Payment Element** + Filament campaigns → EspoCRM
  PrimaNota / Finanziamento. **No** local payment table. Donorbox removed.
- Cookie banner + `gdpr_consents` exist; public analytics tracker **not**
  wired. Legal copy task: “LEGAL — Real Privacy Policy + Cookie model”.
- Security: CSP/HSTS at Caddy; `SecurityHeaders` in PHP; hashed IP/UA on
  forms; rate limiters.

## Phase snapshot (code vs Notion status)

Notion statuses were often stale. Trust git + tests over “Done” in Gomercato.

| Phase | Intent | Notes |
|-------|--------|--------|
| P0 | Git, AGENTS, headers, sessions | Baseline shipped |
| P1 | i18n + schema | Shipped; P1-T10 local payment DB cancelled |
| P2 | Filament + RBAC | Shipped; some rows left in Testing |
| P3 | Aurora frontend | Largely shipped (home, pages, themes, social) |
| P4 | Volunteer + contact / sportelli | Shipped; generic desk + CRM Case.type work in Aug 2026 |
| P5 | Stripe donations | Code shipped; live donate QA often still open |
| P6 | GDPR banner, CI, Caddy | Partial (banner exists; SEO sitemap still post-MVP) |

Dual-agent Auto/Power (`docs/HANDOFF.md`) is **historical**. New work is Spec
Kit only.

## Backlog that was in chat/Notion, not a live spec

These are **not** started as `specs/` yet (queue for `/speckit-specify`):

1. Compliance audit (bugs, vulns, hardcoding, SOLID vs Laravel/Filament docs).
2. Analytics dashboard + Google Ad Grants / sitelinks + SEO (`spatie/laravel-sitemap`
   listed post-MVP; plan file lived at `~/safehouse/PLAN-analytics-adgrants.md`
   outside this repo).
3. Satispay + 5×1000 banners (placement TBD with user).
4. GDPR/ePrivacy vs current cookie/privacy pages (LEGAL task still relevant).

Last Notion task created for this transition: **SITE-SDD-01** (Spec Kit init),
https://app.notion.com/p/3da8d469d40581a0a6ccd7f7a2ae37de — do not continue
logging there.

## What to ignore in archive

- Laravel 11, Octane/Horizon/Telescope as MVP, PayPal-first, Donorbox embed.
- Fake Done on archived web plan.
- Notion as the place to append executor notes after 2026-09-13.
