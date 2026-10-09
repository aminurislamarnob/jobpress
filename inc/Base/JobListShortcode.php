<?php
namespace JobPressInc\Base;

/**
* Job List Shortcode
*
* The [jobpress] shortcode is the listing engine shared by the shortcode and the
* Elementor widget: it resolves the attributes, runs the jobs query and wraps the
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
     * Default attribute values.
     *
     * @return array
     */
    public static function get_default_atts() {
        return array(
            'expand' => '',
            'design' => '', // 1-5; empty uses the design selected in the shortcode settings
            'title' => esc_html__('Job openings', 'jobpress'),
            'subtitle' => esc_html__('Find the right job for you no matter what it is that you do.', 'jobpress'),
            'show_positions' => 'yes', // yes/no to show/hide open positions count
            'per_page' => '', // number of jobs (per category group in grouped designs); empty for all
            'category' => '', // comma-separated jobpress_category slugs
            'type' => '', // comma-separated jobpress_type slugs
            'include' => '', // comma-separated job IDs
            'exclude' => '', // comma-separated job IDs
            'orderby' => 'date', // date, title, menu_order or rand
            'order' => 'DESC', // ASC or DESC
            'show_view_all' => 'no', // yes/no to show a "View all jobs" link to the Jobs Page
            'view_all_text' => esc_html__('View all jobs', 'jobpress'),
        ) + array_fill_keys( array_keys( PublicEnqueue::get_colors() ), '' ); // hex colors; empty uses the appearance settings
    }

    function jobpress_jobs_shortcode($atts) {
        $atts = shortcode_atts( self::get_default_atts(), $atts, 'jobpress' );

        $design = jobpress_sanitize_design( $atts['design'] );
        if ( ! $design ) {
            $design = jobpress_get_short_design_type();
        }

        // Make sure styles load wherever the shortcode renders (e.g. Elementor content).
        PublicEnqueue::enqueue_styles( $design );

        self::$instance_count++;
        $listing_id = 'jp-listing-' . self::$instance_count;

        $template_args = array(
            'title'          => $atts['title'],
            'subtitle'       => $atts['subtitle'],
            'show_positions' => $atts['show_positions'],
            'listing_id'     => $listing_id,
            'atts'           => $atts,
        );
        $template_args = array_merge( $template_args, jobpress_get_listing_jobs( $design, $atts ) );

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
     * Render a listing template.
     *
     * Theme copies of the listing templates made before 2.3.0 run their own jobs
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
