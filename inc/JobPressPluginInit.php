<?php

namespace JobPressInc;

final class JobPressPluginInit
{
	/**
	 * Store all the classes inside an array
	 * @return array Full list of classes
	 */
	public static function get_services() 
	{
		return [
			Base\LoadTextDomain::class, //load text domain
			Base\SettingsLinks::class, //plugin settings link
			Pages\Admin\Admin::class, //admin page
			Base\CustomPostType::class, //custom post type
			Base\JobsMetaBox::class, //custom post type
			Base\AdminEnqueue::class, //admin style & scripts
			Base\PublicEnqueue::class, //public style & scripts
			Base\JobListShortcode::class, //job list page shortcode
			Base\SinglePageTemplate::class, //single page template
			Base\SettingsFormGeneral::class, //General Settings form
			Base\SettingsFormAppearance::class, //Appearance Settings form
			Base\Flush::class, //Flush rewrite rules
		];
	}

    /**
	 * Loop through the classes, initialize them, 
	 * and call the register() method if it exists
	 * @return
	 */
	public static function register_services() 
	{
		foreach ( self::get_services() as $class ) {
			$service = self::instantiate( $class );
			if ( method_exists( $service, 'register' ) ) {
				$service->register();
			}
		}
	}

    /**
     * Initialize the class
     * @param  class $class    class from the services array
     * @return class instance  new instance of the class
     */
	private static function instantiate( $class )
	{
		$service = new $class();
		return $service;
	}

}