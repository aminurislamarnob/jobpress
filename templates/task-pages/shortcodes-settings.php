<h1><?php esc_html_e('JobPress Settings', 'jobpress'); ?></h1>
<div class="jobpress-container">
    <?php require_once JOBPRESS_PLUGIN_PATH . 'templates/task-pages/nav.php'; ?>
    <div class="jobpress-right-content">
        <h2><?php esc_html_e('Available Shortcode', 'jobpress'); ?></h2>
        <h4><?php esc_html_e('Job List Page Shortcode:', 'jobpress'); ?> <code>[jobpress title="Custom Title" subtitle="Custom Subtitle" show_positions="yes/no"]</code></h4>
        <h4><?php esc_html_e('Available Attributes:', 'jobpress'); ?></h4>
        <ul>
            <li><strong>title:</strong> <?php esc_html_e('Custom title for the job list page', 'jobpress'); ?></li>
            <li><strong>subtitle:</strong> <?php esc_html_e('Custom subtitle for the job list page', 'jobpress'); ?></li>
            <li><strong>show_positions:</strong> <?php esc_html_e('Show/hide the number of open positions under the title section. Default is `yes`. You can set it to `no` to hide the number of open positions.', 'jobpress'); ?></li>
        </ul>
    </div>
</div>