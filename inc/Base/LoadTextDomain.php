<?php
namespace JobPressInc\Base;

class LoadTextDomain
{

	public function register() 
	{
        add_action( 'init', array( $this, 'jobpress_textdomain' ) );
	}

	public function jobpress_textdomain() 
	{
		load_plugin_textdomain( 'jobpress', false, JOBPRESS_PLUGIN_URL . '/languages' ); 
	}
}