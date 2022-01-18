<?php
$jobpress_activated_page = $_REQUEST['page'];
?>
<div class="jobpress-left-nav">
    <ul>
        <li><a href="<?php echo admin_url(); ?>edit.php?post_type=jobpress&page=jobpress_settings" class="<?php echo ($jobpress_activated_page == 'jobpress_settings') ? ' active' : ''; ?>"><span class="dashicons dashicons-admin-generic"></span>General</a></li>
        <li><a href="<?php echo admin_url(); ?>edit.php?post_type=jobpress&page=jobpress_shortcode" class="<?php echo ($jobpress_activated_page == 'jobpress_shortcode') ? 'active' : ''; ?>"><span class="dashicons dashicons-shortcode"></span>Shortcodes</a></li>
        <li><a href="<?php echo admin_url(); ?>edit.php?post_type=jobpress&page=jobpress_appearance_settings" class="<?php echo ($jobpress_activated_page == 'jobpress_appearance_settings') ? ' active' : ''; ?>"><span class="dashicons dashicons-admin-appearance"></span>Appearance</a></li>
    </ul>
</div>