# 053 — Locale layout parity (recorded, not built)

**Date**: 2026-09-25
**Agent**: implement pass on `specs/011-volunteer-compact`
**State**: Note only. Do not restyle Diventa socio in this pass.

## Owner finding

Italian `/it/diventa-socio` shows the animated membership landing (marquee, cards). English `/en/diventa-socio` falls back to one plain block. The public pages should look the same in Italian and English. Short heading taglines also look thin; when a later page spec ships, lengthen a too-short tagline without changing the meaning.

## Likely cause

`LandingContent::fromPage` only builds marquee values and slide-in cards when that locale has structured landing meta or HTML split into sections. If the English body is one unsplit block, `landing.blade.php` skips the marquee and cards.

## This pass

Volunteer only: the time-and-skills sentence is the tagline beside the title. Italian and English both use that shape. Diventa socio is unchanged.
