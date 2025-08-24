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

/**
 * Get template part for jobs content.
 *
 * @since 1.0.0
 * @param string $slug Template slug.
 * @param string $name Template name (default: '').
 */
function jobpress_get_template_part( $slug, $name = '' ) {
    $template = '';

    if ( $name ) {
        $template = locate_template(
            array(
                "{$slug}-{$name}.php",
                'jobpress/' . "{$slug}-{$name}.php",
            )
        );

        if ( ! $template ) {
            $fallback = JOBPRESS_PLUGIN_PATH . "templates/{$slug}-{$name}.php";
            $template = file_exists( $fallback ) ? $fallback : '';
        }
    }

    if ( ! $template ) {
        // If template file doesn't exist, look in yourtheme/slug.php and yourtheme/jobpress/slug.php.
        $template = locate_template(
            array(
                "{$slug}.php",
                'jobpress/' . "{$slug}.php",
            )
        );
    }

    // Allow 3rd party plugins to filter template file from their plugin.
    $template = apply_filters( 'jobpress_get_template_part', $template, $slug, $name );

    if ( $template ) {
        load_template( $template, false );
    }
}

/**
 * JobPress Loop Properties and Pagination System
 * 
 * Following WooCommerce's approach for consistent pagination handling
 */

/**
 * Sets up the jobpress_loop global from the passed args or from the main query.
 *
 * @since 1.0.0
 * @param array $args Args to pass into the global.
 */
function jobpress_setup_loop( $args = array() ) {
    $default_args = array(
        'loop'         => 0,
        'name'         => '',
        'is_shortcode' => false,
        'is_paginated' => true,
        'is_search'    => false,
        'is_filtered'  => false,
        'total'        => 0,
        'total_pages'  => 0,
        'per_page'     => 0,
        'current_page' => 1,
    );

    // If this is a main query, use global args as defaults.
    global $wp_query;
    if ( $wp_query->get( 'jobpress_query' ) ) {
        $default_args = array_merge(
            $default_args,
            array(
                'is_search'    => $wp_query->is_search(),
                'is_filtered'  => false, // Can be enhanced later
                'total'        => $wp_query->found_posts,
                'total_pages'  => $wp_query->max_num_pages,
                'per_page'     => $wp_query->get( 'posts_per_page' ),
                'current_page' => max( 1, $wp_query->get( 'paged', 1 ) ),
            )
        );
    }

    // Merge any existing values.
    if ( isset( $GLOBALS['jobpress_loop'] ) ) {
        $default_args = array_merge( $default_args, $GLOBALS['jobpress_loop'] );
    }

    $GLOBALS['jobpress_loop'] = wp_parse_args( $args, $default_args );
}

/**
 * Resets the jobpress_loop global.
 *
 * @since 1.0.0
 */
function jobpress_reset_loop() {
    unset( $GLOBALS['jobpress_loop'] );
}

/**
 * Gets a property from the jobpress_loop global.
 *
 * @since 1.0.0
 * @param string $prop Prop to get.
 * @param string $default Default if the prop does not exist.
 * @return mixed
 */
function jobpress_get_loop_prop( $prop, $default = '' ) {
    jobpress_setup_loop(); // Ensure loop is setup.

    return isset( $GLOBALS['jobpress_loop'], $GLOBALS['jobpress_loop'][ $prop ] ) ? $GLOBALS['jobpress_loop'][ $prop ] : $default;
}

/**
 * Sets a property in the jobpress_loop global.
 *
 * @since 1.0.0
 * @param string $prop Prop to set.
 * @param string $value Value to set.
 */
function jobpress_set_loop_prop( $prop, $value = '' ) {
    if ( ! isset( $GLOBALS['jobpress_loop'] ) ) {
        jobpress_setup_loop();
    }
    $GLOBALS['jobpress_loop'][ $prop ] = $value;
}

/**
 * Check if we will be showing jobs or not.
 *
 * @since 1.0.0
 * @return bool
 */
function jobpress_jobs_will_display() {
    return 0 < jobpress_get_loop_prop( 'total', 0 );
}

/**
 * Display pagination for jobs loop.
 * 
 * @since 1.0.0
 * @return void
 */
function jobpress_pagination() {
    if ( ! jobpress_get_loop_prop( 'is_paginated' ) || ! jobpress_jobs_will_display() ) {
        return;
    }

    $is_shortcode  = jobpress_get_loop_prop( 'is_shortcode' );
    $current_page  = jobpress_get_loop_prop( 'current_page' );
    $total_pages   = jobpress_get_loop_prop( 'total_pages' );

    // Build base + format depending on context
    if ( $is_shortcode ) {
        $args = array(
            'base'    => esc_url_raw( add_query_arg( 'job-page', '%#%', false ) ),
            'format'  => '?job-page=%#%',
            'current' => $current_page,
            'total'   => $total_pages,
        );
    } else {
        $args = array(
            'base'    => esc_url_raw(
                str_replace(
                    999999999,
                    '%#%',
                    get_pagenum_link( 999999999, false )
                )
            ),
            'format'  => '',
            'current' => $current_page,
            'total'   => $total_pages,
        );
    }
    
    // Page numbers
    $page_numbers = paginate_links( array(
        'base'      => $args['base'],
        'format'    => $args['format'],
        'current'   => $current_page,
        'total'     => $total_pages,
        'prev_text' => jobpress_get_left_arrow_svg(),
        'next_text' => jobpress_get_right_arrow_svg(),
        'type'      => 'array',
        'show_all'  => false,
        'end_size'  => 1,
        'mid_size'  => 2,
    ) );
    
    if ( $page_numbers ) {
        echo '<div class="jobpress-pagination" role="navigation" aria-label="Jobs Pagination">';
        echo '<span class="page-numbers-container">';
        echo implode( '', $page_numbers );
        echo '</span>';
        echo '</div>';
    }
}

/**
 * Output an inline SVG left arrow securely with text.
 */
function jobpress_get_left_arrow_svg() {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24"><path d="M17.17,24a1,1,0,0,1-.71-.29L8.29,15.54a5,5,0,0,1,0-7.08L16.46.29a1,1,0,1,1,1.42,1.42L9.71,9.88a3,3,0,0,0,0,4.24l8.17,8.17a1,1,0,0,1,0,1.42A1,1,0,0,1,17.17,24Z"/></svg>';

    // Allowed tags + attributes for inline SVG
    $allowed_svg = [
        'svg'  => [
            'xmlns'   => true,
            'width'   => true,
            'height'  => true,
            'viewBox' => true,
            'fill'    => true,
            'stroke'  => true,
        ],
        'path' => [
            'stroke-linecap'   => true,
            'stroke-linejoin'  => true,
            'stroke-width'     => true,
            'd'                => true,
        ],
    ];
    $svg_icon = wp_kses( $svg, $allowed_svg );
    
    return $svg_icon;
}

/**
 * Output an inline SVG right arrow securely with text.
 */
function jobpress_get_right_arrow_svg() {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24"><path d="M7,24a1,1,0,0,1-.71-.29,1,1,0,0,1,0-1.42l8.17-8.17a3,3,0,0,0,0-4.24L6.29,1.71A1,1,0,0,1,7.71.29l8.17,8.17a5,5,0,0,1,0,7.08L7.71,23.71A1,1,0,0,1,7,24Z"/></svg>';

    // Allowed tags + attributes for inline SVG
    $allowed_svg = [
        'svg'  => [
            'xmlns'   => true,
            'width'   => true,
            'height'  => true,
            'viewBox' => true,
            'fill'    => true,
            'stroke'  => true,
        ],
        'path' => [
            'stroke-linecap'   => true,
            'stroke-linejoin'  => true,
            'stroke-width'     => true,
            'd'                => true,
        ],
    ];
    $svg_icon = wp_kses( $svg, $allowed_svg );
    
    return $svg_icon;
}

/**
 * Get permalink settings for things like products and taxonomies.
 *
 * @return array
 */
function jobpress_get_permalink_structure() {
    $permalinks = [];
	$job_permalinks = array(
        'job_base'  => _x( 'job', 'slug', 'jobpress' ),
        'job_category_base' => _x( 'job-category', 'slug', 'jobpress' ),
        'job_tag_base'  => _x( 'job-tag', 'slug', 'jobpress' ),
    );

	$permalinks['job_rewrite_slug']   = untrailingslashit( $job_permalinks['job_base'] );
	$permalinks['job_category_rewrite_slug']  = untrailingslashit( $job_permalinks['job_category_base'] );
	$permalinks['job_tag_rewrite_slug']       = untrailingslashit( $job_permalinks['job_tag_base'] );

	return $permalinks;
}