<!--
Sync Impact Report
- Version change: (Spec Kit template placeholders) → 1.0.0
- Modified principles:
  - [PRINCIPLE_1_NAME] → I. Spec-Driven Development lock-in
  - [PRINCIPLE_2_NAME] → II. Repository is the system of record
  - [PRINCIPLE_3_NAME] → III. Official-docs supremacy
  - [PRINCIPLE_4_NAME] → IV. Spec persistence (living in-flight, flow-forward shipped)
  - [PRINCIPLE_5_NAME] → V. Meaningful tests
  - added VI. Security, PII, production safety
  - added VII. Stack freeze, simplicity, one problem
  - added VIII. Session integrity
- Added sections: Tech Stack & Official Documentation; Spec Lifecycle & Artifact Layout
- Removed sections: none (template slots filled)
- Follow-up TODOs: none in this file. Deferred: slim AGENTS.md into a router (Lecture 04).
-->

# Safehouse.community Constitution

## Core Principles

### I. Spec-Driven Development lock-in
All product work (features, substantial bugfixes, security remediations,
analytics, SEO, legal copy that changes behaviour) MUST go through Spec Kit
in this repository: `/speckit-specify` → `/speckit-plan` → `/speckit-tasks` →
`/speckit-implement`, using `/speckit-clarify`, `/speckit-analyze`,
`/speckit-checklist`, and `/speckit-converge` when those commands apply.

Agents MUST NOT implement application code, migrations, or deploy changes
outside that chain. Leaving SDD requires an explicit user amendment to this
constitution (version bump). Chat agreement is not enough.

**One in progress:** Only one feature directory may be in implement/converge
at a time. Drafting a sibling spec with `/speckit-specify` is allowed only
when the agent states why it would reshape the active work, and the **user
approves**.

**Rationale:** Spec Kit is the harness loop (specify → verify → handoff).
Ad-hoc coding is how agents overreach and under-finish.

### II. Repository is the system of record
Knowledge that is not in this git tree does not exist for a new agent session
([Learn Harness Engineering, Lecture 03](https://github.com/walkinglabs/learn-harness-engineering/blob/main/docs/en/lectures/lecture-03-why-the-repository-must-become-the-system-of-record/index.md);
OpenAI “repo as spec”).

**Notion is retired** for executor logs, task status, and planning. Do not
create, update, or depend on Notion pages for ongoing work. Historical
Notion is a read-only dump:
[`.specify/progress_old/notion-extract.md`](../progress_old/notion-extract.md).

Authoritative locations:

| Need | Where |
|------|--------|
| Law | this constitution |
| Feature history | `specs/` (each directory is a snapshot) |
| Session handoff | `.specify/progress/` |
| How to run / verify | `README.md` + slim `AGENTS.md` (router) |
| Stack how-to | official docs cited in § Tech Stack |

**Fresh session test:** a new chat with only the repo MUST answer: what this
is, how it is organized, how to run, how to verify, where we are now.

### III. Official-docs supremacy
Before any non-trivial decision in `/speckit-plan`, `/speckit-tasks`,
`/speckit-implement`, `/speckit-analyze`, `/speckit-converge`, **or** any
material action outside those skills (architecture, security, payments,
i18n, CMS, deploy), the agent MUST open the current official documentation
for the involved layer (URLs in § Tech Stack), cite it in the artifact, and
state why the choice matches the docs.

“Because AGENTS.md / we always did” is not a rationale. If docs and this
constitution conflict, **stop**, tell the user, and prefer official docs
plus a constitution amendment if the project must diverge.

Prefer framework-native mechanisms (Laravel, Filament, Spatie packages
already in composer, Stripe.js Payment Element) over custom invention.

### IV. Spec persistence (living in-flight, flow-forward shipped)
Spec Kit does **not** force one mutation model
([spec-persistence](https://github.github.com/spec-kit/concepts/spec-persistence.html),
[evolving-specs](https://github.github.com/spec-kit/guides/evolving-specs.html)).
This project **does**:

**Living spec (in-flight only).** While a feature is not converged/shipped,
small clarifications and minor fixes MUST be appended to the same
`specs/NNN-slug/` (`spec.md` first, then `plan.md` / `tasks.md`, then
`/speckit-analyze`). Add a short **Changelog** at the bottom of `spec.md`.
Do not silently rewrite history: append, date, and keep prior acceptance
criteria unless the user explicitly replaces them.

**Flow-forward (shipped or substantial).** After a feature is converged (or
the change is a serious bugfix / behaviour rewrite of a completed spec),
MUST create a **new** feature directory via `/speckit-specify`. Leave the
old directory untouched as the historical snapshot. Cross-link:

```
Extends: specs/001-form-creation
Lineage: 001.1 (human alias for this follow-up)
```

**Directory names:** Spec Kit’s `create-new-feature.sh` only indexes folders
matching `^[0-9]{3,}-` (integer prefix, then hyphen). Dotted folders such as
`000.1-form-problem-fix` are **not** counted by the CLI. Therefore:

- Folder MUST be the next sequential integer: `002-form-problem-fix`
- Human lineage `001.1` MUST live in `spec.md` title/header, not in the
  folder prefix
- Pass `--short-name` for the kebab slug; do not invent dotted prefixes

**Rationale:** completed specs stay auditable; in-flight work stays aligned
without duplicating every typo as a new feature.

### V. Meaningful tests
Tests MUST cover behaviour that can regress or leak: donations/webhooks,
PII hashing, locale routing, CMS auth/RBAC, consent gating, public forms.
Each spec’s `tasks.md` MUST name the verification command (PHPUnit, Pint,
curl, or a documented manual QA checklist for visual-only work).

MUST NOT chase 100% line coverage or tests that only mirror implementation.
MUST NOT weaken or delete tests to greenwash a break. UI work that changes
user-visible behaviour MUST be verified in a browser or an equivalent
end-to-end check before the spec is called done
([Lecture 08](https://github.com/walkinglabs/learn-harness-engineering/blob/main/docs/en/lectures/lecture-08-why-feature-lists-are-harness-primitives/index.md)
triple: behaviour + verification + state).

### VI. Security, PII, production safety
No secrets in git, specs, progress logs, or chat dumps. Card data never
touches this app (Stripe.js). Volunteer/contact: store `ip_hash` /
`user_agent_hash` only. CSP and HSTS belong in **Caddy**, not PHP.

`$fillable` explicit; no `$guarded = []`; no raw SQL with user input.
Filament panel path remains `/cms-safehouse` (never `/admin`).

Production (`https://safehouse.community`) is live. Deploy and `git push`
MUST NOT happen unless the user explicitly asks. Agents MUST NOT run
`ddev stop`, `ddev poweroff`, `ddev delete`, or add DDEV services without
explicit approval. `ddev start` on the existing project is allowed.
After touching `database/migrations/` or `database/seeders/`, run
`ddev exec php artisan migrate` or `migrate --seed` in the same session.

### VII. Stack freeze, simplicity, one problem
Stay on the current stack (Laravel 13, PHP 8.4, Filament v4, Blade +
Tailwind + Vite, MariaDB, DDEV, Caddy). New libraries require a spec that
cites official docs and lists alternatives. YAGNI: no drive-by refactors.

One Spec Kit feature = one problem. Public UI: Italian primary, plus
Russian and English; zero hardcoded user-facing strings (`__()` / CMS).
Chat with the user in **Russian**; constitution, specs, progress, and code
comments in **English**.

### VIII. Session integrity
A session is not done until: relevant tests/Pint for the change pass,
progress is appended, temp/debug leftovers are gone, and the next session
can start from the repo alone
([Lecture 12](https://github.com/walkinglabs/learn-harness-engineering/blob/main/docs/en/lectures/lecture-12-why-every-session-must-leave-a-clean-state/index.md)).
Do not declare victory because “the code looks complete.”

## Tech Stack & Official Documentation

Locked stack (do not silently replace):

| Layer | Choice | Official docs (MUST consult) |
|-------|--------|------------------------------|
| App | Laravel 13 | https://laravel.com/docs/13.x |
| Language | PHP 8.4 | https://www.php.net/releases/8.4/en.php |
| CMS | Filament v4 | https://filamentphp.com/docs/4.x |
| UI | Blade, Tailwind, Vite | https://laravel.com/docs/13.x/blade · https://tailwindcss.com/docs · https://vite.dev/guide/ |
| I18n | spatie/laravel-translatable + `/{locale}/` | https://spatie.be/docs/laravel-translatable |
| Media | spatie/laravel-medialibrary | https://spatie.be/docs/laravel-medialibrary |
| RBAC | spatie/laravel-permission | https://spatie.be/docs/laravel-permission |
| Donations | Stripe Payment Element → EspoCRM ingest | https://docs.stripe.com/payments/payment-element |
| Local | DDEV (nginx-fpm, MariaDB 11.8) | https://ddev.readthedocs.io/ |
| Edge | Caddy v2 | https://caddyserver.com/docs/ |
| SDD | Spec Kit | https://github.com/github/spec-kit · https://github.github.io/spec-kit/ |

Harness design references (process, not stack):
https://github.com/walkinglabs/learn-harness-engineering/tree/main/docs/en

Architecture in this app: HTTP → middleware → thin controller → Form Request
→ DTO → Service → Model. No business logic and no `new ClassName()` in
controllers (constructor injection).

## Spec Lifecycle & Artifact Layout

Default command order (do not skip plan/tasks for product work):

1. `/speckit-constitution` — only when governance changes
2. `/speckit-specify` — what/why; creates `specs/NNN-slug/`
3. `/speckit-clarify` — if acceptance criteria are ambiguous
4. `/speckit-plan` — how; **cite official docs**
5. `/speckit-tasks` — checkboxes with verification
6. `/speckit-analyze` — before implement
7. `/speckit-implement` — code + tests
8. `/speckit-converge` — until Converged; then stop or new spec

Layout:

```
.specify/memory/constitution.md   # this file (law)
.specify/templates/               # Spec Kit defaults; do not edit unless amending kit
.specify/.gitignore               # kit-owned (feature.json); do not “fix”
.specify/progress/                # append-only session handoffs
.specify/progress_old/            # frozen pre-SDD extract
specs/NNN-slug/                   # immutable after converge (Principle IV)
.cursor/skills/speckit-*/         # committed Cursor skills
```

`AGENTS.md` MUST stay a short **router** (overview, run/verify, pointers),
not a second constitution
([Lecture 04](https://github.com/walkinglabs/learn-harness-engineering/blob/main/docs/en/lectures/lecture-04-why-one-giant-instruction-file-fails/index.md)).
`docs/HANDOFF.md` is historical (Auto/Power); new handoffs go to
`.specify/progress/`.

Protect this constitution before `specify init --here --force` refreshes
kit files ([evolving-specs](https://github.github.com/spec-kit/guides/evolving-specs.html)).

## Governance

This constitution supersedes `AGENTS.md`, old Notion trackers, and chat
memory when they conflict. Amendments: user request → `/speckit-constitution`
→ Sync Impact Report → semantic version:

- MAJOR: remove/redefine a principle or leave SDD
- MINOR: add a principle or material section
- PATCH: wording only

Compliance: every spec plan and implement review MUST check Principles I–VIII.
Unjustified complexity MUST be rejected. Runtime guidance for “how to boot
the site” lives in `README.md`; runtime law lives here.

**Git:** commit only when the user asks; **never push** unless the user asks
in the same instruction. Track constitution, `specs/`, Spec Kit defaults,
and `.cursor/skills/` in git. Do **not** change `.specify/.gitignore`.

**Version**: 1.0.0 | **Ratified**: 2026-09-13 | **Last Amended**: 2026-09-13
