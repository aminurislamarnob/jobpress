<?php
/**
 * JobPress Admin Class
 * 
 * Handles the admin menu and page templates for the JobPress plugin.
 * 
 * @package JobPress
 * @since 1.0.0
 */

namespace JobPressInc\Pages\Admin;

/**
 * Admin class for managing JobPress admin interface
 */
class Admin
{
	/**
	 * Register admin hooks and actions
	 * 
	 * @since 1.0.0
	 * @return void
	 */
	public function register() {
		add_action( 'admin_menu', array( $this, 'add_admin_jobpress_panel_page' ) );
		add_action( 'admin_head', array( $this, 'hide_submenu_items' ) );
	}

	/**
	 * Hide specific submenu items from the admin menu
	 * 
	 * @since 1.0.0
	 * @return void
	 */
	public function hide_submenu_items() {
		global $submenu;
		
		// Hide appearance and shortcode pages from submenu
		if ( isset( $submenu['edit.php?post_type=jobpress'] ) ) {
			foreach ( $submenu['edit.php?post_type=jobpress'] as $key => $item ) {
				if ( in_array( $item[2], array( 'jobpress_appearance_settings', 'jobpress_shortcode' ), true ) ) {
					unset( $submenu['edit.php?post_type=jobpress'][ $key ] );
				}
			}
		}
	}

	/**
	 * Add JobPress admin submenu pages
	 * 
	 * @since 1.0.0
	 * @return void
	 */
	public function add_admin_jobpress_panel_page() {
		// Main plugin settings page
		add_submenu_page(
			'edit.php?post_type=jobpress',
			__( 'JobPress Settings', 'jobpress' ),
			__( 'Settings', 'jobpress' ),
			'manage_options',
			'jobpress_settings',
			array( $this, 'jobpress_settings_template' )
		);

		// Note: Appearance and Shortcode pages are hidden from submenu
		// but remain accessible directly via their URLs for better UX
		// 
		// Appearance settings page - accessible via navigation but hidden from submenu
		add_submenu_page(
			'edit.php?post_type=jobpress',
			__( 'JobPress Appearance Settings', 'jobpress' ),
			__( 'Appearance', 'jobpress' ),
			'manage_options',
			'jobpress_appearance_settings',
			array( $this, 'jobpress_appearance_settings_template' )
		);
		
		// Shortcode settings page - accessible via navigation but hidden from submenu
		add_submenu_page(
			'edit.php?post_type=jobpress',
			__( 'JobPress Shortcodes Settings', 'jobpress' ),
			__( 'Shortcodes', 'jobpress' ),
			'manage_options',
			'jobpress_shortcode',
			array( $this, 'jobpress_shortcode_template' )
		);
	}

	/**
	 * Load the main settings template
	 * 
	 * @since 1.0.0
	 * @return void
	 */
	public function jobpress_settings_template() {
		require_once JOBPRESS_PLUGIN_PATH . 'templates/task-pages/settings.php';
	}

	/**
	 * Load the appearance settings template
	 * 
	 * @since 1.0.0
	 * @return void
	 */
	public function jobpress_appearance_settings_template() {
		require_once JOBPRESS_PLUGIN_PATH . 'templates/task-pages/appearance-settings.php';
	}

	/**
	 * Load the shortcode settings template
	 * 
	 * @since 1.0.0
	 * @return void
	 */
	public function jobpress_shortcode_template() {
		require_once JOBPRESS_PLUGIN_PATH . 'templates/task-pages/shortcodes-settings.php';
	}
}