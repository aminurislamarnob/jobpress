=== JobPress - Your Company Job Board & Career Page ===
Contributors: aminurislam01
Tags: jobpress, job board, careers, job listing, job manager, job portal, job openings, jobs
Requires at least: 5.6
Tested up to: 6.8.2
Requires PHP: 7.4
Stable tag: 2.1.4
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
* Job list page with 5 different layout styles
* Well organized job details page
* ⚡ Contact form 7 support for job applications
* 🎨 Change branding color easily
* ⚙️ Cool plugin settings panel for admin
* 🗺️ You can also add location google map iFrame embed code
* ⚡ Elementor widget support for easy page building

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
* You can display job listings anywhere using shortcodes.  
* The `design` attribute matches the **Select Design** field option.

**👉 Elementor Integration**
Prefer a visual builder?  
JobPress includes a **dedicated Elementor Addon** to display job lists without shortcodes. Simply drag and drop the JobPress widget onto your page.

**👉 Quick Access**
From the WordPress Admin Bar, you’ll get **quick access** to:
* Job Archive / Default Job Listing Page
* JobPress settings

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
Simply drag and drop the "JobPress Jobs" widget from the Elementor editor. You can customize the title, subtitle, and toggle position count display. The design style follows your global JobPress settings.

= How to use the JobPress shortcode? =
You can use the JobPress shortcode in two ways:

1. In WordPress Posts/Pages:
```
[jobpress] // Basic usage with default settings
[jobpress title="We're Hiring!" subtitle="Join Our Team"] // With custom title/subtitle
[jobpress show_positions="no"] // Hide position counts
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

Available Shortcode Attributes:
* title - Custom title for the job listing page (default: empty)
* subtitle - Custom subtitle for the job listing page (default: empty)
* show_positions - Show/hide number of open positions (default: 'yes', set to 'no' to hide)

Example with all attributes:
```
[jobpress title="Join Our Team" subtitle="Explore Amazing Opportunities" show_positions="yes"]
```

== Changelog ==

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
