# Data model: Mobile header drawer

No CRM schema change. Site may add `contact_submissions.last_name`.

## Translation keys

| Key | Locale | Constraint | Used on |
| :--- | :--- | :--- | :--- |
| `site.nav.donate` | it | required string, verbatim **Tutti i modi per donare** | wide-screen donate label |
| `site.nav.donate` | en | required string, verbatim **All ways to donate** | wide-screen donate label |
| `site.nav.donate_short` | it | required string, verbatim **Dona ora** | phone donate label |
| `site.nav.donate_short` | en | required string, verbatim **Donate now** | phone donate label |
| `site.nav.menu` | it/en | existing; used as accessible name of the hamburger | phone hamburger `aria-label` |
| `site.nav.close_menu` | it/en | required; accessible name of the drawer close control | visible close on the open panel |
| `site.pages.contact_lead` | it | required string, verbatim **Per contattarci compila il modulo e scegli lo sportello più adatto.** | Contatti heading after the bar |
| `site.pages.contact_lead` | en | required string, verbatim **Fill in the form and choose the most suitable desk.** | Contact heading after the bar |
| `site.pages.contact_desks_blurb` | it | required; lists Sportello digitale, Sportello legale, Richiesta generica | left column, FAQ-button block |
| `site.pages.contact_desks_blurb` | en | required; English equivalent of those three desks | left column, FAQ-button block |
| `site.pages.contact_last_name` | it | required string, verbatim **Cognome** | contact form surname label |
| `site.pages.contact_last_name` | en | required string, verbatim **Last name** | contact form surname label |

`site.membership.contact_lead` is a different key and MUST NOT change.

## Contact submission

| Field | Constraint | CRM mapping |
| :--- | :--- | :--- |
| `name` | required, max 255 | Lead `firstName` |
| `last_name` | required, max 255 | Lead `lastName` |
| `email` | required email | Lead `emailAddress` |

Existing rows without surname may store empty `last_name`; new public submits require it.

## Drawer state (client only)

| Field | Values | Notes |
| :--- | :--- | :--- |
| open | `true` / `false` | Not persisted. Default `false`. |
| aria-expanded | `"true"` / `"false"` | Mirrors open on the hamburger. |

## State transitions

- Closed → Open: hamburger click.
- Open → Closed: visible close control, hamburger, backdrop, Escape, following a drawer link.

## Validation rules

- Donate destination stays `route('donations.index')`.
- Drawer lists the same `config('navigation.header')` items as the wide-screen nav, including Altre Pagine children from `nav-item-mobile`.
- Do not edit `/home/skoksharov/safehouse/nonprofit-espocrm`.
