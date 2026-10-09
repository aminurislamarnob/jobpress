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
		$is_archive_view = jobpress_is_jobs_page() || is_post_type_archive( 'jobpress' ) || is_tax( array( 'jobpress_category', 'jobpress_type' ) );

		// The jobs archive layout is styled by the v3 stylesheet; everything else follows the selected design.
		$design_type = $is_archive_view ? 3 : jobpress_get_short_design_type();
		wp_register_style( 'jobpress-css', JOBPRESS_PLUGIN_URL . 'assets/public/css/jobpress-style-v' . $design_type . '.css', array(), JOBPRESS_VERSION, 'all' );
		wp_register_style( 'jobpress-common', JOBPRESS_PLUGIN_URL . 'assets/public/css/jobpress-common.css', array( 'jobpress-css' ), JOBPRESS_VERSION, 'all' );
		wp_add_inline_style( 'jobpress-common', $this->get_appearance_styles() );

		// Only load assets where JobPress output is shown. The [jobpress] shortcode
		// also enqueues them itself, which covers page builders such as Elementor.
		if ( $is_archive_view || is_singular( 'jobpress' ) || $this->current_post_has_shortcode() ) {
			self::enqueue_styles();
		}
	}

	/**
	 * Enqueue the registered public styles.
	 */
	public static function enqueue_styles() {
		wp_enqueue_style( 'jobpress-css' );
		wp_enqueue_style( 'jobpress-common' );
	}

	/**
	 * Check whether the current singular post contains the [jobpress] shortcode.
	 *
	 * @return bool
	 */
	private function current_post_has_shortcode() {
		$post = get_post();
		return is_singular() && $post && has_shortcode( $post->post_content, 'jobpress' );
	}

	/**
	 * Build the CSS custom properties from the appearance settings.
	 *
	 * @return string
	 */
	private function get_appearance_styles() {
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
