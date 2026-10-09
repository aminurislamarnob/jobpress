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

    function jobpress_jobs_shortcode($atts) {
        $atts = shortcode_atts( array(
            'expand' => '',
            'design' => '', // 1-5; empty uses the design selected in the shortcode settings
            'title' => esc_html__('Job openings', 'jobpress'),
            'subtitle' => esc_html__('Find the right job for you no matter what it is that you do.', 'jobpress'),
            'show_positions' => 'yes' // yes/no to show/hide open positions count
        ), $atts, 'jobpress' );

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
        );
        $template_args = array_merge( $template_args, jobpress_get_listing_jobs( $design ) );

        //Load Template
        ob_start();
        printf(
            '<div id="%s" class="%s">',
            esc_attr( $listing_id ),
            esc_attr( 'jp-listing jp-design-v' . $design )
        );
        jobpress_get_template( 'listing/jobpress-listing-v' . $design . '.php', $template_args );
        echo '</div>';
        return ob_get_clean();
    }
}
