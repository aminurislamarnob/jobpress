<?php
/**
 * Remove JobPress data when the plugin is deleted.
 *
 * Settings are always removed. Jobs, their categories and types, and the Jobs
 * Page are only removed when JOBPRESS_REMOVE_ALL_DATA is true in wp-config.php,
 * so reinstalling the plugin doesn't lose them.
 *
 * @package JobPress
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

/**
 * Remove JobPress data from the current site.
 */
function jobpress_uninstall_site() {
	global $wpdb;

	if ( defined( 'JOBPRESS_REMOVE_ALL_DATA' ) && true === JOBPRESS_REMOVE_ALL_DATA ) {
		$jobs_page_id = (int) get_option( 'jobpress_jobs_page_id' );
		if ( $jobs_page_id ) {
			wp_delete_post( $jobs_page_id, true );
		}

		$job_ids = get_posts(
			array(
				'post_type'      => 'jobpress',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);
		foreach ( $job_ids as $job_id ) {
			wp_delete_post( $job_id, true );
		}

		// The taxonomies aren't registered while uninstalling, so their terms are removed directly.
		foreach ( array( 'jobpress_category', 'jobpress_type' ) as $taxonomy ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$terms = $wpdb->get_results( $wpdb->prepare( "SELECT term_taxonomy_id, term_id FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s", $taxonomy ) );
			foreach ( $terms as $term ) {
				// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->delete( $wpdb->term_relationships, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
				$wpdb->delete( $wpdb->term_taxonomy, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
				$wpdb->delete( $wpdb->termmeta, array( 'term_id' => $term->term_id ) );
				$wpdb->delete( $wpdb->terms, array( 'term_id' => $term->term_id ) );
				// phpcs:enable
			}
		}
	}

	// Settings: every JobPress option starts with "jobpress_".
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$options = $wpdb->get_col( $wpdb->prepare( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s", $wpdb->esc_like( 'jobpress_' ) . '%' ) );
	foreach ( $options as $option ) {
		delete_option( $option );
	}

	// Table created by JobPress versions before 2.2.1; it was never used.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}jobpress_application" );
}

if ( is_multisite() ) {
	foreach ( get_sites( array( 'fields' => 'ids', 'number' => 0 ) ) as $jobpress_site_id ) {
		switch_to_blog( $jobpress_site_id );
		jobpress_uninstall_site();
		restore_current_blog();
	}
} else {
	jobpress_uninstall_site();
}

// The dismissed template notice is stored per user, network-wide.
delete_metadata( 'user', 0, 'jobpress_dismissed_template_notice', '', true );
