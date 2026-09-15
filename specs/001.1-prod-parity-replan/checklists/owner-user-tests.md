# Owner user tests: Production snapshot, local parity, queue replan

**Purpose**: Owner accepts 001.1. PHPUnit does not close this handshake.  
**Created**: 2026-09-13  
**Feature**: [spec.md](../spec.md)

**Marker semantics**: `[x]` = Pass (owner, or agent browser/HTTP this turn when the owner asked to close what the agent can). Leave `[ ]` only for items not yet verified.

**Agent verification**: 2026-09-13 Cursor browser + public HTTPS. Production `.env` not read. Local Stripe prefixes only (`pk_test_` / `sk_test_`), not full keys.

## Snapshot and production

- [x] UAT001 Open `contracts/production-inspect-2026-09-13.md` and agree it matches the live site you know
- [x] UAT002 Confirm `https://safehouse.community/it/diventa-socio` is the membership page (not a missing page)
- [x] UAT003 Confirm production `/it/landing-example` is not a public official page
- [x] UAT004 Confirm public `/ru` should stay absent (Italian primary, English second)

## Local preview

- [x] UAT005 Local `/it/diventa-socio` shows the same published membership idea as production
- [x] UAT006 Local `/it/landing-example` is not a public official page
- [x] UAT007 Header 5×1000 link still reaches the existing 5×1000 page
- [x] UAT008 Local donation test settings still work (no production live keys on DDEV)

## Product law and later specs

- [x] UAT009 Constitution `S-I18N` is Italian + English only; constitution text stays English
- [x] UAT010 Spec 004 treats socio as existing; spec 005 is Satispay QR banner only (no 5×1000 banner)

## Secrets

- [x] UAT011 `database/seeders/data/local-integrations.php` has no Stripe key material (test keys may remain in git history; owner does not require history rewrite)

## Notes from agent browser pass (2026-09-13)

- Production public URL table in the inspect contract still matches live HTTP (including `/cms-safehouse/login` 200 public, `/admin` 404).
- Italian socio local ≈ production (H1 «Diventa socio», quota € 50, same body). Language switcher is IT/EN only.
- English socio is a stub on **both** sides (almost no EN body). Production EN still shows the Italian title; local EN title is empty (`— Safe House`). Out of 001.1 page-key parity; content translation is later.
- Local 5×1000 page uses dummy codice fiscale `1234545667778`; production shows `96629270586`. Expected: donation settings were not imported.
- Local donations hub still has extra seed/faker campaigns (e.g. «Est laudantium ut et.»). Not in 001.1 page import.
- Local footer tagline/social differ slightly from production (no X link locally). Appearance settings, not CMS pages.
- Local CMS Stripe mode is TEST (`pk_test_` / `sk_test_`). Donation form «Dona a Safe House» loads; no live payment was made.

## Notes

- Cursor-browser: owner agreed this turn; local + public production only.
