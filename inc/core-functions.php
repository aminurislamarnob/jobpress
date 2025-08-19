<?php
/**
 * JobPress Core Functions
 *
 * General core functions available on both the front-end and admin.
 *
 * @package JobPress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get other templates passing attributes and including the file.
 *
 * @param string $template_name Template name.
 * @param array  $args          Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 */
function jobpress_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
    if ( ! empty( $args ) && is_array( $args ) ) {
        extract( $args ); // @codingStandardsIgnoreLine
    }

    $located = jobpress_locate_template( $template_name, $template_path, $default_path );

    if ( ! file_exists( $located ) ) {
        /* translators: %s template */
        _doing_it_wrong( __FUNCTION__, sprintf( __( '%s does not exist.', 'jobpress' ), '<code>' . $located . '</code>' ), '2.1.0' );
        return;
    }

    // Allow 3rd party plugin filter template file from their plugin.
    $located = apply_filters( 'jobpress_get_template', $located, $template_name, $args, $template_path, $default_path );

    do_action( 'jobpress_before_template_part', $template_name, $template_path, $located, $args );

    include $located;

    do_action( 'jobpress_after_template_part', $template_name, $template_path, $located, $args );
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 * yourtheme/jobpress/$template_name
 * yourtheme/$template_name
 * $default_path/$template_name
 *
 * @param string $template_name Template name.
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 * @return string
 */
function jobpress_locate_template( $template_name, $template_path = '', $default_path = '' ) {
    if ( ! $template_path ) {
        $template_path = 'jobpress/';
    }

    if ( ! $default_path ) {
        $default_path = JOBPRESS_PLUGIN_PATH . 'templates/';
    }

    // Look within passed path within the theme - this is priority.
    $template = locate_template(
        array(
            trailingslashit( $template_path ) . $template_name,
            $template_name,
        )
    );

    // Get default template.
    if ( ! $template ) {
        $template = $default_path . $template_name;
    }

    // Return what we found.
    return apply_filters( 'jobpress_locate_template', $template, $template_name, $template_path );
}

/**
 * Get the jobs page ID.
 *
 * @return int
 */
function jobpress_get_jobs_page_id() {
    $page_id = get_option( 'jobpress_jobs_page_id', 0 );
    return (int) $page_id;
}

/**
 * Check if the current page is the jobs page.
 *
 * @return bool
 */
function jobpress_is_jobs_page() {
    return is_page( jobpress_get_jobs_page_id() );
}

/**
 * Get the jobs archive page permalink.
 *
 * @return string
 */
function jobpress_get_jobs_archive_page_permalink() {
    $page_id = jobpress_get_jobs_page_id();
    return $page_id ? get_permalink( $page_id ) : get_post_type_archive_link( 'jobpress' );
}