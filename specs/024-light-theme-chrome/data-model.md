# Data model: Light-theme chrome, 5×1000 mark, slower Home count

No CRM or MariaDB schema change. No new translation keys.

## Chi siamo borders (light)

| Block | Dark today | Light after this spec |
| :--- | :--- | :--- |
| Intro `.template-about-intro` | glass `border-white/10` | generic `--safehouse-glass-border` (unchanged) |
| Values `.template-about-values` | `--safehouse-accent-panel-border` | primary/accent red outline (not glass remap) |
| Closing `.template-about-closing` | `border-l-4` + `border-safehouse-primary` (all-sides color) | same primary color; light remap must not override |

## 5×1000 mark

| State | Geometry | Fill | Text |
| :--- | :--- | :--- | :--- |
| Rest | rectangle (`rounded-sm`, not `rounded-full`) | transparent | **5×** in primary |
| Hover | same rectangle | primary fill | **5×** in white |

Blade copy stays `5×` in `.site-five__mark`.

## News filter surfaces (light)

| Control | Light fill / border |
| :--- | :--- |
| Date inputs | `rgb(255 255 255 / 92%)` / `rgb(0 0 0 / 14%)` |
| Categorie `.news-cat-menu__summary` | same as date inputs |
| View toggle | existing light toggle rule (not this story) |

## Home count

| Token | Before | After |
| :--- | :--- | :--- |
| `COUNT_DURATION_MS` | 2000 | 3500 |
| Easing | `easeOutCubic` | unchanged |
| Reduced motion | skip animation | skip animation |
