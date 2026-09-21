# Data model: 002.4

No new tables.

## Membership application (request payload)

| Field | Source | Validation |
| :--- | :--- | :--- |
| name, last_name | Form §1 | required string |
| birth_place, birth_province, birth_date | Form §1 | required; date before today |
| tax_code | Form §1 C.F. | 16 alnum, uppercased |
| city, province, address, cap | Form §1 residence | required |
| phone, email | Form §1 | required; email |
| accept_statute, accept_mission, accept_fee | Form §2–3 | accepted |
| newsletter_consent | Form newsletter | `0` or `1` |
| company | honeypot | ignored; fake success |
| cf-turnstile-response | Turnstile | required if enabled |

Board-only: esame Consiglio, esito, Libro Soci, quota versata, firma Presidente, tessera — **not stored**.

## Espo Lead (existing)

`firstName`, `lastName`, `emailAddress`, `phoneNumber`, `addressStreet/City/PostalCode/State/Country`, `taxCode`, `birthDate`, `birthPlace`, `birthProvince`, `contactType: ['MemberContact']`, `source: Web Site`, `status: New`, `description` (declarations + newsletter).

## CMS pages touched

Privacy/cookie bodies (`LegalPagesContent`). Contact body FAQ URL removed. Landing template consumes existing `diventa-socio` HTML.
