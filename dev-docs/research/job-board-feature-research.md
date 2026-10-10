# Job board feature research: what JobPress should build next

Goal: let a **single company** run its own career page inside WordPress with minimal setup. JobPress is not a multi-employer marketplace. This report compares JobPress 2.1.6 with six competitors and ranks features to build. Every citation is `plugin-folder/path:line` in the local copies under `wp-content/plugins/`.

## 1. Summary

- **Fix the existing bugs first (item 0).** The meta-box nonce check never fails, taxonomy archives load templates that don't exist, `jobpress_is_jobs_page()` returns true on every page, and the jobs page the plugin creates ignores its own `[jobpress]` shortcode.
- **Ship a built-in apply form with a free applications inbox.** This is the biggest gap in the market: WP Job Manager charges for it, JobBoardWP doesn't have one, and easyjobs sends applicants to its SaaS. Store applications as a private `jobpress_application` CPT and drop the unused table.
- **Make the application flow safe by default.** Protect resume files, add a honeypot with an optional CAPTCHA, and add GDPR consent plus the WordPress privacy exporter and eraser.
- **Send admin and applicant emails that work with no setup,** falling back to the site admin email.
- **Close jobs properly** with an expiry cron, a "filled" flag, and an expired notice that hides the apply form.
- **Output complete Google JobPosting schema** with structured salary, Google employment-type values, a remote flag and a company profile. Every free competitor gets part of this wrong.
- **Add a 3-step setup wizard** (company, careers page, first job) that seeds default job types and creates only the pages a single company needs.
- **Then:** AJAX filters with shareable URLs (built on the existing branch), Gutenberg blocks with block-theme support, an HR role and a simple form builder.

## 2. JobPress today

JobPress 2.1.6 (`jobpress/jobpress.php:6`) has:
- a `jobpress` CPT with the `jobpress_category` and `jobpress_type` taxonomies
- one meta box of free-text fields
- a `[jobpress]` shortcode with five listing designs
- a thin Elementor wrapper
- colour settings
- a "Jobs Listing" page created on activation (`jobpress/inc/Base/Activate.php:37-78`)

**Applications are half-built.** There is no apply form. Each job can embed a Contact Form 7 form, show per-job text, or show global text (`jobpress/templates/single-jobpress.php:198-208`). The `{prefix}jobpress_application` table is created on activation (`jobpress/inc/Base/CreateDbTable.php:14-28`), but no code ever reads or writes it. It has no `job_id` or status column, it isn't recreated on update, and it isn't dropped on uninstall. JobPress never calls `wp_mail` and never handles `$_FILES`.

**Other gaps:**
- The deadline is display-only.
- There is no schema, and salary and location are free text.
- Shortcode listings load every job (`jobpress/templates/listing/jobpress-listing-v1.php:3-11`).
- Templates work only on classic themes (`jobpress/templates/single-jobpress.php:7`).
- There are no blocks and no uninstall cleanup (`jobpress/uninstall.php:1-10`), and job meta isn't available in REST.

**Bugs found:**

| # | Bug | Where |
|---|---|---|
| B1 | The nonce check never fails. `$is_valid_nonce` is the string `'true'` or `'false'`, and both are truthy. The whole check is skipped when the nonce is missing, and `save_post` isn't limited to the job post type | `jobpress/inc/Base/JobsMetaBox.php:134-138`, `:161`, `:10` |
| B2 | When no jobs page is set, `jobpress_is_jobs_page()` calls `is_page(0)`, which is true on every page. The v3 CSS and archive body classes then load site-wide | `jobpress/inc/core-functions.php:99-111`; `jobpress/inc/Base/PublicEnqueue.php:16`; `jobpress/inc/Base/BodyClasses.php:23-28` |
| B3 | Taxonomy archives load `taxonomy-jobpress_{category,type}.php`, but neither file exists | `jobpress/inc/Base/TemplateLoader.php:40-42,58-61` |
| B4 | On the jobs page, `archive-jobpress.php` replaces the whole page, so `[jobpress]` and "Select Design" are ignored | `jobpress/inc/Base/TemplateLoader.php:53-67`; `jobpress/templates/archive-jobpress.php:31-39` |
| B5 | The textdomain is loaded from a URL, so bundled `.mo` files never load | `jobpress/inc/Base/LoadTextDomain.php:14` |
| B6 | No settings have a `sanitize_callback`. Colour values can inject CSS, and the design number goes into a URL without an int cast | `jobpress/inc/Base/PublicEnqueue.php:19-20,34-39`; `jobpress/inc/Pages/Admin/SettingsFormGeneral.php:36,51,65,79` |
| B7 | Output isn't escaped in the image `alt`, the CF7 shortcode title (a `"` in a job title breaks it), or `jobpress_open_positions_text` | `jobpress/templates/single-jobpress.php:63,199`; `jobpress/templates/listing/jobpress-listing-v1.php:42` (also v3, v5) |
| B8 | Designs v2 and v4 drop uncategorized jobs, the v1 title isn't a link, and the `orderby` value is invalid | `jobpress/templates/listing/jobpress-listing-v2.php:3-8`; `jobpress/templates/listing/jobpress-listing-v1.php:8,70` |
| B9 | `#primary` and `#main` IDs appear twice (the archive repeats the wrapper's) | `jobpress/templates/archive-jobpress.php:22-23`; `jobpress/templates/global/wrapper-start.php:37` |
| B10 | An empty jobs page shows nothing, because `jobpress_no_jobs_found` has no callback | `jobpress/templates/archive-jobpress.php:90` |
| B11 | CSS and the inline `:root` block load on every front-end page | `jobpress/inc/Base/PublicEnqueue.php:14-42` |
| B12 | `get_page_by_title` is deprecated, and Elementor registration depends on plugin load order | `jobpress/inc/Base/Activate.php:52`; `jobpress/inc/JobPressPluginInit.php:33-35` |
| B13 | The deadline is printed as raw `Y-m-d`, and `.distignore` ships `CLAUDE.md` and `dev-docs/` | `jobpress/templates/single-jobpress.php:125`; `jobpress/.distignore` |

**`origin/feat/search-and-filter` (not merged, 11 files, +520/−324)** adds:
- query vars `q`, `jobcategory` and `jobtype`
- a GET form with keyword, category and type fields (`templates/global/filter-and-search.php`)
- a `tax_query` and `s` search on the archive
- a "no jobs found" state, which fixes B10
- removal of the duplicate `templates/jobpress-template/` directory

Its limits:
- It only works on the archive and jobs page, not the shortcode or Elementor widget.
- It has no location, salary or remote filter, and no AJAX.
- The query var `q` is generic and may clash with other plugins.

## 3. Competitor feature matrix

**WPJM** = WP Job Manager 2.4.8 · **HireZoot** = wp-job-openings 4.1.0 · **SJB** = Simple Job Board 2.14.5 · **JBWP** = JobBoardWP 1.3.6 · **JobWP** = jobwp 2.5.0 · **easyjobs** = easyjobs 2.8.2

✅ free · 💲 paid add-on or Pro · ☁️ depends on a SaaS · ❌ missing · ⚠️ partial or buggy

| Feature | JobPress | WPJM | HireZoot | SJB | JBWP | JobWP | easyjobs |
|---|---|---|---|---|---|---|---|
| Built-in apply form | ❌ CF7 or text only | 💲 | ✅ | ✅ | ❌ mailto or URL | ✅ | ☁️ off-site |
| Form builder | ⚠️ uses CF7 | 💲 | 💲 | ✅ | ❌ | ⚠️ labels only | ☁️ |
| Resume upload protection | ❌ | 💲 | ✅ hashed names, nonce download | ✅ deny `.htaccess`, capability check | ❌ | ⚠️ public URLs | ☁️ |
| Applications admin + statuses | ❌ unused table | 💲 | ⚠️ inbox free, statuses 💲 | ✅ | ❌ | ⚠️ list only | ☁️ |
| Notes / ratings | ❌ | 💲 | 💲 | ⚠️ one notes field | ❌ | ❌ | ☁️ |
| Emails (admin / applicant) | ❌ | ⚠️ job emails only | ✅ both, plus digest | ✅ both, fixed templates | ⚠️ moderation only | ⚠️ admin only, no default recipient | ☁️ |
| Job expiry cron | ❌ | ✅ | ✅ | 💲 | ✅ | ⚠️ hides from listing only | ☁️ |
| Filled/closed state | ❌ | ✅ | ⚠️ filled 💲 | 💲 | ✅ | ⚠️ active/inactive | ☁️ |
| Salary fields | ⚠️ free text | ✅ | ❌ | ❌ | ⚠️ no yearly unit | ✅ | ☁️ |
| Remote flag | ❌ | ✅ | ❌ | ❌ | ✅ | ❌ | ☁️ |
| Google JobPosting schema | ❌ | ✅ | ⚠️ no salary or remote | 💲 | ⚠️ wrong employmentType | ⚠️ no remote | ❌ |
| AJAX search & filters | ❌ (branch: GET form) | ✅ | ✅ | 💲 | ✅ | ❌ GET form | ☁️ |
| Shareable filter URLs | ❌ (branch: ✅) | ✅ | ✅ | ✅ | ✅ | ✅ | ☁️ |
| Gutenberg blocks | ❌ | ❌ Classic block only | ✅ 1 block, some options 💲 | ⚠️ 1 basic block | ✅ 6 blocks | ❌ | ☁️ 4 blocks |
| Elementor | ⚠️ thin wrapper | ❌ | ✅ beta | 💲 | ❌ | ❌ | ☁️ |
| Setup wizard / auto pages | ⚠️ page ignores its shortcode | ✅ (adds marketplace pages) | ✅ | ⚠️ wizard doesn't auto-open | ⚠️ notice only | ❌ | ☁️ signup |
| Default terms | ❌ | ✅ | ✅ | ❌ | ✅ | ✅ | ☁️ |
| CAPTCHA / anti-spam | ❌ | ⚠️ submit form only | ✅ 3 CAPTCHAs + Akismet | ⚠️ nonce only; 💲 | ❌ | ⚠️ reCAPTCHA v2 on every page | ☁️ |
| GDPR consent + privacy exporter/eraser | ❌ | ⚠️ exporter only | ⚠️ consent only | ✅ | ❌ | ❌ consent 💲 | ☁️ |
| HR role | ❌ | ❌ | ✅ | ❌ | ❌ | 💲 | ☁️ |
| REST API | ⚠️ core, no meta | ✅ core + meta | ⚠️ core + expiry meta | ⚠️ core only | ⚠️ core only | ❌ | ❌ |
| Multilingual | ⚠️ B5 | ✅ | ✅ | ⚠️ shallow | ✅ | ⚠️ `.po` files only | ⚠️ Google Translate |
| RSS | ❌ | ✅ filterable | ⚠️ core feed | ⚠️ core feed | ⚠️ core feed | ❌ | ❌ |
| Uninstall cleanup | ❌ | ✅ opt-in | ✅ opt-in | ⚠️ options only | ✅ opt-in | ⚠️ options only | ❌ |

"Core feed" means the plugin offers only WordPress's default post-type feed; no job-specific feed code was found. easyjobs' `uninstall.php` is empty. It clears its options only when you disconnect it (`easyjobs/includes/class-easyjobs-helper.php:1152-1166`).

## 4. Recommended features

**Conventions for every item:**
- Each feature is a service class in `inc/Base/` with a `register()` method, added to `get_services()` in `inc/JobPressPluginInit.php`.
- Markup goes in `templates/` and is loaded with `jobpress_get_template()`, so themes can override it.
- Settings are `jobpress_*` options, each with a `sanitize_callback`.
- New filters are documented in `dev-docs/filters.md`.

### 0. Prerequisite: fix B1–B13 and merge the search branch

**What it is:** Fix the bugs in §2, then merge `feat/search-and-filter`. Before merging, rename `q` to `job_search` and render the form through a template function the shortcode can call.

**Why it comes first:** B1 is a security issue. B2–B4 make a fresh install look broken. Every later feature changes these same templates and settings.

**Implementation:**
- **B4:** let the jobs page render its own content, or have `archive-jobpress.php` use `jobpress_design_type`.
- **B3:** route both taxonomies to `archive-jobpress.php` with a query scoped to the term.
- **B5:** pass a path relative to `WP_PLUGIN_DIR` (`'jobpress/languages'`, i.e. `dirname( plugin_basename( <main plugin file> ) )`).
- **B6:** add `sanitize_hex_color` and `absint` callbacks.

### Must-have

**1. Built-in apply form and application storage**

**What it is:** A default form on every job with name, email, phone, cover letter, resume and consent fields. It submits over AJAX and links each application to its job.

**Why it fits:** A careers page exists to collect candidates. CF7 and mailto lose the link to the job and leave no record.

**Competitors:**
- HireZoot's free form is the cleanest. It checks a nonce and rejects expired jobs (`wp-job-openings/inc/class-awsm-job-openings-form.php:62-138,428-430,464-492`).
- SJB's free form rejects jobs that aren't published (`simple-job-board/includes/class-simple-job-board-ajax.php:151`).
- **What they get wrong:**
  - WPJM charges $79/yr for a form (`wp-job-manager/readme.txt:49-51`).
  - JBWP offers only mailto or a URL (`jobboardwp/templates/job/footer.php:24-77`).
  - easyjobs sends applicants off-site (`easyjobs/public/partials/default/details.php:187`).
  - JobWP processes submissions only in the single-job template, so `[jobwp_apply_form]` on any other page fails silently (`jobwp/front/view/single/header.php:39-71`). It also links applications to jobs by title (`jobwp/front/view/apply.php:28`).

**Implementation:**
- Add `inc/Base/ApplicationForm.php` and `templates/single/apply-form.php`.
- Handle submissions in `wp_ajax(_nopriv)_jobpress_apply`, which works wherever the form appears. Send the job ID as a hidden field.
- Keep CF7, instructions and a new external URL as other apply methods for each job.
- **Recommendation: use a private CPT `jobpress_application`** (20 characters, the maximum for a post type key). Settings: `public => false`, `show_in_menu` under Jobs, `create_posts => 'do_not_allow'`, `post_parent` set to the job, and fields stored as meta.
  - A CPT comes with list tables, search, trash, bulk actions, capabilities, privacy hooks and REST. HireZoot and SJB both use this approach (`wp-job-openings/inc/class-awsm-job-openings-core.php:150-167`; `simple-job-board/includes/posttypes/class-simple-job-board-post-type-applicants.php:96-118`).
  - JobWP's custom table shows what you lose without one. It has no pagination, filters or search (`jobwp/admin/cls-jobwp-admin.php:733-737`). It mixes `prefix` and `base_prefix` (`jobwp/inc/cls-jobwp-master.php:103` against `jobwp/core/job_application.php:39`). It also runs `SHOW COLUMNS` on every request (`jobwp/inc/cls-jobwp-master.php:129-142`).
- **Migration:** in `Activate::handle_update()`, drop `{prefix}jobpress_application` if it is empty, which it must be since nothing writes to it. Otherwise copy its rows into the CPT first. Then remove `CreateDbTable`.

**2. Applications inbox with statuses**

**What it is:**
- Columns for applicant, job, status and date, with filters by job and status.
- An unread badge in the menu and an "Applications (N)" column on the jobs list.
- A resume download that checks capabilities.

**Why it fits:** It gives a small company a lightweight applicant tracking system (ATS) without a SaaS.

**Competitors:**
- SJB's statuses are free (`simple-job-board/admin/meta-boxes/class-simple-job-board-meta-box-application-status.php:30-40`).
- HireZoot has a good inbox with a conversion column (`wp-job-openings/wp-job-openings.php:750-800`), but statuses are a locked Pro control (`wp-job-openings/admin/templates/meta/application-actions.php:31-44`).
- JobWP promises "review and action" (`jobwp/readme.txt:25`) but only lets you view and delete.

**Implementation:**
- Add `inc/Base/ApplicationsAdmin.php`.
- Store the stage in meta `_jobpress_stage`: New, Reviewing, Interview, Offer, Hired or Rejected. Make the list filterable through `jobpress_application_stages`.
- Add status tabs through `views_edit-jobpress_application`.

**3. Secure resume uploads**

**What it is:**
- Check the real file type with `wp_check_filetype_and_ext` against `jobpress_allowed_file_types` (pdf, doc and docx by default) and enforce a size limit.
- Store files in `uploads/jobpress-resumes/` with random names, a deny-all `.htaccess` and an `index.php`.
- Download only through an `admin-post` handler that checks a nonce and a capability.

**Why it fits:** Resumes are personal data. A leak is a GDPR problem and a reputation problem for the company.

**Competitors:**
- SJB's free design is the best model: a deny-by-default `.htaccess` (`simple-job-board/includes/class-simple-job-board-rewrite.php:86-129`) and a gated download handler (`simple-job-board/includes/class-simple-job-board-resume-download-handler.php:28-60`).
- **JobWP's resumes are publicly reachable.** There is no protection, and the admin links straight to the file (`jobwp/admin/view/application_list.php:15-16,64-68`). The extension check is case-sensitive and looks only at the file name (`jobwp/core/job_application.php:14-22`).
- HireZoot's preview sends the file URL to Microsoft's Office viewer (`wp-job-openings/admin/templates/meta/resume-preview.php:14-57`).

**Implementation:**
- Add `inc/Base/ResumeStorage.php`. Create the folder on activation and recreate it if it is missing.
- Delete the file when its application is deleted.
- Document that nginx ignores `.htaccess`, so random names are the fallback protection there.

**4. Email notifications**

**What it is:** An admin or HR email when someone applies, with the resume link (attachment optional), and an acknowledgement to the applicant. Both are on by default and fall back to `admin_email`. Each has an editable subject and body with tags such as `{applicant_name}`, `{job_title}` and `{company}`.

**Why it fits:** It works with zero configuration, and candidates expect a confirmation.

**Competitors:**
- HireZoot sends both emails and supports tags (`wp-job-openings/inc/class-awsm-job-openings-form.php:692-770`).
- SJB's templates can only be changed with filters (`simple-job-board/includes/class-simple-job-board-notifications.php:47-61`).
- **JobWP sends nothing on a fresh install,** because the recipient defaults to empty (`jobwp/core/general-settings.php:37-39`; `jobwp/front/view/single/header.php:31-35`).
- JBWP saves edited email bodies as files in the theme (`jobboardwp/includes/admin/class-settings.php:1275-1360`). JobPress should avoid this.

**Implementation:**
- Add `inc/Base/Notifications.php`, with templates in `templates/emails/`.
- Store subjects and bodies in `jobpress_email_*` options.
- Send at `shutdown`, as WPJM does (`wp-job-manager/includes/class-wp-job-manager-email-notifications.php:73-114`).

**5. Job expiry and a filled/closed state**

**What it is:**
- An hourly cron that moves jobs past their deadline to a public `jobpress_expired` status.
- A "Position filled" checkbox.
- A notice on expired or filled jobs that replaces the apply form.
- Settings to hide these jobs from listings, and `noindex` on them.

**Why it fits:** Old openings stop collecting applications without anyone remembering to close them.

**Competitors:**
- WPJM (`wp-job-manager/includes/class-wp-job-manager-post-types.php:1193-1272,1860-1930`), HireZoot (`wp-job-openings/wp-job-openings.php:939-990`) and JBWP (`jobboardwp/includes/common/class-job.php:1174-1228`) all have it for free.
- SJB charges for it (`simple-job-board/readme.txt:65,75`).
- JobWP's "hide when the deadline is over" setting also hides jobs with no deadline, and expired jobs still accept applications (`jobwp/front/view/listing/header.php:84-91`).

**Implementation:**
- Add `inc/Base/JobLifecycle.php`. Reuse `jobpress_apply_deadline`, where empty means the job never expires.
- Expire a job on save if its deadline has already passed.
- Clear the cron on deactivation.

**6. Structured job fields and JobPosting schema**

**What it is:**
- Salary minimum, maximum, currency and unit (HOUR to YEAR).
- On-site, hybrid or remote.
- Locality, region and country.
- A Google employment-type term meta on `jobpress_type`.
- JSON-LD output, with all meta registered for REST through `register_post_meta`.

**Why it fits:** Google for Jobs is free traffic, and the schema is built from fields the admin already fills in.

**Competitors:**
- WPJM's schema is the most complete. It uses `TELECOMMUTE` and suppresses the schema for filled jobs (`wp-job-manager/includes/wp-job-manager-template.php:333-511`).
- **JBWP outputs term names such as "Full-time" instead of `FULL_TIME`.** It has no remote support and no yearly salary unit (`jobboardwp/includes/common/class-job.php:956-962,986-994`).
- JobWP upper-cases term names (`jobwp/front/view/single/structured-data.php:31-42`), which works only when the names happen to match. It has no remote support.
- HireZoot has no `baseSalary` and no remote support (`wp-job-openings/wp-job-openings.php:2553-2586`), and it has no salary field at all.
- easyjobs outputs no schema.

**Implementation:**
- Add `inc/Base/StructuredData.php` and extend `JobsMetaBox`.
- Keep the old free-text salary as a display-only note.
- Fill `hiringOrganization` from the company profile (item 7).
- Skip the schema for expired, filled and protected jobs.
- Add a `jobpress_job_structured_data` filter.

**7. Setup wizard, company profile and default terms**

**What it is:** A skippable wizard that opens once after activation, through a transient redirect. It has three steps:
1. Company name, logo, website and HR email.
2. Create a careers page or pick an existing one.
3. An optional sample job.

Activation seeds the job types Full Time, Part Time, Contract, Temporary and Internship, each mapped to Google's values.

**Why it fits:** This is the core "minimal effort" feature. The company details are entered once and reused in emails, the schema and the page header.

**Competitors:**
- HireZoot's 3-field wizard sets the notification addresses (`wp-job-openings/admin/class-awsm-job-openings-info.php:32-114`).
- WPJM's wizard creates "Post a Job" and "Dashboard" pages a single company doesn't need (`wp-job-manager/includes/admin/class-wp-job-manager-setup.php:104-145`).
- WPJM and JBWP store company data on each job.
- JBWP's "Create Pages" notice adds marketplace pages (`jobboardwp/includes/admin/class-notices.php:217-270`).
- WPJM seeds typed job terms (`wp-job-manager/includes/class-wp-job-manager-install.php:152-199`).

**Implementation:**
- Add `inc/Pages/Admin/SetupWizard.php` and `templates/task-pages/setup-*.php`.
- Store the details in the `jobpress_company_*` options.
- Replace `get_page_by_title` with the stored page ID.

**8. Spam protection and privacy compliance**

**What it is:**
- A honeypot and a time-trap, always on.
- Optional Turnstile or reCAPTCHA v3, with the script loaded only where the form is.
- A consent checkbox.
- Registration with `wp_privacy_personal_data_exporters` and `wp_privacy_personal_data_erasers`, matched by email and including resumes.
- Suggested privacy-policy text.

**Why it fits:** Storing applications makes the company a data controller, so it needs to stay compliant without extra plugins.

**Competitors:**
- SJB has an exporter and an eraser (`simple-job-board/includes/class-simple-job-board-privacy-eraser.php:25-125`).
- HireZoot has CAPTCHAs and Akismet (`wp-job-openings/inc/class-awsm-job-openings-third-party.php:33-95`) but no privacy hooks.
- WPJM has no eraser.
- JobWP loads reCAPTCHA on every page (`jobwp/front/cls-jobwp-front.php:56`).

**Implementation:** Add `inc/Base/AntiSpam.php` and `inc/Base/Privacy.php`. With the CPT from item 1, erasing an application is `wp_delete_post` plus deleting the file.

### Should-have

**9. AJAX search and filters with shareable URLs**

**What it is:** Keyword, category, type, location and remote filters that refresh over AJAX and update the URL with `history.replaceState`. The branch's GET form stays as the fallback when JavaScript is off.

**Why it fits:** It pays off once a company has a dozen or more openings, and the fallback means it works on any site.

**Competitors:**
- HireZoot (`wp-job-openings/assets/js/public/job-listings.js:133-151`) and JBWP (`jobboardwp/assets/frontend/js/jobs.js:87`) keep filters in the URL.
- WPJM's filters require JavaScript (`wp-job-manager/templates/job-filters.php:87`).
- SJB charges for AJAX search.

**Implementation:** Add `inc/Base/JobSearch.php`, with one query builder (plus a `jobpress_job_query_args` filter) shared by the archive, the shortcode and the blocks. Serve results from a `jobpress/v1/jobs` REST route.

**10. Gutenberg blocks and block-theme support**

**What it is:** Job list, job details and apply form, and career page header blocks. Single jobs are injected through `the_content`, so block themes work.

**Why it fits:** The local site already has Twenty Twenty-Four and Twenty Twenty-Five, and JobPress's `get_header()` templates don't work with them.

**Competitors:**
- JBWP has six server-rendered blocks and supports block themes (`jobboardwp/includes/common/class-blocks.php:27-158`; `jobboardwp/includes/frontend/class-templates.php:345-389`).
- WPJM locks its editor to the Classic block (`wp-job-manager/includes/class-wp-job-manager-post-types.php:509-510`).
- HireZoot puts its layouts and multi-select filters behind Pro (`wp-job-openings/inc/class-awsm-job-openings-block.php:37-51`).

**Implementation:**
- Add a `Blocks` service with dynamic `block.json` blocks that reuse `templates/listing/*`. This needs a small `@wordpress/scripts` build.
- Register Elementor on `elementor/loaded` (fixes B12).

**11. Simple form builder**

**What it is:** A global field repeater (text, textarea, email, phone, select, checkbox and file fields), with a per-job override checkbox.

**Why it fits:** Most companies use one form with the occasional extra question.

**Competitors:**
- SJB has this design for free (`simple-job-board/admin/settings/class-simple-job-board-settings-application-form-fields.php:80-110`). Its resume field is always required, and there is no file field type (`simple-job-board/templates/v2/single-jobpost/job-application.php:295-303`).
- HireZoot (`wp-job-openings/admin/class-awsm-job-openings-settings.php:113-120`) and WPJM charge for a form builder.

**Implementation:** Store the global form in `jobpress_form_fields` and per-job overrides in `_jobpress_form_fields`. The validator from item 1 reads the same schema.

**12. HR role and capabilities**

**What it is:** Custom capabilities on both CPTs, plus a `jobpress_hr` role limited to jobs and applications.

**Why it fits:** Recruiters can get access without being made administrators.

**Competitors:** HireZoot has this for free (`wp-job-openings/inc/class-awsm-job-openings-core.php:271-277`). JobWP charges for it. WPJM and JBWP have only marketplace "employer" roles.

**Implementation:** Add the role and capabilities in `Activate`, remove them on uninstall, and check them in `ApplicationsAdmin` and the resume handler.

**13. Notes, ratings and status emails**

**What it is:** Timestamped notes stored as comments of type `jobpress_note`, a 1–5 rating, and optional applicant emails when the status changes.

**Competitors:** WPJM and HireZoot charge for notes and ratings. SJB's notes are a single editor field (`simple-job-board/includes/class-simple-job-board-applicants.php:189-197`), and it sends no status emails (`simple-job-board/includes/class-simple-job-board-notifications.php:34-141`).

**Implementation:** Extend `ApplicationsAdmin` and `Notifications`.

**14. Better shortcode behaviour**

**What it is:** Attributes for `design`, `category`, `type`, `limit`, `pagination` and `filters`, support for `jobpress_jobs_per_page`, and assets loaded only where they are needed. The readme already claims some of these (`jobpress/readme.txt:49-50`).

**Competitor:** WPJM's `[jobs]` shortcode has more than 20 attributes and reads URL parameters (`wp-job-manager/includes/class-wp-job-manager-shortcodes.php:195-228,260-275`).

**Implementation:** Extend `JobListShortcode` to use the query builder from item 9.

**15. Multilingual support and uninstall cleanup**

**What it is:** A `wpml-config.xml` and Polylang-aware page IDs, plus an opt-in "delete all data" setting.

**Competitors:** WPJM (`wp-job-manager/wpml-config.xml:1-20`) and JBWP (`jobboardwp/includes/integrations/class-init.php:32-44`; `jobboardwp/uninstall.php:18-90`).

**Implementation:** Rewrite `uninstall.php` to remove options, posts, terms, the resume folder, the role and the cron when `jobpress_delete_data` is set.

### Nice-to-have

16. **Filterable RSS job feed,** modelled on WPJM's (`wp-job-manager/includes/class-wp-job-manager-post-types.php:714-762`).
17. **REST endpoints for applications** that check capabilities, so other tools can integrate.
18. **CSV export of applications.** Everyone else charges for this, and JobWP's export method is an empty stub (`jobwp/admin/cls-jobwp-admin.php:758-766`).
19. **View counts and conversion rate** per job, stored as post meta (`wp-job-openings/wp-job-openings.php:750-800,1317-1333`).
20. **Daily applications digest** (`wp-job-openings/wp-job-openings.php:1009-1087`).
21. **"Duplicate job"** action in the admin. WPJM has it only on the front end (`wp-job-manager/includes/wp-job-manager-functions.php:1801-1855`).
22. **Employer-branding section:** cover image, intro, "Life at {Company}" gallery and benefits. Copy easyjobs' landing page (`easyjobs/public/partials/default/landing.php:12-190`), but store everything locally.
23. **Quick-apply modal** on listings (`simple-job-board/includes/class-simple-job-board-ajax.php:323-363`).
24. **Auto-delete** applications after N months.

## 5. Not recommended

| Feature | Seen in | Reason |
|---|---|---|
| Front-end job submission and moderation | WPJM (`wp-job-manager/includes/forms/class-wp-job-manager-form-submit-job.php:108-120`), JBWP | Staff post jobs in wp-admin. Front-end posting only adds spam and moderation work. |
| Employer dashboard, guest submitters, employer role | WPJM (`wp-job-manager/includes/class-job-dashboard-shortcode.php:193-330`), JBWP | These only make sense with several employers. The HR role (item 12) covers internal staff. |
| A company field on each job, or a company taxonomy | WPJM, JBWP, SJB 💲, JobWP 💲 | There is only one company. A global profile (item 7) is simpler. |
| Paid listings and packages | WPJM 💲 | Nobody pays to post jobs on a company's own site. |
| Candidate accounts, resume database, job alerts | WPJM 💲, SJB 💲 | They mean managing accounts and mailing lists. RSS (item 16) covers alerts. |
| SaaS dependence | easyjobs (`easyjobs/includes/class-easyjobs-api.php:602-689`) | Data leaves the site, an account is required, and the free plan allows one published job (`easyjobs/admin/includes/class-easyjobs-admin-jobs.php:1010`). Being fully local is JobPress's selling point. |
| Geocoding and radius search | WPJM, JBWP | They need a Google API key and billing. Structured location fields are enough. |
| Kanban board, assessments, messaging | easyjobs | They need a heavy React UI. Statuses and filters are enough for a small team. |
| Third-party resume preview | HireZoot | It leaks resume URLs to Microsoft. |
| More listing CSS files | JobPress | Merge the five into one base stylesheet with small overrides when the blocks are built. |

## 6. Suggested roadmap

| Release | Theme | Items |
|---|---|---|
| **2.2: Stabilise** | Make what exists work | 0, 5, 6, 7, 14 |
| **3.0: Applications** | Built-in hiring with no add-ons | 1 (CPT, drop the table), 2, 3, 4, 8, 12 |
| **3.1: Modern editing** | Block themes, builders and teams | 9, 10, 11, 13, 15 |
| **3.2: Polish** | Based on feedback | 16–24 |

Release 2.2 has no breaking changes. Release 3.0 changes the data model, adding a CPT and capabilities and dropping the table, so it is a major version.

## 7. Sources

These are the local copies in `/Users/welabs/Sites/jobpress/wp-content/plugins/`:

| Folder | Plugin | Version |
|---|---|---|
| `jobpress` | JobPress (`feat/revamp`, which is `develop` plus `CLAUDE.md`) | 2.1.6 |
| `wp-job-manager` | WP Job Manager | 2.4.8 |
| `wp-job-openings` | HireZoot (formerly WP Job Openings) | 4.1.0 |
| `simple-job-board` | Simple Job Board | 2.14.5 |
| `jobboardwp` | JobBoardWP | 1.3.6 |
| `jobwp` | JobWP (free build) | 2.5.0 |
| `easyjobs` | easyjobs | 2.8.2 |

All citations are `folder/path:line` in these local copies. They come from read-only research notes that checked each readme claim against the code. Paid (💲) items are based on readmes and locked upsell screens, because the free builds don't include paid code.

Re-checked directly against the code while writing this report:
- JBWP and JobWP `employmentType` handling
- JobPress bugs B1 and B2
- the full paths of the cited files

The `feat/search-and-filter` details come from `git diff develop..origin/feat/search-and-filter`.
