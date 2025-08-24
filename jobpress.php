<?php
/*
Plugin Name: JobPress
Plugin URI: https://wordpress.org/plugins/jobpress/
Description: JobPress is the ultimate WordPress job board plugin for a company. It is designed & developed to build your company own job board and career page by few clicks.
Version: 2.1.4
Author: Aminur Islam Arnob
Author URI: https://aiarnob.com/
License: GPLv2 or later
Text Domain: jobpress
*/

// If this file is called firectly, abort!!!
defined( 'ABSPATH' ) or die( 'Hey, what are you doing here?' );

// Require once the Composer Autoload
if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

// Define Version.
if ( ! defined( 'JOBPRESS_VERSION' ) ) {
	define( 'JOBPRESS_VERSION', '2.1.4' );
}

// Define CONSTANTS
define( 'JOBPRESS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'JOBPRESS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'JOBPRESS_PLUGIN', plugin_basename( __FILE__ ) );

// Include core files
require_once JOBPRESS_PLUGIN_PATH . 'inc/core-functions.php';
require_once JOBPRESS_PLUGIN_PATH . 'inc/template-functions.php';
require_once JOBPRESS_PLUGIN_PATH . 'inc/template-hooks.php';

use JobPressInc\Base\Activate;
use JobPressInc\Base\Deactivate;
use JobPressInc\JobPressPluginInit;
use JobPressInc\Base\CreateDbTable;


/**
 * The code that runs during plugin activation
 */
function jobpress_plugin_activate() {
	Activate::activate();
	CreateDbTable::create_application_db_table();
	JobPressInc\Base\Flush::add_flush_rewrite_rules_flag();
}

/**
 * The code that runs during plugin deactivation
 */
function jobpress_plugin_deactivate() {
	Deactivate::deactivate();
}

register_activation_hook( __FILE__, 'jobpress_plugin_activate' );
register_deactivation_hook( __FILE__, 'jobpress_plugin_deactivate' );

/**
 * Initialize all the core classes of the plugin
 */
if ( class_exists( 'JobPressInc\\JobPressPluginInit' ) ) {
	JobPressPluginInit::register_services();
}

/**
 * Handle plugin updates via WordPress upgrader
 */
function jobpress_upgrader_process_complete( $upgrader, $options ) {
	// Only run for our plugin
	if ( $options['action'] === 'update' && $options['type'] === 'plugin' ) {
		foreach ( $options['plugins'] as $plugin ) {
			if ( $plugin === plugin_basename( __FILE__ ) ) {
				Activate::handle_update();
				break;
			}
		}
	}
}

// Hook to run when plugins are updated (more efficient)
add_action( 'upgrader_process_complete', 'jobpress_upgrader_process_complete', 10, 2 );