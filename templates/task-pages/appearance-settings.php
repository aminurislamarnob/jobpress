<h1><?php esc_html_e('JobPress Settings', 'jobpress'); ?></h1>
<div class="jobpress-container">
    <?php require_once JOBPRESS_PLUGIN_PATH . 'templates/task-pages/nav.php'; ?>
    <div class="jobpress-right-content">
        <?php settings_errors(); ?>
        <form action="options.php" method="POST">
            <?php do_settings_sections('jobpress_appearance_section');?>
            <?php settings_fields('jobpress_appearance_settings_section');?>
            <?php submit_button();?>
        </form>
        <script>
        jQuery(document).ready(function($){
            //Color Picker
            $('.color-field').wpColorPicker();
        });
        </script>
    </div>
</div>