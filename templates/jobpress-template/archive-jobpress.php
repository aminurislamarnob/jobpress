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

        // Query for jobs with proper pagination support
        $jobs_per_page = get_option( 'jobpress_jobs_per_page', 10 );
        $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
        
        $jobs_query = new WP_Query( array(
            'post_type'      => 'jobpress',
            'post_status'    => 'publish',
            'posts_per_page' => $jobs_per_page,
            'paged'          => $paged,
        ) );

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
            do_action( 'jobpress_before_jobs_loop', $jobpress_design_type );
        
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
