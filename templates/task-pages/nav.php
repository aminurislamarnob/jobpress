<?php
$jobpress_activated_page = sanitize_text_field($_REQUEST['page']);
?>
<div class="jobpress-left-nav">
    <ul>
        <li><a href="<?php echo esc_url(admin_url() . 'edit.php?post_type=jobpress&page=jobpress_settings'); ?>" class="<?php echo esc_attr(($jobpress_activated_page == 'jobpress_settings') ? ' active' : ''); ?>"><span class="dashicons dashicons-admin-generic"></span><?php esc_html_e('General', 'jobpress'); ?></a></li>
        <li><a href="<?php echo esc_url(admin_url() . 'edit.php?post_type=jobpress&page=jobpress_shortcode'); ?>" class="<?php echo esc_attr(($jobpress_activated_page == 'jobpress_shortcode') ? 'active' : ''); ?>"><span class="dashicons dashicons-shortcode"></span><?php esc_html_e('Shortcodes', 'jobpress'); ?></a></li>
        <li><a href="<?php echo esc_url(admin_url() . 'edit.php?post_type=jobpress&page=jobpress_appearance_settings'); ?>" class="<?php echo esc_attr(($jobpress_activated_page == 'jobpress_appearance_settings') ? ' active' : ''); ?>"><span class="dashicons dashicons-admin-appearance"></span><?php esc_html_e('Appearance', 'jobpress'); ?></a></li>
    </ul>
</div>