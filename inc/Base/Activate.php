<?php
namespace JobPressInc\Base;

class Activate
{
    public static function activate() {
        // Create/Update Jobs page
        self::create_jobs_page();

        // Rewrite rules are flushed on the next request's init (see Flush), once the
        // post type and taxonomies are registered; flushing here would drop job URLs.
        Flush::add_flush_rewrite_rules_flag();
    }

    /**
     * Handle plugin updates and ensure jobs page exists.
     */
    public static function handle_update() {
        // Check if this is an update
        $current_version = get_option( 'jobpress_version', '0.0.0' );
        $plugin_version = defined( 'JOBPRESS_VERSION' ) ? JOBPRESS_VERSION : '0.0.0';
        
        if ( version_compare( $current_version, $plugin_version, '<' ) ) {
            // This is an update - ensure jobs page exists
            self::create_jobs_page();
            
            // Update version
            update_option( 'jobpress_version', $plugin_version );
            
            // Flush rewrite rules for new features
            Flush::add_flush_rewrite_rules_flag();
        }
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

        // Check by title if page exists (get_page_by_title() is deprecated since WP 6.2)
        if ( ! $page_exists ) {
            $pages_by_title = get_posts( array(
                'post_type'              => 'page',
                'post_status'            => 'any',
                'title'                  => __( 'Jobs Listing', 'jobpress' ),
                'numberposts'            => 1,
                'update_post_term_cache' => false,
                'update_post_meta_cache' => false,
            ) );
            $page_exists = $pages_by_title ? $pages_by_title[0] : null;
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