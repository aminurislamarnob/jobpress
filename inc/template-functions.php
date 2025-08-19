<?php
/**
 * JobPress Template Functions
 *
 * Functions used in the template files to output content.
 *
 * @package JobPress
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'jobpress_get_job_loop_header' ) ) {
    /**
     * Output the start of a job loop. By default this is a UL.
     */
    function jobpress_get_job_loop_header() {
        jobpress_get_template( 'loop/header.php' );
    }
}

if ( ! function_exists( 'jobpress_page_title' ) ) {
    /**
     * Page Title function.
     *
     * @param bool $echo Echo the string or return it.
     * @return string
     */
    function jobpress_page_title( $echo = true ) {
        if ( is_search() ) {
            /* translators: %s: search query */
            $page_title = sprintf( __( 'Search results: %s', 'jobpress' ), get_search_query() );

            if ( get_query_var( 'paged' ) ) {
                /* translators: %s: page number */
                $page_title .= sprintf( __( '&nbsp;&ndash; Page %s', 'jobpress' ), get_query_var( 'paged' ) );
            }
        } elseif ( is_tax() ) {
            $page_title = single_term_title( '', false );
        } else {
            $jobs_page_id = get_option( 'jobpress_jobs_page_id' );
            $page_title   = get_the_title( $jobs_page_id );
        }

        /**
         * Filter the page title.
         *
         * @since 2.1.0
         * @param string $page_title Page title.
         */
        $page_title = apply_filters( 'jobpress_page_title', $page_title );

        if ( $echo ) {
            echo esc_html( $page_title );
        } else {
            return $page_title;
        }
    }
}