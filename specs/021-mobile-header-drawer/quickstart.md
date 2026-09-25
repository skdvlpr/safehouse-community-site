# Quickstart: Mobile header drawer

Prerequisites: DDEV up at `https://safehouse-community-site.ddev.site`. After CSS or JS edits run `bash bin/dev-rebuild-frontend.sh`.

## Feature tests

```bash
ddev exec php artisan test --filter=SiteLayoutTest
ddev exec php artisan test --filter=HomePageTest
ddev exec php artisan test --filter=ContactHeadingTest
ddev exec php artisan test --filter=ContactFormTest
ddev exec php artisan test --filter=ContactFormMailTest
ddev exec ./vendor/bin/pint --dirty
```

Expect Italian home HTML to include the hamburger (`site-header__menu-trigger` or the drawer button class from implementation), both donate strings, and the short Contatti heading. Expect `/en` donate short **Donate now**.

## Owner UAT (phone and wide)

1. Phone home: hamburger visible; donate reads **Dona ora**; no sideways scroll.
2. Open drawer: slides from the left; transparent with strong blur; Chi siamo, Servizi, Notizie, Donazioni, Contatti, Altre Pagine children are tappable.
3. Close via the visible close control, hamburger, tap outside, Escape, and a link.
4. Wide home: row menu still there; donate still **Tutti i modi per donare**; no drawer.
5. 5 x 1000 strip centered under the menu on wide, centered on phone; copy C.F. still works.
6. Contatti: line after `|` is the short sentence; sportelli descriptions sit with Domande frequenti; left column matches form height on wide; form is tighter; Cognome is required.
7. Diventa socio unchanged.
8. English phone donate **Donate now**; English Contact heading matches the spec.

Stop. Do not start `019-donations-row` in the same implement run.
