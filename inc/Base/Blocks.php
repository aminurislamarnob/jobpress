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
			// Designs that render each element: card fields, the button and the open positions count.
			'designFields' => jobpress_get_listing_design_fields() + array( 'positions' => array( 1, 3, 5 ) ),
			'settingsUrl'  => admin_url( 'edit.php?post_type=jobpress&page=jobpress_shortcode' ),
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

		return sprintf( '<div %s>%s</div>', get_block_wrapper_attributes(), $listing );
	}
}
