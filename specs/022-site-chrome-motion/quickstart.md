# Quickstart: Site chrome, motion, and ETS naming

1. Point `.specify/feature.json` at `specs/022-site-chrome-motion`.
2. Add `drawer_title`, change `title_suffix`.
3. Drawer: Home link + ETS title. Desktop nav untouched.
4. Default `page-hero` align to center; CSS `lg:items-center`.
5. Wrap glass blocks in `.landing-reveal` except `landing.blade.php`.
6. Altre Pagine / drawer: `news_title` / `editorial_title`. Home buttons keep All.
7. Home stats: `data-count-to` + `impact-count.js` (~2s).
8. Tests: SiteLayoutTest, HomePageTest, listing/menu assertions.
9. `ddev exec -- php artisan test --filter=SiteLayoutTest`
10. `ddev exec -- php artisan test --filter=HomePageTest`
11. `ddev exec -- ./vendor/bin/pint`
12. `bash bin/dev-rebuild-frontend.sh`
13. **Commit US1–US6.**
14. Then add the light red `.site-five` tint and commit that alone.

Owner UAT after implement (Russian):

- Телефон: выезжающее меню называется Safe House ETS, сверху Home. Десктоп: Home в шапке нет.
- Вкладка: `… — Safe House ETS`.
- Заголовки `Title | tagline`: текст справа по вертикали по центру везде.
- Блоки на Chi siamo / Home / Contatti заезжают как на socio. Diventa socio не менять.
- Notizie / Articoli в меню; на Home кнопки «Tutte le…» как сейчас.
- Счётчики CRM с нуля ~2 секунды.
- Оттенок баннера — отдельный коммит, можно откатить.
