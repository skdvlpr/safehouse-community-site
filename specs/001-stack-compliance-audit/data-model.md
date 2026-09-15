# Data model: 001 findings register

No database tables. Entities are markdown records.

## Finding

A single issue **or** an explicit all-clear for one coverage row.

| Field | Type | Rules |
| :--- | :--- | :--- |
| `id` | string | `F-001`, `F-002`, … sequential in `findings.md` |
| `title` | string | One line, English |
| `severity` | enum | `blocker` \| `high` \| `medium` \| `low` \| `note` |
| `area` | enum | See Area |
| `status` | enum | `open` (default) \| `none-found` (all-clear row) |
| `env` | enum | `local` \| `production` \| `both` \| `unknown` |
| `evidence` | text | Reproduce steps **or** file path + symbol. No secret values |
| `impact` | text | Who is hurt and how |
| `constitution` | text | Principle / `S-*` id if applicable |
| `docs_url` | URL | Official page when the finding is stack/security |
| `bucket` | enum | `001.K` \| `002` \| `003` \| `004` \| `005` \| `006` \| `owner-ops` \| `other-repo` |
| `bucket_why` | text | One sentence |

### Area values

- `security-payments`
- `personal-forms`
- `cookie-consent`
- `staff-cms`
- `public-journeys`
- `i18n`
- `architecture`
- `secrets-repo`
- `crm-integration`

### Severity (testable)

| Severity | Use when |
| :--- | :--- |
| `blocker` | Secrets in git, fail-open payments, raw PII stored, staff CMS on `/admin` |
| `high` | Authz hole, unsigned webhook accepted, unhashed IP/UA stored, mass-assignment of privileged fields |
| `medium` | Broken public journey, empty-catch hiding donor/CRM failure, mixed locale on a primary page |
| `low` | Hardcoded string, deprecated CSRF helper with equivalent protection still on, docs drift |
| `note` | Locked-decision reminder (e.g. Stripe Checkout Sessions recommendation vs S-STRIPE) |

### State

Findings are born `open` or `none-found`. This feature does **not** transition them to fixed. Fixes are later specs.

## AreaCoverage

One row per mandatory area in [contracts/area-coverage.md](./contracts/area-coverage.md).

| Field | Rules |
| :--- | :--- |
| `area` | Must match Area enum |
| `outcome` | `findings` (list ids) **or** `none-found` |
| `checked_at` | ISO date |
| `env` | Same as Finding.env for the pass |

FR-003: silence is invalid. Every coverage row MUST exist.

## EnvironmentNote

Header of `findings.md`:

| Field | Rules |
| :--- | :--- |
| `local_url` | DDEV URL used |
| `production_url` | `https://safehouse.community` or “not inspected” |
| `production_permission` | `yes` / `no` / `not-asked` |
| `crm_repo_read` | `yes` / `no` (must be yes if donation/sportello findings exist) |

## Relationships

- AreaCoverage 1—n Finding (zero findings allowed only if outcome is `none-found`).
- Finding.bucket points at a later spec or ops queue; MUST NOT point at `002`–`006` for true security holes.
