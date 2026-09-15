# Research: CMS captcha settings (001.3)

**Date**: 2026-09-15

## Dedicated Settings page vs tab vs keep Sportelli

- **Decision**: Add a Filament custom page `ManageCaptchaSettings` in navigation group Settings (`cms.nav.groups.settings`), `navigationSort` **98** (Sportelli 96, Social 97, Integrations 99). Italian nav label **Captcha**. Slug `captcha`. `canAccess`: super-admin only, same as Integrations/Sportelli ([custom pages](https://filamentphp.com/docs/4.x/navigation/custom-pages)).
- **Rationale**: Spec FR-001: same neighbourhood as Integrations, not inside help-desk desks/mail. Owner asked for a page, not a Stripe sub-tab.
- **Alternatives considered**: Tab on `ManageIntegrations` (hides captcha behind payments). Keep Sportelli tab (spec FR-006 forbids two editors). Cluster/subnav (YAGNI).

## Secret field behaviour

- **Decision**: Copy Integrations Stripe secret pattern: `TextInput::password()->revealable()`, `dehydrated` only when filled ([password / revealable](https://filamentphp.com/docs/4.x/forms/text-input)). `SiteSettingsService::formValues()` already blanks encrypted keys; `updateMany` skips empty encrypted values. Persist via `Crypt::encryptString` already in `SiteSetting::storePlaintext` ([encryption](https://laravel.com/docs/13.x/encryption)).
- **Rationale**: FR-003 / FR-004. Local keys from 2026-09-15 must survive implement and blank saves.
- **Alternatives considered**: Always show decrypted secret in the form (leaks in screenshots). Separate “clear secret” button (YAGNI).

## Sportelli must not keep writing Turnstile

- **Decision**: Remove the Captcha tab and all `turnstile.*` mount/save from `ManageSportelliConfig`. Sportelli `save` MUST NOT include `turnstile.enabled` / `site_key` / `secret_key` in `updateFromFormState` (today `nestedFormValues()` dumps the whole settings bag into Livewire state — leftover keys would still save without UI). New page calls `updateMany` **only** for the three Turnstile keys. Add a Sportelli helper line: captcha is now Impostazioni → Captcha. Update `cms.helpers.sportelli_config_link` (it currently says captcha lives on Sportelli).
- **Rationale**: FR-006 single source of truth; desks/mail/CRM case types stay.
- **Alternatives considered**: Leave hidden turnstile state on Sportelli (two writers). Redirect-only stub page (extra click, no editor).

## Public forms stay as 001.2

- **Decision**: Do not change `TurnstileVerifier`, contact/volunteer Blade, or Form Requests. Widget shows iff enabled **and** both keys non-empty ([Turnstile](https://developers.cloudflare.com/turnstile/get-started/): siteverify is already server-side). Donate, cookie, CMS login stay out.
- **Rationale**: Spec assumption; YAGNI. Incomplete keys already fail closed (`enabled()` false).
- **Alternatives considered**: Re-embed widgets (risk). Cloudflare widget create inside CMS (out of spec).

## Tests

- **Decision**: Propose **one** Feature test (PHPUnit, [Laravel testing](https://laravel.com/docs/13.x/testing); page is a Livewire component, [Filament testing](https://filamentphp.com/docs/4.x/testing/overview)): acting as super-admin, save captcha form with blank secret after a secret exists → `getRaw('turnstile.secret_key')` unchanged; `enabled()` false when toggle on but site key empty. Keep `TurnstileVerifierTest` and `VolunteerFormTest`. No HTML snapshots. No coverage %.
- **Rationale**: Constitution V — blank-secret wipe and half-config lockout are real bugs UAT can miss. Owner may drop the row at `/speckit-tasks`.
- **Alternatives considered**: Full Livewire click-through of Cloudflare widget (needs network, dummy keys, flaky). Zero tests (weaker than spec SC-003/FR-004).

## Copy and i18n

- **Decision**: Reuse existing `cms.fields.turnstile_*` / `cms.helpers.turnstile_*` on the new page. New `cms.nav.captcha` it+en. Notification `cms.notifications.captcha_saved`. CMS Italian primary.
- **Rationale**: S-I18N for staff UI already it+en. Do not invent a second set of field keys.
- **Alternatives considered**: Rename all keys to `captcha.*` (migration of `site_settings` keys — out of scope, would risk wiping local secrets).

## Out of scope (confirmed)

- Cloudflare Connect-your-domain / nameservers
- Production CMS key paste, Caddy reload
- Stripe listen / webhook secret
- Specs 002–008
