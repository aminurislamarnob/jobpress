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
        register_setting('jobpress_shortcode_settings_section', 'jobpress_design_type', array( 'sanitize_callback' => 'absint' ) );
    }

    //Design select dropdown
    function jobpress_design_type_field_callback(){
        $jobpress_design_type_value = jobpress_get_short_design_type();
    ?>
        <select name="jobpress_design_type" class="regular-text">
            <?php foreach ( jobpress_get_design_names() as $design => $name ) : ?>
                <option value="<?php echo esc_attr( $design ); ?>"<?php selected( $jobpress_design_type_value, (int) $design ); ?>><?php echo esc_html( $name ); ?></option>
            <?php endforeach; ?>
        </select>
        <br>
        <small><?php esc_html_e( 'The default design of the [jobpress] shortcode, the JobPress Jobs block and the JobPress Elementor widget, which can each choose their own. The Jobs Page set in General Settings always uses the built-in jobs archive layout with search and filters.', 'jobpress' ); ?></small>
    <?php
    }

    //Plugin settings page section text
    function jobpress_shortcode_section_text() {
        printf('%s %s %s', '<p>', esc_html__('You can change shortcode settings from here.', 'jobpress'), '</p>');
    }
}