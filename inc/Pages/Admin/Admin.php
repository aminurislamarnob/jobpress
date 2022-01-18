<?php 
namespace JobPressInc\Pages\Admin;

/**
* 
*/
class Admin
{
	public function register() {
		add_action( 'admin_menu', array( $this, 'add_admin_jobpress_panel_page' ) );
	}

	public function add_admin_jobpress_panel_page() {

		//plugin settings page
		add_submenu_page(
			esc_url('edit.php?post_type=jobpress'),
			__( 'JobPress Settings', 'jobpress' ),
			__( 'Settings', 'jobpress' ),
			'manage_options',
			'jobpress_settings',
			array( $this, 'jobpress_settings_template' )
		);

		//plugin settings Appearance page
		add_submenu_page(
            esc_url('edit.php?post_type=jobpress'),
			__( 'JobPress Appearance Settings', 'jobpress' ),
			__( 'Appearance', 'jobpress' ),
			'manage_options',
			'jobpress_appearance_settings',
			array( $this, 'jobpress_appearance_settings_template' )
		);

		//plugin settings Appearance page
		add_submenu_page(
            esc_url('edit.php?post_type=jobpress'),
			__( 'JobPress Shortcodes Settings', 'jobpress' ),
			__( 'Shortcodes', 'jobpress' ),
			'manage_options',
			'jobpress_shortcode',
			array( $this, 'jobpress_shortcode_template' )
		);
	}

	public function jobpress_settings_template() {
		require_once JOBPRESS_PLUGIN_PATH . 'templates/task-pages/settings.php';
	}

	public function jobpress_appearance_settings_template() {
		require_once JOBPRESS_PLUGIN_PATH . 'templates/task-pages/appearance-settings.php';
	}

	public function jobpress_shortcode_template() {
		require_once JOBPRESS_PLUGIN_PATH . 'templates/task-pages/shortcodes-settings.php';
	}
}