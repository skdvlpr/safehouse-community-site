# Contract: Owner Tag Manager edits (before combined UAT)

Cite: [GTM web](https://developers.google.com/tag-platform/tag-manager/web), [Publish](https://support.google.com/tagmanager/answer/6107163), [Custom events](https://support.google.com/analytics/answer/12229021), [GTM consent](https://support.google.com/tagmanager/answer/10718549).

The site will `dataLayer.push({ event: '<name>' })` with **exactly** these strings. Tag Manager must listen for the same strings. Do this in the **same** web container already used for donate/volunteer. Do **not** paste snippets into Laravel. Do **not** put `GTM-` / `G-` in git.

## Retire the old contact tag

1. Workspace → Tags → the GA4 Event tag whose trigger is Custom Event `contact_success`.
2. **Pause** or **Delete** that tag.
3. Delete or pause the Custom Event trigger named for `contact_success` so it cannot fire.

If you leave it live, GA4 will still get a generic contact row and desks stay mixed.

## Add three tags (copy the working donate Event tag)

For **each** name below, clone the existing GA4 Event tag that fires `donate_success` (same GA4 Measurement ID **constant**, same Consent Settings: **No additional consent required**).

| Tag name (suggestion) | Event Name field | Custom Event trigger Event name |
| :--- | :--- | :--- |
| GA4 - contact_generic_success | `contact_generic_success` | `contact_generic_success` |
| GA4 - contact_slegale_success | `contact_slegale_success` | `contact_slegale_success` |
| GA4 - contact_sdigitale_success | `contact_sdigitale_success` | `contact_sdigitale_success` |

Per tag:

1. **Trigger** → New → Custom Event → Event name = the string **exactly** (case-sensitive, no spaces). This trigger fires on Custom Events matching that name.
2. Tag type **Google Analytics: GA4 Event**.
3. Measurement ID = existing Constant (same `G-` as donate).
4. Event Name = the **same** string (not `contact_success`, not a parameter).
5. No event parameters (no desk label, no email).
6. Advanced → Consent Settings → **No additional consent required** (site already withholds GTM until Accetta tutti).
7. Save in the same folder as the other conversion tags.

## Publish

Submit → Publish and Create Version. Name example: `002.2 three contact desks`. Preview alone is not enough for other browsers. [Publish](https://support.google.com/tagmanager/answer/6107163)

Optional later: GA4 Admin → Events → mark the three names as key events (same as donate). Not required for Realtime debug.

## Do not

- Create a fourth `contact_success` trigger “just in case”.
- Import a CMP gallery banner.
- Turn on Google Signals.
- Add Google Ads tags.
