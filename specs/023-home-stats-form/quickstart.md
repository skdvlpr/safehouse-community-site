# Quickstart: 023 Home stats, side reveals, donation form

## Owner UAT (local DDEV)

1. Open `https://safehouse-community-site.ddev.site/it` — hero, then three number cards, then the manifesto quote.
2. Open `/en` — same order, English labels.
3. Open Chi siamo (`/it/about-us`) with motion allowed — intro, values, closing slide from left and right, not a short upward fade.
4. Open Donazioni listing — featured, recurring, and campaign cards slide from opposite sides.
5. Open 5 x 1000 and a one-time campaign `show` — glass/form enters from a side.
6. Desktop (~1280px) on one-time and recurring payment pages — form card is wider; fields sit in two columns and do not stretch to the full content width; Payment Element still appears after Continue.
7. Phone (~390px) on the same form — stacked narrow card as today.
8. Contatti / volunteer / news — previous reveal unchanged. Diventa socio unchanged.

## Verify

```sh
ddev exec php artisan test
ddev exec ./vendor/bin/pint --test
```

Frontend stale CSS: `bash bin/dev-rebuild-frontend.sh`
