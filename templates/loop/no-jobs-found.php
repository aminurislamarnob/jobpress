<?php
/**
 * Displayed when no jobs are found matching the current query
 *
 * This template can be overridden by copying it to yourtheme/jobpress/loop/no-jobs-found.php.
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="jobpress-no-jobs-found">
    <img src="<?php echo esc_url( JOBPRESS_PLUGIN_URL . 'assets/public/images/not-found.svg' ); ?>" alt="<?php esc_attr_e( 'No jobs found', 'jobpress' ); ?>">
	<div class="job-not-found-text">
		<h2><?php echo esc_html__( 'No jobs found', 'jobpress' ); ?></h2>
		<p><?php echo esc_html__( 'We couldn\'t find any jobs matching your search criteria.', 'jobpress' ); ?></p>
	</div>
</div>
