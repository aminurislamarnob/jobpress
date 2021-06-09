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
		// enqueue all our scripts
		$jobpress_design_type = !empty(get_option('jobpress_design_type')) ? get_option('jobpress_design_type') : '1';
		wp_enqueue_style( 'jobpress-css', JOBPRESS_PLUGIN_URL . 'assets/public/css/jobpress-style-v'.$jobpress_design_type.'.css', array(), JOBPRESS_VERSION, 'all' );
	}
}