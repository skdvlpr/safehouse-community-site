# CRM agent brief — Lead Associato from the public site

Do **not** edit `nonprofit-espocrm` from the site repo. This is work for a CRM-side session.

Official vendor: [Espo REST API](https://docs.espocrm.com/development/api/). Lead convert layouts already exist under `layouts/Contact/detailConvert.json`.

## What the site already writes

After staff+applicant mail succeeds, the public site `POST /api/v1/Lead` with:

| Espo field | Site form |
| :--- | :--- |
| `firstName` / `lastName` | Nome / Cognome |
| `emailAddress` | E-mail |
| `phoneNumber` | Telefono |
| `addressStreet` `addressCity` `addressPostalCode` `addressState` | Indirizzo, città, CAP, prov. |
| `addressCountry` | `Italy` |
| `taxCode` | Codice fiscale (16, uppercase) |
| `birthDate` | `YYYY-MM-DD` |
| `birthPlace` / `birthProvince` | Nato/a a / Prov. |
| `contactType` | `["MemberContact"]` (label IT: Associato) |
| `source` | `Web Site` |
| `status` | `New` |
| `description` | Statute/mission/quota accepted + newsletter yes/no |

Mail failure **blocks** the Lead. Lead failure is logged and does **not** fail the visitor.

## Why staff may not see tax/birth fields

In `entityDefs/Lead.json` those fields have `"layoutAvailabilityList": []`, so they are hidden from default layouts even when the API stored them. The CRM agent should add them to Lead detail/list for Associato, matching Contact `detailConvert.json`.

## Convert Lead → Contact Associato

Convert must copy `contactType` `MemberContact` plus tax/birth/address onto Contact. Contact formula already sets `joinDate` when `MemberContact` is new.

Do **not** put Consiglio Direttivo / Libro Soci / tessera / firma Presidente on the website. Those remain CRM/PDF after approval.

## PDF / Libro Soci

Out of this site hotfix. Keep existing association PDF templates in CRM. Map converted Contact Associato into whatever print layout the board uses.

## Volunteer

Do **not** create Lead Volontario in this pass. Site volunteer form stays mail-only until a later spec.
