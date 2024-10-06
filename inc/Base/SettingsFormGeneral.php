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

        /**
        *Design type field
         **/
        // register a new field in the "jobpress_general_settings_section" section for design type field
        add_settings_field(
            'jobpress_design_type', 
            __('Select Design','jobpress'), array( $this, 'jobpress_design_type_field_callback' ), 
            'jobpress_general_section',  
            'jobpress_general_settings_section'
        );

        // register a new setting for design type field
        register_setting('jobpress_general_settings_section', 'jobpress_design_type');

        /**
         *Sidebar field
         **/
        // register a new field in the "jobpress_general_settings_section" section for category checkbox field
        add_settings_field(
            'jobpress_single_sidebar', 
            __('Single Page Sidebar Position', 'jobpress'), array( $this, 'jobpress_single_sidebar_field_callback' ), 
            'jobpress_general_section',  
            'jobpress_general_settings_section'
        );

        // register a new setting for category checkbox field
        register_setting('jobpress_general_settings_section', 'jobpress_single_sidebar');


        /**
         *Sidebar field
         **/
        // register a new field in the "jobpress_general_settings_section" section for application instruction text
        add_settings_field(
            'jobpress_single_resume_instruction',
            __('Resume Submit Instruction', 'jobpress'), array( $this, 'jobpress_single_resume_instruction_field_callback' ),
            'jobpress_general_section',
            'jobpress_general_settings_section'
        );

        // register a new setting for application instruction text
        register_setting('jobpress_general_settings_section', 'jobpress_single_resume_instruction');

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

    //Single page sidebar
    function jobpress_single_sidebar_field_callback() {
        $jobpress_sidebar_position_value = get_option('jobpress_single_sidebar');
    ?>
        <input name="jobpress_single_sidebar" type="checkbox" id="jobpress_single_sidebar" class="regular-text" value="1" <?php echo esc_attr(($jobpress_sidebar_position_value == 1) ? 'checked' : ''); ?>>
        <label for="jobpress_single_sidebar"><?php esc_html_e( 'Show single job page sidebar on left side (Default right side).', 'jobpress' ); ?></label>
    <?php
    }

    //Single Page resume instruction field
    function jobpress_single_resume_instruction_field_callback() {
        $jobpress_single_resume_instruction_value = get_option('jobpress_single_resume_instruction');
        ?>
        <textarea name="jobpress_single_resume_instruction" type="text" id="jobpress_single_resume_instruction" class="regular-text" placeholder="<?php esc_attr_e('Example: Send your resume along with your cover letter to career@aiarnob.com', 'jobpress');?>"><?php echo esc_html(!empty($jobpress_single_resume_instruction_value) ? $jobpress_single_resume_instruction_value : ''); ?></textarea>
        <br>
        <small><?php esc_html_e('Single Page Resume Submit Description With Email Address', 'jobpress'); ?></small>
        <?php
    }

    //Plugin settings page section text
    function jobpress_general_section_text() {
        printf('%s %s %s', '<p>', esc_html__('You can change job listing & job single page design from here.', 'jobpress'), '</p>');
    }
}