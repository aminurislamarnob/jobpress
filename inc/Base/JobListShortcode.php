<?php
namespace JobPressInc\Base;

/**
* Job List Shortcode
*
* The [jobpress] shortcode is the listing engine shared by the shortcode, the
* JobPress Jobs block and the Elementor widget: it resolves the attributes, runs the jobs query and wraps the
* listing design template in an element scoped to that design.
*/
class JobListShortcode
{
	/**
	 * Number of listings rendered so far in this request, used for unique IDs.
	 *
	 * @var int
	 */
	private static $instance_count = 0;

	public function register() {
        add_shortcode('jobpress', array( $this, 'jobpress_jobs_shortcode' ) );
	}

    /**
     * Default attribute values: the global listing settings, then built-in defaults.
     *
     * @return array
     */
    public static function get_default_atts() {
        $defaults = array(
            'expand' => '',
            'design' => '', // 1-5; empty uses the design selected in the shortcode settings
            'per_page' => '', // number of jobs (per category group in grouped designs); empty for all
            'category' => '', // comma-separated jobpress_category slugs
            'type' => '', // comma-separated jobpress_type slugs
            'include' => '', // comma-separated job IDs
            'exclude' => '', // comma-separated job IDs
            'orderby' => 'date', // date, title, menu_order or rand
            'order' => 'DESC', // ASC or DESC
        ) + array_fill_keys( array_keys( PublicEnqueue::get_colors() ), '' ); // hex colors; empty uses the appearance settings

        // Header (title, subtitle, show_title, show_subtitle, show_positions), card fields
        // (show_category, ..., button_text), show_search, show_view_all and view_all_text.
        foreach ( array_keys( jobpress_get_listing_settings() ) as $key ) {
            $defaults[ $key ] = jobpress_get_listing_setting( $key );
        }

        /**
         * Filters the default [jobpress] attributes: the global listing settings
         * merged over the built-in defaults, before a listing's own attributes apply.
         *
         * @param array $defaults Attribute defaults.
         */
        return apply_filters( 'jobpress_listing_defaults', $defaults );
    }

    /**
     * Resolve a listing's attributes: an attribute that is missing or empty
     * inherits its default, see get_default_atts().
     *
     * @param array|string $atts Shortcode attributes.
     * @return array
     */
    public static function resolve_atts( $atts ) {
        $defaults = self::get_default_atts();
        $atts     = shortcode_atts( $defaults, $atts, 'jobpress' );

        foreach ( $atts as $key => $value ) {
            if ( '' === trim( (string) $value ) ) {
                $atts[ $key ] = $defaults[ $key ];
            }
        }

        // Yes/no options are passed to templates as 'yes' or 'no'.
        foreach ( $atts as $key => $value ) {
            if ( 0 === strpos( $key, 'show_' ) ) {
                $atts[ $key ] = jobpress_string_to_bool( $value ) ? 'yes' : 'no';
            }
        }

        return $atts;
    }

    function jobpress_jobs_shortcode($atts) {
        $atts = self::resolve_atts( $atts );

        $design = jobpress_sanitize_design( $atts['design'] );
        if ( ! $design ) {
            $design = jobpress_get_short_design_type();
        }

        // Make sure styles load wherever the shortcode renders (e.g. Elementor content).
        PublicEnqueue::enqueue_styles( $design );

        self::$instance_count++;
        $listing_id = 'jp-listing-' . self::$instance_count;

        // A hidden title or subtitle is passed empty: templates (including theme copies) skip empty ones.
        $template_args = array(
            'title'          => 'yes' === $atts['show_title'] ? $atts['title'] : '',
            'subtitle'       => 'yes' === $atts['show_subtitle'] ? $atts['subtitle'] : '',
            'show_positions' => $atts['show_positions'],
            'listing_id'     => $listing_id,
            'atts'           => $atts,
        );
        foreach ( array( 'show_category', 'show_type', 'show_location', 'show_experience', 'show_vacancy', 'show_deadline', 'button_text' ) as $key ) {
            $template_args[ $key ] = $atts[ $key ];
        }
        $template_args = array_merge( $template_args, jobpress_get_listing_jobs( $design, $atts ) );
        $template_args['search_form'] = 'yes' === $atts['show_search'] ? $this->get_search_form( $listing_id, $atts ) : '';

        //Load Template
        ob_start();
        $color_overrides = PublicEnqueue::get_color_overrides( $atts );
        printf(
            '<div id="%s" class="%s"%s>',
            esc_attr( $listing_id ),
            esc_attr( 'jp-listing jp-design-v' . $design ),
            $color_overrides ? ' style="' . esc_attr( $color_overrides ) . '"' : ''
        );
        $this->render_template( 'listing/jobpress-listing-v' . $design . '.php', $template_args, $atts );
        if ( 'yes' === $atts['show_view_all'] ) {
            printf(
                '<div class="jp-listing__footer"><a class="jp-listing__view-all" href="%s">%s</a></div>',
                esc_url( jobpress_get_listing_view_all_url( $atts ) ),
                esc_html( $atts['view_all_text'] )
            );
        }
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * Render the search form of a listing: it opens the Jobs Page results, with
     * the listing's category or type preselected when it shows a single one.
     *
     * @param string $listing_id Listing element ID, used to keep the form's IDs unique.
     * @param array  $atts       Shortcode attributes.
     * @return string
     */
    private function get_search_form( $listing_id, $atts ) {
        $query = jobpress_get_listing_query_atts( $atts );

        ob_start();
        echo '<div class="jp-listing__search">';
        jobpress_get_template( 'global/filter-and-search.php', array(
            'form_id_suffix'    => '-' . $listing_id,
            'selected_category' => 1 === count( $query['categories'] ) ? $query['categories'][0] : '',
            'selected_type'     => 1 === count( $query['types'] ) ? $query['types'][0] : '',
        ) );
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * Render a listing template.
     *
     * Theme copies of the listing templates made before 2.2.0 run their own jobs
     * queries; while one renders, the listing's query attributes are applied to
     * those queries so the theme copy still shows the right jobs.
     *
     * @param string $template_name Listing template.
     * @param array  $template_args Template variables.
     * @param array  $atts          Shortcode attributes.
     */
    private function render_template( $template_name, $template_args, $atts ) {
        $is_theme_copy = 0 !== strpos( wp_normalize_path( jobpress_locate_template( $template_name ) ), wp_normalize_path( JOBPRESS_PLUGIN_PATH ) );
        if ( ! $is_theme_copy ) {
            jobpress_get_template( $template_name, $template_args );
            return;
        }

        // The plugin's templates print the search form under their header; theme copies may not.
        echo $template_args['search_form']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the search template.
        $template_args['search_form'] = '';

        $apply_query_atts = function ( $query ) use ( $atts ) {
            if ( 'jobpress' !== $query->get( 'post_type' ) ) {
                return;
            }
            // Keep the copy's own tax_query (e.g. its category group) and add the listing's.
            $own_tax_query = $query->get( 'tax_query' );
            $args          = jobpress_get_listing_query_args( $atts, is_array( $own_tax_query ) ? $own_tax_query : array() );
            foreach ( $args as $key => $value ) {
                $query->set( $key, $value );
            }
        };
        $limit_groups = function ( $groups ) use ( $atts ) {
            return jobpress_filter_listing_groups( $groups, $atts );
        };

        add_action( 'pre_get_posts', $apply_query_atts );
        add_filter( 'jobpress_listing_category_groups', $limit_groups, PHP_INT_MAX );
        jobpress_get_template( $template_name, $template_args );
        remove_filter( 'jobpress_listing_category_groups', $limit_groups, PHP_INT_MAX );
        remove_action( 'pre_get_posts', $apply_query_atts );
    }
}
