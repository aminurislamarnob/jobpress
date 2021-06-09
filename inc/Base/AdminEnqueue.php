<?php
namespace JobPressInc\Base;

/**
* Enqueue admin styles and scripts
*/
class AdminEnqueue
{
	public function register() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}
	
	function enqueue( $hook_suffix ) {
		$screen = get_current_screen();

		if( is_object( $screen ) && 'jobpress' == $screen->post_type ){

            // enqueue all our scripts
			wp_enqueue_style( 'jobpress-admin-css', JOBPRESS_PLUGIN_URL . 'assets/admin/css/jobpress-admin-style.css', array(), JOBPRESS_VERSION, 'all' );
			wp_enqueue_script( 'jobpress-admin-js', JOBPRESS_PLUGIN_URL . 'assets/admin/js/jobpress-admin-script.js', array('jquery'), JOBPRESS_VERSION, true );

			//wp color picker
			wp_enqueue_style( 'wp-color-picker');
			wp_enqueue_script( 'wp-color-picker');

        }
	}
}