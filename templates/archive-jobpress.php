<?php
/**
 * The Template for displaying job archives
 *
 * This template can be overridden by copying it to yourtheme/jobpress/archive-jobpress.php.
 *
 * @package JobPress
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

get_header( 'jobpress' );

/**
 * Hook: jobpress_before_main_content.
 */
do_action( 'jobpress_before_main_content' );
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">  
        <?php
        /**
         * Hook: jobpress_job_loop_header.
         */
        do_action( 'jobpress_job_loop_header' );

        // Get search and filter parameters
        $keyword = trim( (string) get_query_var( 'q' ) );
        $jobcategory = (string) get_query_var( 'jobcategory' );
        $jobtype = (string) get_query_var( 'jobtype' );

        // Query for jobs with proper pagination support
        $jobs_per_page = get_option( 'jobpress_jobs_per_page', 10 );
        $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

        // Build query arguments
        $query_args = array(
            'post_type'      => 'jobpress',
            'post_status'    => 'publish',
            'posts_per_page' => $jobs_per_page,
            'paged'          => $paged,
        );

        // Add keyword search
        if ( $keyword !== '' ) {
            $query_args['s'] = sanitize_text_field( $keyword );
        }

        // Add taxonomy queries
        $tax_query = array();
        if ( $jobcategory !== '' ) {
            $tax_query[] = array(
                'taxonomy' => 'jobpress_category',
                'field'    => 'slug',
                'terms'    => sanitize_title( $jobcategory ),
            );
        }

        if ( $jobtype !== '' ) {
            $tax_query[] = array(
                'taxonomy' => 'jobpress_type',
                'field'    => 'slug',
                'terms'    => sanitize_title( $jobtype ),
            );
        }

        if ( ! empty( $tax_query ) ) {
            $query_args['tax_query'] = $tax_query;
        }
        
        $jobs_query = new WP_Query( $query_args );

        if ( $jobs_query->have_posts() ) {
            
            // Setup the loop with query data
            jobpress_setup_loop( array(
                'is_shortcode' => false,
                'is_paginated' => true,
                'total'        => $jobs_query->found_posts,
                'total_pages'  => $jobs_query->max_num_pages,
                'per_page'     => $jobs_per_page,
                'current_page' => $paged,
            ) );
            
            /**
             * Hook: jobpress_before_jobs_loop.
             *
             */
            do_action( 'jobpress_before_jobs_loop' );
        
            jobpress_jobs_loop_start();
        
            while ( $jobs_query->have_posts() ) {
                $jobs_query->the_post();
    
                /**
                 * Hook: jobpress_job_loop.
                 */
                do_action( 'jobpress_job_loop' );

                jobpress_get_template_part( 'content', 'job' );
            }
        
            jobpress_jobs_loop_end();
        
            /**
             * Hook: jobpress_after_jobs_loop.
             *
             * @hooked jobpress_pagination - 10
             * @hooked jobpress_reset_loop - 999
             */
            do_action( 'jobpress_after_jobs_loop' );

            // Reset post data
            wp_reset_postdata();
        } else {
            /**
             * Hook: jobpress_no_jobs_found.
             *
             * @hooked jobpress_no_jobs_found - 10
             */
            do_action( 'jobpress_no_jobs_found' );
        }
        ?>
    </main>
</div>
<?php
/**
 * Hook: jobpress_after_main_content.
 */
do_action( 'jobpress_after_main_content' );

get_footer( 'jobpress' );
