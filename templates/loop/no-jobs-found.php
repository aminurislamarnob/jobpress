<?php
/**
 * Displayed when no jobs are found matching the current query
 *
 * This template can be overridden by copying it to yourtheme/jobpress/loop/no-jobs-found.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="jobpress-no-jobs-found">
    <img src="<?php echo esc_url( JOBPRESS_PLUGIN_URL . 'assets/public/images/not-found.svg' ); ?>" alt="No jobs found">
	<div class="job-not-found-text">
		<h2><?php echo esc_html__( 'No jobs found', 'jobpress' ); ?></h2>
		<p><?php echo esc_html__( 'We couldn\'t find any jobs matching your search criteria.', 'jobpress' ); ?></p>
	</div>
</div>
