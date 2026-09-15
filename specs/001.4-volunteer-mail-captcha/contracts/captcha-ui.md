# UI contract: challenge widget layout (001.4)

Cite: [Turnstile embed / theme / size](https://developers.cloudflare.com/turnstile/get-started/client-side-rendering/#configuration-options), [Tailwind width](https://tailwindcss.com/docs/width), [justify-content](https://tailwindcss.com/docs/justify-content).

Applies to **contact** and **volunteer** public forms only.

## Layout (mandatory)

The challenge box sits in the same column as the text fields:

- Container is full field width (`w-full`) and centres the widget (`justify-center`), **or** the widget uses Turnstile size `flexible` so it spans the field width.
- Must **not** sit flush right with empty space on the left (current UAT fail).

Visual matrix (owner UAT, no PHPUnit snapshots):

| Page | Locale | Theme | Expect |
| :--- | :--- | :--- | :--- |
| Volunteer | it | dark | Widget centred or full width |
| Volunteer | it | light | Same layout; light widget if provider honours theme |
| Contact | it | dark | Same |
| Volunteer or contact | en | either | Spot-check layout |

Donate / cookie / CMS login: still **no** widget.

## Theme (should)

`data-theme` is `light` or `dark` copied from `html[data-theme]` — **not** hardcoded `dark`, **not** Turnstile `auto` (OS ≠ site toggle).

If a live theme toggle does not restyle the iframe without reload, first paint after reload is enough for FR-007; FR-006 still required.

Managed mode may show a green check without pictures — expected; not a defect.
