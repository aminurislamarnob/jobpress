<?php
namespace JobPressInc\Base;

/**
* Enqueue public/frontend styles and scripts
*/
class PublicEnqueue
{
	public function register() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action('wp_head', array( $this, 'settings_appeareance_styles' ), 100);
	}
	
	function enqueue() {
		// enqueue all our scripts
		$jobpress_design_type = !empty(get_option('jobpress_design_type')) ? get_option('jobpress_design_type') : '1';
		wp_enqueue_style( 'jobpress-css', JOBPRESS_PLUGIN_URL . 'assets/public/css/jobpress-style-v'.$jobpress_design_type.'.css', array(), JOBPRESS_VERSION, 'all' );
	}

	function settings_appeareance_styles(){
		$jobpress_brand_color_value = !empty(get_option('jobpress_brand_color')) ? get_option('jobpress_brand_color') : '#0086fe';
		$jobpress_heading_color_value = !empty(get_option('jobpress_heading_color')) ? get_option('jobpress_heading_color') : '#283339';
		$jobpress_content_color_value = !empty(get_option('jobpress_content_color')) ? get_option('jobpress_content_color') : '#3a3a3a';
		$jobpress_secondary_color_value = !empty(get_option('jobpress_secondary_color')) ? get_option('jobpress_secondary_color') : '#5f7681';
		$jobpress_border_color_value = !empty(get_option('jobpress_border_color')) ? get_option('jobpress_border_color') : '#e7ebee';
		$jobpress_hover_color_value = !empty(get_option('jobpress_hover_color')) ? get_option('jobpress_hover_color') : '#006dcc';
		echo '<style>
		:root {
			--jp-primary-color: '.esc_html($jobpress_heading_color_value).';
			--jp-secondary-color: '.esc_html($jobpress_secondary_color_value).';
			--jp-content-color: '.esc_html($jobpress_content_color_value).';
			--jp-border-color: '.esc_html($jobpress_border_color_value).';
			--jp-brand-color: '.esc_html($jobpress_brand_color_value).';
			--jp-hover-color: '.esc_html($jobpress_hover_color_value).';
		}
		</style>';
	}
}