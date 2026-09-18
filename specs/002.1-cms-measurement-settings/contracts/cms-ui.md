# Contract: CMS UI (002.1)

Screen: existing Filament page `ManageIntegrations` ([custom pages](https://filamentphp.com/docs/4.x/navigation/custom-pages), [text input](https://filamentphp.com/docs/4.x/forms/text-input)).

Access: super-admin only (`canAccess` unchanged).

New tab (it/en labels): measurement / analytics.

Fields:

- Toggle `measurement.enabled` (default off). Helper: public Tag Manager loads only after visitor analytics consent; dashboard is Google Analytics, not this CMS.
- Text `measurement.container_id` visible when enabled; placeholder `GTM-`; max 32; not password. Helper: container id only, never paste Google’s head/noscript snippet, never a `G-` id.

Save: same `updateFromFormState` as today. Stripe/CRM/mail fields unchanged. Encrypted secrets still `dehydrated` only when filled.

After save: notification already used for integrations.
