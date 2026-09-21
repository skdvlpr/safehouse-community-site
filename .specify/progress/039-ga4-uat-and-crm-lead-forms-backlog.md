# 039 — GA4 hit UAT script + CRM lead forms backlog

**Date**: 2026-09-21

**HEAD at writing**: `619558e` `main` (settlement CRM ingest + analytics code, measurement **off** on prod).

Owner: local donations succeed; GA4 Home / Realtime / Landing page still empty. Want a minimal hit checklist (local then prod). After that, new membership form on `diventa-socio` + volunteer form → CRM Lead. Record only — **no specify/implement this turn**.

Official this note: [GTM Preview](https://support.google.com/tagmanager/answer/6107056), [Realtime](https://support.google.com/analytics/answer/9271392), [DebugView](https://support.google.com/analytics/answer/7201382), [GTM web](https://developers.google.com/tag-platform/tag-manager/web), [custom events](https://support.google.com/analytics/answer/12229021).

## Analytics

Empty **Home** and **Landing page (28 days)** are processed reports, not UAT. Checklist: [`specs/002-consent-gated-analytics/checklists/ga4-hit-verification.md`](../../specs/002-consent-gated-analytics/checklists/ga4-hit-verification.md).

Donate → CRM does **not** send GA4. Hits need CMS Analytics **on**, Accetta tutti, published GTM, Preview Connect to **DDEV** locally.

Do not enable production measurement until local section B passes. Do not apply Caddy until the owner asks.

Google signals UI prompt: keep **off**.

## Next product (after 002 UAT) — owner reorder note

Not `003` SEO and not `004` ads-hardening. Owner wants a **new site spec** (name TBD at `/speckit-specify`):

1. **Diventa socio** (`/it/diventa-socio`, existing page key): attach owner-supplied form. Submit → (a) email To/templates from **CMS UI** (same class as volunteer mail), (b) Espo **Lead** type **Associato** (owner will create / confirm in CRM). CRM should fill a **PDF** from the Lead for print or onward send.
2. **Diventa volontario** (existing `/it/volunteers`): keep current emails; **also** create Espo Lead type **Volontario** / Volunteer.

**Owner must do in CRM before/during that spec (remind at specify):**

- Confirm Lead `contactType` options. Sibling CRM already has `Volunteer`, `MemberContact`, `Other` (`nonprofit-espocrm` 004.3). Map Associato → `MemberContact` (or add a new option — **that is a CRM repo change**: stop, list files, wait — `S-CROSS-REPO`).
- Create the **PDF template** (Espo Template / Dompdf) for membership print. Site repo must not invent CRM fields.
- Tell the implementer when types + PDF template exist.

Do **not** write `/home/skoksharov/safehouse/nonprofit-espocrm` from the site agent. Do **not** start this spec until 002 hit UAT is closed **or** the owner says interrupt. Do **not** glue into `004-ad-grants-landings` (that spec forbids a new membership CRM workflow).

SITE queue stays S02a analytics UAT → then this interrupt **if the owner still wants it before 003**.
