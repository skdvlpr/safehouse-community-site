# Owner user tests: Stack and compliance audit

**Purpose**: Owner accepts the findings register. Automated tests do not close this handshake.  
**Created**: 2026-09-13  
**Feature**: [spec.md](../spec.md) · [findings.md](../findings.md)

**Marker semantics**: `[x]` = owner Pass. Leave `[ ]` until the owner replies. Agent MUST NOT tick these.

## Register usability

- [x] UAT001 Open `specs/001-stack-compliance-audit/findings.md` and understand the severity order (high → note)
- [x] UAT002 Agree or dispute buckets (`001.K` vs `002`–`006` vs `owner-ops`)
- [x] UAT003 Confirm every area in the coverage table has findings or “none found”
- [x] UAT004 Confirm the site was not changed by this feature (public pages behave as before)



## Highest-risk findings (spot-check)

- [x] UAT005 Accept F-001 (no `/ru`) as a real gap vs three languages
- [x] UAT006 Accept F-002 (webhook signature 4xx) as a repair candidate for `001.K`
- [x] UAT007 Accept F-003 (Italian donor strings on English checkout)



## Environment

- [x] UAT008 Production was not inspected this turn — Skip until the owner asks for a production pass, or Fail if that was required



## Notes

- PHPUnit: 325 passed (2026-09-13) — code contract only
- Cursor-browser / production: not used (owner did not agree this turn)

