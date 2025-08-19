<?php
namespace JobPressInc\Base;

class Activate
{
    public static function activate() {
        // Create/Update Jobs page
        self::create_jobs_page();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Create Jobs page on activation.
     */
    private static function create_jobs_page() {
        $jobs_page_id = get_option( 'jobpress_jobs_page_id' );
        $page_exists = false;

        if ( $jobs_page_id ) {
            $page_exists = get_post( $jobs_page_id );
        }

        // Check by slug if page exists
        if ( ! $page_exists ) {
            $page_exists = get_page_by_path( 'jobs-listing' );
        }

        // Check by title if page exists
        if ( ! $page_exists ) {
            $page_exists = get_page_by_title( __( 'Jobs Listing', 'jobpress' ) );
        }

        if ( $page_exists ) {
            if ( ! $jobs_page_id ) {
                update_option( 'jobpress_jobs_page_id', $page_exists->ID );
            }
            return;
        }

        // Create page if it doesn't exist
        $page_data = array(
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'post_author'    => get_current_user_id(),
            'post_name'      => 'jobs-listing',
            'post_title'     => __( 'Jobs Listing', 'jobpress' ),
            'post_content'   => '[jobpress]',
            'post_parent'    => 0,
            'comment_status' => 'closed'
        );

        $jobs_page_id = wp_insert_post( $page_data );

        update_option( 'jobpress_jobs_page_id', $jobs_page_id );
        update_option( 'jobpress_jobs_page_version', JOBPRESS_VERSION );
    }
}