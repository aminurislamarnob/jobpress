<?php
namespace JobPressInc\Base;

class SettingsFormShortcode
{

	public function register() 
	{
        add_action('admin_init', array( $this, 'jobpress_shortcode_settings' ) );
	}

    /**
     * Plugin settings page
     */
    function jobpress_shortcode_settings() {
        
        // register a new section
        add_settings_section(
            'jobpress_shortcode_settings_section', 
            __('Shortcode Style Settings', 'jobpress'), array( $this, 'jobpress_shortcode_section_text' ), 
            'jobpress_shortcode_section'
        );

        /**
        *JobPress Shortcode field
         **/
        // register a new field in the "jobpress_general_settings_section" section for design type field
        add_settings_field(
            'jobpress_design_type', 
            __('Select Design','jobpress'), array( $this, 'jobpress_design_type_field_callback' ), 
            'jobpress_shortcode_section',  
            'jobpress_shortcode_settings_section'
        );

        // register a new setting for design type field
        register_setting('jobpress_shortcode_settings_section', 'jobpress_design_type');
    }

    //Design select dropdown
    function jobpress_design_type_field_callback(){
        $jobpress_design_type_value = get_option('jobpress_design_type');
    ?>
        <select name="jobpress_design_type" class="regular-text">
            <option value="1"<?php echo esc_attr(( $jobpress_design_type_value == 1 ) ? 'selected' : ''); ?>><?php esc_html_e('Default Design', 'jobpress'); ?></option>
            <option value="2"<?php echo esc_attr(( $jobpress_design_type_value == 2 ) ? 'selected' : ''); ?>><?php esc_html_e( 'Design V2', 'jobpress'); ?></option>
            <option value="3"<?php echo esc_attr(( $jobpress_design_type_value == 3 ) ? 'selected' : ''); ?>><?php esc_html_e( 'Design V3', 'jobpress'); ?></option>
            <option value="4"<?php echo esc_attr(( $jobpress_design_type_value == 4 ) ? 'selected' : ''); ?>><?php esc_html_e( 'Design V4', 'jobpress'); ?></option>
            <option value="5"<?php echo esc_attr(( $jobpress_design_type_value == 5 ) ? 'selected' : ''); ?>><?php esc_html_e( 'Design V5', 'jobpress'); ?></option>
        </select>
    <?php
    }

    //Plugin settings page section text
    function jobpress_shortcode_section_text() {
        printf('%s %s %s', '<p>', esc_html__('You can change shortcode settings from here.', 'jobpress'), '</p>');
    }
}