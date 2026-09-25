# Quickstart: Donation form width and one-time heading

1. Point `.specify/feature.json` at `specs/019.1-donation-form-heading`.
2. Add `campaign_tagline` in `lang/it/site.php` and `lang/en/site.php`.
3. Cap `#donation-form` at `max-w-2xl mx-auto`.
4. One-time `show` heading: campaign title | `campaign_tagline`. Recurring unchanged.
5. Pass `align => 'center'` on donation listing, 5 x 1000, and `show` page-headers.
6. Update `DonationShowRouteTest` and listing/5 x 1000 heading tests.
7. `ddev exec -- php artisan test --filter=DonationShowRouteTest`
8. `ddev exec -- php artisan test --filter=DonationFivePerMilleTest`
9. `ddev exec -- ./vendor/bin/pint`
10. `bash bin/dev-rebuild-frontend.sh` if CSS changed.

Owner UAT (Russian, after implement):

- Широкое окно: платёжная карточка узкая по центру, заголовок на всю колонку.
- Обычный сбор: **имя сбора | Sostieni Safe House con un dono.**
- Рекуррентный: заголовок как уже приняли.
- Текст справа от `|` по вертикали по центру на listing / 5x1000 / show.
