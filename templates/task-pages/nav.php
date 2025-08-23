<?php
/**
 * JobPress Settings Navigation Template
 * 
 * @package JobPress
 * @since 1.0.0
 */

// Get current page safely using WordPress functions
$current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
?>
<div class="jobpress-left-nav">
    <ul>
        <li>
            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=jobpress&page=jobpress_settings' ) ); ?>" 
               class="<?php echo esc_attr( ( $current_page === 'jobpress_settings' ) ? 'active' : '' ); ?>">
                <span class="dashicons dashicons-admin-generic"></span>
                <?php esc_html_e( 'General', 'jobpress' ); ?>
            </a>
        </li>
        <li>
            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=jobpress&page=jobpress_appearance_settings' ) ); ?>" 
               class="<?php echo esc_attr( ( $current_page === 'jobpress_appearance_settings' ) ? 'active' : '' ); ?>">
                <span class="dashicons dashicons-admin-appearance"></span>
                <?php esc_html_e( 'Appearance', 'jobpress' ); ?>
            </a>
        </li>
        <li>
            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=jobpress&page=jobpress_shortcode' ) ); ?>" 
               class="<?php echo esc_attr( ( $current_page === 'jobpress_shortcode' ) ? 'active' : '' ); ?>">
                <span class="dashicons dashicons-shortcode"></span>
                <?php esc_html_e( 'Shortcodes', 'jobpress' ); ?>
            </a>
        </li>
    </ul>
</div>