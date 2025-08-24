<?php
namespace JobPressInc\Base;

/**
* Jobpress Single Page Template
*/
class SinglePageTemplate
{
	public function register() {
		add_filter( 'post_thumbnail_html', array($this, 'jobpress_remove_single_content_featured_img'), 10, 3 );
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
}