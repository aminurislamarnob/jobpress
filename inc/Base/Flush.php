<?php
namespace JobPressInc\Base;

class Flush
{
    public function register() 
	{
        // Run after the jobpress post type and taxonomies are registered on init (priority 10),
        // otherwise the regenerated rules would not contain the job URLs.
        add_action( 'init', [ $this, 'init_plugin' ], 999 );
	}

    /**
     * Load the plugin after WP User Frontend is loaded
     *
     * @return void
     */
    public function init_plugin() {
        //Flash permalink
        if ( get_option( 'jobpress_flush_rewrite_rules_flag' ) ) {
            $this->flush_rewrite_rules();
            delete_option( 'jobpress_flush_rewrite_rules_flag' );
        }
    }

	/**
     * Flush rewrite rules after black_wall_streets is activated or woocommerce is activated
     *
     */
    public function flush_rewrite_rules() {
        // fix rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Add flush rewrite rules flag to apply flush_rewrite_rules() on plugin loaded hook
     *
     */
    public static function add_flush_rewrite_rules_flag(){
        //add flush flag
        if ( ! get_option( 'jobpress_flush_rewrite_rules_flag' ) ) {
            add_option( 'jobpress_flush_rewrite_rules_flag', true );
        }
    }
}