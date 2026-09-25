# Tasks: News/articles filter bar on a phone

**Input**: `/specs/017.1-news-filter-mobile/`

**Tests**: Feature assertion for toolbar classes. Overflow is owner UAT.

- [x] T001 Confirm `.specify/feature.json` points at `specs/017.1-news-filter-mobile`
- [x] T002 Extend `tests/Feature/NewsHeadingTest.php` so `/it/news` and `/it/articles` see `news-toolbar__group--dates` and `news-date-filters`
- [x] T003 In `resources/css/app.css` stack `.news-toolbar__group` on small screens; give `.news-date-filters` a wrapping grid with `min-w-0` date inputs and full-width **Applica**; keep `md:` as the current row. Cite [min-width](https://tailwindcss.com/docs/min-width) and [grid-template-columns](https://tailwindcss.com/docs/grid-template-columns)
- [x] T004 Add a phone stack class on the toolbar in `resources/views/pages/articles/partials/listing-toolbar.blade.php` only if CSS needs a hook
- [x] T005 Pint, `NewsHeadingTest`, `bash bin/dev-rebuild-frontend.sh`. Stop for owner UAT. Do not edit Diventa socio
