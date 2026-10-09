<?php
namespace JobPressInc\Base;

/**
 * JobPress Jobs block (jobpress/jobs)
 *
 * A front end for the [jobpress] listing engine, like the Elementor widget: its
 * attributes are the shortcode attributes, and empty ones inherit the global
 * settings. The block is dynamic, so it is rendered here on every request. Its
 * editor script is built from src/blocks into assets/blocks (npm run build:blocks).
 */
class Blocks
{
	/**
	 * Block name.
	 */
	const NAME = 'jobpress/jobs';

	/**
	 * Lowest WordPress version the block is registered on (block API version 3).
	 */
	const MIN_WP_VERSION = '6.3';

	public function register() {
		if ( ! self::is_supported() ) {
			return;
		}

		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'add_editor_data' ) );
	}

	/**
	 * Whether this WordPress version runs the block.
	 *
	 * @return bool
	 */
	public static function is_supported() {
		return version_compare( get_bloginfo( 'version' ), self::MIN_WP_VERSION . '-alpha', '>=' );
	}

	/**
	 * Register the block from its built block.json.
	 */
	public function register_block() {
		// The editor styles (block.json editorStyle) are the public stylesheets.
		PublicEnqueue::register_styles();

		add_filter( 'block_type_metadata', array( $this, 'add_listing_attributes' ) );
		register_block_type(
			JOBPRESS_PLUGIN_PATH . 'assets/blocks/jobs',
			array( 'render_callback' => array( $this, 'render' ) )
		);
		remove_filter( 'block_type_metadata', array( $this, 'add_listing_attributes' ) );
	}

	/**
	 * Pass the editor data to the block's editor script.
	 */
	public function add_editor_data() {
		wp_add_inline_script(
			generate_block_asset_handle( self::NAME, 'editorScript' ),
			'window.jobpressBlock = ' . wp_json_encode( $this->get_editor_data() ) . ';',
			'before'
		);
	}

	/**
	 * Add an attribute for every global listing setting (Listing Defaults), so a
	 * new setting becomes a block attribute too. The editor reads the attributes
	 * from the server, so they are only declared here.
	 *
	 * @param array $metadata Block metadata from block.json.
	 * @return array
	 */
	public function add_listing_attributes( $metadata ) {
		if ( isset( $metadata['name'] ) && self::NAME === $metadata['name'] ) {
			foreach ( array_keys( jobpress_get_listing_settings() ) as $key ) {
				if ( ! isset( $metadata['attributes'][ $key ] ) ) {
					$metadata['attributes'][ $key ] = array(
						'type'    => 'string',
						'default' => '',
					);
				}
			}
		}
		return $metadata;
	}

	/**
	 * Data the editor needs: the designs, the global settings each control
	 * inherits, and which designs render each element.
	 *
	 * @return array
	 */
	private function get_editor_data() {
		$settings = array();
		foreach ( jobpress_get_listing_settings() as $key => $setting ) {
			$settings[ $key ] = array(
				'type'  => $setting['type'],
				'value' => jobpress_get_listing_setting( $key ),
			);
		}

		return array(
			'designs'      => jobpress_get_design_names(),
			'globalDesign' => (string) jobpress_get_short_design_type(),
			'settings'     => $settings,
			// Designs that render each element: card fields, the text button, the open positions
			// count, the apply button (v2's is an arrow link) and the grid columns.
			'designFields' => jobpress_get_listing_design_fields() + array(
				'positions'   => array( 1, 3, 5 ),
				'applyButton' => array( 1, 2, 3, 4 ),
				'columns'     => array( 5 ),
			),
			'settingsUrl'  => admin_url( 'edit.php?post_type=jobpress&page=jobpress_shortcode' ),
			'addJobUrl'    => admin_url( 'post-new.php?post_type=jobpress' ),
			// For converting [jobpress] shortcodes: the attributes the block shares with them.
			'shortcodeAttributes' => array_keys( JobListShortcode::get_default_atts() ),
		);
	}

	/**
	 * Map block attributes onto shortcode attributes. Empty ones are left out, so
	 * the shortcode falls back to the global settings.
	 *
	 * @param array $attributes Block attributes.
	 * @return array
	 */
	public static function get_shortcode_atts( $attributes ) {
		$atts = array();
		foreach ( array_keys( JobListShortcode::get_default_atts() ) as $key ) {
			if ( isset( $attributes[ $key ] ) && is_scalar( $attributes[ $key ] ) && '' !== trim( (string) $attributes[ $key ] ) ) {
				$atts[ $key ] = (string) $attributes[ $key ];
			}
		}
		return $atts;
	}

	/**
	 * Render the block.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public function render( $attributes ) {
		$listing = ( new JobListShortcode() )->jobpress_jobs_shortcode( self::get_shortcode_atts( $attributes ) );

		// The style settings are scoped by a class named after them, so blocks with
		// the same styles share it, also across separately rendered editor previews.
		$declarations = self::get_style_rules( $attributes );
		$class        = $declarations ? 'jp-block-' . substr( md5( wp_json_encode( $declarations ) ), 0, 8 ) : '';
		$style        = '';
		if ( $declarations ) {
			foreach ( $declarations as $selector => $css ) {
				$scope  = '.' . $class . ' .jp-listing.jp-listing';
				$style .= $scope . str_replace( ', ', ', ' . $scope . ' ', $selector ) . '{' . $css . '}';
			}
			$style = '<style>' . $style . '</style>';
		}

		// The editor preview sits inside the editor's own block wrapper, which has the block supports' styles.
		$is_preview = defined( 'REST_REQUEST' ) && REST_REQUEST && ! empty( $_GET['jobpress_preview'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$wrapper    = $is_preview
			? ( $class ? 'class="' . esc_attr( $class ) . '"' : '' )
			: get_block_wrapper_attributes( $class ? array( 'class' => $class ) : array() );

		return $style . sprintf( '<div %s>%s</div>', $wrapper, $listing );
	}

	/**
	 * CSS of the block's style settings, as declarations keyed by selector. The
	 * selectors are relative to the listing; one may list several, separated by ", ".
	 *
	 * @param array $attributes Block attributes.
	 * @return string[]
	 */
	public static function get_style_rules( $attributes ) {
		$rules = array();
		$add   = function ( $selector, $css ) use ( &$rules ) {
			$rules[ $selector ] = ( isset( $rules[ $selector ] ) ? $rules[ $selector ] : '' ) . $css;
		};
		$number = function ( $key, $max ) use ( $attributes ) {
			if ( ! isset( $attributes[ $key ] ) || ! is_numeric( $attributes[ $key ] ) ) {
				return null;
			}
			return max( 0, min( $max, (int) $attributes[ $key ] ) );
		};
		$color = function ( $key ) use ( $attributes ) {
			return isset( $attributes[ $key ] ) ? self::sanitize_color( $attributes[ $key ] ) : '';
		};

		$columns = $number( 'columns', 6 );
		if ( $columns ) {
			// A maximum, like the Elementor widget's: the grid drops columns where cards would be narrower than 180px.
			$add( ' .jobpress-job-grids .jp-row', 'display:grid;grid-template-columns:repeat(auto-fill,minmax(min(100%,max(180px,calc(100% / ' . $columns . ' - var(--jp-grid-gap, 24px)))),1fr));' );
		}

		$gap = $number( 'cardGap', 200 );
		if ( null !== $gap ) {
			$add( ' .jobpress-job-lists.jp-listing__jobs', 'display:flex;flex-direction:column;gap:' . $gap . 'px;' );
			$add( ' .jobpress-job-grids .jp-row', 'gap:' . $gap . 'px;--jp-grid-gap:' . $gap . 'px;' );
			$add( ' .jp-listing__jobs .jp-listing__card', 'margin-bottom:0;' );
		}

		$padding = $number( 'cardPadding', 200 );
		if ( null !== $padding ) {
			$add( ' .jp-listing__jobs .jp-listing__card', 'padding:' . $padding . 'px;' );
		}

		$radius = $number( 'cardRadius', 200 );
		if ( null !== $radius ) {
			$add( ' .jp-listing__jobs .jp-listing__card', 'border-radius:' . $radius . 'px;' );
		}

		if ( $color( 'cardBackground' ) ) {
			$add( ' .jp-listing__jobs .jp-listing__card', 'background-color:' . $color( 'cardBackground' ) . ';' );
		}

		$button = ' .jp-listing__button';
		$hover  = ' .jp-listing__button:hover, .jp-listing__button:focus';
		if ( $color( 'buttonColor' ) ) {
			// The designs set the button text color with !important; v2's arrow button is an icon.
			$add( $button, 'color:' . $color( 'buttonColor' ) . ' !important;' );
			$add( ' .jp-listing__button svg', 'fill:' . $color( 'buttonColor' ) . ';' );
		}
		if ( $color( 'buttonBackground' ) ) {
			$add( $button, 'background-color:' . $color( 'buttonBackground' ) . ';' );
		}
		if ( $color( 'buttonHoverColor' ) ) {
			$add( $hover, 'color:' . $color( 'buttonHoverColor' ) . ' !important;' );
			$add( ' .jp-listing__button:hover svg, .jp-listing__button:focus svg', 'fill:' . $color( 'buttonHoverColor' ) . ';' );
		}
		if ( $color( 'buttonHoverBackground' ) ) {
			$add( $hover, 'background-color:' . $color( 'buttonHoverBackground' ) . ';' );
		}

		return $rules;
	}

	/**
	 * Validate a color from the block's color pickers.
	 *
	 * @param mixed $color Hex, rgb() or hsl() color.
	 * @return string The color, or '' when it is not one.
	 */
	public static function sanitize_color( $color ) {
		$color = trim( (string) $color );
		if ( sanitize_hex_color( $color ) ) {
			return $color;
		}
		return preg_match( '/^(rgb|hsl)a?\([\d\s.,%\/deg]+\)$/i', $color ) ? $color : '';
	}
}
