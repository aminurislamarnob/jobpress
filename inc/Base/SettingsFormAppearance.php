<?php
namespace JobPressInc\Base;

class SettingsFormAppearance
{

	public function register() 
	{
        add_action('admin_init', array( $this, 'jobpress_appearance_settings' ) );
	}

    /**
     * Plugin appearance settings page
     */
    function jobpress_appearance_settings() {
        
        // register a new section
        add_settings_section(
            'jobpress_appearance_settings_section', 
            __('Appearance Settings', 'jobpress'), array( $this, 'jobpress_appearance_section_text' ), 
            'jobpress_appearance_section'
        );

        // register a new setting for brand color field
        add_settings_field(
            'jobpress_brand_color', 
            __('Primary/Branding Color','jobpress'), array( $this, 'jobpress_brand_color_field_callback' ), 
            'jobpress_appearance_section',  
            'jobpress_appearance_settings_section'
        );
        register_setting('jobpress_appearance_settings_section', 'jobpress_brand_color');


        // register a new setting for heading color field
        add_settings_field(
            'jobpress_heading_color', 
            __('Heading Title Color','jobpress'), array( $this, 'jobpress_heading_color_field_callback' ), 
            'jobpress_appearance_section',  
            'jobpress_appearance_settings_section'
        );
        register_setting('jobpress_appearance_settings_section', 'jobpress_heading_color');


        // register a new setting for content color field
        add_settings_field(
            'jobpress_content_color', 
            __('Content/Descriptions Color', 'jobpress'), array( $this, 'jobpress_content_color_field_callback' ), 
            'jobpress_appearance_section',  
            'jobpress_appearance_settings_section'
        );
        register_setting('jobpress_appearance_settings_section', 'jobpress_content_color');

        
        // register a new setting for secondary color field
        add_settings_field(
            'jobpress_secondary_color', 
            __('Secondary Color', 'jobpress'), array( $this, 'jobpress_secondary_color_field_callback' ), 
            'jobpress_appearance_section',  
            'jobpress_appearance_settings_section'
        );
        register_setting('jobpress_appearance_settings_section', 'jobpress_secondary_color');


        // register a new setting for border color field
        add_settings_field(
            'jobpress_border_color', 
            __('Border Color', 'jobpress'), array( $this, 'jobpress_border_color_field_callback' ), 
            'jobpress_appearance_section',  
            'jobpress_appearance_settings_section'
        );
        register_setting('jobpress_appearance_settings_section', 'jobpress_border_color');


        // register a new setting for hover color field
        add_settings_field(
            'jobpress_hover_color', 
            __('Hover Color', 'jobpress'), array( $this, 'jobpress_hover_color_field_callback' ), 
            'jobpress_appearance_section',  
            'jobpress_appearance_settings_section'
        );
        register_setting('jobpress_appearance_settings_section', 'jobpress_hover_color');
    }


    //Brand Color
    function jobpress_brand_color_field_callback(){
        $jobpress_brand_color_value = !empty(get_option('jobpress_brand_color')) ? get_option('jobpress_brand_color') : '#0086fe';
        printf('<input name="jobpress_brand_color" type="text" class="color-field regular-text" value="%s"/>', esc_attr($jobpress_brand_color_value));
    }

    //Heading Color
    function jobpress_heading_color_field_callback(){
        $jobpress_heading_color_value = !empty(get_option('jobpress_heading_color')) ? get_option('jobpress_heading_color') : '#283339';
        printf('<input name="jobpress_heading_color" type="text" class="color-field regular-text" value="%s"/>', esc_attr($jobpress_heading_color_value));
    }

    //Content Color
    function jobpress_content_color_field_callback(){
        $jobpress_content_color_value = !empty(get_option('jobpress_content_color')) ? get_option('jobpress_content_color') : '#3a3a3a';
        printf('<input name="jobpress_content_color" type="text" class="color-field regular-text" value="%s"/>', esc_attr($jobpress_content_color_value));
    }

    //Seconday Color
    function jobpress_secondary_color_field_callback(){
        $jobpress_secondary_color_value = !empty(get_option('jobpress_secondary_color')) ? get_option('jobpress_secondary_color') : '#5f7681';
        printf('<input name="jobpress_secondary_color" type="text" class="color-field regular-text" value="%s"/>', esc_attr($jobpress_secondary_color_value));
    }

    //Border Color
    function jobpress_border_color_field_callback(){
        $jobpress_border_color_value = !empty(get_option('jobpress_border_color')) ? get_option('jobpress_border_color') : '#e7ebee';
        printf('<input name="jobpress_border_color" type="text" class="color-field regular-text" value="%s"/>', esc_attr($jobpress_border_color_value));
    }


    //Hover Color
    function jobpress_hover_color_field_callback(){
        $jobpress_hover_color_value = !empty(get_option('jobpress_hover_color')) ? get_option('jobpress_hover_color') : '#006dcc';
        printf('<input name="jobpress_hover_color" type="text" class="color-field regular-text" value="%s"/>', esc_attr($jobpress_hover_color_value));
    }

    //Plugin settings page section text
    function jobpress_appearance_section_text() {
        printf('%s %s %s', '<p>', esc_html__('You can adjust JobPress colors from here.', 'jobpress'), '</p>');
    }
}