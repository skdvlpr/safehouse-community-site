# Contract: Light-theme chrome, 5×1000 mark, slower Home count

Cite: [border-color](https://tailwindcss.com/docs/border-color), [border-radius](https://tailwindcss.com/docs/border-radius), [requestAnimationFrame](https://developer.mozilla.org/en-US/docs/Web/API/Window/requestAnimationFrame), [prefers-reduced-motion](https://developer.mozilla.org/en-US/docs/Web/CSS/@media/prefers-reduced-motion), [Blade](https://laravel.com/docs/13.x/blade), [Laravel testing](https://laravel.com/docs/13.x/testing).

## Chi siamo

`GET /it/about-us` HTML contains `template-about-values` and `template-about-closing`. Closing markup still uses class `template-about-closing`. CSS for closing keeps `border-safehouse-primary`. Light-theme rules MUST NOT set those two blocks to `--safehouse-glass-border`.

## 5×1000 strip

Public pages that show the strip (`GET /it`) include `<span class="site-five__mark"` with text `5×`. CSS for `.site-five__mark` MUST NOT use `rounded-full` as the rest geometry.

## News Categorie

`GET /it/news` and `GET /it/articles` include `news-cat-menu__summary`. Light CSS for that summary MUST share fill and border with `.news-date-filters__field input`.

## Home count

`resources/js/impact-count.js` exports `initImpactCount` and uses `COUNT_DURATION_MS` of 3500. Reduced-motion still returns before `requestAnimationFrame`. `GET /it` still has `data-count-to` when CRM totals exist.

Diventa socio and Payment Element are out of contract.
