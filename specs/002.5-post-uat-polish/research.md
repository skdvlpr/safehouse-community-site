# Research: 002.5 post-UAT polish

## Ticker pause

**Decision**: Remove the on-page Pausa/Riprendi button. Keep CSS `prefers-reduced-motion` so the track does not auto-play for those visitors ([WCAG 2.2.2](https://www.w3.org/WAI/WCAG22/Understanding/pause-stop-hide.html)).

**Rationale**: Owner UAT: the control is clutter on the phone ticker.

**Alternatives**: Hide pause only on mobile (rejected — they asked to remove it from the ticker). Keep pause (rejected).

## Phone “more below” cue

**Decision**: A `md:hidden` hint between hero and cards: Italian “Scorri in basso”, English “Swipe down”, plus a downward chevron. Decorative (`aria-hidden` on the arrow; short visible text).

**Rationale**: Cards still reveal late; the hint is the requested affordance. [Tailwind max-width / breakpoints](https://tailwindcss.com/docs/max-width) via existing `md:` stack.

**Alternatives**: Always-visible hint (rejected — desktop does not need it). Auto-scroll (rejected — owner said delayed paint is OK).

## Desktop Contattaci filler

**Decision**: From `lg` (1024px), split the red band: keep title, lead, Compila domanda, sede; add larger square social buttons (`SocialLinksSettings::filled()`, same icons as footer) and text links Statuto / FAQ / Donazioni / Volontariato. Below `lg` CSS/Tailwind hides that filler so the compact phone band stays (including landscape phones under 1024px). Visual work in the implementing chat (requested Grok 4.7 Extra High subagent is not an allowed slug).

**Rationale**: Owner: fill PC emptiness; do not spoil phone.

**Statuto**: No CMS `statuto` page exists. Default href = existing FAQ (`urlForKey('faq')`) until the owner supplies a PDF or page. FAQ, donate (`donations.index`), volunteer (`volunteers.show`) already exist.

**Alternatives**: Same filler on phone (rejected). New CMS statute page this spec (out of scope unless owner pastes a URL at task approval).

## Legal T-shape and opacity

**Decision**: Cookie/privacy: hero and document share one `max-w-*` column (`mx-auto` both) ([Tailwind max-width](https://tailwindcss.com/docs/max-width)). Hero background uses the same glass/translucent token as `.template-legal-doc` (`safehouse-glass` / current paper), not opaque `--color-safehouse-modal`.

**Rationale**: Screenshots show a wide opaque header over a narrower paper.

**Alternatives**: Widen paper only (still a T if widths differ). Keep opaque hero (rejected).

## Locale switches

**Decision**: Reuse `App\Support\LocalizedUrl::forLocale` ([Laravel localization](https://laravel.com/docs/13.x/localization); [Blade](https://laravel.com/docs/13.x/blade)). Footer: other locale label, no `data-cookie-reopen`. Cookie + privacy heroes: same link. Banner: visible IT and EN buttons (current locale marked). Switching locale must not clear `sh_cookie_consent`.

**Rationale**: Owner wants obvious IT/EN, not the header gear. Preferences stay on the cookie page button.

**Alternatives**: Banner-only i18n without URL change (rejected — would fork copy from the page). Keep footer reopen (rejected).

## Production legal document

**Decision**: Author copy still in `LegalPagesContent`. Drop the sentence that points to footer preferences. Preview: `ddev exec php artisan site:sync-legal-pages --force` ([Artisan](https://laravel.com/docs/13.x/artisan)). Production: `deploy/sync-legal-pages-once.sh` called from `post-deploy.sh` if present; script self-deletes after success so later deploys do not overwrite CMS.

**Rationale**: Owner: next push must publish the September document; they did not ask for perpetual CMS clobber.

**Alternatives**: Add sync to every post-deploy (rejected — staff CMS edits would vanish). Manual SSH only (rejected — they asked it in the push).
