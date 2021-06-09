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
        ), $atts) );
    
        //Load Template
        ob_start();
        $theme_files = array('jobpress-default.php', 'jobpress/templates/listing/jobpress-default.php');
        $exists_in_theme = locate_template($theme_files, false);
        if ( $exists_in_theme != '' ) {
            require $exists_in_theme;
        }else{
            $jobpress_design_type = !empty(get_option('jobpress_design_type')) ? get_option('jobpress_design_type') : '1';
            require JOBPRESS_PLUGIN_PATH . 'templates/jobpress-template/listing/jobpress-listing-v'.$jobpress_design_type.'.php';
        }
        $string = ob_get_clean();
        return $string;
    }
}