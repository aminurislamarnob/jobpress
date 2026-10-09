# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

JobPress is a WordPress job-board plugin published on WordPress.org (text domain `jobpress`, PHP ≥ 7.4, WP ≥ 5.6). There is no build step or JS bundler — CSS/JS in `assets/` are hand-written and served as-is. There is no PHP unit test suite; behaviour is covered by Playwright end-to-end tests in `tests/e2e/`.

## Commands

The plugin lives inside a local WordPress install (`/Users/welabs/Sites/jobpress`); run WP-CLI from the site root.

```bash
composer dump-autoload                          # after adding/renaming classes in inc/
wp plugin deactivate jobpress && wp plugin activate jobpress   # re-run activation hook (jobs page, DB table, rewrite flush)
wp rewrite flush                                # after changing CPT/taxonomy slugs
wp i18n make-pot . languages/jobpress.pot       # regenerate translations template

npm install && npx playwright install chromium  # one-time e2e setup
npm run test:e2e                                # run all e2e tests
npm run test:e2e -- specs/jobs-page.spec.js     # run one spec file
npm run test:e2e -- -g "filters jobs by type"   # run one test by name
npm run test:e2e:report                         # open the last HTML report
```

E2E tests run against a live WordPress site with JobPress active. `tests/e2e/.env` (gitignored; copy `.env.example`) sets `WP_BASE_URL`, `WP_USERNAME`, `WP_PASSWORD` — locally this is `http://jobpress.test` with the `e2e-admin` user. Without it they target wp-env defaults (`npm run env:start`, needs Docker; config in `.wp-env.json`). Tests run serially because they change shared plugin options; the `jobPress` fixture in `tests/e2e/fixtures.js` creates jobs/terms/pages over REST, changes settings through the real settings screens, and reverts everything after each test, so the suite is safe to run against a site with real content. URLs come from REST `link` fields, so tests work with plain or pretty permalinks. Set `E2E_THEME=<slug>` to run against a specific installed theme; CI runs the suite on a classic theme (`twentytwentyone`) and a block theme (`twentytwentyfive`).

## Architecture

**Bootstrap (`jobpress.php`)** — loads the Composer PSR-4 autoloader (`JobPressInc\` → `inc/`), defines `JOBPRESS_VERSION` / `JOBPRESS_PLUGIN_PATH` / `JOBPRESS_PLUGIN_URL`, requires the procedural files `inc/core-functions.php`, `inc/template-functions.php`, `inc/template-hooks.php`, then calls `JobPressPluginInit::register_services()`.

**Service registry (`inc/JobPressPluginInit.php`)** — every feature is a class in `inc/Base/` (or `inc/Pages/Admin/`) with a `register()` method that adds its WP hooks. A new class does nothing until it is added to the `get_services()` array. Elementor widgets (`ElementorInit`) are only registered if `did_action('elementor/loaded')` is true at plugin load time.

**Activation / updates** — the activation hook runs `Activate::activate()` (creates the "Jobs Listing" page containing `[jobpress]` and stores its ID in `jobpress_jobs_page_id`), `CreateDbTable` (creates `{prefix}jobpress_application` via `dbDelta`), and sets a flag that `Flush` consumes on the next `plugins_loaded`. On plugin updates, `Activate::handle_update()` runs via `upgrader_process_complete` and compares the `jobpress_version` option against `JOBPRESS_VERSION`. When bumping the version, update it in **three places**: the plugin header and `JOBPRESS_VERSION` in `jobpress.php`, and `Stable tag` in `readme.txt` (plus the changelog there).

**Data model** — custom post type `jobpress` with taxonomies `jobpress_category` and `jobpress_type` (`inc/Base/CustomPostType.php`); job fields are post meta via `JobsMetaBox`. Settings are individual `jobpress_*` options registered through the WP Settings API in the three `SettingsForm*` classes, rendered by the admin views in `templates/task-pages/`.

**Templating (WooCommerce-style)** — two lookup paths, both theme-overridable:
- `TemplateLoader` hooks `template_include` for the single job, the CPT archive, taxonomies, and the configured jobs page; it checks the theme for `jobpress.php`, `single-jobpress-{slug}.php`, then `jobpress/<file>` before falling back to `templates/`.
- `jobpress_get_template()` / `jobpress_locate_template()` / `jobpress_get_template_part()` in `core-functions.php` load partials, checking `{theme}/jobpress/` first.
- Page templates call `jobpress_get_header()` / `jobpress_get_footer()` (not `get_header()`): for block themes these open the document and render the theme's header/footer template parts, since block themes have no header.php. The default content wrapper (`global/wrapper-start.php`) adds a `.jp-container` for themes without their own wrapper case; match it by class in tests, as some classic themes open their own `#primary` in header.php.
- Template output is composed through action hooks (`jobpress_before_main_content`, `jobpress_before_jobs_loop`, `jobpress_after_jobs_loop`, …) wired in `inc/template-hooks.php` to functions in `inc/template-functions.php`. Loop state lives in `jobpress_setup_loop()` / `jobpress_get_loop_prop()`.

**Listing designs** — the `[jobpress]` shortcode (`JobListShortcode`, attrs `title`, `subtitle`, `show_positions`) renders `templates/listing/jobpress-listing-v{N}.php`, where N is the `jobpress_design_type` option (1–5). `PublicEnqueue` registers `assets/public/css/jobpress-style-v{N}.css` (v3 on the jobs page and category/type archives, which use `archive-jobpress.php` rather than a listing design) plus `jobpress-common.css` with the appearance colors as inline CSS variables. It only enqueues them on JobPress views or posts containing `[jobpress]`; the shortcode also calls `PublicEnqueue::enqueue_styles()` itself so page-builder content gets styled. Adding a design means adding both a listing template and a CSS file with the same number. Designs v2/v4 group jobs via `jobpress_get_listing_category_groups()`.

**Search & filters** — the jobs archive reads query vars `job_search`, `jobcategory`, `jobtype` (registered in `CustomPostType`); the form is `templates/global/filter-and-search.php`, hooked on `jobpress_job_loop_header`. The `jobpress` post type has `has_archive => false`: the Jobs Page (`jobpress_jobs_page_id`) *is* the archive.

**Public filters** are documented in `dev-docs/filters.md` — keep it updated when adding `apply_filters` calls.

## Release / deployment

- `vendor/` is listed in `.gitignore` but **is committed** (tracked files) and must stay so: WordPress.org gets the repo contents minus `.distignore`, which strips `composer.json`/`composer.lock` but not `vendor/`. Commit regenerated autoload files.
- Pushing a git tag deploys that version to WordPress.org SVN (`10up/action-wordpress-plugin-deploy`); only users with write/admin permission may push tags.
- The default branch is `develop`. A workflow is set up so that pushing to a `trunk` branch (none exists on the remote yet) updates only the readme and `.wordpress-org/` assets (banners, screenshots) on WordPress.org.
