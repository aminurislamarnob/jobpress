<?php
/**
 * JobPress Settings Navigation Template
 * 
 * @package JobPress
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Get current page safely using WordPress functions
$current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

// The docs section for the current settings screen.
$docs_pages = array(
    'jobpress_settings'            => 'settings#general',
    'jobpress_appearance_settings' => 'settings#appearance',
    'jobpress_shortcode'           => 'shortcode',
);
$docs_url = jobpress_get_docs_url( isset( $docs_pages[ $current_page ] ) ? $docs_pages[ $current_page ] : 'settings' );
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
    <p class="jobpress-docs-link">
        <span class="dashicons dashicons-book" aria-hidden="true"></span>
        <?php esc_html_e( 'Need help?', 'jobpress' ); ?>
        <a href="<?php echo esc_url( $docs_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Read the docs', 'jobpress' ); ?></a>
    </p>
</div>