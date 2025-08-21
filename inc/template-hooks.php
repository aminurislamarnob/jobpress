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
 * Pagination
 *
 * @see jobpress_pagination()
 */
add_action( 'jobpress_after_jobs_loop', 'jobpress_pagination', 10 );
