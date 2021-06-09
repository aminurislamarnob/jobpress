<?php
namespace JobPressInc\Base;

class SettingsFormGeneral
{

	public function register() 
	{
        add_action('admin_init', array( $this, 'jobpress_general_settings' ) );
	}

    /**
     * Plugin settings page
     */
    function jobpress_general_settings() {
        
        // register a new section
        add_settings_section(
            'jobpress_general_settings_section', 
            __('General Settings', 'jobpress'), array( $this, 'jobpress_general_section_text' ), 
            'jobpress_general_section'
        );

        // register a new field in the "jobpress_general_settings_section" section
        add_settings_field(
            'jobpress_design_type', 
            __('Select Design','jobpress'), array( $this, 'jobpress_design_type_field_callback' ), 
            'jobpress_general_section',  
            'jobpress_general_settings_section'
        );

        // register a new setting for design type field
        register_setting('jobpress_general_settings_section', 'jobpress_design_type');

        // register a new field in the "jobpress_general_settings_section" section
        add_settings_field(
            'jobpress_is_category', 
            __('Categorized Listing', 'jobpress'), array( $this, 'jobpress_is_category_field_callback' ), 
            'jobpress_general_section',  
            'jobpress_general_settings_section'
        );

        // register a new setting for category checkbox field
        register_setting('jobpress_general_settings_section', 'jobpress_is_category');

    }

    //Login redirect field content
    function jobpress_design_type_field_callback(){
        $jobpress_design_type_value = get_option('jobpress_design_type');
    ?>
        <select name="jobpress_design_type" class="regular-text">
            <option value="1"<?php echo ( $jobpress_design_type_value == 1 ) ? 'selected' : ''; ?>>Default Design</option>
            <option value="2"<?php echo ( $jobpress_design_type_value == 2 ) ? 'selected' : ''; ?>>Design V2</option>
            <option value="3"<?php echo ( $jobpress_design_type_value == 3 ) ? 'selected' : ''; ?>>Design V3</option>
            <option value="4"<?php echo ( $jobpress_design_type_value == 4 ) ? 'selected' : ''; ?>>Design V4</option>
            <option value="5"<?php echo ( $jobpress_design_type_value == 5 ) ? 'selected' : ''; ?>>Design V5</option>
        </select>
    <?php
    }

    //Logout redirect field content
    function jobpress_is_category_field_callback() {
        $jobpress_is_category_value = get_option('jobpress_is_category');
    ?>
        <input name="jobpress_is_category" type="checkbox" id="jobpress_is_category" class="regular-text" value="1" <?php echo ($jobpress_is_category_value == 1) ? 'checked' : ''; ?>>
        <label for="jobpress_is_category"><?php esc_html_e( 'If you want to show categorized job listing then check it, please.', 'jobpress' ); ?></label>
    <?php
    }

    //Plugin settings page section text
    function jobpress_general_section_text() {
        printf('%s %s %s', '<p>', __('You can change job listing & job single page design from here.', 'jobpress'), '</p>');
    }
}