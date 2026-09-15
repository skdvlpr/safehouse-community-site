# Data model: 001.1 snapshot, parity, remap

No new MariaDB tables. Uses existing `pages` and `donation_campaigns`. New durable records are markdown.

## ProductionSnapshot

| Field | Type | Rules |
| :--- | :--- | :--- |
| `dated` | date | `2026-09-13` baseline |
| `public_urls` | list | path, HTTP status, notes |
| `headers` | map | HSTS, CSP, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Via |
| `pages` | list of PublishedPage | from production CMS, no bodies required in the public contract; bodies may live in the export file |
| `campaigns` | list of DonationCampaignRef | slug, title, `is_active` only |
| `articles_published` | int | count |
| `log_note` | text | redacted counts only |
| `cms_login_public` | bool | true if HTTP 200 from the inspector network |
| `caddy_community_block` | enum | `minimal` \| `matches-snippet` |

Validation: no `sk_`, `whsec_`, `APP_KEY`, Espo API keys.

## PublishedPage

Existing `App\Models\Page` ([Spatie translatable](https://spatie.be/docs/laravel-translatable/v6/installation-setup)).

| Field | Constraint |
| :--- | :--- |
| `key` | required string, unique |
| `template` | required string |
| `is_published` | boolean |
| `slug` | JSON locales; public routing uses `it` and `en` after S-I18N amend |
| `title` | JSON locales |
| `body` / `meta` | JSON; import locally; do not print secrets |

**Production keys (2026-09-13, all published):** `about`, `services`, `contact`, `privacy`, `cookie`, `trasparenza`, `home`, `faq`, `diventa-socio`.

**Must not be publicly published on local after parity:** `demo-landing` (slug `landing-example`).

## DonationCampaignRef

Existing `donation_campaigns`. Export **must not** include processor keys (none on this table today).

| Field | Constraint |
| :--- | :--- |
| `slug` | required |
| `title` | translatable |
| `is_active` | boolean |

**Production (2026-09-13):** `donate-to-safe-house` active; `donazione-ricorrente` active; `recurring-donation` active.

## QueueRemap

| Field | Rules |
| :--- | :--- |
| `finding_id` | `F-001`…`F-020` plus optional new inspect notes |
| `owner_decision` | short English |
| `bucket` | `001.1` \| `001.2` \| `002` \| `003` \| `004` \| `005` \| `006` \| `later-checkout` \| `owner-ops` \| `closed` |

## LocalePolicy

| Field | After implement |
| :--- | :--- |
| `available` | `it`, `en` |
| `primary` | `it` |
| `ru` | not routed, not in staff locale picker defaults |
| `chat_uat` | Russian scripts remain |

## State transitions

### Page publish (local parity)

```text
demo-landing published --unpublish or delete--> not public
diventa-socio missing --import--> published
faq missing --import--> published
```

### Secret file

```text
local-integrations.php with sk_test_/pk_test_ --> placeholders / env-only
git history unchanged
```

### Product law

```text
S-I18N it+ru+en --constitution MAJOR--> S-I18N it+en
S-STRIPE Payment Element --unchanged--> Payment Element
```
