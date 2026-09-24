# Research: Ads-ready public refresh

## Decision: Membership is the motion sample, not the width

**Rationale**: Owner: use Diventa socio as the sample and do not make every page widescreen. Large screens may widen the reading column. [Tailwind max-width](https://tailwindcss.com/docs/max-width) `max-w-6xl` (72rem) is the large-screen cap for reading pages. The membership landing stays on its current full-width template.

**Alternatives considered**: Copy the membership layout onto every page (rejected). Leave the old narrow column (rejected).

## Decision: Entrance motion respects reduced motion

**Rationale**: Cards and page sections animate in on open. [Tailwind hover, focus, and other states](https://tailwindcss.com/docs/hover-focus-and-other-states) `motion-safe:` runs the animation only when the visitor has not requested reduced motion. `motion-reduce:` shows the final state immediately.

**Alternatives considered**: Always animate (rejected). A separate motion library (rejected; the site already uses CSS).

## Decision: Home strip is one mixed query

**Rationale**: Latest news and articles are one list, newest `published_at` first, at most four, current locale only. Two links sit with the strip: all news, all articles. No items means no strip. [Laravel Blade](https://laravel.com/docs/13.x/blade) renders the partial from data the home controller already can pass.

**Alternatives considered**: Two separate rows (rejected; owner asked for whichever came out last). A carousel (rejected; small windows, not a slider).

## Decision: 5×1000 banner on every public page except membership

**Rationale**: Removing the header item must not hide the tax donation. The banner is in the public layout and hidden when the current page key is `diventa-socio`. It links to the existing 5×1000 page.

**Alternatives considered**: Home only (rejected; inner pages would lose it). Header highlight kept (rejected by the owner).

## Decision: Header order is configuration

**Rationale**: [config/navigation.php](../../config/navigation.php) already drives the header. Replace the nine items with Who we are, Services, Become a member, Volunteering, Contact. Donate stays the existing button. News and articles stay reachable from Other pages because their keys are not in `standard_page_keys` only if they are pages; they are routes, so the header simply omits those routes. Add `diventa-socio` handling via `page_key` and volunteering via `volunteers.show`.

**Alternatives considered**: A new menu model (rejected).

## Decision: 007 is not a second redesign

**Rationale**: Public typeface already shipped in `002.4`. This feature takes the remaining public motion. `007` is marked superseded.

**Alternatives considered**: Implement `007` after this feature (rejected; two visual passes).
