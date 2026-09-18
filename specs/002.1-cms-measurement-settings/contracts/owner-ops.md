# Contract: Owner ops (002.1)

## Local preview

1. CMS `/cms-safehouse` → Impostazioni → Integrazioni → measurement tab.
2. Paste the **web container id** (`GTM-…`) only. Do not paste install snippets. Do not put a Measurement ID here.
3. Toggle on → save. Hard-refresh a public page.
4. Combined UAT with 002 (consent, four events, legal copy).

## Production (only when the owner asks)

Repeat the same save on **live** CMS. Live Caddy CSP from 002 still needs an owner apply. This feature does not reload Caddy and does not write live CMS.

## Git / chat

Do not commit live `GTM-` / `G-` ids. `.env.example` stays empty fallback.

## Agent fill

Local `.env` has no measurement keys. Agent MUST NOT copy container ids from chat into the database.
