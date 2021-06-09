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
        ?>
        <div class="jobpress-dflex">
            <div class="jobpress-text-field jobpress-field jobpress_vacancy">
                <label for="jobpress_vacancy"><?php _e( 'Job Vacancy', 'jobpress' )?></label>
                <input type="text" name="jobpress_vacancy" id="jobpress_vacancy" class="regular-text" value="<?php if ( isset ( $jobpress_stored_meta['jobpress_vacancy'] ) ) echo $jobpress_stored_meta['jobpress_vacancy'][0]; ?>" />
            </div>
            <div class="jobpress-text-field jobpress-field jobpress_experience">
                <label for="jobpress_experience"><?php _e( 'Experience', 'jobpress' )?></label>
                <input type="text" name="jobpress_experience" id="jobpress_experience" class="regular-text" value="<?php if ( isset ( $jobpress_stored_meta['jobpress_experience'] ) ) echo $jobpress_stored_meta['jobpress_experience'][0]; ?>" />
            </div>
        </div>
        <div class="jobpress-dflex">
            <div class="jobpress-text-field jobpress-field jobpress_working_hour">
                <label for="jobpress_working_hour"><?php _e( 'Working Hours', 'jobpress' )?></label>
                <input type="text" name="jobpress_working_hour" id="jobpress_working_hour" class="regular-text" value="<?php if ( isset ( $jobpress_stored_meta['jobpress_working_hour'] ) ) echo $jobpress_stored_meta['jobpress_working_hour'][0]; ?>" />
            </div>
            <div class="jobpress-text-field jobpress-field jobpress_working_days">
                <label for="jobpress_working_days"><?php _e( 'Working Days', 'jobpress' )?></label>
                <input type="text" name="jobpress_working_days" id="jobpress_working_days" class="regular-text" value="<?php if ( isset ( $jobpress_stored_meta['jobpress_working_days'] ) ) echo $jobpress_stored_meta['jobpress_working_days'][0]; ?>" />
            </div>
        </div>
        <div class="jobpress-dflex">
            <div class="jobpress-text-field jobpress-field jobpress_salary">
                <label for="jobpress_salary"><?php _e( 'Salary', 'jobpress' )?></label>
                <input type="text" name="jobpress_salary" id="jobpress_salary" class="regular-text" value="<?php if ( isset ( $jobpress_stored_meta['jobpress_salary'] ) ) echo $jobpress_stored_meta['jobpress_salary'][0]; ?>" />
            </div>
            <div class="jobpress-text-field jobpress-field jobpress_apply_deadline">
                <label for="jobpress_apply_deadline"><?php _e( 'Application Deadline', 'jobpress' )?></label>
                <input type="date" name="jobpress_apply_deadline" id="jobpress_apply_deadline" class="regular-text" value="<?php if ( isset ( $jobpress_stored_meta['jobpress_apply_deadline'] ) ) echo $jobpress_stored_meta['jobpress_apply_deadline'][0]; ?>" />
            </div>
        </div>
        <div class="jobpress-text-field jobpress-field jobpress_location w-100">
            <label for="jobpress_location"><?php _e( 'Job Location', 'jobpress' )?></label>
            <input type="text" name="jobpress_location" id="jobpress_location" class="regular-text" value="<?php if ( isset ( $jobpress_stored_meta['jobpress_location'] ) ) echo $jobpress_stored_meta['jobpress_location'][0]; ?>" />
        </div>
        <div class="jobpress-text-field jobpress-field jobpress_location w-100">
            <label for="jobpress_location"><?php _e( 'Google Map iFrame Embed Code', 'jobpress' )?></label>
            <textarea rows="1" cols="40" name="google_map_iframe" id="google_map_iframe"><?php if ( isset ( $jobpress_stored_meta['google_map_iframe'] ) ) echo $jobpress_stored_meta['google_map_iframe'][0]; ?></textarea>
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

        $allowed_html = array(
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
            update_post_meta( $post_id, 'google_map_iframe', esc_html( wp_kses( $_POST['google_map_iframe'], $allowed_html ) ) );
        }
    }

}