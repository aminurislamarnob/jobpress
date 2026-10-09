<h1><?php esc_html_e('JobPress Settings', 'jobpress'); ?></h1>
<div class="jobpress-container">
    <?php require_once JOBPRESS_PLUGIN_PATH . 'templates/task-pages/nav.php'; ?>
    <div class="jobpress-right-content">
        <?php settings_errors(); ?>
        <div class="jobpress-shortcode-content">
            <h2><?php esc_html_e('Available Shortcode', 'jobpress'); ?></h2>
            <h4><?php esc_html_e('Job List Shortcode:', 'jobpress'); ?></h4>
            <code>[jobpress]</code>
            <p><?php esc_html_e( 'Shows a job listing using the settings below. Each listing can override them with attributes, and the JobPress Jobs block and Elementor widget offer the same options. A missing or empty attribute uses the setting.', 'jobpress' ); ?></p>
            <code>[jobpress design="5" category="engineering" per_page="6" show_view_all="yes"]</code>
            <h4><?php esc_html_e('Available Attributes:', 'jobpress'); ?></h4>
            <ul>
                <li><strong>design</strong> &mdash; <?php esc_html_e( '1 to 5, the listing design.', 'jobpress' ); ?></li>
                <li><strong>title, subtitle</strong> &mdash; <?php esc_html_e( 'Header text.', 'jobpress' ); ?></li>
                <li><strong>show_title, show_subtitle, show_positions</strong> &mdash; <?php esc_html_e( 'yes or no, to show or hide the header parts.', 'jobpress' ); ?></li>
                <li><strong>show_category, show_type, show_location, show_experience, show_vacancy, show_deadline</strong> &mdash; <?php esc_html_e( 'yes or no, to show or hide job card details.', 'jobpress' ); ?></li>
                <li><strong>button_text</strong> &mdash; <?php esc_html_e( 'Apply button text.', 'jobpress' ); ?></li>
                <li><strong>show_search</strong> &mdash; <?php esc_html_e( 'yes or no, a search bar that opens the Jobs Page results.', 'jobpress' ); ?></li>
                <li><strong>show_view_all, view_all_text</strong> &mdash; <?php esc_html_e( 'A link to the Jobs Page under the listing, and its text.', 'jobpress' ); ?></li>
                <li><strong>per_page</strong> &mdash; <?php esc_html_e( 'Number of jobs to show (per category in the grouped designs). All by default.', 'jobpress' ); ?></li>
                <li><strong>category, type</strong> &mdash; <?php esc_html_e( 'Comma-separated category or job type slugs to show.', 'jobpress' ); ?></li>
                <li><strong>include, exclude</strong> &mdash; <?php esc_html_e( 'Comma-separated job IDs to show only, or to leave out.', 'jobpress' ); ?></li>
                <li><strong>orderby, order</strong> &mdash; <?php esc_html_e( 'date, title, menu_order or rand; ASC or DESC.', 'jobpress' ); ?></li>
                <li><strong>brand_color, hover_color, heading_color, secondary_color, content_color, border_color</strong> &mdash; <?php esc_html_e( 'Hex colors overriding the Appearance colors.', 'jobpress' ); ?></li>
            </ul>
        </div>
        <form action="options.php" method="POST">
            <?php do_settings_sections('jobpress_shortcode_section');?>
            <?php settings_fields('jobpress_shortcode_settings_section');?>
            <?php submit_button();?>
        </form>
    </div>
</div>
