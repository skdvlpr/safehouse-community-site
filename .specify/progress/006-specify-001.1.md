# 006 — Specified 001.1 production snapshot, local parity, queue replan

**Date:** 2026-09-13  
**Command:** `/speckit-specify`  
**Directory:** `specs/001.1-prod-parity-replan`

## Why

Owner UAT of S01: drop public Russian (including constitution), inspect production over SSH, align local published content with production, rewrite 002–006, leave app/webhook/checkout repairs for later.

## Inspect (read-only, same turn as specify)

- Public HTTPS probes + SSH as `deploy` with `~/.ssh/safehouse-deploy` (personal `id_ed25519` denied).
- No production writes. `.env` not read.

## Stop

`/speckit-plan` next. Implement only when the owner says implement.
