# Contract: Consent UI (002)

Reuse one banner. Strings via `__()` ([localization](https://laravel.com/docs/13.x/localization)).

## First visit

Visible actions:

- Accept all → store `all`
- Essentials only → store `essential`
- Preferences → analytics checkbox default **unchecked**; Save → `all` or `essential`; Close preferences without Save → **no** analytics accept
- Dismiss/X on the first-visit card → store `essential` (default deny)

Scroll does not store a choice.

After a stored choice, the first-visit card stays hidden on later pages (until the visitor clears storage, tools change significantly, or a long interval — do not re-prompt every page).

## Reopen / withdraw

Footer legal row includes a button (not only a policy link) that reopens the **same** preferences panel. Cookie policy text points at that control.

Switch `all` → `essential` must dispatch the existing `cookie-consent:changed` event so measurement stops in that visit.

## Audit

`POST /{locale}/cookie-consent` unchanged: `level` in `essential|all`; hashed identifiers only ([CookieConsentTest](../../../tests/Feature/CookieConsentTest.php) stays the contract).

## Tests (propose)

- Home includes dismiss control and footer reopen control.
- Endpoint still hashes IP/UA; still rejects missing level; still rate-limits.
