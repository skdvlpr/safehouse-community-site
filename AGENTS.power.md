# Safehouse.community — Power Agent Rulebook

**Role:** Power executor for high-responsibility architecture work.  
**Companion doc:** read [`AGENTS.md`](AGENTS.md) first — this file adds sprint scope and handoff rules only.  
**Last updated:** 2026-06-29  
**User chat:** Russian | **Notion / docs / code comments:** English

---

## When you run

The **Auto agent** (default Cursor chat) handles P0 and P1. The user switches to **you** only when Auto says:

> «Переключайся на Power» — after **P1-T12 Done** and before **P2-T01**.

You are **not** the default executor. Do not start work unless the user pasted the launch prompt (below) or explicitly invoked Power mode.

---

## Active Power Sprint 1 — Filament CMS core

**Goal:** Secure admin CMS foundation before public frontend.

| Task | Notion name | Deliverable |
|------|-------------|-------------|
| P2-T01 | Install Filament v4 | Package installed, panel boots on Laravel 13 |
| P2-T02 | Panel path `cms-safehouse` | `FILAMENT_PATH=cms-safehouse`; `/admin` not used |
| P2-T03 | RBAC | `spatie/laravel-permission` + roles `super-admin`, `editor` seed |
| P2-T04 | Filament Page resource | CRUD for `Page` model, translatable IT/RU/EN tabs |

**Stretch (only if tokens remain):** P2-T05 Article resources.  
**Out of sprint:** P2-T06 media, P3+ frontend, P6 deploy/Caddy (CSP for Stripe).

**Cancelled on public site:** P1-T10, P5-T01, P5-T02 — no local payment DB; ingest via EspoCRM REST from Stripe webhook.

**P5 (done in Auto):** native Stripe donations — Filament `DonationCampaignResource`, Payment Element, webhook → EspoCRM.

### Notion links

| Resource | URL |
|----------|-----|
| Project | https://app.notion.com/p/38e8d469d405810980bbf8fbcf34ae94 |
| Tasks DB | https://app.notion.com/p/38d8d469d40580b8b87ee0681b9d929c |
| Site content (IT copy) | On project page → «Site content — source copy» |

Italian CMS seed text lives in Notion — use for default Page fixtures if needed.

---

## Inherited rules (from AGENTS.md — do not violate)

1. **Laravel 13** — no downgrade.
2. **One Notion task = one problem** — but you may complete the whole sprint in one session if each task is logged separately in Notion.
3. **Testing → Done** only after user confirms User QA checklist.
4. **Proactive Notion logs** — append executor notes to task + project pages; never overwrite prior logs; English only in Notion.
5. **No CSP / HSTS in PHP** — Caddy only in production.
6. **Admin path** `/cms-safehouse` — never `/admin`.
7. **`$fillable` on every model** — no `$guarded = []`.
8. **DDEV:** `ddev start` OK. **Never** `ddev stop`, `ddev delete`, new services, or `config.yaml` changes without user ask.
9. **No git push** unless user explicitly asks.
10. **Locales:** `it` (primary), `ru`, `en` — translatable fields in Filament.

---

## Token economy (Google AI Pro / limited Claude)

You have a **small token budget**. Act accordingly:

| Do | Don't |
|----|-------|
| Read `docs/HANDOFF.md` + this file + targeted repo files | Re-read entire archive Notion project |
| Implement sprint tasks only | Refactor unrelated code |
| Run `ddev exec php artisan test` for your changes | Broad codebase exploration |
| Stop early with clean HANDOFF | Silently leave half-done work |

**If tokens run low:** finish the **current** Notion task, write [`docs/HANDOFF.md`](docs/HANDOFF.md), update Notion, tell user:

- **Gemini** can take **P2-T05 / P2-T06** if listed in HANDOFF, or
- Return to **Auto chat** with: `Продолжай по docs/HANDOFF.md`

---

## CMS security checklist (this sprint)

Before marking any P2 task **Testing**:

- [ ] Filament panel URL is `/cms-safehouse` (verify `php artisan route:list`)
- [ ] No default `/admin` route exposed as primary panel
- [ ] Roles seeded; admin user can log in
- [ ] Page resource uses translatable fields (spatie/laravel-translatable) for IT/RU/EN
- [ ] No secrets in committed files
- [ ] Migrations applied: `ddev exec php artisan migrate` (or `--seed` if seeders changed) — **agent runs before handoff**
- [ ] `php artisan test` passes (or new tests for Filament smoke where feasible)
- [ ] Notion task has User QA checklist

IP allowlist for CMS is **Caddy** (P6-T03) — document in HANDOFF, do not block sprint on production Caddyfile.

---

## Exit protocol (mandatory before end of session)

1. **Notion:** append executor log on each touched task; project page summary.
2. **`docs/HANDOFF.md`:** fill all sections (see template in that file).
3. **User message (Russian):** «Вернись в Auto-чат и напиши: `Продолжай по docs/HANDOFF.md`»
4. **Status:** tasks → **Testing** (not Done) unless user already said OK in this session.

---

## Handoff back to Auto

After Power Sprint 1, **Auto** resumes with:

- P2-T05 / P2-T06 if you did not finish, **or**
- **P3-T01** Tailwind design tokens if P2 sprint complete and user QA OK

Auto reads `docs/HANDOFF.md` first — keep **Next for Auto** precise (Notion task ID + command).

---

## Copy-paste launch prompt (user sends this to Power chat)

```
You are the Power agent for Safehouse.community (Laravel 13 NGO website).

Read first (in order):
1. Repository AGENTS.md — full rules
2. Repository AGENTS.power.md — your sprint scope
3. Repository docs/HANDOFF.md — context from Auto agent
4. Notion project: https://app.notion.com/p/38e8d469d405810980bbf8fbcf34ae94
5. Notion tasks DB: https://app.notion.com/p/38d8d469d40580b8b87ee0681b9d929c

Your sprint (Power Sprint 1): Complete Notion tasks P2-T01 through P2-T04 only:
- P2-T01 Install Filament v4
- P2-T02 Panel path cms-safehouse
- P2-T03 RBAC (spatie/laravel-permission)
- P2-T04 Filament Page resource (translatable IT/RU/EN)

Constraints: Laravel 13. No CSP/HSTS in PHP. Proactive Notion logs (English). Testing→Done only after user QA. No ddev stop/delete. No git push unless user asks.

Before you stop: Update docs/HANDOFF.md, append Notion project executor log, tell user to return to Auto chat with: Продолжай по docs/HANDOFF.md

If tokens run low: Finish current task, write HANDOFF, suggest Gemini for P2-T05/T06 or Auto for P3.

User communicates in Russian; docs/Notion in English.
```

---

## Power Sprint 2 (future — not active)

Reserved for: P2-T05/T06 completion, or production **Caddyfile** + CSP with Stripe domains (P6-T03). Auto will update this section when Sprint 2 opens.
