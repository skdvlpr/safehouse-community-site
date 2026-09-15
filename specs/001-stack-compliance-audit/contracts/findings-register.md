# Contract: findings register

**Artifact**: `specs/001-stack-compliance-audit/findings.md` (created at implement)

**Consumers**: Owner (UAT), later `/speckit-specify` for `001.K` and implementers of `002`–`006`.

## Header (required)

```markdown
# Findings register — Safehouse.community S01

- Date: YYYY-MM-DD
- Local URL: …
- Production: inspected | not inspected
- Production permission that turn: yes | no | not-asked
- CRM repo read: yes | no
```

## Finding template (required for each `open` row)

```markdown
## F-NNN — <title>

- Severity: blocker | high | medium | low | note
- Area: <area enum>
- Env: local | production | both | unknown
- Bucket: 001.K | 002 | 003 | 004 | 005 | 006 | owner-ops | other-repo
- Bucket why: …
- Constitution: …
- Docs: <https URL or “n/a”>

**Evidence**

…

**Impact**

…
```

## All-clear template (required when an area has zero opens)

```markdown
## <area> — none found

- Env: …
- Evidence: what was checked (files, URLs, tests)
```

## Invariants

1. No secret values, tokens, or `.env` contents.
2. Every [area-coverage](./area-coverage.md) row is referenced.
3. Overlaps with missing analytics/SEO/ads/banners/privacy copy use buckets `002`–`006`, not `001.K`.
4. `other-repo` findings name the CRM path and stop; no invented field names.
5. English only in this file. Russian summary is chat + owner checklist, not this contract.
