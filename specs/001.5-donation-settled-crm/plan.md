# Implementation Plan: Donation ledger only after settlement (001.5)

**Branch**: `001.5-donation-settled-crm` (spec directory; git remains current until the owner asks) | **Date**: 2026-09-18 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/001.5-donation-settled-crm/spec.md`

## Summary

Stop treating “payment succeeded” as “fee/net are already on the Charge”. Stripe’s default asynchronous capture leaves `balance_transaction` null on `payment_intent.succeeded` / `charge.succeeded`; the fee arrives on **`charge.updated`**. Today the site (1) returns **502** when settlement is missing, (2) **ignores** `charge.updated` for ingest, (3) **blocks thank-you** on up to ~10 s of settlement retries. This amendment: ingest when a usable BalanceTransaction exists (including `charge.updated`); acknowledge “not ready yet” with **200**; keep status webhooks first in the pipeline; thank-you never waits on retries.

Official docs opened this turn: [Asynchronous capture](https://docs.stripe.com/payments/payment-intents/asynchronous-capture) · [Webhooks](https://docs.stripe.com/webhooks) · [Signatures](https://docs.stripe.com/webhooks/signatures) · [Event types](https://docs.stripe.com/api/events/types) · [CLI listen](https://docs.stripe.com/cli/listen) · [Payment Element](https://docs.stripe.com/payments/payment-element) · [Laravel testing](https://laravel.com/docs/13.x/testing) · [Laravel logging](https://laravel.com/docs/13.x/logging) · [Laravel Pint](https://laravel.com/docs/13.x/pint).

## Technical Context

**Language/Version**: PHP 8.4 / Laravel 13 (locked)

**Primary Dependencies**: Existing Stripe PHP SDK, `StripeWebhookController`, `StripePaymentService`, `DonationIngestService`, `PrimaNotaPaymentStatusService`, `StripeDonationThankYouSync`. No new Composer/npm packages. Payment Element unchanged (`S-STRIPE`).

**Storage**: No new tables. Prima Nota still in sibling EspoCRM via existing API. Site MariaDB unchanged.

**Testing**: PHPUnit Feature: `charge.updated` with donation metadata + usable BT → ingest 200; `payment_intent.succeeded` without BT → **200** pending, no CRM create; unsigned → 400; refund/dispute still handled before ingest; thank-you does not call multi-second settlement retry. No coverage %. Owner UAT needs `stripe listen` (not PHPUnit).

**Target Platform**: Local DDEV `https://safehouse-community-site.ddev.site` + `stripe listen --forward-to …/api/webhooks/stripe`. Production Dashboard event-type allowlist = owner-ops.

**Project Type**: Existing Laravel web app; S01 amendment `001.5`; interrupt of `002`/`002.1`.

**Performance Goals**: Thank-you HTML after Stripe redirect without a several-second stall (SC-002). CRM row in the same sitting once Stripe has fee/net (SC-001).

**Constraints**: Do not write CRM git. Do not change live Dashboard unless the owner asks. Do not `ddev stop`. Do not `config:cache` in DDEV. Do not invent fee=0. Status event types stay 200 + existing mappings. Invalid signature stays 4xx (`001.2`). Real Espo failures still 502 (retryable).

**Scale/Scope**: Webhook ingest map + thank-you non-blocking attempt + tests + owner-ops event list. No donate UI, no analytics, no Checkout Sessions.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Gate | Status | Notes |
| :--- | :--- | :--- |
| I SDD lock-in | PASS | 001.5 only; implement from `tasks.md`; return to 002/002.1 after UAT; do not start 003 |
| II Repo SoR | PASS | Plan, research, contracts, progress in this tree |
| III Official docs | PASS | URLs opened this turn; cited above |
| IV Spec persistence | PASS | Dotted `001.5`; owner interrupt of 002.1 |
| V Meaningful tests | PASS | Settlement-pending vs ready; charge.updated ingest; status path; thank-you no long retry |
| VI Security / PII | PASS | No secrets in logs; signature fail-closed; no client_secret in CRM |
| VII Stack freeze | PASS | No new libs; Payment Element stays |
| VIII Owner UAT | PASS | Checklist at implement; Russian script; browser offer + wait |
| IX Ask on doubt | PASS | Ingest on `charge.updated` + 200-pending on succeeded (not capture_method-only) |
| X Write this repo | PASS | No CRM repo writes |
| XI Model table | N/A | Printed at `/speckit-tasks` |
| XII Errors/logs | PASS | Typed “settlement not ready” → 200; Espo ingest fail → 502; thank-you logs warning, still renders |
| S-STRIPE | PASS | Payment Element / PI checkout unchanged |
| S-I18N | PASS | No new public copy required |
| S-CSP | PASS | No CSP/HSTS in PHP |
| S-CMS-PATH | PASS | Untouched |
| S-GDPR | PASS | No DPA |
| S-CROSS-REPO | PASS | Site only |

**Post-design re-check**: PASS. Design is webhook event map + settlement-ready gate + thank-you one-shot. Status `applyFromStripeEvent` stays first. Live `charge.updated` allowlist is owner-ops.

## Project Structure

### Documentation (this feature)

```text
specs/001.5-donation-settled-crm/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── http.md
│   └── owner-ops.md
├── checklists/
│   └── requirements.md
└── tasks.md                  # /speckit-tasks (not this command)
```

### Source Code (repository root)

```text
app/Http/Controllers/StripeWebhookController.php
app/Http/Controllers/DonationCampaignController.php   # thank-you still renders; ingest non-blocking
app/Services/Donations/StripeDonationThankYouSync.php
app/Services/Donations/PrimaNotaPaymentStatusService.php  # must not mark charge.updated handled
app/Services/Payments/StripePaymentService.php
  # paymentIntentIdFromWebhookEvent + retrieveSettled attempts
app/Exceptions/…                                      # settlement-not-ready (typed)
tests/Feature/StripeWebhookDonationTest.php
tests/Feature/StripeDonationThankYouSyncTest.php
docs/STRIPE-SETUP.md                                  # event list + listen (comment only if needed)
```

## Complexity Tracking

None — no constitution violations.
