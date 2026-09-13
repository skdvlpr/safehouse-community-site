# 000 — Constitution v1.0.0

**Date:** 2026-09-13  
**Agent:** Cursor Grok  
**Spec Kit:** v1.0.6 (`cursor-agent`, scripts `sh`)

## State

- Official Spec Kit initialized in this repo.
- Constitution ratified at `.specify/memory/constitution.md` **v1.0.0**.
- Notion executor logging **retired**. Frozen extract:
  `.specify/progress_old/notion-extract.md`.
- No product feature spec yet. No application code in this step.

## Files

- `.specify/` (kit defaults + constitution + progress)
- `.cursor/skills/speckit-*/`
- Root `.gitignore`: track `.cursor/skills/`, leave kit `.specify/.gitignore` alone

## Verification

- `specify version` → 1.0.6
- Template resolve: `constitution-template` succeeded
- No `.specify/extensions.yml` hooks

## Next

User QA of `/speckit-*` skills, then `/speckit-specify` for the queued backlog
(audit → analytics/SEO/Ad Grants → banners → GDPR) one feature at a time.
