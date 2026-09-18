# Research: CMS measurement settings (002.1)

**Date**: 2026-09-17

## 1. Where to edit

**Decision**: Add a tab on existing **Impostazioni → Integrazioni** (`ManageIntegrations`), not a new Settings page.

**Rationale**: Owner asked one UI entry for integrations. Stripe, CRM, and mail already live there. Captcha already has its own page (001.3) because it was wrongly on Sportelli; measurement is an integration credential like Stripe.

**Alternatives rejected**: Dedicated page (extra nav item); env-only (rejected by owner this turn).

**Cite**: [Filament custom pages](https://filamentphp.com/docs/4.x/navigation/custom-pages)

## 2. Encryption

**Decision**: `measurement.enabled` and `measurement.container_id` are **not** encrypted. Existing Stripe/CRM/SMTP secrets on the same page stay encrypted via `Crypt::encryptString` ([encryption](https://laravel.com/docs/13.x/encryption)).

**Rationale**: After analytics consent the container id is in the browser (`gtm.js?id=GTM-…`), same class as Stripe publishable key. Encrypting it would blank the field after save (`SiteSettingsService::formValues`) so staff could not re-read `GTM-…`. Owner asked encryption **if sensitive**.

**Alternatives rejected**: Encrypt container id and leave the field blank (poor ops); add a G- Measurement ID field (002 already keeps that inside GTM).

## 3. Read path

**Decision**: `MeasurementBootService` uses `IntegrationConfig` (CMS non-empty row wins, else `config/measurement.php` / env). Invalid `GTM-` ⇒ not bootable, no exception. Preview/CMS still never bootable.

**Cite**: [configuration](https://laravel.com/docs/13.x/configuration), [GTM web](https://developers.google.com/tag-platform/tag-manager/web)

## 4. Tests

**Decision**: Feature test Livewire save on `ManageIntegrations` for measurement keys; CMS off overrides env on; invalid id off. Keep env-fallback tests in `MeasurementGateTest`.

**Cite**: [Laravel testing](https://laravel.com/docs/13.x/testing), [Filament testing](https://filamentphp.com/docs/4.x/testing/overview)
