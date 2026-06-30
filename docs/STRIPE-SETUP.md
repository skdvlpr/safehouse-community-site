# Stripe setup — Safe House donations

Standard **Stripe account** (not Connect): card payments go to the association’s Stripe balance, then **payout to the linked bank account** (Settings → Payouts in Dashboard).

No code changes needed beyond `.env` keys and a webhook endpoint.

---

## Local mock (no Stripe keys)

When `APP_ENV=local` and `STRIPE_SECRET` is empty, the site runs in **Stripe mock mode** automatically:

- Donation form shows a yellow banner and **Simula pagamento riuscito** instead of Stripe Payment Element.
- `POST /api/donations/intents/{slug}` returns a fake `pi_mock_…` intent.
- `POST /api/donations/mock/{paymentIntent}/complete` runs the same EspoCRM ingest as the production webhook.

Override with `STRIPE_MOCK=true|false` in `.env`. Verify mock mode: `php artisan stripe:verify`.

---

1. Log in: [https://dashboard.stripe.com](https://dashboard.stripe.com)
2. **Activate the account** (if not done):
   - Settings → Business details
   - Settings → Payouts → **add Italian bank account (IBAN)**
3. Confirm **Charges** and **Payouts** show as enabled (Dashboard home).

For Italian non-profits, business type is often **Non-profit**; Stripe may ask for fiscal code / association documents.

---

## 2. API keys → site `.env`

Developers → [API keys](https://dashboard.stripe.com/apikeys)

| Phase | `STRIPE_KEY` | `STRIPE_SECRET` |
|-------|----------------|-----------------|
| **Test first** | `pk_test_…` | `sk_test_…` |
| **Production** | `pk_live_…` | `sk_live_…` |

```env
STRIPE_KEY=pk_test_xxxxxxxx
STRIPE_SECRET=sk_test_xxxxxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxx
STRIPE_DEFAULT_CURRENCY=EUR
STRIPE_STATEMENT_DESCRIPTOR=SAFE HOUSE
STRIPE_WEBHOOK_URL=https://safehouse.community/api/webhooks/stripe
```

- **Never commit** live secret keys.
- Test and live keys must **both** be test or both live (never mix).

---

## 3. Verify from the site

```bash
ddev exec php artisan stripe:verify
```

Checks: key format, API connection, `charges_enabled`, `payouts_enabled`, currency EUR, statement descriptor, webhook config.

---

## 4. Webhook (required for CRM ledger)

Without `payment_intent.succeeded` webhook, payment succeeds in Stripe but **Prima Nota is not created** in EspoCRM.

### Local (DDEV)

Install [Stripe CLI](https://stripe.com/docs/stripe-cli):

```bash
stripe login
stripe listen --forward-to https://safehouse-community-site.ddev.site/api/webhooks/stripe
```

Copy the printed `whsec_…` into `STRIPE_WEBHOOK_SECRET`.

### Production

Developers → [Webhooks](https://dashboard.stripe.com/webhooks) → Add endpoint:

| Field | Value |
|-------|--------|
| URL | `https://safehouse.community/api/webhooks/stripe` |
| Events | `payment_intent.succeeded` |

Copy **Signing secret** → `STRIPE_WEBHOOK_SECRET` on the server.

---

## 5. End-to-end test (test mode)

1. `php artisan stripe:verify` → all pass  
2. CMS → create active **Donation Campaign** (`espocrm_finanziamento_name` = existing Opportunity in CRM)  
3. Open `/it/donazioni/{slug}`  
4. Card: `4242 4242 4242 4242`, any future expiry, any CVC  
5. Stripe Dashboard → Payments → succeeded  
6. EspoCRM → **Prima nota** with `Donazione Stripe ordine #pi_…`

---

## 6. Go live

1. Complete Stripe activation + bank account  
2. Switch `.env` to **live** keys (`pk_live_`, `sk_live_`)  
3. Create **live** webhook endpoint + new `whsec_…`  
4. Run `php artisan stripe:verify` on production  
5. One real small donation (e.g. €1) and confirm payout schedule in Dashboard  

---

## Money flow (reference)

```
Donor card → Stripe Payment Element → PaymentIntent
       → Stripe balance (association account)
       → Payout to association IBAN (Stripe schedule, usually T+2–7)
       → Webhook → site → EspoCRM Prima Nota (accounting only)
```

The site does **not** store card data (PCI SAQ A).
