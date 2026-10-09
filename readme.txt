=== JobPress - Your Company Job Board & Career Page ===
Contributors: aminurislam01
Donate link: https://www.buymeacoffee.com/aiarnob
Tags: jobpress, job board, careers, job listing, job manager, job portal, job openings, jobs
Requires at least: 5.6
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 2.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

JobPress is the ultimate WordPress job board plugin for a company.

== Description ==

This plugin enables you to build/create your company job board inside your WordPress website. It's designed & developed to build your company own job board and career page by few clicks. It's easy to use just install, active and ready to go.

= ✨ Plugin Features =
* Dedicated "JobPress" WordPress admin menu for manage jobs
* Add/Edit/Delete jobs from WordPress admin panel
* Job type support
* Job category support
* Job list page with 5 different layout styles, mixable on one page
* Job listings anywhere with the `[jobpress]` shortcode: choose jobs by category, type or ID, limit and order them, add a search bar and a "View all jobs" link
* Well organized job details page
* ⚡ Contact form 7 support for job applications
* 🎨 Change branding color easily
* ⚙️ Cool plugin settings panel for admin
* 🗺️ You can also add location google map iFrame embed code
* ⚡ Full-featured Elementor widget: every listing option plus Elementor style controls (colors, typography, spacing, borders, hover states)

= **Documentation** =

**👉 Getting Started**
* Install and activate the **JobPress** plugin.
* After activation, you will see a new menu called **JobPress** in your WordPress admin dashboard.
* From there, you can add, edit, and manage jobs just like WordPress posts.

**👉 Job Management**
* **Jobs**: Create and manage job listings.
* **Job Categories**: Organize jobs into categories (submenu under JobPress).
* **Job Types**: Manage job types (submenu under JobPress).

**👉 Settings**
JobPress provides a dedicated **Settings** menu to configure plugin options:

**👉 👉 General Settings**
* Set sidebar position for the Single Job page.
* Add global resume submission instructions.
* Select the Job Archive page (default page created during activation).
* Control how many jobs to display on the Job Archive page.

**👉 👉 Appearance Settings**
* Customize plugin colors to match your theme style.

**👉 👉 Shortcodes Settings**
* You can display job listings anywhere using the `[jobpress]` shortcode.
* **Select Design** sets the default listing design.
* **Listing Defaults** set what every listing shows by default: the title and subtitle, the open positions count, job card details, the apply button text, a search bar and a "View all jobs" link. The job card options also apply to the Jobs Page.
* Each shortcode or Elementor widget can override these defaults.

**👉 Elementor Integration**
Prefer a visual builder?  
JobPress includes a **dedicated Elementor Addon** to display job lists without shortcodes. Simply drag and drop the JobPress widget onto your page, then pick its design, which jobs to show and what each card shows, and style every part of it from Elementor's Style tab. Settings you leave untouched follow the global JobPress settings.

**👉 Quick Access**
From the WordPress Admin Bar, you’ll get **quick access** to:
* Job Archive / Default Job Listing Page
* JobPress settings

== Support ==
If you find this plugin useful, consider supporting its development through a [donation](https://www.buymeacoffee.com/aiarnob).

== Installation ==

= FOR STANDARD INSTALLATION: =
Installing this plugin is very easy just like any other WordPress plugin. Please follow these instructions:
1. In your WordPress admin panel, go to Plugins > Add New, search for "JobPress" and click on "Install Now" button.
2. Alternatively, download the plugin and upload the jobpress.zip to your plugins directory, which usually is /wp-content/plugins/.
3. Activate the plugin from plugins page.
4. Go to Setting > Permalinks and update the permalink settings by clicking on save change button.
5. Now plugin is ready to go.
6. Go to all pages from the admin pages menu then you will find the "Jobs Listing" page there. You can also create custom page just need to place shortcode "[jobpress]" there.


== Screenshots ==

1. Job List Style 01
2. Job List Style 02
3. Job List Style 03
4. Job List Style 04
5. Job Grid Style 01
6. Job Details Style 01
7. Job Details Style 02
8. Admin Panel General Settings
9. Job Appearance Settings
10. Job List Shortcode


== Frequently Asked Questions ==

= Is there any preset style for job listing? =
You there are 5 preset.

= Is it mandatory to update/flash permalinks settings after install plugin? =
Yes, It's mandatory.

= Can I Add/Edit/Delete jobs? =
Yes, you can.

= Is it support only Contact Form 7 for resume collection or job application? =
Yes, for now only support Contact Form 7. In-future we will add support for all forms. Also, You can also add description to collect resume by your email address.

= How can change settings? =
Just go to settings page from "JobPress" admin menu.

= How to use JobPress with Elementor? =
Drag and drop the "JobPress Jobs" widget from the Elementor editor. In the Content tab, choose the design, the header text, which details each job card shows, a search bar, a "View all jobs" link, and which jobs to list (categories, types, specific jobs, number and order). In the Style tab, override the JobPress colors for this widget and style the header, job cards, job titles, details, buttons, category headers and search bar. Every setting left on "Default" (or empty) follows the global JobPress settings, so changing those settings still updates the widget.

= How to use the JobPress shortcode? =
You can use the JobPress shortcode in two ways:

1. In WordPress Posts/Pages:
```
[jobpress] // Basic usage with default settings
[jobpress title="We're Hiring!" subtitle="Join Our Team"] // With custom title/subtitle
[jobpress show_positions="no"] // Hide position counts
[jobpress design="5" category="engineering" per_page="6" show_view_all="yes"] // A grid of the 6 newest engineering jobs
```

2. In PHP Files (e.g., theme templates):
```php
<?php 
// Basic usage
echo do_shortcode('[jobpress]');

// With custom attributes
echo do_shortcode('[jobpress title="Current Openings" subtitle="Find Your Dream Job" show_positions="yes"]');
?>
```

Available Shortcode Attributes (a missing or empty attribute uses the Settings > Shortcodes and Appearance settings):
* design - Listing design, 1 to 5
* title, subtitle - Header text
* show_title, show_subtitle, show_positions - Show/hide the header parts (yes/no)
* show_category, show_type, show_location, show_experience, show_vacancy, show_deadline - Show/hide job card details (yes/no)
* button_text - Apply button text
* show_search - Show a search bar that opens the Jobs Page results (yes/no)
* show_view_all, view_all_text - Show a link to the Jobs Page under the listing (yes/no), and its text
* per_page - Number of jobs to show (per category in the grouped designs 2 and 4); all by default
* category, type - Comma-separated category or job type slugs to show
* include, exclude - Comma-separated job IDs to show only, or to leave out
* orderby - date, title, menu_order or rand; order - ASC or DESC
* brand_color, hover_color, heading_color, secondary_color, content_color, border_color - Hex colors for this listing

Example:
```
[jobpress title="Join Our Team" type="remote" show_search="yes" brand_color="#7c3aed"]
```

== Changelog ==

= v2.3.0 (Oct 9, 2026)  =
* **feat:** Full-featured Elementor widget: choose the design, header, job card details, search bar, "View all jobs" link and which jobs to list, and style everything from the Style tab (colors, typography, spacing, borders, shadows, hover states). Settings left on "Default" follow the global settings.
* **feat:** New `[jobpress]` attributes: `design`, the six colors, `show_title`, `show_subtitle`, job card toggles (`show_category`, `show_type`, `show_location`, `show_experience`, `show_vacancy`, `show_deadline`), `button_text`, `show_search`, `show_view_all`, `view_all_text`, and query attributes `per_page`, `category`, `type`, `include`, `exclude`, `orderby`, `order`.
* **feat:** New Listing Defaults settings (Settings > Shortcodes): default title and subtitle, header and job card visibility, button text, search bar and "View all jobs" link. The job card options also apply to the Jobs Page.
* **feat:** Listings with different designs can share a page.
* **feat:** The open positions count counts the jobs the listing shows (e.g. one category's), not all jobs.
* **feat:** Admin notice when the theme overrides JobPress templates with outdated copies.
* **feat:** Developer hooks `jobpress_listing_defaults`, `jobpress_listing_query_args` and `jobpress_elementor_widget_controls`; `shortcode_atts_jobpress` now fires.
* **update:** Each page only loads the stylesheets of the designs it shows. Rules shared by all designs moved to `jobpress-common.css`, and design stylesheets are registered as `jobpress-design-v1` to `jobpress-design-v5` (previously one `jobpress-css` handle).
* **fix:** Design v5 (grid) overflowed narrow columns and phones. Narrow cards now scale their text down instead of breaking words, and the cards in a row share one height.
* **fix:** The Elementor widget fills its container when the container is set to a row direction, instead of shrinking to the width of its header.
* **fix:** Design v2 gives the job title the free space in narrow listings, and its experience text no longer inherits the theme's body size.
* **fix:** Designs v3 and v4 keep each job detail (e.g. the deadline) on one line when they wrap.
* **fix:** Category groups show "1 OPENING" instead of "1 OPENINGS".
* **fix:** Design v1 no longer starts a job's details with a dash when the job has no category.
* **Upgrade notes:**
* Design stylesheet rules are now scoped to `.jp-design-v1` to `.jp-design-v5` (on each listing's wrapper, and on the body of the jobs archive and single job pages). Custom CSS that overrides listing styles may need a more specific selector, e.g. `.jp-listing .jp-single-job-list`.
* An empty `title` or `subtitle` attribute now uses the default text; hide them with `show_title="no"` or `show_subtitle="no"`.
* Elementor widgets saved before 2.3.0 that kept the widget's default title "Job Openings" or subtitle "Find your dream job" may now show the global listing title and subtitle instead (set them in Settings > Shortcodes > Listing Defaults, or in the widget).
* Theme copies of the listing templates keep working, and still show the jobs a listing selects, but don't get the new options or style hooks until updated from the plugin's `templates/listing` folder.

= v2.2.0 (Oct 9, 2026)  =
* **feat:** Search, category and job type filters on the jobs page.
* **feat:** Block theme support: job pages now show the theme's header and footer on block themes such as Twenty Twenty-Five.
* **feat:** The single job page shows the job title (can be hidden with the `jobpress_show_single_job_title` filter).
* **feat:** "No jobs found" message when a search or filter has no results.
* **feat:** Designs v2 and v4 list jobs without a category under "Other openings".
* **update:** Job pages and listings sit in a centered, padded container on themes without their own wrapper.
* **update:** Application deadlines use the site's date format.
* **update:** Email addresses in the application text are clickable links.
* **update:** Styles only load on pages that show jobs, and load in the Elementor editor preview.
* **fix:** Job details are only saved from the job editor, with a valid security check.
* **fix:** Job, category and type URLs could return "Page not found" after activating the plugin.
* **fix:** Category and type archives use the JobPress jobs layout and show only that category's or type's jobs.
* **fix:** The jobs page styles loaded on every page when no Jobs Page was set.
* **fix:** An Elementor widget title or subtitle containing "]" lost all widget settings.
* **fix:** Translations were not loaded.
* **fix:** Settings are sanitized on save, and output is escaped throughout the templates.
* **fix:** Replaced the deprecated `get_page_by_title()` function.
* Tested up to WordPress 7.1.

= v2.1.5 & v2.1.6 (Dec 24, 2025)  =
* Compatibility check with latest WordPress Version v6.9

= v2.1.4 (Aug 24, 2025)  =
* **Update:** Added plugin setup and configuration quick guideline in documentation.

= v2.1.3 (Aug 24, 2025)  =
* **feat:** Introduced a new template feature to the plugin, enabling extended customization and layout control for job listings.
* **feat:** Added support for template management within the plugin’s workflow.
* **feat:** Implemented initial template files and supporting code.
* **update:** Refactored codebase to accommodate new template structure.
* **update:** Updated various components to integrate seamlessly with the new template system.
* **fix:** Minor bug fixes and code improvements during template integration.

= v2.1.1 & v2.1.2 (Aug 19, 2025)  =
* Set the shortcode title & subtitle default value.
* Update readme file to guide user how to use Shortcode in proper way.

= v2.1.0 (Aug 18, 2025)  =
* Added new shortcode attribute 'title' to customize job listing title
* Added new shortcode attribute 'subtitle' to customize job listing subtitle
* Added new shortcode attribute 'show_positions' to control visibility of open positions count
* Added new "JobPress" category in Elementor editor
* Added Elementor widget support with title, subtitle and position count controls

= v2.0.0 =
* PCP detected security issues fix.
* Update plugin tags.
* Check compatibility with latest version of WordPress.

= v1.0.0 =
* Initial release.
