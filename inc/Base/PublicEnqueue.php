<?php
namespace JobPressInc\Base;

/**
* Enqueue public/frontend styles and scripts
*
* jobpress-common.css holds the shared styles; each listing design has its own
* stylesheet (jobpress-design-v{N}) with rules scoped to .jp-design-v{N}, and
* only the designs shown on the page are loaded.
*/
class PublicEnqueue
{
	public function register() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_filter( 'body_class', array( $this, 'add_design_body_class' ) );
	}

	function enqueue() {
		self::register_styles();

		// Only load assets where JobPress output is shown. The [jobpress] shortcode
		// also enqueues them itself, as a fallback for content we can't detect here.
		$page_design = self::get_page_design();
		if ( $page_design ) {
			self::enqueue_styles( $page_design );
		}

		// The Jobs Page holds [jobpress], but the archive template replaces its content.
		if ( self::is_archive_view() ) {
			return;
		}

		foreach ( $this->get_current_post_designs() as $design ) {
			self::enqueue_styles( $design );
		}
	}

	/**
	 * Register the public styles, once per request.
	 */
	public static function register_styles() {
		if ( wp_style_is( 'jobpress-common', 'registered' ) ) {
			return;
		}

		wp_register_style( 'jobpress-common', JOBPRESS_PLUGIN_URL . 'assets/public/css/jobpress-common.css', array(), JOBPRESS_VERSION, 'all' );
		wp_add_inline_style( 'jobpress-common', self::get_appearance_styles() );

		for ( $design = 1; $design <= 5; $design++ ) {
			wp_register_style( 'jobpress-design-v' . $design, JOBPRESS_PLUGIN_URL . 'assets/public/css/jobpress-style-v' . $design . '.css', array( 'jobpress-common' ), JOBPRESS_VERSION, 'all' );
		}
	}

	/**
	 * Enqueue the public styles, registering them first if needed.
	 *
	 * @param int $design Listing design whose stylesheet to load (1-5). Defaults to the
	 *                    design of the current page or, failing that, the global design.
	 */
	public static function enqueue_styles( $design = 0 ) {
		self::register_styles();

		$design = jobpress_sanitize_design( $design );
		if ( ! $design ) {
			$design = self::get_page_design();
		}
		if ( ! $design ) {
			$design = jobpress_get_short_design_type();
		}

		wp_enqueue_style( 'jobpress-common' );
		wp_enqueue_style( 'jobpress-design-v' . $design );
	}

	/**
	 * Enqueue the shared styles and every design's stylesheet, e.g. for the
	 * Elementor editor preview, where the design can change without a page load.
	 */
	public static function enqueue_all_styles() {
		for ( $design = 1; $design <= 5; $design++ ) {
			self::enqueue_styles( $design );
		}
	}

	/**
	 * Mark JobPress pages with the design that styles them, so the design's scoped rules apply.
	 *
	 * @param string[] $classes Body classes.
	 * @return string[]
	 */
	public function add_design_body_class( $classes ) {
		$design = self::get_page_design();
		if ( $design ) {
			$classes[] = 'jp-design-v' . $design;
		}
		return $classes;
	}

	/**
	 * Design of a JobPress page: the jobs archive layout is styled by the v3
	 * stylesheet, and single job pages follow the selected design.
	 *
	 * @return int Design number, or 0 when the current view is not a JobPress page.
	 */
	private static function get_page_design() {
		if ( self::is_archive_view() ) {
			return 3;
		}
		if ( is_singular( 'jobpress' ) ) {
			return jobpress_get_short_design_type();
		}
		return 0;
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
	 * Designs of the job lists the current singular post shows: [jobpress]
	 * shortcodes and JobPress Jobs blocks in its content, and JobPress widgets in
	 * its Elementor layout.
	 *
	 * Detecting them here loads the styles in the head; otherwise the shortcode
	 * enqueues them late and they print in the footer, after the list renders.
	 *
	 * @return int[]
	 */
	private function get_current_post_designs() {
		$post = get_post();
		if ( ! is_singular() || ! $post ) {
			return array();
		}

		$designs = array();

		if ( has_shortcode( $post->post_content, 'jobpress' ) && preg_match_all( '/' . get_shortcode_regex( array( 'jobpress' ) ) . '/', $post->post_content, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				$atts      = shortcode_parse_atts( $match[3] );
				$designs[] = isset( $atts['design'] ) ? $atts['design'] : '';
			}
		}

		if ( has_block( Blocks::NAME, $post ) ) {
			$designs = array_merge( $designs, self::get_block_designs( parse_blocks( $post->post_content ) ) );
		}

		$elementor_data = get_post_meta( $post->ID, '_elementor_data', true );
		if ( is_string( $elementor_data ) && false !== strpos( $elementor_data, '"widgetType":"jobpress_jobs"' ) ) {
			$elements = json_decode( $elementor_data, true );
			if ( is_array( $elements ) ) {
				$designs = array_merge( $designs, self::get_elementor_widget_designs( $elements ) );
			}
		}

		$designs = array_map(
			function ( $design ) {
				$design = jobpress_sanitize_design( $design );
				return $design ? $design : jobpress_get_short_design_type();
			},
			$designs
		);

		return array_values( array_unique( $designs ) );
	}

	/**
	 * Collect the design attribute of every JobPress Jobs block, including nested ones.
	 *
	 * @param array[] $blocks Parsed blocks.
	 * @return string[] Raw design attributes ('' means the global design).
	 */
	private static function get_block_designs( $blocks ) {
		$designs = array();
		foreach ( $blocks as $block ) {
			if ( Blocks::NAME === $block['blockName'] ) {
				$designs[] = isset( $block['attrs']['design'] ) ? $block['attrs']['design'] : '';
			}
			if ( ! empty( $block['innerBlocks'] ) ) {
				$designs = array_merge( $designs, self::get_block_designs( $block['innerBlocks'] ) );
			}
		}
		return $designs;
	}

	/**
	 * Collect the design setting of every JobPress widget in Elementor data.
	 *
	 * @param array $elements Elementor elements.
	 * @return string[] Raw design settings ('' means the global design).
	 */
	private static function get_elementor_widget_designs( $elements ) {
		$designs = array();
		foreach ( $elements as $element ) {
			if ( ! is_array( $element ) ) {
				continue;
			}
			if ( isset( $element['widgetType'] ) && 'jobpress_jobs' === $element['widgetType'] ) {
				$designs[] = isset( $element['settings']['design'] ) ? $element['settings']['design'] : '';
			}
			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$designs = array_merge( $designs, self::get_elementor_widget_designs( $element['elements'] ) );
			}
		}
		return $designs;
	}

	/**
	 * The appearance colors: the shortcode attribute (and Elementor control) name
	 * of each, with its CSS custom property, setting and default value.
	 *
	 * @return array[] Keyed by attribute name.
	 */
	public static function get_colors() {
		return array(
			'heading_color'   => array( '--jp-primary-color', 'jobpress_heading_color', '#283339' ),
			'secondary_color' => array( '--jp-secondary-color', 'jobpress_secondary_color', '#5f7681' ),
			'content_color'   => array( '--jp-content-color', 'jobpress_content_color', '#3a3a3a' ),
			'border_color'    => array( '--jp-border-color', 'jobpress_border_color', '#e7ebee' ),
			'brand_color'     => array( '--jp-brand-color', 'jobpress_brand_color', '#0086fe' ),
			'hover_color'     => array( '--jp-hover-color', 'jobpress_hover_color', '#006dcc' ),
		);
	}

	/**
	 * Build the CSS custom properties from the appearance settings.
	 *
	 * @return string
	 */
	private static function get_appearance_styles() {
		$css = ':root {';
		foreach ( self::get_colors() as $color ) {
			// Re-validate on output too, since values saved before sanitizing was added may be unsafe.
			$value = sanitize_hex_color( get_option( $color[1] ) );
			$css  .= $color[0] . ': ' . ( $value ? $value : $color[2] ) . ';';
		}
		$css .= '}';

		return $css;
	}

	/**
	 * Build the CSS custom properties that override the appearance colors for one listing.
	 *
	 * @param array $atts Shortcode attributes; valid hex values of the color attributes are used.
	 * @return string Declarations for a style attribute, or '' when no color is overridden.
	 */
	public static function get_color_overrides( $atts ) {
		$css = '';
		foreach ( self::get_colors() as $attribute => $color ) {
			$value = isset( $atts[ $attribute ] ) ? sanitize_hex_color( $atts[ $attribute ] ) : '';
			if ( $value ) {
				$css .= $color[0] . ':' . $value . ';';
			}
		}
		return $css;
	}
}
