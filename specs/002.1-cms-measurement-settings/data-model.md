# Data model: CMS measurement settings (002.1)

No new tables. Reuse `site_settings` (`key`, `value`, `is_encrypted`) via `SiteSettingsService` ([encryption](https://laravel.com/docs/13.x/encryption) for other keys on the same screen).

Catalog in `config/site_settings.php`:

| Key | Encrypted | Config fallback | Notes |
| :--- | :--- | :--- | :--- |
| `measurement.enabled` | no | `measurement.enabled` | `'1'` / `'0'`; default env false |
| `measurement.container_id` | no | `measurement.container_id` | Must match `^GTM-[A-Z0-9]+$` to boot |

**Bootable** (unchanged 002, new read path): not preview, enabled truthy, valid container id.

**CMS wins**: if a `site_settings` row exists for the key, use decrypted/plain value even when empty-off. `IntegrationConfig::get` treats empty string as fall through to env — **enabled off must still be stored as `'0'`** so `has()` + `isTruthy()` work. `MeasurementBootService` should use `SiteSettingsService::isTruthy('measurement.enabled')` when `has('measurement.enabled')`, else config fallback.

**Do not store**: `G-` Measurement ID, GTM snippets, Ads keys.
