# Quickstart: 001.5 donation settled CRM

Validate after implement. Cite: [CLI listen](https://docs.stripe.com/cli/listen) · [Laravel testing](https://laravel.com/docs/13.x/testing) · [Pint](https://laravel.com/docs/13.x/pint).

## Automated

```bash
ddev exec php artisan test --filter=StripeWebhookDonationTest
ddev exec php artisan test --filter=StripeDonationThankYouSyncTest
ddev exec ./vendor/bin/pint --test
```

Expect: PI.succeeded without BT → 200 pending; `charge.updated` with donation metadata + BT → CRM fake ingest; bad signature → 400; refund/dispute fixtures still 200 handled; thank-you test does not depend on a long retry.

## Owner sandbox (listen on)

1. Confirm CLI is forwarding to `https://safehouse-community-site.ddev.site/api/webhooks/stripe`.
2. Donate on a one-time campaign (sandbox card). Thank-you heading appears **without** a multi-second wait.
3. In listen: `payment_intent.succeeded` may be 200 pending; a later `charge.updated` should be 200 OK (not Ignored) once fee exists.
4. CRM Prima Nota: new row **without** Import; fee matches Stripe (not 0 when Stripe shows a fee).
5. Optional: Dashboard/CLI resend `charge.refunded` or a dispute event on that charge → status moves; no second income row.

Fail if: thank-you hangs; listen 502 on missing BT; `charge.updated` still Ignored and no row; Import is the only way to see the donation.

## Production

Do not treat preview UAT as live. Owner enables `charge.updated` on the live endpoint when deploying ([owner-ops](./contracts/owner-ops.md)).
