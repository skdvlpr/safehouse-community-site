# Contract: Conversion events (002)

Custom event names ([GA4 custom events](https://support.google.com/analytics/answer/12229021)):

| HTML marker | Event name | Surface |
| :--- | :--- | :--- |
| `data-measurement-event="donate_success"` | `donate_success` | One-time campaign thank-you (`allows_recurring` false) |
| `data-measurement-event="donate_recurring_success"` | `donate_recurring_success` | Recurring-campaign thank-you (`allows_recurring` true; site signup, not later invoices) |
| `data-measurement-event="volunteer_success"` | `volunteer_success` | Volunteer form after **real** mail success |
| `data-measurement-event="contact_success"` | `contact_success` | Contact form after **real** store success |

JS copies **only** the event name onto `dataLayer` when consent is `all` and measurement is bootable. One thank-you MUST set exactly one donate marker.

Cite: [GA4 custom events](https://support.google.com/analytics/answer/12229021) — extra donate context is a **second event name** here, not a `value`/amount parameter (FR-006). A `donation_kind` parameter was rejected: it needs a custom dimension to show in standard Events; two names are visible as two rows.

MUST NOT appear on the marker or in the payload: donor name, email, phone, message, amount, payment intent id.

## Honeypot

Volunteer and contact honeypot still redirect with the visitor success flash (unchanged anti-spam). They MUST NOT set `data-measurement-event`. Implement via a separate session flag (e.g. `measurement_conversion`) set only on the real success path.

## Donate thank-you

Greeting may still use `donor_name` query for the human heading. The measurement marker is a sibling/data attribute without that value.

**Page URL / Enhanced measurement:** GA4 page_view must **not** send `donor_name` (or other form fields) in `page_location`. Strip the query on that view in site JS or GTM. Re-check in DebugView — listed in [ga4-gtm-owner-setup.md](../checklists/ga4-gtm-owner-setup.md).

## Tests (propose)

- One-time thank-you with `donor_name=Mario Rossi`: assert marker `donate_success`, no `donate_recurring_success`, marker node does not contain `Mario`.
- Recurring thank-you: assert marker `donate_recurring_success` and not `donate_success`; still no donor name on the marker.
- Volunteer honeypot: `assertSessionHas('volunteer_success')` and `assertDontSee('data-measurement-event="volunteer_success"', false)`.
- Volunteer real success: marker present.
- Same pair for contact.
- Home impact stats partial still renders (CRM numbers not replaced).
