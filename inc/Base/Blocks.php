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

		register_block_type(
			JOBPRESS_PLUGIN_PATH . 'assets/blocks/jobs',
			array( 'render_callback' => array( $this, 'render' ) )
		);

		wp_add_inline_script(
			generate_block_asset_handle( self::NAME, 'editorScript' ),
			'window.jobpressBlock = ' . wp_json_encode( $this->get_editor_data() ) . ';',
			'before'
		);
	}

	/**
	 * Data the editor needs: the designs and the global design.
	 *
	 * @return array
	 */
	private function get_editor_data() {
		return array(
			'designs'      => jobpress_get_design_names(),
			'globalDesign' => (string) jobpress_get_short_design_type(),
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
