<?php
namespace JobPressInc\Base;

/**
* Jobpress Single Page Template
*/
class SinglePageTemplate
{
	public function register() {
		add_filter('the_content', array($this, 'jobpress_single_content'));
		add_filter( 'post_thumbnail_html', array($this, 'jobpress_remove_single_content_featured_img'), 10, 3 );
		// add_filter( 'the_title', array($this, 'jobpress_remove_single_content_title'), 10, 2 );
		// add_filter( 'single_template', array( $this, 'jobpress_single_page_template' ) );
	}

	public function jobpress_remove_single_content_title( $title, $id = null ){
		global $post;
		if(!is_singular('jobpress') || !in_the_loop()) {
			return $title;
		}

		if('jobpress' === $post->post_type) {
			return '';
		}
		return $title;
	}

	/**
	 * Remove featured image from single job template.
	 *
	 * @param string $html
	 * @param int $post_id
	 * @param int $post_image_id
	 * @return string
	 */
	public function jobpress_remove_single_content_featured_img( $html, $post_id, $post_image_id ) {
		global $post;
		if(!is_singular('jobpress') || !in_the_loop()) {
			return $html;
		}

		if('jobpress' === $post->post_type) {
			return '';
		}
		return $html;
	}

	/**
	 * Replace single content by jobpress single content
	 *
	 * @param string $content
	 * @return string
	 */
	public function jobpress_single_content($content){
		global $post;

		if(!is_singular('jobpress') || !in_the_loop()) {
			return $content;
		}
		remove_filter('the_content', array($this, 'jobpress_single_content'));
		if('jobpress' === $post->post_type) {
			ob_start();
			do_action('jobpress_single_content_start');
			$this->get_jobpress_single_template_part();
			do_action('jobpress_single_content_end');
			$content = ob_get_clean();
		}
		add_filter('the_content', array($this, 'jobpress_single_content'));
		return apply_filters('event_manager_single_event_content', $content, $post);
	}

	/**
	 * Get template part (for templates in loops).
	 *
	 */
	function get_jobpress_single_template_part(){
		$theme_files = array('single-jobpress.php', 'jobpress/templates/single-jobpress.php');
		$exists_in_theme = locate_template($theme_files, false);
		$template = '';
		if ( $exists_in_theme != '' ) {
			$template = $exists_in_theme;
		}else{
			$template = JOBPRESS_PLUGIN_PATH . "templates/jobpress-template/single-jobpress.php";
		}

		if($template) {
			load_template($template, false);
		}
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