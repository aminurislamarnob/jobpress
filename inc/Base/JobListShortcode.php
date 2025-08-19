<?php
namespace JobPressInc\Base;

/**
* Job List Shortcode
*/
class JobListShortcode
{
	public function register() {
        add_shortcode('jobpress', array( $this, 'jobpress_jobs_shortcode' ) );
	}
	
    function jobpress_jobs_shortcode($atts) {
        extract( shortcode_atts( array(
            'expand' => '',
            'title' => esc_html__('Job openings', 'jobpress'),
            'subtitle' => esc_html__('Find the right job for you no matter what it is that you do.', 'jobpress'),
            'show_positions' => 'yes' // yes/no to show/hide open positions count
        ), $atts) );
    
        //Load Template
        ob_start();
        $theme_files = array('jobpress-default.php', 'jobpress/templates/listing/jobpress-default.php');
        $exists_in_theme = locate_template($theme_files, false);
        if ( $exists_in_theme != '' ) {
            require $exists_in_theme;
        }else{
            // First check shortcode type, then fallback to global setting
            $jobpress_design_type = !empty(get_option('jobpress_design_type')) ? get_option('jobpress_design_type') : '1';
            require JOBPRESS_PLUGIN_PATH . 'templates/jobpress-template/listing/jobpress-listing-v'.$jobpress_design_type.'.php';
        }
        $string = ob_get_clean();
        return $string;
    }
}