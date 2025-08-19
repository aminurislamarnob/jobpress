<?php
/**
 * JobPress job loop header
 *
 * This template can be overridden by copying it to yourtheme/jobpress/loop/header.php.
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<header class="jobpress-job-loop-header">
	<?php
	/**
	 * Hook: jobpress_show_page_title.
	 *
	 * Allow developers to remove the product taxonomy archive page title.
	 *
	 */
	if ( apply_filters( 'jobpress_show_page_title', true ) ) :
		?>
		<h1 class="jobpress-job-loop-header__title page-title"><?php jobpress_page_title(); ?></h1>
	<?php endif; ?>

	<?php
	/**
	 * Hook: jobpress_archive_description.
	 */
	do_action( 'jobpress_archive_description' );
	?>
</header>
