<?php
namespace JobPressInc\Base;

class Activate
{
	public static function activate() {

		/***
		**Create jobpress list page on plugin activation time
		***/
		$jobpress_page_title     = __('Jobs Listing','jobpress');
		$jobpress_page_content   = '[jobpress]';
		$jobpress_page_check = get_page_by_title($jobpress_page_title);
		$jobpress_new_page = array(
				'post_type'     => 'page', 
				'post_title'    => $jobpress_page_title,
				'post_content'  => $jobpress_page_content,
				'post_status'   => 'publish',
				'post_author'   => get_current_user_id(),
				'post_name'     => 'jobs-list',
		);
		// If the page doesn't already exist, create it
		if(!isset($jobpress_page_check->ID)){
			wp_insert_post($jobpress_new_page);
		}
	}
}