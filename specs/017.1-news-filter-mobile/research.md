# Research: News filter phone overflow

## Decision: Stack the date group on small screens; keep the desktop row

**Rationale**: Owner screenshots of `/it/news` and `/it/articles` on a phone. The date group is one nowrap flex: label **Data**, two `input type="date"`, **Applica**. Native date fields will not shrink ([min-width](https://tailwindcss.com/docs/min-width) must be `0` / `min-w-0`). Centered overflow clips **Data** to **A** on the left and **Applica** on the right. 017 already required no sideways scroll. Phone: column group, date fields in a two-column grid, **Applica** on the next row full width ([grid-template-columns](https://tailwindcss.com/docs/grid-template-columns)). `md:` keeps today’s row.

**Alternatives considered**: Horizontal scroll inside the glass (owner still cannot tap Applica). Shrink font only (date widgets ignore that). Hide dates on a phone (rejected: still needed).

## Decision: One CSS change for both listings

**Rationale**: Articoli uses the same `listing-toolbar` (`018`). Fix `.news-toolbar` / `.news-date-filters` once.
