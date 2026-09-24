# Quickstart: 010 ads-ready public refresh

Validate after implementation. Do not treat this file as the implementation.

Prerequisites: DDEV up, local content aligned with production, at least one published news item and one published article.

## Home and menu

1. Open `/it` and `/en`. Primary button is Become a member. Donate and volunteer are beside it. Up to four story windows animate in. Both section links work.
2. Header has five text items plus Donate. No Home, no news, no 5×1000 in the header. Logo opens home.
3. Open `/it/diventa-socio`. The 5×1000 banner is absent. The form is still there.
4. Open `/it/volunteers`, `/it/news`, and `/it/about-us`. The banner is visible and opens 5×1000.

## Width and motion

1. On a wide window, the volunteer form is wider than on a phone and does not touch both screen edges.
2. A news article column is wider than the old narrow stack and is not full-bleed.
3. In the browser, set reduced motion. Reload home. Stories and sections are visible and do not animate in.

## Commands

```bash
ddev exec php artisan test --filter=NavigationMenuTest
```

Cite: [Laravel testing](https://laravel.com/docs/13.x/testing).

Owner UAT is still required. Do not push from this guide.
