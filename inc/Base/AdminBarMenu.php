<?php
namespace JobPressInc\Base;

/**
 * Admin Bar Menu Class
 */
class AdminBarMenu {

    /**
     * Register hooks
     */
    public function register() {
        add_action( 'admin_bar_menu', array( $this, 'add_jobs_menu_item' ), 35 );
    }

    /**
     * Add Jobs menu item to admin bar
     *
     * @param \WP_Admin_Bar $admin_bar Admin bar instance.
     */
    public function add_jobs_menu_item( $admin_bar ) {
        if ( ! current_user_can( 'edit_posts' ) ) {
            return;
        }

        $jobs_archive_url = jobpress_get_jobs_archive_page_permalink();

        if ( ! $jobs_archive_url ) {
            return;
        }

        $admin_bar->add_menu( array(
            'id'     => 'jobpress-archive',
            'parent' => 'site-name',
            'title'  => esc_html__( 'Visit Jobs', 'jobpress' ),
            'href'   => esc_url( $jobs_archive_url ),
            'meta'   => array(
                'title' => esc_attr__( 'Visit Jobs', 'jobpress' )
            )
        ) );
    }
}
