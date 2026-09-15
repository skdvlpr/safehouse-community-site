# Contract: mandatory area coverage

FR-001 / FR-003. Implementer ticks every row in `findings.md` (finding ids or none-found).

| Area id | Must check | Typical evidence |
| :--- | :--- | :--- |
| `security-payments` | Card data not on this app; Payment Element still used; webhook signature fail-closed (4xx); CSRF exclusion of Stripe webhook is the minimum needed, not a wide hole; amount/currency handling | `StripePaymentService`, `StripeWebhookController`, `tests/Feature/StripeWebhookDonationTest.php`, https://docs.stripe.com/webhooks/signatures |
| `personal-forms` | Volunteer, contact/sportello: validation via Form Requests; throttle; hashed IP/UA if stored; no message bodies in cookie audit | `VolunteerFormTest`, `ContactFormTest`, Gdpr/Volunteer models |
| `cookie-consent` | Banner exists; analytics scripts not loaded today; consent POST throttled; stored choice essential\|all | `CookieConsentTest`, `resources/js/cookie-consent.js` |
| `staff-cms` | Path `/cms-safehouse` not `/admin`; auth required; Spatie roles | `AdminPanelProvider`, `FilamentPanelTest`, `RbacTest`, https://filamentphp.com/docs/4.x/panel-configuration |
| `public-journeys` | home, donate, 5×1000, volunteer, contact, about, news × locales it/en/ru; demo pages not presented as official | routes, seeders vs live, browser if owner agrees |
| `i18n` | No new hardcoded visitor strings; missing locale content listed | `lang/*`, Blade `__()`, page translations |
| `architecture` | Thin controllers; Form Requests; `$fillable`; empty-catch; no `new` in controllers | constitution VII/XII, https://laravel.com/docs/13.x/validation |
| `secrets-repo` | `.env` not committed; no keys in specs/progress | git status / grep without printing values |
| `crm-integration` | Site→CRM mapping vs sibling repo; silent failures | read `nonprofit-espocrm`; ingest services |

Deferred (record as finding bucketed to later spec, **or** a single combined note per spec — not `001.K`):

| Gap | Bucket |
| :--- | :--- |
| No measurement product | `002` |
| No SEO title/description/index | `003` |
| Membership landing / sitelink pack | `004` |
| Campaign banners | `005` |
| Full operational privacy rewrite | `006` |
