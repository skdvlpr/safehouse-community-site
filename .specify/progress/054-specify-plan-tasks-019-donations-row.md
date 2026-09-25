# 054 — Specify / plan / tasks `019-donations-row` (expanded)

**Date**: 2026-09-25
**Agent**: Cursor Grok 4.6
**Active spec**: `specs/019-donations-row/`
**Feature pointer**: `.specify/feature.json` → `specs/019-donations-row`

## Current state

Owner accepted `021-mobile-header-drawer` UAT (“отлично”) and asked to start the donations spec, expanding it in the same turn:

- Listing: **Donazioni | Sostieni Safe House. 5 x 1000 o pagamenti digitali.**; 5 x 1000 + bank transfer on one wide row
- 5 x 1000 page and Stripe campaign pages: Chi siamo heading **outside** the glass/form card
- One-time: **Donazione |** campaign name (example Dona a Safe House)
- Recurring: **Donazione ricorrente | Sostieni Safe House ogni mese con un contributo ricorrente.**
- Drop body sentence about interrupting via the Stripe donor portal; red cancel panel stays

Specify, plan, and tasks updated. **Implement is waiting** on the owner’s task/model table.

## Files

- `specs/019-donations-row/spec.md`
- `specs/019-donations-row/plan.md`
- `specs/019-donations-row/tasks.md`
- `specs/019-donations-row/research.md`
- `specs/019-donations-row/data-model.md`
- `specs/019-donations-row/contracts/heading.md`
- `specs/019-donations-row/quickstart.md`
- `specs/019-donations-row/checklists/requirements.md`

## Verification

Checklist passed. Vendor docs opened this turn: Blade, Tailwind grid-template-columns, Laravel testing, Stripe Payment Element. `S-STRIPE` unchanged.

## Blockers

Owner must approve the tasks complexity/model table before `/speckit-implement`. Silence is not approval for a non-inherit model; this table proposes inherit for every row.

## Next steps

1. Owner approves (or edits) the task table
2. `/speckit-implement` from `tasks.md` only
3. Owner UAT, then the next page spec
