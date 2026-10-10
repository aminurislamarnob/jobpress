# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

JobPress is a WordPress job-board plugin published on WordPress.org (text domain `jobpress`, PHP ≥ 7.4, WP ≥ 5.6). CSS/JS in `assets/` are hand-written and served as-is, except the JobPress Jobs block: its editor script is built with `@wordpress/scripts` from `src/blocks/` into `assets/blocks/`, and the build output is committed (WordPress.org deploys the repo without a build step). Rebuild and commit `assets/blocks/` whenever you change `src/blocks/`; CI fails when they differ. There is no PHP unit test suite; behaviour is covered by Playwright end-to-end tests in `tests/e2e/`.

## Commands

The plugin lives inside a local WordPress install (`/Users/welabs/Sites/jobpress`); run WP-CLI from the site root.

```bash
composer dump-autoload                          # after adding/renaming classes in inc/
wp plugin deactivate jobpress && wp plugin activate jobpress   # re-run activation hook (jobs page, DB table, rewrite flush)
wp rewrite flush                                # after changing CPT/taxonomy slugs
wp i18n make-pot . languages/jobpress.pot       # regenerate translations template

npm install                                     # dev tooling (block build, e2e)
npm run build:blocks                            # build the block (src/blocks → assets/blocks); commit the result
npm run start:blocks                            # rebuild the block on change

npx playwright install chromium                 # one-time e2e setup
npm run test:e2e                                # run all e2e tests
npm run test:e2e -- specs/jobs-page.spec.js     # run one spec file
npm run test:e2e -- -g "filters jobs by type"   # run one test by name
npm run test:e2e:report                         # open the last HTML report
```

E2E tests run against a live WordPress site with JobPress active. `tests/e2e/.env` (gitignored; copy `.env.example`) sets `WP_BASE_URL`, `WP_USERNAME`, `WP_PASSWORD` — locally this is `http://jobpress.test` with the `e2e-admin` user. Without it they target wp-env defaults (`npm run env:start`, needs Docker; config in `.wp-env.json`). Tests run serially because they change shared plugin options; the `jobPress` fixture in `tests/e2e/fixtures.js` creates jobs/terms/pages over REST, changes settings through the real settings screens, and reverts everything after each test, so the suite is safe to run against a site with real content. URLs come from REST `link` fields, so tests work with plain or pretty permalinks. Elementor widget specs seed pages with `_elementor_data` over REST (`jobPress.createElementorPage()`) and assert the frontend, never the editor UI; they skip when Elementor isn't active (wp-env installs it). Block specs likewise create pages with block markup over REST (`jobPress.createBlockPage()`) and assert the frontend; only the editor tests in `block.spec.js` drive the block editor. They skip when the block isn't registered (WordPress before 6.3). Set `E2E_THEME=<slug>` to run against a specific installed theme; CI runs the suite on a classic theme (`twentytwentyone`) and a block theme (`twentytwentyfive`).

## Architecture

**Bootstrap (`jobpress.php`)** — loads the Composer PSR-4 autoloader (`JobPressInc\` → `inc/`), defines `JOBPRESS_VERSION` / `JOBPRESS_PLUGIN_PATH` / `JOBPRESS_PLUGIN_URL`, requires the procedural files `inc/core-functions.php`, `inc/template-functions.php`, `inc/template-hooks.php`, then calls `JobPressPluginInit::register_services()`.

**Service registry (`inc/JobPressPluginInit.php`)** — every feature is a class in `inc/Base/` (or `inc/Pages/Admin/`) with a `register()` method that adds its WP hooks. A new class does nothing until it is added to the `get_services()` array. Elementor widgets (`ElementorInit`) are only registered if `did_action('elementor/loaded')` is true at plugin load time.

**Activation / updates** — the activation hook runs `Activate::activate()` (creates the "Jobs Listing" page containing `[jobpress]` and stores its ID in `jobpress_jobs_page_id`), and sets a flag that `Flush` consumes on the next `plugins_loaded`. On plugin updates, `Activate::handle_update()` runs via `upgrader_process_complete` and compares the `jobpress_version` option against `JOBPRESS_VERSION` (it also drops the unused `{prefix}jobpress_application` table older versions created, if empty). `uninstall.php` removes the settings, and jobs, terms and the Jobs Page only when `JOBPRESS_REMOVE_ALL_DATA` is true. When bumping the version, update it in **three places**: the plugin header and `JOBPRESS_VERSION` in `jobpress.php`, and `Stable tag` in `readme.txt` (plus the changelog there).

**Data model** — custom post type `jobpress` with taxonomies `jobpress_category` and `jobpress_type` (`inc/Base/CustomPostType.php`); job fields are post meta via `JobsMetaBox`. Settings are individual `jobpress_*` options registered through the WP Settings API in the `SettingsForm*` classes, rendered by the admin views in `templates/task-pages/`. The Listing Defaults (Shortcodes screen, `SettingsFormListing`) are declared once in `jobpress_get_listing_settings()`, keyed by shortcode attribute; adding an entry there adds the setting, its field and the attribute's global default (`jobpress_get_listing_setting()`).

**Templating (WooCommerce-style)** — two lookup paths, both theme-overridable:
- `TemplateLoader` hooks `template_include` for the single job, the CPT archive, taxonomies, and the configured jobs page; it checks the theme for `jobpress.php`, `single-jobpress-{slug}.php`, then `jobpress/<file>` before falling back to `templates/`.
- `jobpress_get_template()` / `jobpress_locate_template()` / `jobpress_get_template_part()` in `core-functions.php` load partials, checking `{theme}/jobpress/` first.
- Page templates call `jobpress_get_header()` / `jobpress_get_footer()` (not `get_header()`): for block themes these open the document and render the theme's header/footer template parts, since block themes have no header.php. The default content wrapper (`global/wrapper-start.php`) adds a `.jp-container` for themes without their own wrapper case; match it by class in tests, as some classic themes open their own `#primary` in header.php.
- Template output is composed through action hooks (`jobpress_before_main_content`, `jobpress_before_jobs_loop`, `jobpress_after_jobs_loop`, …) wired in `inc/template-hooks.php` to functions in `inc/template-functions.php`. Loop state lives in `jobpress_setup_loop()` / `jobpress_get_loop_prop()`.

**Listing designs** — the `[jobpress]` shortcode (`JobListShortcode`) is the listing engine shared with the Elementor widget and the block: it resolves attributes (empty ones inherit the global settings), runs the jobs query (`jobpress_get_listing_jobs()`; one query per category group for the grouped designs v2/v4) and wraps `templates/listing/jobpress-listing-v{N}.php` in `<div id="jp-listing-{n}" class="jp-listing jp-design-v{N}">`, where N is the `design` attribute or else the `jobpress_design_type` option (1–5). Templates receive the ready query and expose `jp-listing__*` hook classes that Elementor style controls target. CSS: `jobpress-common.css` holds everything shared by all designs (plus the appearance colors as inline CSS variables); each `jobpress-style-v{N}.css` (handle `jobpress-design-v{N}`) holds only rules scoped to `.jp-design-v{N}`, so different designs can share a page. Archive views get the `jp-design-v3` body class (they use `archive-jobpress.php`, styled by v3) and single job pages the global design's class. `PublicEnqueue` loads common plus the designs a page needs: detected in the head from the post's `[jobpress]` shortcodes, JobPress Jobs blocks and Elementor widgets, and enqueued again by the shortcode as a fallback. Adding a design means adding both a listing template and a scoped CSS file with the same number.

**Search & filters** — the jobs archive reads query vars `job_search`, `jobcategory`, `jobtype` (registered in `CustomPostType`); the form is `templates/global/filter-and-search.php`, hooked on `jobpress_job_loop_header`. The `jobpress` post type has `has_archive => false`: the Jobs Page (`jobpress_jobs_page_id`) *is* the archive. Listings with `show_search` reuse the same form (with a per-instance ID suffix) as a launcher that submits to the Jobs Page; listings never filter in place.

**Elementor widget (`ElementorWidgets`)** — a front end for the listing engine: `get_shortcode_atts()` maps its settings onto `[jobpress]` attributes and leaves empty ones out, so they inherit the global settings (visibility settings are Default/Show/Hide selects, since a switcher can't express "inherit"). Controls for elements a design doesn't render use `get_design_condition()`, which also matches "Default" when the global design qualifies. Style controls only write Elementor CSS, against the `jp-listing__*` hook classes, prefixed with `ElementorWidgets::SCOPE` to outrank the design rules; colors set the `--jp-*` variables on the widget.

**JobPress Jobs block (`Blocks`, `src/blocks/jobs/`)** — the block editor's front end for the listing engine, registered only on WordPress 6.3+ (`Blocks::is_supported()`). It is dynamic: `Blocks::render()` passes the attributes that are `[jobpress]` attributes (`get_shortcode_atts()`, empty ones left out so they inherit) to `JobListShortcode` and wraps the listing in `get_block_wrapper_attributes()`. Block attributes use the shortcode's names; the Listing Defaults attributes aren't in `block.json` but added from `jobpress_get_listing_settings()` by `filter_metadata()` (a `block_type_metadata` filter, which also versions the block's stylesheets with the plugin), and the editor gets attribute definitions from the server. Style attributes (camelCase) are block-only: `get_style_rules()` turns them into CSS printed before the block, scoped by a `jp-block-{hash}` class named after the styles so editor previews (each rendered separately) don't clash. The editor preview is `ServerSideRender` inside `Disabled`, with `jobpress_preview=1` so the render leaves out the wrapper (the editor's own wrapper has the supports' styles). Editor data (global values, designs, per-design elements) comes from `Blocks::get_editor_data()` as `window.jobpressBlock`. JSX files use the classic runtime pragma so the build doesn't need WordPress 6.6's `react-jsx-runtime`.

**Public hooks** are documented in `dev-docs/filters.md` — keep it updated when adding `apply_filters`/`do_action` calls. The `[jobpress]` attributes are documented in `dev-docs/shortcode-attributes.md`, `readme.txt` and the Shortcodes settings screen; keep them in sync. Bump a template's `@version` when changing it, so `TemplateOverrideNotice` flags outdated theme copies.

## Release / deployment

- `vendor/` is listed in `.gitignore` but **is committed** (tracked files) and must stay so: WordPress.org gets the repo contents minus `.distignore`, which strips `composer.json`/`composer.lock` but not `vendor/`. Commit regenerated autoload files.
- Pushing a git tag deploys that version to WordPress.org SVN (`10up/action-wordpress-plugin-deploy`); only users with write/admin permission may push tags.
- The default branch is `develop`. A workflow is set up so that pushing to a `trunk` branch (none exists on the remote yet) updates only the readme and `.wordpress-org/` assets (banners, screenshots) on WordPress.org.
