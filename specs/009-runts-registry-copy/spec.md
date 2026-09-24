# Feature Specification: Public RUNTS registry copy

**Feature Branch**: `009-runts-registry-copy`

**Created**: 2026-09-24

**Status**: Draft — specified only. Do not plan or implement until the owner asks.

**Queue**: Owner interrupt beside `003-public-seo-foundation` (specified, not started). This spec does not start SEO work and does not replace `003`.

**Input**: Owner 2026-09-24: publish, for visitors, a copy of the official RUNTS registry information for Safe House ETS and three downloadable documents hosted on this site. Tell visitors they can open the official RUNTS search and check the information themselves. Put the material in the Trasparenza section. Link to it from Diventa socio and from the footer on every public page. Two source files are signed containers; the statute, once opened as a document, has every page after the first rotated. Visitors must receive readable copies.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Read the public registry extract (Priority: P1)

A visitor opens Trasparenza and reads a clearly marked copy of the Safe House ETS entry in the national third-sector register: identity, seat, activities, people in office, workforce and membership counts, 5 per mille, and governing bodies. A short note says this is a copy and that the visitor can check it on the official RUNTS search.

**Why this priority**: The owner wants the official registry facts on the site before the file downloads. Without the note, the copy could be mistaken for a live register.

**Independent Test**: Open Trasparenza in Italian and in English. The extract lists the fields in the Registry extract section. The verification note and the link to the official RUNTS search are visible without downloading a file.

**Acceptance Scenarios**:

1. **Given** a visitor on Trasparenza, **When** they read the registry section, **Then** they see Safe House ETS, repertorio 156768, codice fiscale 96629270586, and the inscription date 21/07/2025.
2. **Given** the same page, **When** they look for how to verify, **Then** they see a note that the block is a copy and a link to the official RUNTS entity search, where they can look the organisation up themselves.
3. **Given** the English locale, **When** they open Trasparenza, **Then** the verification note is in English and the same registry facts are present.

---

### User Story 2 - Download the three official documents (Priority: P1)

A visitor downloads three documents from the same Trasparenza section: the inscription decision, the constitutive act, and the statute. Each file opens as a normal document. Every page of the statute is upright and readable.

**Why this priority**: The register lists these three acts. The owner wants them on this site, not only inside the RUNTS session.

**Independent Test**: From Trasparenza, download each of the three files and open them. All three open. The statute has no sideways pages.

**Acceptance Scenarios**:

1. **Given** Trasparenza, **When** the visitor uses the three document links, **Then** each link downloads one document: Provvedimento di iscrizione, Atto costitutivo, and Statuto.
2. **Given** the statute file, **When** the visitor opens it, **Then** page 1 and every following page are upright.
3. **Given** a visitor who does not use signature software, **When** they open any of the three files, **Then** they can read the document.

---

### User Story 3 - Reach the extract from membership and the footer (Priority: P2)

A visitor on Diventa socio, and a visitor in the footer of any public page, can open the Trasparenza registry section in one step.

**Why this priority**: The owner asked for those entry points. The extract is still useful if the links come second.

**Independent Test**: From Diventa socio, follow the registry link and land on the Trasparenza extract. From the footer of Home and of one other public page, follow the same destination.

**Acceptance Scenarios**:

1. **Given** Diventa socio, **When** the visitor activates the RUNTS / registry link, **Then** they arrive at the Trasparenza registry section.
2. **Given** any public page, **When** the visitor uses the footer, **Then** a link to that same section is available in both locales.

---

### Edge Cases

- The official RUNTS card has no public address that stays valid. The site sends people to the official search, not to a card that only works inside a private session.
- The copy can go out of date. The page shows the date the extract was taken (24 September 2026) so visitors know it is a snapshot.
- The statute source is rotated after the first page. The published statute must not keep that rotation.
- Two source files are signed containers. Visitors still receive readable documents, not a container they cannot open.
- Existing Trasparenza text stays. This feature adds the registry section; it does not rewrite privacy, cookie, or other legal pages.
- Seat spelling on other pages is unchanged by this feature. The extract uses the registry wording (Via Delleani 26, Anzio).

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Trasparenza MUST show a registry section for Safe House ETS that visitors can read without creating an account.
- **FR-002**: The section MUST state that it is a copy of the public register and MUST link to the official RUNTS entity search (`https://servizi.lavoro.gov.it/runts/it-it/Ricerca-enti`) so visitors can check the entry themselves.
- **FR-003**: The section MUST show the extract date 24 September 2026.
- **FR-004**: The section MUST include the registry facts in the Registry extract section below, including activities, office-holders, counts, 5 per mille, and governing bodies. Ministry website chrome (menus, logos, social links, site map) is not part of the copy.
- **FR-005**: The section MUST offer three hosted downloads: Provvedimento di iscrizione, Atto costitutivo, and Statuto. Each download MUST be a readable document.
- **FR-006**: Every page of the published statute MUST be upright.
- **FR-007**: Diventa socio MUST link to the Trasparenza registry section.
- **FR-008**: The footer of every public page MUST link to the Trasparenza registry section in Italian and in English.
- **FR-009**: Italian and English locales MUST both show the verification note in that locale and the same registry facts.
- **FR-010**: The site MUST NOT present the copy as a live feed of the register and MUST NOT send visitors to a RUNTS card address that only works inside a browser session.

### Key Entities

- **Registry extract**: Snapshot of the Safe House ETS public RUNTS entry dated 24 September 2026. Attributes are the fields listed below.
- **Registry document**: One of three hosted files (inscription decision, constitutive act, statute) that a visitor can download from the extract.

### Registry extract

Identity:

- Name: SAFE HOUSE ETS
- Repertorio: 156768
- Codice fiscale: 96629270586
- Iscritto il 21/07/2025
- Iscritto nella sezione in data 21/07/2025
- Sezione: ALTRI ENTI DEL TERZO SETTORE
- Iscrizione come rete associativa: No
- Forma giuridica: ASSOCIAZIONE
- Email PEC: PROJECTSAFEHOUSE@NAMIRIALPEC.IT
- Atto costitutivo: 05/06/2025
- Ultimo aggiornamento statutario: 06/05/2025

Sede legale:

- Stato: I
- Provincia: RM
- Comune: ANZIO
- Indirizzo: VIA DELLEANI
- Civico: 26
- CAP: 00042

Attività:

- Ente non commerciale: Sì
- Attività di interesse generale, art. 5 comma 1 d.lgs. 117/2017:
  1. A — Interventi e servizi sociali
  2. C — Prestazioni socio-sanitarie
  3. D — Educazione, istruzione e formazione professionale, nonché le attività culturali di interesse sociale con finalità educativa
  4. L — Formazione extra-scolastica, finalizzata alla prevenzione della dispersione scolastica e al successo scolastico e formativo, alla prevenzione del bullismo e al contrasto della povertà educativa
  5. N — Cooperazione allo sviluppo
  6. U — Beneficenza, sostegno a distanza, cessione gratuita di alimenti o prodotti o erogazione di denaro, beni o servizi a sostegno di persone svantaggiate o di attività di interesse generale a norma del presente articolo
  7. X — Cura di procedure di adozione internazionale
- Classificazione ICNPO:
  1. 4100 — SERVIZI DI ASSISTENZA SOCIALE
  2. 12100 — ALTRE ATTIVITA' NON CLASSIFICATE ALTROVE
  3. 7100 — ORGANIZZAZIONI CIVICHE E DI TUTELA DEI DIRITTI
- Previsione statutaria attività diverse: Sì

Persone:

1. Persona fisica, rappresentante legale. Matteo Grossi. Stato I, provincia RM, comune Roma. Carica: legale rappresentante. Nomina 05/06/2025.
2. Persona fisica, non rappresentante legale. Claudio Grossi. Carica: componente dell'organo di amministrazione. Nomina 05/06/2025.
3. Persona fisica, non rappresentante legale. Rosa Fagioli. Carica: componente dell'organo di amministrazione. Nomina 05/06/2025.

Numeri:

- Lavoratori subordinati/parasubordinati: 0
- Volontari iscritti nel registro dell'ente: 2
- Volontari di enti aderenti: 0
- Soci persone fisiche: 3

Cinque per mille: accreditamento sì.

Organi:

1. Organo di amministrazione. Nomina 05/06/2025. Componenti: 3.
2. Organo assembleare di indirizzo. Nomina 05/06/2025. Componenti: 3.

Atti e documenti (labels visitors see; files are hosted here):

| Document | Practice code |
| --- | --- |
| Provvedimento di iscrizione | PROVISC |
| Atto costitutivo | C01 |
| Statuto | C02 |

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: A visitor finds repertorio 156768 and codice fiscale 96629270586 on Trasparenza in under a minute, in either locale.
- **SC-002**: From that section, the visitor opens the official RUNTS search in one action and can read the on-site note that the block is a copy.
- **SC-003**: Each of the three documents downloads and opens on the first attempt. A reader can turn every statute page without rotating the view.
- **SC-004**: Diventa socio and the footer of every public page each expose one link that lands on this section.

## Assumptions

- The extract is a static snapshot of the public RUNTS page the owner copied on 24 September 2026. It is not refreshed automatically.
- Office-holder names, the PEC address, and the seat are published because they already appear on the public register and the owner asked for that full extract.
- The verification sentence is translated. Registry labels and official wording stay Italian on both locales so the copy does not drift from the register.
- The existing short Trasparenza paragraph stays. This feature adds a section; it does not replace privacy or cookie pages.
- The RUNTS link is the public search, not a direct card. Visitors use repertorio 156768 or codice fiscale 96629270586 on that search.
- Source files supplied by the owner are the inscription decision (already a readable document) plus signed containers for the constitutive act and the statute. The statute container, opened as a document, has every page after the first rotated. Publishing turns those sources into three readable documents. How the files are prepared is left to planning.
- This spec does not change the RUNTS portal, does not call a registry service, and does not start `003-public-seo-foundation`.
