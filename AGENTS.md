# AGENTS.md — `safehouse-community-site`

Always-on rails for this git repo. Product law is
[`.specify/memory/constitution.md`](.specify/memory/constitution.md).
This file is a **router**, not a second constitution.

## Official vendor docs (never banned)

At **every** Laravel / Filament / Stripe / Spatie / Tailwind / Vite / DDEV /
Caddy decision you MUST **open** the matching official page **this turn** and
**cite** that URL in the committed artifact. URLs: constitution § Tech Stack.

No page opened this turn → the stack decision was **not** made.

Past Notion / bloated AGENTS / `docs/HANDOFF.md`: open **only** if a detail
is missing from the constitution. MUST NOT use them instead of vendor docs.
Conflict → stop, structured question menu, wait.

## First launch

If `.specify/memory/constitution.md` still has Spec Kit placeholders
(`[PRINCIPLE_1_NAME]`): **STOP.** Do not specify. Owner runs
`/speckit-constitution` first.

## After constitution: working set

1. `.specify/memory/constitution.md`
2. Official vendor URLs (open this turn)
3. **Read** `/home/skoksharov/safehouse/nonprofit-espocrm` when donations /
   PrimaNota / sportelli / Aurora assets are in scope
4. `.cursor/skills/speckit-*/SKILL.md`
5. `.specify/progress/` (where we are now)

## Write vs read

| Path | Write | Read |
| :--- | :--- | :--- |
| `/home/skoksharov/safehouse/safehouse-community-site` | **YES — only here** | yes |
| `/home/skoksharov/safehouse/nonprofit-espocrm` | **NO** | **YES** when integrating |
| `/home/skoksharov/safehouse/PLAN-analytics-adgrants.md` | no | optional backlog hint |

If the feature cannot work without **changing** CRM: **stop**, tell the owner
path / files / why / official URL, wait.

## Languages

- Owner chat: typically Russian. UAT scripts in chat = that language.
- Constitution, specs, plans, tasks, checklists, progress: **English** (not Italian).
- Site UI: Italian primary, English second. No public `/ru`.

## Spec-driven path

**Mandatory:** `/speckit-specify` → `/speckit-plan` → `/speckit-tasks` →
`/speckit-implement`. Implement **only** from `tasks.md`.

**Optional (owner asks):** clarify, checklist, analyze, converge.

- Main features: `specs/NNN-short-name/`
- Fixes / UAT repairs: `specs/NNN.K-short-name/` (`001.1`, …). Finish
  amendments before `NNN+1`. For dotted dirs set `SPECIFY_FEATURE_DIRECTORY`.
- One active spec. SITE queue: **S01 audit → S02 analytics/SEO/Ad Grants →
  S03 banners → S04 GDPR** unless the owner reorders.
- Hotfix: owner must say hotfix; capture in a spec.
- Skipping specify/plan/tasks before implement is **forbidden**.

## Tasks, models, tests

On **every** `/speckit-tasks` item: complexity **1–10** + proposed
model/subagent + why (non-mechanical). Print a table in chat and **wait**.

Advanced models (Opus / GPT / Gemini / Fable): ask **Launch vs Replace**.
Silence ≠ approval. `tasks.md` is not a launch order.

**Tests:** propose only for real logic; `test: yes/no` + why. No coverage %.
No stupid tests. Owner UAT is not replaced by PHPUnit.

## Other hard stops

- Closed `S-*` in the constitution are **not** forks.
- Uncertainty / docs≠code / two designs → **stop and AskQuestion**.
- No git commit/push unless the owner asks.
- No `ddev stop` / `poweroff` / `delete` / extra services unless asked.
- After migrations/seeders: `ddev exec php artisan migrate` (`--seed` if
  seeders). Never `php artisan config:cache` in DDEV.
- CSP/HSTS: Caddy only. CMS path `/cms-safehouse`.
- After implement: owner user-test checklist in Russian and **wait**.
- Cursor-browser: offer, wait; do not drive live UI unless agreed that turn.
- Legal DPA wording: STOP. Operational cookies/CMS policies: in scope.

## This repo

- Laravel **13** / PHP **8.4** / Filament **v4** / MariaDB 11.8 / DDEV
  nginx-fpm (`https://safehouse-community-site.ddev.site`). Production:
  https://safehouse.community
- How to boot: [`README.md`](README.md)
- Verify: `ddev exec php artisan test` · `ddev exec ./vendor/bin/pint`
- Frontend stale CSS: `bash bin/dev-rebuild-frontend.sh`
