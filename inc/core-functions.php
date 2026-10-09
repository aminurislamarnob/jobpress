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
    // Takes the keys of the array and makes them into variables inside the local scope.
    if ( ! empty( $args ) && is_array( $args ) ) {
        extract( $args ); // phpcs:ignore
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
 * Get the selected listing design number (1-5).
 *
 * @return int
 */
function jobpress_get_short_design_type() {
    $style_type = absint( get_option( 'jobpress_design_type', 1 ) );
    return ( $style_type >= 1 && $style_type <= 5 ) ? $style_type : 1;
}

/**
 * Get the job groups used by the category-grouped listing designs (v2, v4).
 *
 * Each non-empty category becomes a group, followed by an "Other openings"
 * group for jobs without a category so they are not dropped from the listing.
 *
 * @return array[] List of groups with 'name', 'description', 'slug' (category groups only) and 'tax_query' keys.
 */
function jobpress_get_listing_category_groups() {
    $groups = array();
    $terms  = get_terms( array(
        'taxonomy'   => 'jobpress_category',
        'orderby'    => 'name',
        'order'      => 'ASC',
        'hide_empty' => true,
    ) );

    if ( ! is_wp_error( $terms ) ) {
        foreach ( $terms as $term ) {
            $groups[] = array(
                'name'        => $term->name,
                'description' => $term->description,
                'slug'        => $term->slug,
                'tax_query'   => array(
                    array(
                        'taxonomy' => 'jobpress_category',
                        'field'    => 'term_id',
                        'terms'    => $term->term_id,
                    ),
                ),
            );
        }
    }

    $groups[] = array(
        'name'        => __( 'Other openings', 'jobpress' ),
        'description' => '',
        'tax_query'   => array(
            array(
                'taxonomy' => 'jobpress_category',
                'operator' => 'NOT EXISTS',
            ),
        ),
    );

    return apply_filters( 'jobpress_listing_category_groups', $groups );
}

/**
 * Validate a listing design number.
 *
 * @param mixed $design Design number, e.g. from a shortcode attribute.
 * @return int The design (1-5), or 0 when the value is empty or invalid.
 */
function jobpress_sanitize_design( $design ) {
    $design = is_numeric( $design ) ? (int) $design : 0;
    return ( $design >= 1 && $design <= 5 ) ? $design : 0;
}

/**
 * Interpret a yes/no attribute or setting value.
 *
 * @param mixed $value E.g. 'yes', 'no', 'true', '1', or a boolean.
 * @return bool
 */
function jobpress_string_to_bool( $value ) {
    return is_bool( $value ) ? $value : in_array( strtolower( trim( (string) $value ) ), array( 'yes', 'true', '1', 'on' ), true );
}

/**
 * The global listing settings (Settings > Shortcodes > Listing Defaults): defaults
 * for every [jobpress] shortcode and JobPress Elementor widget, which can override them.
 *
 * @return array[] Keyed by shortcode attribute: 'option' name, 'type' (text or
 *                 checkbox), 'label', 'default', and 'archive' when the setting
 *                 also applies to the jobs archive.
 */
function jobpress_get_listing_settings() {
    return array(
        'title'          => array(
            'option'  => 'jobpress_listing_title',
            'type'    => 'text',
            'label'   => __( 'Title', 'jobpress' ),
            'default' => __( 'Job openings', 'jobpress' ),
        ),
        'show_title'     => array(
            'option'  => 'jobpress_listing_show_title',
            'type'    => 'checkbox',
            'label'   => __( 'Show the title', 'jobpress' ),
            'default' => 'yes',
        ),
        'subtitle'       => array(
            'option'  => 'jobpress_listing_subtitle',
            'type'    => 'text',
            'label'   => __( 'Subtitle', 'jobpress' ),
            'default' => __( 'Find the right job for you no matter what it is that you do.', 'jobpress' ),
        ),
        'show_subtitle'  => array(
            'option'  => 'jobpress_listing_show_subtitle',
            'type'    => 'checkbox',
            'label'   => __( 'Show the subtitle', 'jobpress' ),
            'default' => 'yes',
        ),
        'show_positions' => array(
            'option'  => 'jobpress_listing_show_positions',
            'type'    => 'checkbox',
            'label'   => __( 'Show the open positions count', 'jobpress' ),
            'default' => 'yes',
        ),
    );
}

/**
 * Get the value of a global listing setting.
 *
 * @param string $key Shortcode attribute, see jobpress_get_listing_settings().
 * @return string Text, or 'yes'/'no' for checkboxes. Empty text falls back to the default.
 */
function jobpress_get_listing_setting( $key ) {
    $settings = jobpress_get_listing_settings();
    if ( ! isset( $settings[ $key ] ) ) {
        return '';
    }

    $setting = $settings[ $key ];
    $value   = get_option( $setting['option'], '' );

    if ( 'checkbox' === $setting['type'] ) {
        return in_array( $value, array( 'yes', 'no' ), true ) ? $value : $setting['default'];
    }

    return '' === trim( (string) $value ) ? $setting['default'] : (string) $value;
}

/**
 * Whether a listing design groups jobs under category headings.
 *
 * @param int $design Listing design number (1-5).
 * @return bool
 */
function jobpress_is_grouped_design( $design ) {
    return in_array( (int) $design, array( 2, 4 ), true );
}

/**
 * Split a comma-separated attribute value into a list.
 *
 * @param string|array $value Comma-separated values, or a list.
 * @return string[] Trimmed, non-empty values.
 */
function jobpress_parse_list( $value ) {
    if ( ! is_array( $value ) ) {
        $value = explode( ',', (string) $value );
    }
    return array_values( array_filter( array_map( 'trim', array_map( 'strval', $value ) ), 'strlen' ) );
}

/**
 * Normalize the query attributes of a listing.
 *
 * @param array $atts Shortcode attributes.
 * @return array {
 *     @type int      $per_page   Jobs to show (per group in grouped designs); -1 for all.
 *     @type string[] $categories jobpress_category slugs; any of them matches.
 *     @type string[] $types      jobpress_type slugs; any of them matches.
 *     @type int[]    $include    Job IDs to limit the listing to.
 *     @type int[]    $exclude    Job IDs to leave out.
 *     @type string   $orderby    date, title, menu_order or rand.
 *     @type string   $order      ASC or DESC.
 * }
 */
function jobpress_get_listing_query_atts( $atts ) {
    $per_page = isset( $atts['per_page'] ) && is_numeric( $atts['per_page'] ) ? (int) $atts['per_page'] : -1;
    $orderby  = isset( $atts['orderby'] ) ? strtolower( trim( $atts['orderby'] ) ) : '';
    $order    = isset( $atts['order'] ) ? strtoupper( trim( $atts['order'] ) ) : '';

    return array(
        'per_page'   => $per_page > 0 ? $per_page : -1,
        'categories' => array_map( 'sanitize_title', jobpress_parse_list( isset( $atts['category'] ) ? $atts['category'] : '' ) ),
        'types'      => array_map( 'sanitize_title', jobpress_parse_list( isset( $atts['type'] ) ? $atts['type'] : '' ) ),
        'include'    => array_filter( array_map( 'absint', jobpress_parse_list( isset( $atts['include'] ) ? $atts['include'] : '' ) ) ),
        'exclude'    => array_filter( array_map( 'absint', jobpress_parse_list( isset( $atts['exclude'] ) ? $atts['exclude'] : '' ) ) ),
        'orderby'    => in_array( $orderby, array( 'date', 'title', 'menu_order', 'rand' ), true ) ? $orderby : 'date',
        'order'      => in_array( $order, array( 'ASC', 'DESC' ), true ) ? $order : 'DESC',
    );
}

/**
 * Build the WP_Query arguments for a listing.
 *
 * @param array $atts      Shortcode attributes.
 * @param array $tax_query Extra tax_query clauses, e.g. a category group's.
 * @return array
 */
function jobpress_get_listing_query_args( $atts, $tax_query = array() ) {
    $query = jobpress_get_listing_query_atts( $atts );
    $args  = array(
        'posts_per_page' => $query['per_page'],
        'post_type'      => 'jobpress',
        'post_status'    => 'publish',
        // ID breaks ties, e.g. between jobs published in the same second.
        'orderby'        => 'rand' === $query['orderby'] ? 'rand' : array(
            $query['orderby'] => $query['order'],
            'ID'              => $query['order'],
        ),
    );

    $taxonomies = array(
        'jobpress_category' => $query['categories'],
        'jobpress_type'     => $query['types'],
    );
    foreach ( $taxonomies as $taxonomy => $slugs ) {
        if ( $slugs ) {
            $tax_query[] = array(
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => $slugs,
            );
        }
    }
    if ( $tax_query ) {
        $args['tax_query'] = $tax_query;
    }

    // WP_Query ignores post__not_in when post__in is set, so subtract the excluded jobs here.
    if ( $query['include'] ) {
        $include          = array_values( array_diff( $query['include'], $query['exclude'] ) );
        $args['post__in'] = $include ? $include : array( 0 );
    } elseif ( $query['exclude'] ) {
        $args['post__not_in'] = $query['exclude'];
    }

    /**
     * Filters the WP_Query arguments of a job listing ([jobpress] shortcode or
     * Elementor widget), including each category group's query in grouped designs.
     *
     * @param array $args WP_Query arguments.
     * @param array $atts The listing's shortcode attributes.
     */
    return apply_filters( 'jobpress_listing_query_args', $args, $atts );
}

/**
 * Get the category groups of a grouped listing, limited to the listing's categories.
 *
 * @param array $atts Shortcode attributes.
 * @return array[] Groups, see jobpress_get_listing_category_groups().
 */
function jobpress_get_listing_groups( $atts ) {
    return jobpress_filter_listing_groups( jobpress_get_listing_category_groups(), $atts );
}

/**
 * Limit category groups to the listing's categories.
 *
 * Groups without a slug (e.g. "Other openings") are kept and left to the query,
 * which finds no jobs for them when they don't match the categories.
 *
 * @param array[] $groups Groups, see jobpress_get_listing_category_groups().
 * @param array   $atts   Shortcode attributes.
 * @return array[]
 */
function jobpress_filter_listing_groups( $groups, $atts ) {
    $categories = jobpress_get_listing_query_atts( $atts )['categories'];
    if ( ! $categories ) {
        return $groups;
    }

    return array_values(
        array_filter(
            $groups,
            function ( $group ) use ( $categories ) {
                return empty( $group['slug'] ) || in_array( $group['slug'], $categories, true );
            }
        )
    );
}

/**
 * Run the jobs queries for a listing.
 *
 * Grouped designs get one query per category group; the others get a single query.
 *
 * @param int   $design Listing design number (1-5).
 * @param array $atts   Shortcode attributes.
 * @return array {
 *     Template variables.
 *
 *     @type WP_Query|null $jobs_query Jobs of a flat design.
 *     @type array[]       $job_groups Groups of a grouped design: the group keys plus a 'query' WP_Query.
 *     @type int           $total_jobs Number of jobs matching the listing (not limited by per_page).
 * }
 */
function jobpress_get_listing_jobs( $design, $atts = array() ) {
    $result = array(
        'jobs_query' => null,
        'job_groups' => array(),
        'total_jobs' => 0,
    );

    if ( jobpress_is_grouped_design( $design ) ) {
        foreach ( jobpress_get_listing_groups( $atts ) as $group ) {
            $group['query'] = new WP_Query( jobpress_get_listing_query_args( $atts, $group['tax_query'] ) );
            if ( $group['query']->have_posts() ) {
                $result['job_groups'][] = $group;
                $result['total_jobs']  += $group['query']->found_posts;
            }
        }
    } else {
        $result['jobs_query'] = new WP_Query( jobpress_get_listing_query_args( $atts ) );
        $result['total_jobs'] = $result['jobs_query']->found_posts;
    }

    return $result;
}

/**
 * Get the URL of the listing's "View all jobs" link: the Jobs Page, keeping the
 * listing's category or type when it shows a single one (the Jobs Page filters
 * by one of each).
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function jobpress_get_listing_view_all_url( $atts ) {
    $query = jobpress_get_listing_query_atts( $atts );
    $args  = array();
    if ( 1 === count( $query['categories'] ) ) {
        $args['jobcategory'] = $query['categories'][0];
    }
    if ( 1 === count( $query['types'] ) ) {
        $args['jobtype'] = $query['types'][0];
    }
    return add_query_arg( $args, jobpress_get_jobs_archive_page_permalink() );
}

/**
 * Format a stored job date (e.g. the Y-m-d application deadline) using the site's date format.
 *
 * @param string $date Raw date value.
 * @return string Localized date, or the raw value if it cannot be parsed.
 */
function jobpress_format_date( $date ) {
    $timestamp = $date ? strtotime( $date ) : false;
    return $timestamp ? date_i18n( get_option( 'date_format' ), $timestamp ) : (string) $date;
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
 * Get the number of jobs shown per page on the jobs page and category/type archives.
 *
 * @return int
 */
function jobpress_get_jobs_per_page() {
    $per_page = absint( get_option( 'jobpress_jobs_per_page', 10 ) );
    return $per_page ? $per_page : 10;
}

/**
 * Check if the current page is the jobs page.
 *
 * @return bool
 */
function jobpress_is_jobs_page() {
    $page_id = jobpress_get_jobs_page_id();
    // is_page( 0 ) matches every page, so bail out when no jobs page is set.
    return $page_id > 0 && is_page( $page_id );
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

/**
 * Check if a term is selected.
 *
 * @param string $term_slug The term slug.
 * @param string $taxonomy The taxonomy.
 * @return bool
 */
function jobpress_is_term_selected( $term_slug, $taxonomy ){
    if( empty( $term_slug ) ){
        return false;
    }
    $term_exists = term_exists( $term_slug, $taxonomy );
    if ( $term_exists ) {
        return true;
    }
    return false;
}