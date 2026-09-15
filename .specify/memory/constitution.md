<!--
Sync Impact Report
- Version change: 1.1.0 → 2.0.0 (MAJOR: redefine locked S-I18N; drop public Russian)
- Modified principles:
  - VII. Public UI locales: Italian primary + English (constitution document stays English)
- Locked Decisions: S-I18N it + en; IT primary (was it + ru + en)
- S-STRIPE unchanged (Payment Element until a later Checkout Sessions spec)
- Follow-up TODOs: none
-->

<!--
Previous report (1.0.0 → 1.1.0) kept for lineage:
- Modified principles:
  - I. Spec-Driven Development lock-in — mandatory four vs optional quality gates
  - IV. Spec persistence — parent-adjacent NNN.K amendments (gm-edu style)
  - V. Meaningful tests — propose-only rows; owner may drop; no stupid tests
  - VIII. Session integrity — owner UAT handshake; Cursor-browser wait
- Added principles:
  - IX. Ask on doubt
  - X. Write this repo only; read siblings
  - XI. Tasks: complexity + model; launch on approval
  - XII. Errors and logs
- Added sections: Locked Decisions; SITE queue
- Removed sections: none
- Follow-up TODOs: none
-->

# Safehouse.community Constitution

## Core Principles

### I. Spec-Driven Development lock-in
Product work in this git repo MUST follow the **mandatory** Spec Kit path:

`/speckit-specify` → `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`

Catalog (clarify, checklist, analyze, converge, …) stays available:
https://github.com/github/spec-kit/blob/main/docs/quickstart.md
https://github.com/github/spec-kit/blob/main/docs/reference/agentic-sdd.md

MUST:

- Run the four mandatory commands in order for every feature.
- Implement **only** from the current feature `tasks.md`.
- Keep **one** active spec in this repo (`S-SPEC-SERIAL`).
- Use main-feature directories `specs/NNN-short-name/`.
- A correction, regression, or owner-UAT repair of an existing main
  feature MUST use `specs/NNN.K-short-name/` (`001.1`, `001.2`, …). Finish
  opened amendments before `NNN+1`.
- Before implement: compliance-review this constitution and the feature
  artifacts (spec, plan, tasks).
- After implement (including hotfix): complete Principle VIII owner UAT
  before starting the next SITE-queue row.

**Owner-discretion (optional):** `/speckit-clarify`, `/speckit-checklist`,
`/speckit-analyze`, `/speckit-converge`. Run them **only** when the owner
asks, or when an opened hole needs that gate. MUST NOT treat a missing
optional step as a block on `/speckit-implement`. If the owner asks for
converge and it appends tasks, implement those, then converge again until
it reports converged (or the owner stops).

MUST NOT:

- Ship product PHP/JS/CSS/Blade from chat without the mandatory path.
- Skip specify, plan, or tasks before implement.
- Glue two SITE-queue rows into one mega-spec.
- Start the next queue row because PHPUnit or Cursor-browser passed.

Hotfix: the owner MUST say hotfix (or an explicit interrupt). Capture it in
a spec, then return to the interrupted feature.

**Rationale:** same operating default as `gm-edu-crm` Principle I — specs
gate code; optional quality gates are tools the owner chooses.

### II. Repository is the system of record
Knowledge that is not in this git tree does not exist for a new session
([Learn Harness Lecture 03](https://github.com/walkinglabs/learn-harness-engineering/blob/main/docs/en/lectures/lecture-03-why-the-repository-must-become-the-system-of-record/index.md)).

**Notion is retired** for executor logs, status, and planning. Historical
dump only: [`.specify/progress_old/notion-extract.md`](../progress_old/notion-extract.md).
Old `AGENTS.md` encyclopedias and `docs/HANDOFF.md` MAY be opened **only
when a detail is missing here** — MUST NOT every turn, MUST NOT replace
this constitution or official vendor docs.

| Need | Where |
|------|--------|
| Law | this constitution |
| Feature history | `specs/` |
| Session handoff | `.specify/progress/` |
| How to run / verify | `README.md` + `AGENTS.md` (router) |
| Stack how-to | official docs in § Tech Stack |

**Fresh session test:** what this is, how it is organized, how to run, how
to verify, where we are now — answerable from the repo alone.

### III. Official-docs supremacy
Two classes. MUST NOT confuse them.

**A — Official vendor docs. REQUIRED at every later decision.**

On **every** Laravel / Filament / Stripe / Spatie / Tailwind / Vite / DDEV /
Caddy / PHP decision (specify, plan, tasks, implement, CSS, routes, CMS),
the agent MUST **open** the matching official page **this turn** (URLs in
§ Tech Stack) and **cite** that URL in the committed artifact. Memory of
docs is not a cite. No page opened this turn → the stack decision was
**not** made.

**B — This constitution** is product law for Safehouse.community. Past
chat, Notion, and bloated AGENTS.md are not substitutes for class A.

If vendor docs disagree with this constitution or with this repo’s code:
**STOP**, notify the owner, ask via the structured question menu. MUST NOT
silently pick a side.

Prefer framework-native mechanisms already in composer (Laravel, Filament,
Spatie, Stripe.js Payment Element) over custom invention.

### IV. Spec persistence (living in-flight, flow-forward shipped)
Spec Kit names three models
([spec-persistence](https://github.github.com/spec-kit/concepts/spec-persistence.html)).
This project locks:

**Living spec (before first implement only).** Small clarifications MAY be
edited in place on `spec.md` / `plan.md` / `tasks.md`. Append a dated
**Changelog** on `spec.md`.

**Flow-forward after implement / UAT.** Do not rewrite a shipped directory.
New behaviour or a serious bugfix MUST be a new directory:

- Next **main** feature: next integer `specs/NNN-slug/`
- Repair of parent `NNN`: `specs/NNN.K-slug/` with
  `Extends: specs/NNN-…` and `Lineage: NNN.K`

`create-new-feature.sh --number` accepts **integers only**. For `NNN.K`
the specify command MUST set `SPECIFY_FEATURE_DIRECTORY=specs/NNN.K-slug`
explicitly (do not rely on auto-increment).

### V. Meaningful tests
After `/speckit-tasks` the agent MUST **propose** tests only for real logic
that rebuild and owner UAT cannot prove: services, webhooks, Form Requests,
PII hashing, locale routing, RBAC, consent gating, payment ingest. Each
such task MUST include `test: yes/no` plus one why. The owner MAY drop test
rows at the post-tasks wait. Silence is not “cover the whole module”.

MUST NOT require a coverage percentage. MUST NOT write stupid tests
(getters, JSON roundtrip, “HTTP 200” without a contract, i18n snapshots of
whole templates, tests of Laravel/Filament core). If the agent cannot name
the bug the test would catch, the test MUST NOT be written.

PHPUnit / Pint prove **code** contracts. They MUST NOT replace owner UAT
(Principle VIII). Visual CSS MAY use a documented visual matrix plus owner
UAT.

### VI. Security, PII, production safety
No secrets in git, specs, progress, or chat. Card data never touches this
app (Stripe.js). Volunteer/contact: `ip_hash` / `user_agent_hash` only.
CSP and HSTS belong in **Caddy**, not PHP. `$fillable` explicit; no
`$guarded = []`; no raw SQL with user input. Filament path
`/cms-safehouse` (never `/admin`).

Production `https://safehouse.community` is live. `git push` and deploy
MUST NOT happen unless the owner asks in that instruction. Agents MUST NOT
run `ddev stop` / `poweroff` / `delete` or add DDEV services without
approval. `ddev start` on the existing project is allowed. After touching
`database/migrations/` or `database/seeders/`, run
`ddev exec php artisan migrate` or `migrate --seed` in the same session.

Legal / DPA / lawyer-grade GDPR wording: **STOP**. Operational cookie
banner, consent storage, and policy **pages in CMS** are in scope; legal
counsel is not (`S-GDPR`).

Dangerous instance changes: estimate danger and ASK. MUST NOT log secrets.

### VII. Stack freeze, simplicity, one problem
Stay on Laravel 13, PHP 8.4, Filament v4, Blade + Tailwind + Vite, MariaDB,
DDEV, Caddy. New libraries require a spec that cites official docs and
lists alternatives. YAGNI: no drive-by refactors. One feature = one SITE
queue row.

Public UI: **Italian primary**, English second; zero hardcoded
user-facing strings (`__()` / CMS). There is **no** public Russian locale.
Chat: owner’s language (typically Russian). Constitution, specs, plans,
tasks, checklists, progress: **English** (not Italian). Numbered UAT
scripts in chat MUST be the owner’s language; Italian UI labels MAY be
quoted inside that script.

Architecture: HTTP → middleware → thin controller → Form Request → DTO →
Service → Model. No business logic and no `new ClassName()` in controllers
(constructor injection).

### VIII. Session integrity and owner UAT
A session is not done until relevant tests/Pint for the change pass,
progress is appended, temp/debug leftovers are gone, and the next session
can start from the repo alone
([Lecture 12](https://github.com/walkinglabs/learn-harness-engineering/blob/main/docs/en/lectures/lecture-12-why-every-session-must-leave-a-clean-state/index.md)).

After `/speckit-implement` (including hotfix), the agent MUST NOT call the
feature accepted and MUST NOT open the next SITE-queue row until the owner
has run a **user-test checklist** (`S-OWNER-UAT`):

- Durable English list: `specs/NNN-*/checklists/owner-user-tests.md`
- Same turn, paste a numbered script in **Russian** (menu path, URL, quoted
  Italian label, expected result, Pass / Fail / Skip)
- Wait. Fail → triage (hotfix spec if needed). Skip → record the reason.
- Live Stripe / Espo / Ads credentials: mark **Skip until credentials exist**
- PHPUnit does not close the handshake
- **Cursor-browser:** offer what screens and why, then **wait**. MUST NOT
  drive `https://safehouse-community-site.ddev.site` or production unless
  the owner agrees **that turn**

### IX. Ask on doubt — do not guess
Any fork, unknown API, docs≠code, or “A or B” MUST STOP. List options in
the **Cursor structured question menu** (`AskQuestion`) and wait. MUST NOT
pick silently. Closed `S-*` rows are not questions.

### X. Write this repo only; read siblings
MUST write/edit **only** `/home/skoksharov/safehouse/safehouse-community-site`.

MUST **read** `/home/skoksharov/safehouse/nonprofit-espocrm` when the feature
touches donations ingest, PrimaNota, sportelli Case types, or shared Aurora
assets. Reading is not optional for those features. MUST NOT invent CRM
field names without opening that repo (and Espo docs if the mapping is an
Espo API).

If the feature cannot work without **changing** the CRM repo: STOP before
any write there. Tell the owner: path, files, why, official URL. Wait.

MUST NOT git commit or git push unless the owner explicitly asks.

### XI. Tasks: complexity, reasoned model proposals, owner approval
Every `/speckit-tasks` item MUST include complexity **1–10**, a proposed
model or subagent, and for non-trivial rows one short why.

After `/speckit-tasks` (and after `/speckit-plan` if `tasks.md` exists),
print in owner chat a table: task ID, one-line work, `[C#]`, proposed
model, short why. MUST wait for keep / change / drop.

Before spawning an **advanced** model (Claude Opus / GPT / Gemini / Fable,
or any non-default parent the owner has not approved for that row): ask
openly, naming task ID + model — Launch vs Replace. Silence, “continue”,
“implement”, or “ok” without naming launch vs replace is **not** approval.
Suggested models in `tasks.md` are proposals, never a launch order.

Mechanical rows (copy, Pint, grep, checklist ticks) SHOULD be proposed as
Auto or Composer 2.5 Fast.

### XII. Errors and logs
Expected failure MUST be a typed exception or an explicit catch that logs
and translates. MUST NOT empty-catch `Throwable`, return bool success, or
swallow into `null` when the caller must know. MUST NOT log API keys,
Stripe secrets, or Bearer tokens. Invalid Stripe webhook signatures: fail
closed (4xx), MUST NOT 500-retry storms.

## Tech Stack & Official Documentation

Locked stack (do not silently replace):

| Layer | Choice | Official docs (MUST open this turn) |
|-------|--------|-------------------------------------|
| App | Laravel 13 | https://laravel.com/docs/13.x |
| Language | PHP 8.4 | https://www.php.net/releases/8.4/en.php |
| CMS | Filament v4 | https://filamentphp.com/docs/4.x |
| UI | Blade, Tailwind, Vite | https://laravel.com/docs/13.x/blade · https://tailwindcss.com/docs · https://vite.dev/guide/ |
| I18n | spatie/laravel-translatable | https://spatie.be/docs/laravel-translatable |
| Media | spatie/laravel-medialibrary | https://spatie.be/docs/laravel-medialibrary |
| RBAC | spatie/laravel-permission | https://spatie.be/docs/laravel-permission |
| Donations | Stripe Payment Element | https://docs.stripe.com/payments/payment-element |
| Tests | PHPUnit (Laravel) | https://laravel.com/docs/13.x/testing |
| Style | Pint | https://laravel.com/docs/13.x/pint |
| Local | DDEV | https://ddev.readthedocs.io/en/stable/ |
| Edge | Caddy v2 | https://caddyserver.com/docs/ |
| SDD | Spec Kit | https://github.com/github/spec-kit |

Harness (process): https://github.com/walkinglabs/learn-harness-engineering/tree/main/docs/en

Sibling CRM (read when integrating): `/home/skoksharov/safehouse/nonprofit-espocrm`

## Spec Lifecycle & Artifact Layout

Mandatory order: specify → plan → tasks → implement (Principle I).

SITE queue (`S-SITE-QUEUE`) — one row at a time, owner UAT between rows:

| ID | Feature |
| :--- | :--- |
| S01 | Stack/compliance audit (ranked findings; remediations = later `001.K` or new NNN) |
| S02 | Analytics + Ad Grants / sitelinks + SEO |
| S03 | Satispay + 5×1000 banners (placement with owner) |
| S04 | GDPR / cookie / privacy operational alignment |

MUST NOT start S02 while S01 owner UAT is open. Remediations from S01 MUST
NOT be glued into S02.

Layout:

```
.specify/memory/constitution.md
.specify/templates/               # kit defaults; do not casually edit
.specify/.gitignore               # kit-owned; do not “fix”
.specify/progress/                # append-only handoffs
.specify/progress_old/            # frozen Notion extract
specs/NNN-slug/  or  specs/NNN.K-slug/
.cursor/skills/speckit-*/
```

`AGENTS.md` MUST stay a short **router**
([Lecture 04](https://github.com/walkinglabs/learn-harness-engineering/blob/main/docs/en/lectures/lecture-04-why-one-giant-instruction-file-fails/index.md)).
Protect this constitution before `specify init --here --force`.

## Locked Decisions

Closed. MUST NOT reopen as design forks.

| ID | Decision |
| :--- | :--- |
| S-SPEC-SERIAL | One active spec; no parallel implement |
| S-SITE-QUEUE | Order = S01→S04 unless the owner reorders |
| S-NO-NOTION | No Notion executor logs after 2026-09-13 |
| S-STRIPE | Native Stripe Payment Element; no local payment DB; no Donorbox |
| S-CMS-PATH | Filament `/cms-safehouse` only |
| S-I18N | UI it + en; IT primary; no public `/ru` |
| S-CSP | CSP + HSTS in Caddy only |
| S-NO-OCTANE | No Octane / Horizon / Telescope in production |
| S-NO-PUBLIC-ACCOUNTS | No public user accounts or comments |
| S-OWNER-UAT | Owner checklist after implement; PHPUnit does not close it |
| S-GDPR | Operational consent in scope; legal DPA = STOP |
| S-TEST-PROPOSE | Propose real-logic tests only; no coverage % |
| S-MODEL-APPROVAL | Advanced models launch only on explicit owner approval |
| S-CROSS-REPO | Write site repo only; read CRM when integrating |

## Governance

This constitution supersedes `AGENTS.md`, old Notion trackers, and chat
memory when they conflict. Amendments: owner request →
`/speckit-constitution` → Sync Impact Report → semantic version:

- MAJOR: remove/redefine a principle or leave SDD
- MINOR: new principle/section or materially expanded guidance
- PATCH: wording only

`RATIFICATION_DATE` stays 2026-09-13. `LAST_AMENDED_DATE` is the amendment
day.

Compliance review is mandatory before `/speckit-implement`: Principles I–XII
and the locked table. Missing vendor-doc open/cite on a stack decision =
non-compliant. After implement, Principle VIII MUST complete before the
next queue row.

**Git:** commit only when the owner asks; **never push** unless asked in the
same instruction. Track constitution, `specs/`, Spec Kit defaults, and
`.cursor/skills/`. Do **not** change `.specify/.gitignore`.

**Next Actions footer:** every substantive Spec Kit or governance milestone
MUST end owner chat with short Next Actions (realistic next commands).

**Version**: 2.0.0 | **Ratified**: 2026-09-13 | **Last Amended**: 2026-09-13
