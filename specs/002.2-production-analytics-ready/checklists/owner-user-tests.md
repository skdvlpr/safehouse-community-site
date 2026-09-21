# Owner UAT — 002.2 combined (one round)

Staff dashboard: **analytics.google.com** Realtime / DebugView. Not Home.

Official: [Realtime](https://support.google.com/analytics/answer/9271392), [Preview](https://support.google.com/tagmanager/answer/6107056), [DebugView](https://support.google.com/analytics/answer/7201382).

## Edge + consent

- [ ] Live Accetta tutti: `gtm.js` 200 and a collect request. CMS still opens.
- [ ] Essentials / X: no `gtm.js`.
- [ ] Realtime shows the visit within 5 minutes **or** DebugView `page_view` while Preview is connected to `https://safehouse.community/it`. Home zeros are OK.

## Conversions (analytics already accepted)

- [ ] One-time thank-you → `donate_success`
- [ ] Recurring thank-you → `donate_recurring_success`
- [ ] Volunteer real → `volunteer_success`
- [ ] Contact generica → `contact_generic_success`
- [ ] Contact legale → `contact_slegale_success`
- [ ] Contact digitale → `contact_sdigitale_success`
- [ ] No `contact_success` event

## Out of this UAT

Membership/volunteer CRM Lead (progress 039). Legal DPA. Google Signals.
