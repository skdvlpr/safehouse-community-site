# Agent Handoff — Safehouse.community

Living document. **Auto** writes before Power switch; **Power** writes before returning to Auto.

---

## Last updated

| Field | Value |
|-------|-------|
| **Agent** | Auto |
| **Date** | 2026-06-29 |
| **Sprint** | Phase P3 (frontend) — after Power Sprint 1 |

---

## Completed (Notion task → evidence)

| Task | Status | Evidence |
|------|--------|----------|
| P0-T01 … P0-T08 | Done | Phase P0 complete |
| P1-T01 … P1-T12 | Done | Phase P1 complete (P1-T10 cancelled) |
| P2-T01 … P2-T04 | **Testing** | Power Sprint 1 — see QA below |
| **P3-T01** | **Testing** | `resources/css/app.css` Aurora tokens + JetBrains Sans |

---

## Architectural decisions

### Filament v4 API (Power Sprint 1)

- `form()` uses `Filament\Schemas\Schema` not `Form`
- Layout: `Filament\Schemas\Components\`; fields: `Filament\Forms\Components\`
- Translatable CMS: native Tabs + dot notation (`title.it`, …) — no deprecated spatie Filament plugin

### Donations — no DB on public website

Payment records in **EspoCRM** via Donorbox API/webhooks. Cancelled: P1-T10, P5-T01, P5-T02. Remaining: P5-T03 embed page.

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

---

## Blocked

| Item | Blocker |
|------|---------|
| Notion Tasks DB | Intermittent MCP access — log on project page |

---

## Files touched (recent — Auto)

- `resources/css/app.css` — Safehouse Aurora `@theme` + CSS variables
- `vite.config.js` — removed Instrument Sans (Bunny); JetBrains self-hosted
- `public/fonts/JetBrainsSans[wght].woff2` — from CRM repo
- `tests/Feature/DesignTokensTest.php`

---

## Verification run

```bash
ddev exec php artisan test                    # 47 passed
ddev exec php artisan route:list --path=cms-safehouse
ddev npm install && ddev npm run build        # if frontend assets stale
```

---

## Git status

| Branch | `main` — ahead of origin (P2 commit `16d7ffb`; P3-T01 uncommitted) |
|--------|----------------------------------------------------------------------|

---

## Next for Auto (immediate)

| Field | Value |
|-------|-------|
| **Notion task** | P3-T02 — Base layout (header/footer/locale switcher) |
| **Prerequisite** | P3-T01 user QA OK |
| **After P2 QA** | Mark P2-T01…T04 Done in Notion |

---

## Notes for user

1. Confirm **P2 CMS** manual QA → reply «P2 ок» to mark Done.
2. Confirm **P3-T01** tokens visually → reply «го» for P3-T02.
3. Commit P3-T01 when ready (`git status` shows uncommitted changes).
