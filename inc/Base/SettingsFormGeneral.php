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

        /**
         * Jobs Page Selection field
         **/
        // register a new field in the "jobpress_general_settings_section" section for jobs page selection
        add_settings_field(
            'jobpress_jobs_page_id',
            __('Jobs Page', 'jobpress'), array( $this, 'jobpress_jobs_page_field_callback' ),
            'jobpress_general_section',
            'jobpress_general_settings_section'
        );

        // register a new setting for jobs page selection
        register_setting('jobpress_general_settings_section', 'jobpress_jobs_page_id');

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

    //Jobs Page selection field
    function jobpress_jobs_page_field_callback() {
        $selected_page_id = get_option('jobpress_jobs_page_id', 0);
        
        // Get all published pages
        $pages = get_pages(array(
            'sort_column' => 'menu_order,post_title',
            'hierarchical' => 0,
            'post_status' => 'publish'
        ));
        ?>
        <select name="jobpress_jobs_page_id" id="jobpress_jobs_page_id">
            <option value="0"><?php esc_html_e('-- Select a Page --', 'jobpress'); ?></option>
            <?php foreach ($pages as $page) : ?>
                <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($selected_page_id, $page->ID); ?>>
                    <?php echo esc_html($page->post_title); ?> (ID: <?php echo esc_html($page->ID); ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <br>
        <small><?php esc_html_e('Select the page that will display the jobs listing. This page ID will be used by the plugin to identify the jobs page.', 'jobpress'); ?></small>
        <?php
    }

    //Plugin settings page section text
    function jobpress_general_section_text() {
        printf('%s %s %s', '<p>', esc_html__('You can change job listing & job single page design from here.', 'jobpress'), '</p>');
    }
}