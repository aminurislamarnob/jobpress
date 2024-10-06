<?php
namespace JobPressInc\Base;

class JobsMetaBox
{

	public function register() 
	{
        add_action( 'add_meta_boxes', array( $this, 'jobs_custom_meta' ) );
        add_action( 'save_post', array( $this, 'jobs_meta_save' ) );
	}

    
    /**
     * Adds meta box to the job editing screen
     */
    function jobs_custom_meta(){
        add_meta_box( 'jobpress_meta_box', __( 'Job Others Informations', 'jobpress' ), array( $this, 'jobpress_meta_fields_callback' ), 'jobpress', 'advanced', 'high' );
    }

    /**
     * Outputs the content of the meta box
     */
    function jobpress_meta_fields_callback( $post ) {
        wp_nonce_field( basename( __FILE__ ), 'jobpress_nonce' );
        $jobpress_stored_meta = get_post_meta( $post->ID );
        $jobpress_wpcf7_exists = post_type_exists( 'wpcf7_contact_form' ); //check contact form 7 is activated or not
        ?>
        <div class="jobpress-dflex">
            <div class="jobpress-text-field jobpress-field jobpress_vacancy">
                <label for="jobpress_vacancy"><?php esc_html_e( 'Job Vacancy', 'jobpress' )?></label>
                <input type="text" name="jobpress_vacancy" id="jobpress_vacancy" class="regular-text" value="<?php if ( isset ( $jobpress_stored_meta['jobpress_vacancy'] ) ) echo esc_attr($jobpress_stored_meta['jobpress_vacancy'][0]); ?>" />
            </div>
            <div class="jobpress-text-field jobpress-field jobpress_experience">
                <label for="jobpress_experience"><?php esc_html_e( 'Experience', 'jobpress' )?></label>
                <input type="text" name="jobpress_experience" id="jobpress_experience" class="regular-text" value="<?php if ( isset ( $jobpress_stored_meta['jobpress_experience'] ) ) echo esc_attr($jobpress_stored_meta['jobpress_experience'][0]); ?>" />
            </div>
        </div>
        <div class="jobpress-dflex">
            <div class="jobpress-text-field jobpress-field jobpress_working_hour">
                <label for="jobpress_working_hour"><?php esc_html_e( 'Working Hours', 'jobpress' )?></label>
                <input type="text" name="jobpress_working_hour" id="jobpress_working_hour" class="regular-text" value="<?php if ( isset ( $jobpress_stored_meta['jobpress_working_hour'] ) ) echo esc_attr($jobpress_stored_meta['jobpress_working_hour'][0]); ?>" />
            </div>
            <div class="jobpress-text-field jobpress-field jobpress_working_days">
                <label for="jobpress_working_days"><?php esc_html_e( 'Working Days', 'jobpress' )?></label>
                <input type="text" name="jobpress_working_days" id="jobpress_working_days" class="regular-text" value="<?php if ( isset ( $jobpress_stored_meta['jobpress_working_days'] ) ) echo esc_attr($jobpress_stored_meta['jobpress_working_days'][0]); ?>" />
            </div>
        </div>
        <div class="jobpress-dflex">
            <div class="jobpress-text-field jobpress-field jobpress_salary">
                <label for="jobpress_salary"><?php esc_html_e( 'Salary', 'jobpress' )?></label>
                <input type="text" name="jobpress_salary" id="jobpress_salary" class="regular-text" value="<?php if ( isset ( $jobpress_stored_meta['jobpress_salary'] ) ) echo esc_attr($jobpress_stored_meta['jobpress_salary'][0]); ?>" />
            </div>
            <div class="jobpress-text-field jobpress-field jobpress_apply_deadline">
                <label for="jobpress_apply_deadline"><?php esc_html_e( 'Application Deadline', 'jobpress' )?></label>
                <input type="date" name="jobpress_apply_deadline" id="jobpress_apply_deadline" class="regular-text" value="<?php if ( isset ( $jobpress_stored_meta['jobpress_apply_deadline'] ) ) echo esc_attr($jobpress_stored_meta['jobpress_apply_deadline'][0]); ?>" />
            </div>
        </div>
        <div class="jobpress-text-field jobpress-field jobpress_location w-100">
            <label for="jobpress_location"><?php esc_html_e( 'Job Location', 'jobpress' )?></label>
            <input type="text" name="jobpress_location" id="jobpress_location" class="regular-text" value="<?php if ( isset ( $jobpress_stored_meta['jobpress_location'] ) ) echo esc_attr($jobpress_stored_meta['jobpress_location'][0]); ?>" />
        </div>
        <div class="jobpress-text-field jobpress-field jobpress_location w-100">
            <label for="jobpress_location"><?php esc_html_e( 'Google Map iFrame Embed Code', 'jobpress' )?></label>
            <textarea rows="1" cols="40" name="google_map_iframe" id="google_map_iframe"><?php if ( isset ( $jobpress_stored_meta['google_map_iframe'] ) )  echo esc_attr($jobpress_stored_meta['google_map_iframe'][0]); ?></textarea>
        </div>
        <div class="jobpress_enable_form jobpress-text-field jobpress-check-field">
            <label for=""><strong><?php esc_html_e( 'Job Application Collect Medium', 'jobpress' )?></strong></label>
            <div class="form-check mb-5 mt-5">
                <input class="form-check-input" type="radio" name="jobpress_application_collect_medium" id="application_by_email" value="<?php echo esc_attr(1); ?>" <?php if ( isset ( $jobpress_stored_meta['jobpress_application_collect_medium'] ) && ($jobpress_stored_meta['jobpress_application_collect_medium'][0] == 1) ) echo esc_attr('checked'); ?>>
                <label class="form-check-label" for="application_by_email"><?php esc_html_e('Collect application through email.', 'jobpress'); ?></label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="jobpress_application_collect_medium" id="application_by_form" value="<?php echo esc_attr(2); ?>" <?php if ( isset ( $jobpress_stored_meta['jobpress_application_collect_medium'] ) && ($jobpress_stored_meta['jobpress_application_collect_medium'][0] == 2) ) echo esc_attr('checked'); ?> <?php echo (!$jobpress_wpcf7_exists) ? esc_attr('disabled') : ''; ?>>
                <label class="form-check-label" for="application_by_form">
                    <?php esc_html_e('Collect application through form.', 'jobpress'); ?>
                    <?php
                    if (!$jobpress_wpcf7_exists) {
                        esc_html_e('(Please install & activate the Contact Form 7 plugin from WordPress.org, then refresh this page.)', 'jobpress');
                    }
                    ?>
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="jobpress_application_collect_medium" id="application_by_default" value="<?php echo esc_attr(3); ?>" <?php if ( isset ( $jobpress_stored_meta['jobpress_application_collect_medium'] ) && ($jobpress_stored_meta['jobpress_application_collect_medium'][0] == 3) ) echo esc_attr('checked'); ?>>
                <label class="form-check-label" for="application_by_default"><?php esc_html_e('Default (That you inserted on JobPress settings page.)', 'jobpress'); ?></label>
            </div>
        </div>
        <div class="jobpress-text-field jobpress-field jobpress_email w-100<?php echo esc_attr(( isset ( $jobpress_stored_meta['jobpress_application_collect_medium'] ) && ($jobpress_stored_meta['jobpress_application_collect_medium'][0] == 1) ) ? '' : ' d-none'); ?>">
            <label for="jobpress_email"><?php esc_html_e( 'Resume Submit Description With Email Address', 'jobpress' )?></label>
            <textarea rows="1" cols="40" name="jobpress_email" id="jobpress_email" placeholder="<?php esc_attr_e('Example: Send your resume along with your cover letter to career@aiarnob.com', 'jobpress');?>"><?php if ( isset ( $jobpress_stored_meta['jobpress_email'] ) ) echo esc_html( $jobpress_stored_meta['jobpress_email'][0] ); ?></textarea>
        </div>
        <div class="jobpress-text-field jobpress-field jobpress_contact_form_id w-100<?php echo esc_attr(( isset ( $jobpress_stored_meta['jobpress_application_collect_medium'] ) && ($jobpress_stored_meta['jobpress_application_collect_medium'][0] == 2) ) ? '' : ' d-none'); ?>">
            <label for="jobpress_contact_form_7"><?php esc_html_e( 'Select Contact Form 7 (By this form candidate can apply to this job.)', 'jobpress' )?></label>

            <?php
            if($jobpress_wpcf7_exists){
            ?>

            <select name="jobpress_contact_form_7" id="jobpress_contact_form_7" class="regular-text"> 
                <option value=""><?php esc_html_e('--Select Form--', 'jobpress'); ?></option>
                <?php
                    $jobpress_contact_form_7 = '';
                    if ( isset ( $jobpress_stored_meta['jobpress_contact_form_7'] ) ) {
                        $jobpress_contact_form_7 = $jobpress_stored_meta['jobpress_contact_form_7'][0];
                    }
                    
                    $wpcf7_posts = get_posts(array(
                        'post_type'     => 'wpcf7_contact_form',
                        'numberposts'   => -1
                    ));
                    foreach ( $wpcf7_posts as $post ) {
                        echo '<option value="'.esc_attr($post->ID).'"'.esc_attr(selected($post->ID,$jobpress_contact_form_7,false)).'>'.esc_html($post->post_title.' ('.$post->ID.')').'</option>';
                    }
                ?>
            </select>

            <?php }else{ ?>
                <div class="jobpress-error-notify">
                    <p><?php esc_html_e( 'Please install & active contact form 7 plugin from wordpress.org then refresh this page.', 'jobpress' ); ?></p>
                </div>
            <?php } ?>
        </div>
    
        <?php
    }

    /**
     * Check is secuired
    */
    function jobpress_is_secured($nonce_field, $post_id){
        $is_autosave = wp_is_post_autosave( $post_id );
        $is_revision = wp_is_post_revision( $post_id );
        $is_valid_nonce = ( isset( $nonce_field ) && wp_verify_nonce( $nonce_field, basename( __FILE__ ) ) ) ? 'true' : 'false';

        if(!$is_valid_nonce){
            return false;
        }

        if($is_autosave){
            return false;
        }

        if($is_revision){
            return false;
        }

        if(!current_user_can('edit_post', $post_id)){
            return false;
        }

        return true;
    }


    /**
     * Saves the custom meta input
     */
    function jobs_meta_save( $post_id ) {
    
        if(isset($_POST[ 'jobpress_nonce' ]) && !$this->jobpress_is_secured($_POST[ 'jobpress_nonce' ], $post_id)){
            return;
        }
    
        // Checks for input and sanitizes/saves if needed
        if( isset( $_POST[ 'jobpress_vacancy' ] ) ) {
            update_post_meta( $post_id, 'jobpress_vacancy', sanitize_text_field( $_POST[ 'jobpress_vacancy' ] ) );
        }

        if( isset( $_POST[ 'jobpress_experience' ] ) ) {
            update_post_meta( $post_id, 'jobpress_experience', sanitize_text_field( $_POST[ 'jobpress_experience' ] ) );
        }

        if( isset( $_POST[ 'jobpress_working_hour' ] ) ) {
            update_post_meta( $post_id, 'jobpress_working_hour', sanitize_text_field( $_POST[ 'jobpress_working_hour' ] ) );
        }

        if( isset( $_POST[ 'jobpress_working_days' ] ) ) {
            update_post_meta( $post_id, 'jobpress_working_days', sanitize_text_field( $_POST[ 'jobpress_working_days' ] ) );
        }

        if( isset( $_POST[ 'jobpress_salary' ] ) ) {
            update_post_meta( $post_id, 'jobpress_salary', sanitize_text_field( $_POST[ 'jobpress_salary' ] ) );
        }

        if( isset( $_POST[ 'jobpress_apply_deadline' ] ) ) {
            update_post_meta( $post_id, 'jobpress_apply_deadline', sanitize_text_field( $_POST[ 'jobpress_apply_deadline' ] ) );
        }

        if( isset( $_POST[ 'jobpress_location' ] ) ) {
            update_post_meta( $post_id, 'jobpress_location', sanitize_text_field( $_POST[ 'jobpress_location' ] ) );
        }

        $jobpress_iframe_allowed_html = array(
            'iframe' => array(
                'src' => array(),
                'width' => array(),
                'height' => array(),
                'style' => array(),
                'allowfullscreen' => array(),
                'loading' => array(),
            )
        );
        if( isset( $_POST[ 'google_map_iframe' ] ) ) {
            update_post_meta( $post_id, 'google_map_iframe', wp_kses( $_POST['google_map_iframe'], $jobpress_iframe_allowed_html ) );
        }

        if( isset( $_POST[ 'jobpress_application_collect_medium' ] ) ) {
            update_post_meta( $post_id, 'jobpress_application_collect_medium', sanitize_text_field( $_POST[ 'jobpress_application_collect_medium' ] ) );
        }

        if( isset( $_POST[ 'jobpress_email' ] ) ) {
            update_post_meta( $post_id, 'jobpress_email', sanitize_text_field( $_POST[ 'jobpress_email' ] ) );
        }

        if( isset( $_POST[ 'jobpress_contact_form_7' ] ) ) {
            update_post_meta( $post_id, 'jobpress_contact_form_7', sanitize_text_field( $_POST[ 'jobpress_contact_form_7' ] ) );
        }

    }

}