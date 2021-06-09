<?php
namespace JobPressInc\Base;

/**
* Jobpress Single Page Template
*/
class SinglePageTemplate
{
	public function register() {
        add_filter( 'single_template', array( $this, 'jobpress_single_page_template' ) );
	}
	
	// Load Single Template
	public function jobpress_single_page_template( $single_template ) {
		global $post;

		if ( 'jobpress' === $post->post_type ) {
		    $theme_files = array('single-jobpress.php', 'jobpress/templates/single-jobpress.php');
		    $exists_in_theme = locate_template($theme_files, false);
		    if ( $exists_in_theme != '' ) {
		      	return $exists_in_theme;
		    }else{
                return JOBPRESS_PLUGIN_PATH . "/templates/jobpress-template/single-jobpress.php";
            }
		}
		return $single_template;
	}
}