# `[jobpress]` attributes

The `[jobpress]` shortcode renders a job listing. The JobPress Elementor widget renders the same listing, and each of its settings maps onto one of these attributes.

**Defaults.** Every attribute falls back to the global settings when it is missing or empty: **Settings → Shortcodes** (the *Select Design* field and the *Listing Defaults* section) and **Settings → Appearance** (colors). Developers can change the defaults with the `jobpress_listing_defaults` filter. Yes/no attributes take `yes` or `no`.

## Design

| Attribute | Values | Default |
|---|---|---|
| `design` | `1`–`5`: list, grouped by category, cards, cards grouped by category, grid | Settings → Shortcodes → Select Design |
| `brand_color`, `hover_color`, `heading_color`, `secondary_color`, `content_color`, `border_color` | Hex color, e.g. `#0086fe` | Settings → Appearance |

## Header

| Attribute | Values | Default |
|---|---|---|
| `title` | Text | Listing Defaults (built in: "Job openings") |
| `subtitle` | Text | Listing Defaults |
| `show_title`, `show_subtitle` | `yes` / `no` | Listing Defaults (`yes`) |
| `show_positions` | `yes` / `no`: the open positions count (designs 1, 3, 5). It counts every job the listing matches, not only those shown. | Listing Defaults (`yes`) |

## Job cards

| Attribute | Values | Default |
|---|---|---|
| `show_category` | `yes` / `no` (designs 1, 5) | Listing Defaults (`yes`) |
| `show_type` | `yes` / `no` (all designs) | Listing Defaults (`yes`) |
| `show_location` | `yes` / `no` (designs 1, 2) | Listing Defaults (`yes`) |
| `show_experience` | `yes` / `no` (designs 2, 3, 4) | Listing Defaults (`yes`) |
| `show_vacancy`, `show_deadline` | `yes` / `no` (designs 3, 4) | Listing Defaults (`yes`) |
| `button_text` | Text of the apply button (designs 1, 3, 4; the accessible label of design 2's arrow link) | Listing Defaults ("Apply") |

The global type, experience, vacancy and deadline toggles and the button text also apply to the job cards of the Jobs Page and category/type archives.

## Search and links

| Attribute | Values | Default |
|---|---|---|
| `show_search` | `yes` / `no`: a search bar under the header that opens the Jobs Page results, with the listing's category/type preselected when it has a single one | Listing Defaults (`no`) |
| `show_view_all` | `yes` / `no`: a link to the Jobs Page under the listing, keeping a single category/type filter | Listing Defaults (`no`) |
| `view_all_text` | Text | Listing Defaults ("View all jobs") |

## Query

| Attribute | Values | Default |
|---|---|---|
| `per_page` | Number of jobs to show; in grouped designs (2, 4), per category group | All jobs |
| `category` | Comma-separated job category slugs. Jobs in any of them are shown; in grouped designs, only these categories get a group. | All |
| `type` | Comma-separated job type slugs. Jobs of any of them are shown. Combined with `category`, jobs must match both. | All |
| `include` | Comma-separated job IDs: only these jobs | — |
| `exclude` | Comma-separated job IDs to leave out | — |
| `orderby` | `date`, `title`, `menu_order` or `rand` | `date` |
| `order` | `ASC` or `DESC` | `DESC` |

## Examples

```
[jobpress design="5" category="engineering,design" per_page="6" show_view_all="yes"]
[jobpress title="Remote roles" type="remote" show_search="yes" brand_color="#7c3aed"]
[jobpress design="3" show_vacancy="no" show_deadline="no" button_text="View role"]
```

## Markup and styling

Each listing is wrapped in `<div id="jp-listing-{n}" class="jp-listing jp-design-v{N}">`. The design's stylesheet only styles elements inside `.jp-design-v{N}`, so listings with different designs can share a page. Elements carry stable hook classes for custom CSS (and the Elementor widget's style controls):

`jp-listing__header`, `jp-listing__title`, `jp-listing__subtitle`, `jp-listing__count`, `jp-listing__search`, `jp-listing__jobs`, `jp-listing__card`, `jp-listing__job-title`, `jp-listing__meta` (with `jp-listing__category`, `jp-listing__type`, `jp-listing__vacancy`, `jp-listing__deadline` where the design shows them separately), `jp-listing__experience`, `jp-listing__button`, `jp-listing__group`, `jp-listing__group-header`, `jp-listing__group-title`, `jp-listing__group-description`, `jp-listing__group-count`, `jp-listing__footer`, `jp-listing__view-all`.

Theme copies of the listing templates (`yourtheme/jobpress/listing/jobpress-listing-v{N}.php`) receive the ready jobs query (`$jobs_query`, or `$job_groups` in grouped designs) and the attributes as template variables; see the docblock of each template.
