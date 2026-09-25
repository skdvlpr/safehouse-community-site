# Quickstart: Donations heading family

Open these in Italian on a wide window and on a phone. English equivalents on `/en/…`.

1. `/it/donations` — heading **Donazioni | Sostieni Safe House. 5 x 1000 o pagamenti digitali.** No red label. 5 x 1000 and bank transfer on one row when wide.
2. `/it/donations/5-per-thousand` — heading outside the glass. Card still has tax code.
3. A one-time campaign (example Dona a Safe House) — **Donazione |** plus that name, outside the form. Form still pays.
4. Recurring campaign — **Donazione ricorrente | Sostieni Safe House ogni mese con un contributo ricorrente.** Body does not repeat the portal-interrupt sentence. Red cancel panel remains.
5. Diventa socio — unchanged.

```bash
ddev exec php artisan test --filter=DonationFivePerMilleTest
ddev exec php artisan test --filter=DonationShowRouteTest
```

Wide-row layout and “heading sits outside the card” are owner UAT. Do not run `php artisan config:cache` in DDEV.
