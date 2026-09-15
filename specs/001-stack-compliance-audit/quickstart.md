# Quickstart: validate S01 audit deliverable

This feature does not change the running site. Validation is: register completeness + app tree untouched.

## Prerequisites

- Repo root: `safehouse-community-site`
- DDEV already configured. `ddev start` is allowed; **do not** `ddev stop`.
- Official stack docs from [research.md](./research.md) available in the implement session.

## 1. Confirm the app was not modified

```bash
git status --short
ddev exec php artisan test
ddev exec ./vendor/bin/pint --test
```

Expected: no unexpected `app/`, `resources/`, `routes/`, `database/` edits from this feature. Test/Pint pass as they did before (existing suite).

Docs: https://laravel.com/docs/13.x/testing · https://laravel.com/docs/13.x/pint

## 2. Confirm the register exists

Required files after implement:

- `specs/001-stack-compliance-audit/findings.md` matching [contracts/findings-register.md](./contracts/findings-register.md)
- `specs/001-stack-compliance-audit/checklists/owner-user-tests.md`
- Coverage for every row in [contracts/area-coverage.md](./contracts/area-coverage.md)

## 3. Completeness checks (SC-002, SC-004)

1. Every area id has either `F-*` rows or a **none found** section.
2. Header states local URL and whether production was inspected.
3. Count findings whose title is about missing analytics / SEO / sitelinks / banners / privacy copy: at least 90% bucketed `002`–`006`.
4. No secret values in `findings.md`.
5. Donation/sportello mapping findings: `CRM repo read: yes` in the header.

## 4. Owner UAT (SC-001, SC-005)

Do **not** treat PHPUnit as acceptance.

1. Owner opens `findings.md`.
2. Agent pastes a numbered **Russian** script (constitution VIII).
3. Owner marks Pass / Fail / Skip on `owner-user-tests.md`.
4. Fail → `001.K` or fix the register (living spec, still before next queue row).
5. Cursor-browser against DDEV/production only if the owner agrees **that turn**.

## 5. Stop conditions

- Need to edit CRM → stop, tell owner (Principle X).
- Need to change checkout to Checkout Sessions API → note only (S-STRIPE); not a repair.
- Legal DPA wording → stop (`S-GDPR`).
