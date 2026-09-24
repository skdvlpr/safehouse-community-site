# 050 — Reconcile 003 public SEO against the site after 002.5

**Date**: 2026-09-24

**Spec**: [specs/003-public-seo-foundation/spec.md](../../specs/003-public-seo-foundation/spec.md) (Draft, 2026-09-13). No `plan.md` or `tasks.md`.

**Queue**: S02b. S01 is closed. Analytics and operational cookie/privacy shipped as `002`–`002.5`. Owner UAT of that interrupt is accepted (2026-09-24). Next planned row is still 003, then 004, then S03 (`005`), then leftover S04 (`006`).

## Verdict

The spec is still the right next feature. Discovery metadata was never built. Later visual, legal, and CRM work does not replace it and does not force a new specify. Carry three notes into `/speckit-plan`; do not reopen the spec unless the owner rejects one of them.

## What the code does today

Checked `resources/views/layouts/app.blade.php`, `routes/web.php`, `public/robots.txt`, preview controllers, and Filament page/campaign/article forms.

| Spec item | Now |
| :--- | :--- |
| FR-001 locale title + short description | Document `<title>` is the visible title plus `— Safe House`. No `<meta name="description">`. |
| FR-002 editor override per locale | No search-title or search-description fields on pages, campaigns, news, or editorial articles. |
| FR-003 published URL index | No sitemap route. `public/robots.txt` is `User-agent: *` / `Disallow:` with no sitemap line. |
| FR-004 unpublished + preview non-indexable | Preview routes for pages, news, and editorial articles send `X-Robots-Tag: noindex, nofollow`. There is no `<meta name="robots">` and no sitemap to exclude them from. |
| FR-005 language alternatives | `hreflang` exists only on the visible IT/EN controls (footer, legal pages, cookie banner). The document head has no `rel="alternate"` links. |
| FR-006 share preview in the URL locale | No `og:title`, `og:description`, or `og:image`. |
| FR-007 staff labels IT + EN | Nothing to label yet. |
| SC-005 do not rewrite visible donate/volunteer/about copy | Still the right boundary. 002.4–002.5 changed landing, home, legal chrome, and cookie/privacy bodies. 003 must not touch those. |

## Still true in the spec

- Public locales stay `it` and `en`. No `/ru`.
- Do not install measurement, ads tags, or AdSense.
- Do not rewrite sitelink landing copy (that is 004) or add a Satispay banner (that is 005).
- Italian-only documents must not invent an English title or list a missing locale as an alternative.
- Published campaign and news URLs belong in the index; unpublished ones do not.
- Demo pages that are still published stay in the index. 004 must not use them as ads sitelinks.

## Notes for plan (not spec changes)

1. **New public URLs since 13 September.** The index must include the membership landing (`diventa-socio`), cookie, and privacy as ordinary published CMS pages. Donation thank-you and per-campaign privacy are real routes and were not named in the spec. Plan should treat thank-you as non-indexable (a result page) and campaign privacy as indexable only while that campaign is published. Confirm at plan time; do not start a new specify for this.
2. **Preview noindex is already half done.** Keep the existing `X-Robots-Tag`. 003 still has to add the head `noindex` if the header is not enough for the spec's "marked noindex", and must keep preview URLs out of the sitemap.
3. **Checklist note is stale.** `checklists/requirements.md` says do not plan until prior-queue UAT. Owner closed that UAT on 2026-09-24. Planning 003 is allowed.

## Not in this queue

Volunteer → Lead Volontario stays a CRM/site backlog. It is not S02, S03, or S04.
