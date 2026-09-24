# Contract: Home strip, header, and 5×1000 banner

## Home

- Address stays `/it` and `/en`.
- Primary action: Become a member, linking to the membership page.
- Secondary: Donate, Become a volunteer.
- Up to four story windows, newest first, mixed news and articles, current locale only.
- Windows animate in on open. `motion-reduce` shows them without the animation ([Tailwind states](https://tailwindcss.com/docs/hover-focus-and-other-states)).
- Two links: all news, all articles.
- No published stories: no strip.

## Header

Order: Who we are, Services, Become a member, Volunteering, Contact, then the existing Donate button.

Absent from the header: Home, news, articles, 5×1000.

Logo opens home.

## Cookie banner language

The IT/EN control inside the cookie banner swaps only that banner's copy. It does not navigate and does not change the page locale. The footer control still uses `LocalizedUrl` and changes the whole page.

## Banner

On every public page except Diventa socio. Text names 5×1000. One link to the existing 5×1000 page. Visible without opening the menu, on phone and wide screens.

## Width

Reading pages and the volunteer form use a large-screen cap of `max-w-6xl` ([Tailwind max-width](https://tailwindcss.com/docs/max-width)). The membership page keeps its current width. Phone layouts stay stacked.
