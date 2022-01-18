<?php
namespace JobPressInc\Base;

class SettingsLinks
{
	protected $plugin;

	public function __construct()
	{
		$this->plugin = JOBPRESS_PLUGIN;
	}

	public function register() 
	{
		add_filter( "plugin_action_links_$this->plugin", array( $this, 'settings_link' ) );
	}

	public function settings_link( $links ) 
	{
		$settings_link = '<a href="'.esc_url('edit.php?post_type=jobpress&page=jobpress_settings').'">'.esc_html__('Settings', 'jobpress').'</a>';
		array_push( $links, $settings_link );
		return $links;
	}
}