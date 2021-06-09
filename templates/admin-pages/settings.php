<h1>JobPress Settings</h1>
<div class="jobpress-container">
    <div class="jobpress-left-nav">
        <ul>
            <li><a href="<?php echo admin_url(); ?>edit.php?post_type=jobpress&page=jobpress_settings" class="active"><span class="dashicons dashicons-admin-generic"></span>General</a></li>
            <!-- <li><a href="#"><span class="dashicons dashicons-admin-page"></span>Page Setting</a></li> -->
            <li><a href="<?php echo admin_url(); ?>edit.php?post_type=jobpress&page=jobpress_shortcode"><span class="dashicons dashicons-shortcode"></span>Shortcodes</a></li>
            <li><a href="<?php echo admin_url(); ?>edit.php?post_type=jobpress&page=jobpress_appearance_settings"><span class="dashicons dashicons-admin-appearance"></span>Appearance</a></li>
        </ul>
    </div>
    <div class="jobpress-right-content">
        <?php settings_errors(); ?>
        <form action="options.php" method="POST">
            <?php do_settings_sections('jobpress_general_section');?>
            <?php settings_fields('jobpress_general_settings_section');?>
            <?php submit_button();?>
        </form>
    </div>
</div>