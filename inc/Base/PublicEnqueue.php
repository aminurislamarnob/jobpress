<?php
namespace JobPressInc\Base;

/**
* Enqueue public/frontend styles and scripts
*/
class PublicEnqueue
{
	public function register() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
	}
	
	function enqueue() {
		self::register_styles();

		// Only load assets where JobPress output is shown. The [jobpress] shortcode
		// also enqueues them itself, as a fallback for content we can't detect here.
		if ( self::is_archive_view() || is_singular( 'jobpress' ) || $this->current_post_shows_jobs() ) {
			self::enqueue_styles();
		}
	}

	/**
	 * Register the public styles, once per request.
	 */
	public static function register_styles() {
		if ( wp_style_is( 'jobpress-common', 'registered' ) ) {
			return;
		}

		// The jobs archive layout is styled by the v3 stylesheet; everything else follows the selected design.
		$design_type = self::is_archive_view() ? 3 : jobpress_get_short_design_type();
		wp_register_style( 'jobpress-css', JOBPRESS_PLUGIN_URL . 'assets/public/css/jobpress-style-v' . $design_type . '.css', array(), JOBPRESS_VERSION, 'all' );
		wp_register_style( 'jobpress-common', JOBPRESS_PLUGIN_URL . 'assets/public/css/jobpress-common.css', array( 'jobpress-css' ), JOBPRESS_VERSION, 'all' );
		wp_add_inline_style( 'jobpress-common', self::get_appearance_styles() );
	}

	/**
	 * Enqueue the public styles, registering them first if needed.
	 */
	public static function enqueue_styles() {
		self::register_styles();
		wp_enqueue_style( 'jobpress-css' );
		wp_enqueue_style( 'jobpress-common' );
	}

	/**
	 * Whether the current view is the jobs page or a category/type archive.
	 *
	 * @return bool
	 */
	private static function is_archive_view() {
		return jobpress_is_jobs_page() || is_post_type_archive( 'jobpress' ) || is_tax( array( 'jobpress_category', 'jobpress_type' ) );
	}

	/**
	 * Check whether the current singular post shows a job list: the [jobpress]
	 * shortcode in its content, or the JobPress widget in its Elementor layout.
	 *
	 * Detecting it here loads the styles in the head; otherwise the shortcode
	 * enqueues them late and they print in the footer, after the list renders.
	 *
	 * @return bool
	 */
	private function current_post_shows_jobs() {
		$post = get_post();
		if ( ! is_singular() || ! $post ) {
			return false;
		}

		if ( has_shortcode( $post->post_content, 'jobpress' ) ) {
			return true;
		}

		$elementor_data = get_post_meta( $post->ID, '_elementor_data', true );
		return is_string( $elementor_data ) && false !== strpos( $elementor_data, '"widgetType":"jobpress_jobs"' );
	}

	/**
	 * Build the CSS custom properties from the appearance settings.
	 *
	 * @return string
	 */
	private static function get_appearance_styles() {
		$colors = array(
			'--jp-primary-color'   => array( 'jobpress_heading_color', '#283339' ),
			'--jp-secondary-color' => array( 'jobpress_secondary_color', '#5f7681' ),
			'--jp-content-color'   => array( 'jobpress_content_color', '#3a3a3a' ),
			'--jp-border-color'    => array( 'jobpress_border_color', '#e7ebee' ),
			'--jp-brand-color'     => array( 'jobpress_brand_color', '#0086fe' ),
			'--jp-hover-color'     => array( 'jobpress_hover_color', '#006dcc' ),
		);

		$css = ':root {';
		foreach ( $colors as $property => $option ) {
			// Re-validate on output too, since values saved before sanitizing was added may be unsafe.
			$value = sanitize_hex_color( get_option( $option[0] ) );
			$css  .= $property . ': ' . ( $value ? $value : $option[1] ) . ';';
		}
		$css .= '}';

		return $css;
	}
}
