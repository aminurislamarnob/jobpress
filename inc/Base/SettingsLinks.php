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
		add_filter( 'plugin_row_meta', array( $this, 'docs_link' ), 10, 2 );
	}

	public function settings_link( $links ) 
	{
		$settings_link = '<a href="'.esc_url('edit.php?post_type=jobpress&page=jobpress_settings').'">'.esc_html__('Settings', 'jobpress').'</a>';
		array_push( $links, $settings_link );
		return $links;
	}

	/**
	 * Add a "Docs" link to the plugin's row on the Plugins screen.
	 *
	 * @param string[] $links Row meta links.
	 * @param string   $file  Plugin basename of the row.
	 * @return string[]
	 */
	public function docs_link( $links, $file )
	{
		if ( $file === $this->plugin ) {
			$links[] = '<a href="' . esc_url( jobpress_get_docs_url() ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Docs', 'jobpress' ) . '</a>';
		}
		return $links;
	}
}
