<?php
/**
 * JobPress Template Hooks
 *
 * Action/filter hooks used for JobPress functions/templates.
 *
 * @package JobPress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Header
 *
 * @see jobpress_get_job_loop_header()
 */
add_action( 'jobpress_job_loop_header', 'jobpress_get_job_loop_header', 10 );

/**
 * Loop Setup
 *
 * @see jobpress_setup_loop()
 */
add_action( 'jobpress_before_jobs_loop', 'jobpress_setup_loop', 5 );

/**
 * Loop Reset
 *
 * @see jobpress_reset_loop()
 */
add_action( 'jobpress_after_jobs_loop', 'jobpress_reset_loop', 999 );

/**
 * Pagination
 *
 * @see jobpress_pagination()
 */
add_action( 'jobpress_after_jobs_loop', 'jobpress_pagination', 10 );

/**
 * Content Wrappers.
 *
 * @see jobpress_output_content_wrapper()
 * @see jobpress_output_content_wrapper_end()
 */
add_action( 'jobpress_before_main_content', 'jobpress_output_content_wrapper', 10 );
add_action( 'jobpress_after_main_content', 'jobpress_output_content_wrapper_end', 10 );