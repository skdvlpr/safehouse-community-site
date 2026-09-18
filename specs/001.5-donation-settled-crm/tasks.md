# Tasks: Donation ledger only after settlement (001.5)

**Input**: Design documents from `/specs/001.5-donation-settled-crm/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/, quickstart.md

**Tests**: Only real-logic rows (pending vs ingest HTTP, `charge.updated` booking, junk charge skip, thank-you no retry loop, status path not swallowed). No coverage %. Owner UAT needs `stripe listen` ([quickstart.md](./quickstart.md)).

**Organization**: Tasks grouped by user story. Each item: `test: yes/no`; complexity `C1`–`C10`; proposed model.

**Models (this feature, owner-assigned)**: Complex rows **Fable 5.1**. All other rows **Grok extra high**. This file is **not** a launch order. Fable still needs **Launch vs Replace** in chat before spawn. Suggested slugs: Fable 5.1 → `claude-fable-5-1-thinking-high`; Grok extra high → `cursor-grok-4.6-xhigh`.

Official pages: [asynchronous capture](https://docs.stripe.com/payments/payment-intents/asynchronous-capture) · [webhooks](https://docs.stripe.com/webhooks) · [signatures](https://docs.stripe.com/webhooks/signatures) · [event types](https://docs.stripe.com/api/events/types) · [CLI listen](https://docs.stripe.com/cli/listen) · [Laravel testing](https://laravel.com/docs/13.x/testing) · [Laravel logging](https://laravel.com/docs/13.x/logging) · [Laravel Pint](https://laravel.com/docs/13.x/pint)

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Parallelizable (different files, no incomplete dependency)
- **[Story]**: US1–US4 from spec.md
- Exact file paths required

---

## Phase 1: Setup

**Purpose**: Rails; no new packages

- [x] T001 Confirm `.specify/feature.json` is `specs/001.5-donation-settled-crm`. Do not add Composer/npm packages. Do not force-ingest TEST 5. Do not PATCH live Stripe Dashboard (test: no; C1; model: Grok extra high — mechanical)

---

## Phase 2: Foundational (typed settlement gate)

**Purpose**: Callers can distinguish “fee not published yet” from CRM failure. **BLOCKS** US1–US2.

- [x] T002 Add `app/Exceptions/StripeSettlementNotReadyException.php` extending `RuntimeException` (mirror `app/Exceptions/UnsupportedCurrencyException.php`). Message may include PaymentIntent id only — never secrets ([logging](https://laravel.com/docs/13.x/logging)) (test: no; C3; model: Grok extra high — typed exception, constitution XII)
- [x] T003 In `app/Services/Payments/StripePaymentService.php` make `retrieveSettledPaymentIntent` throw `StripeSettlementNotReadyException` when BalanceTransaction/`fee` is missing after attempts (not a generic `RuntimeException`). Add an optional `$maxAttempts` argument; webhook ingest MUST pass `1` (no 20×500ms sleep). Keep `hasUsableBalanceTransaction` as the “usable BT” predicate (`fee` present). Do not invent fee=0 ([asynchronous capture](https://docs.stripe.com/payments/payment-intents/asynchronous-capture)) (test: yes covered by T004; C6; model: Fable 5.1 — settlement SoT; easy to re-break ingest)

**Checkpoint**: Settlement-not-ready is typed; retrieve can run with 1 attempt

---

## Phase 3: User Story 1 — Complete ledger row without Import (P1) MVP

**Goal**: Ingest when fee/net exist (`charge.updated` included). If BT missing → 200 pending, no Prima Nota. Skip unrelated charges (no `campaign_id` / subscription id). Espo failures stay 502.

**Independent Test**: PHPUnit: PI.succeeded without BT → 200 `Pending settlement`, no CRM POST; `charge.updated` with donation metadata + usable BT → ingest 200; unsigned still 400. Owner: listen + sandbox donate → Prima Nota without Import.

**Invariants** ([data-model.md](./data-model.md)): no create when BT missing; no create when PI is not a site donation; one PI → at most one income row; status types run first.

### Tests for User Story 1

- [x] T004 [US1] Extend `tests/Feature/StripeWebhookDonationTest.php` ([Laravel testing](https://laravel.com/docs/13.x/testing)): (1) `payment_intent.succeeded` where `retrieveSettledPaymentIntent` throws `StripeSettlementNotReadyException` → **200**, body `Pending settlement`, `Http::assertNothingSent` to CRM; (2) `charge.updated` with `payment_intent` + campaign metadata + usable BT → 200 `OK` and PrimaNota create as today’s succeeded ingest; (3) `charge.updated` without `campaign_id` and without subscription metadata → 200 Ignored/skip, no CRM create; (4) keep `test_webhook_returns_502_when_crm_ingest_fails` as 502. Tests MUST fail before T005/T006 (test: yes — pending vs ingest vs junk is the bug; C8; model: Fable 5.1 — contract matrix, easy to mock the wrong Stripe method)

### Implementation for User Story 1

- [x] T005 [US1] In `app/Services/Payments/StripePaymentService.php` `paymentIntentIdFromWebhookEvent`: besides `payment_intent.succeeded` and `invoice.paid`, resolve `charge.updated` from `data.object.payment_intent` (string id or expanded object). Do **not** map `charge.succeeded` ([event types](https://docs.stripe.com/api/events/types), [contracts/http.md](./contracts/http.md)) (test: yes covered by T004; C5; model: Grok extra high — event field extract)
- [x] T006 [US1] In `app/Http/Controllers/StripeWebhookController.php` keep `applyFromStripeEvent` first. After PI id: `retrieveSettledPaymentIntent($id, 1)`; catch `StripeSettlementNotReadyException` → **200** `Pending settlement` + `Log::info` with PI id, no secrets. After settled retrieve, skip ingest (200 Ignored) unless metadata has `campaign_id` or subscription id (FR-009). Keep `UnsupportedCurrencyException` 200 skip; other `RuntimeException` **502** `CRM ingest failed` ([webhooks](https://docs.stripe.com/webhooks), [signatures](https://docs.stripe.com/webhooks/signatures), [contracts/http.md](./contracts/http.md)) (test: yes covered by T004; C7; model: Fable 5.1 — HTTP status fork is the listen 502 bug)

**Checkpoint**: Pending is 200; `charge.updated` books; junk charges skip; CRM 502 unchanged

---

## Phase 4: User Story 2 — Thank-you does not wait (P1)

**Goal**: Thank-you HTML without settlement retry loop. Optional one-shot ingest only if BT already usable.

**Independent Test**: Thank-you GET with unsettled PI still 200 HTML; `retrieveSettledPaymentIntent` is not called (or is never looped). Heading renders immediately.

### Tests for User Story 2

- [x] T007 [US2] Extend `tests/Feature/StripeDonationThankYouSyncTest.php` (and HTTP thank-you in the same file or `DonationCampaign` feature test if one exists): (1) when record retrieve has no usable BT, thank-you still 200 and **no** CRM create; (2) mock MUST NOT expect `retrieveSettledPaymentIntent` / `retrievePaymentIntent` (alias of settled) on the thank-you path — use `retrievePaymentIntentRecord` (already used in `DonationCampaignController` for portal). Update existing `test_thank_you_page_syncs_succeeded_payment_intent_to_crm` if it still stubs `retrievePaymentIntent` (test: yes — stall is retrieveSettled 20×500ms; C6; model: Fable 5.1 — easy to keep calling the settled alias)

### Implementation for User Story 2

- [x] T008 [US2] In `app/Services/Donations/StripeDonationThankYouSync.php`: one-shot `retrievePaymentIntentRecord`; ingest only if usable BT + site donation metadata; otherwise `Log::info`/`warning` and return. Catch `StripeSettlementNotReadyException` if any settled helper is still used. Do not `usleep`. Keep mock-mode early return. `DonationCampaignController::thankYou` stays render-first after the non-blocking call ([logging](https://laravel.com/docs/13.x/logging)) (test: yes covered by T007; C5; model: Grok extra high — wiring after T003/T007)

**Checkpoint**: Thank-you no longer waits on fee

---

## Phase 5: User Story 3 — Status notices still move rows (P1)

**Goal**: `applyFromStripeEvent` stays first; `charge.updated` MUST NOT be `handled` there. Refund/dispute/payout/cancel/fail mappings unchanged.

**Independent Test**: Existing refund/dispute/fail/subscription-deleted tests still 200 + PUT status. New: `charge.updated` is not status-handled.

- [x] T009 [US3] In `tests/Feature/StripeWebhookDonationTest.php` add: `charge.updated` does not take the status-only OK path without ingest (handled=false). Keep `test_webhook_marks_prima_nota_refunded_on_charge_refunded`, dispute, invoice.payment_failed, subscription.deleted. Optional: one test that refund still PUTs after a prior ingest mock (test: yes — FR-006 swallow risk; C5; model: Grok extra high — regression on existing mappings)
- [x] T010 [US3] In `app/Services/Donations/PrimaNotaPaymentStatusService.php` confirm `charge.updated` is **not** in the handled switch (default unhandled). Do not remap status enums. Do not write CRM git (test: yes covered by T009; C2; model: Grok extra high — guard, likely no code)

**Checkpoint**: Status types still update; settlement ingest does not steal them

---

## Phase 6: User Story 4 — Listen required; live catalog owner-ops (P2)

**Goal**: Docs list `charge.updated`. Agent does not change live `we_…`. Unsigned stays 400.

**Independent Test**: `docs/STRIPE-SETUP.md` + [contracts/owner-ops.md](./contracts/owner-ops.md) list ingest+status types. PHPUnit unsigned still 400.

- [x] T011 [P] [US4] In `docs/STRIPE-SETUP.md` add `charge.updated` to the production Events list and money-flow note (fee arrives on charge.updated; PI.succeeded may be pending). Cite [asynchronous capture](https://docs.stripe.com/payments/payment-intents/asynchronous-capture) and [CLI listen](https://docs.stripe.com/cli/listen). Do not edit live Dashboard (test: no; C2; model: Grok extra high — docs)
- [x] T012 [US4] Confirm `tests/Feature/StripeWebhookDonationTest.php` unsigned/invalid signature cases remain **400** not 5xx ([signatures](https://docs.stripe.com/webhooks/signatures), `001.2`). No new listen in PHPUnit (test: yes already exists — do not regress; C2; model: Grok extra high — fail-closed hygiene)

**Checkpoint**: Owner-ops catalog ready; signatures still 4xx

---

## Phase 7: Polish

- [x] T013 `ddev exec ./vendor/bin/pint` and `ddev exec php artisan test` ([Pint](https://laravel.com/docs/13.x/pint), [testing](https://laravel.com/docs/13.x/testing)). No `config:cache`. No frontend rebuild unless JS/CSS changed (they should not) (test: no; C2; model: Grok extra high — verify)
- [x] T014 Write `specs/001.5-donation-settled-crm/checklists/owner-user-tests.md` from [quickstart.md](./quickstart.md): listen on, thank-you fast, Prima Nota without Import, fee not invented, refund/dispute status, live `charge.updated` checklist. Russian numbered script in chat. Cursor-browser: offer, wait. Do not git commit/push. Do not start `003`. After UAT return to `002`/`002.1` (test: no; C3; model: Grok extra high — Principle VIII)

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (T001)**: start immediately
- **Foundational (T002–T003)**: blocks US1–US2
- **US1 (T004→T005→T006)**: MVP
- **US2 (T007→T008)**: after T003; ideally after US1 so ingest helper exists
- **US3 (T009–T010)**: after US1 webhook pipeline (same controller)
- **US4 (T011 [P] with US3 docs; T012 after US1 tests)**
- **Polish (T013–T014)**: after stories

### User Story Dependencies

- **US1**: after T003
- **US2**: after T003; uses US1 metadata skip rules
- **US3**: after T006 so `charge.updated` is ingest, not Ignored-by-default in a way that hides status bugs
- **US4**: docs parallel; signature tests after T004

### Parallel Opportunities

```text
T011 docs || T009 tests (different files) after US1
T002 exception file || T001 feature.json
```

Same-file: T003 + T005 (`StripePaymentService.php`) sequential. T004 + T009 (`StripeWebhookDonationTest.php`) sequential.

---

## Parallel Example: User Story 1

```bash
# After T003:
# T004 tests first (must fail)
# Then T005 then T006 (service then controller)
```

---

## Implementation Strategy

### MVP First (US1)

1. T001–T003
2. T004 fail → T005–T006
3. PHPUnit pending + `charge.updated` ingest
4. Then US2 thank-you, US3 status, US4 docs, polish

### Incremental Delivery

Do not ship US1 without US2 in the same implement (owner stall is P1). US4 docs can follow in the same implement.

---

## Notes

- `[P]` = different files, no incomplete dependency
- Do not write `/home/skoksharov/safehouse/nonprofit-espocrm`
- Do not `ddev stop` / live Caddy / git push
- `tasks.md` is not a Fable launch order
