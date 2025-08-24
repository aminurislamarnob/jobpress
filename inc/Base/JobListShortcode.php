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
        $template_name = 'listing/jobpress-listing-v'.jobpress_get_short_design_type().'.php';
        jobpress_get_template( $template_name, array(
            'title' => $title,
            'subtitle' => $subtitle,
            'show_positions' => $show_positions
        ) );
        $string = ob_get_clean();
        return $string;
    }
}