# 057 — Swap Diventa socio cards 04 and 06

**Date**: 2026-09-25
**Agent**: Cursor Grok 4.6
**Active spec**: `specs/023-home-stats-form/` (owner UAT passed). Card swap is a content-order follow-up, not a new SITE-queue row.

## Current state

Owner approved 023 (“Всё классно”). Asked to swap Diventa socio numbered cards **04 Chi può diventare socio?** with **06 Cosa significa essere socio?**, then commit, push, and deploy (owner watches deploy).

Local cards come from CMS HTML (`landing_cards` empty). Swap is in page body + seeder export. Migration also swaps `meta.landing_cards` when production has structured cards.

## Files

- `app/Support/MembershipLandingCardOrder.php`
- `database/migrations/2026_09_25_124400_swap_membership_landing_cards_04_and_06.php`
- `database/seeders/data/deploy-pages.php` (diventa-socio IT body only; FAQ unchanged)
- `tests/Unit/MembershipLandingCardOrderTest.php`

## Next steps

Commit 023 + this swap, push `main` (CI deploy). Owner controls deploy watch.
