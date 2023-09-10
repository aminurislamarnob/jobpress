<?php
/**
 * The template for displaying all single posts.
 *
 */

use JobPressInc\Base\SinglePageTemplate;


// global $post;
// get_header('jobpress');
// while ( have_posts() ) : the_post();

$jobpress_ft_image = get_the_post_thumbnail_url(get_the_ID(), 'full');
//jobpress meta

$jobpress_vacancy = get_post_meta( get_the_ID(), 'jobpress_vacancy', true );
$jobpress_experience = get_post_meta( get_the_ID(), 'jobpress_experience', true );
$jobpress_work_hour = get_post_meta( get_the_ID(), 'jobpress_working_hour', true );
$jobpress_work_day = get_post_meta( get_the_ID(), 'jobpress_working_days', true );
$jobpress_salary = get_post_meta( get_the_ID(), 'jobpress_salary', true );
$jobpress_deadline = get_post_meta( get_the_ID(), 'jobpress_apply_deadline', true );
$jobpress_location = get_post_meta( get_the_ID(), 'jobpress_location', true );
$jobpress_gmap = get_post_meta( get_the_ID(), 'google_map_iframe', true );
$jobpress_contact_form_7 = get_post_meta( get_the_ID(), 'jobpress_contact_form_7', true );
$jobpress_application_collect_medium = get_post_meta( get_the_ID(), 'jobpress_application_collect_medium', true );
$jobpress_email = get_post_meta( get_the_ID(), 'jobpress_email', true );

//Resume submit instruction common
$jobpress_resume_common_instruction = get_option('jobpress_single_resume_instruction');

//job category
$job_category = get_the_terms( get_the_ID(), 'jobpress_category' );
$job_category_str = '';
if ( $job_category && ! is_wp_error( $job_category ) ){
    $job_category_str = join(', ', wp_list_pluck($job_category, 'name'));
}

//job type
$job_type = get_the_terms( get_the_ID(), 'jobpress_type' );
$job_type_str = '';
if ( $job_type && ! is_wp_error( $job_type ) ){
    $job_type_str = join(', ', wp_list_pluck($job_type, 'name'));
}

//sidebar position
$jobpress_sidebar_position = get_option('jobpress_single_sidebar');
if( !empty($jobpress_sidebar_position) && $jobpress_sidebar_position == 1 ){
    $jobpress_sidebar_order_1 = 'jp-order-2';
    $jobpress_sidebar_order_2 = 'jp-order-1';
}else{
    $jobpress_sidebar_order_1 = 'jp-order-1';
    $jobpress_sidebar_order_2 = 'jp-order-2';
}
?>
    <div class="jp-single-wrapper">
        <div class="jp-single-content-area">
            <div class="jp-content <?php echo esc_attr( $jobpress_sidebar_order_1 ); ?>">
                <?php if($jobpress_ft_image) : ?>
                <div class="jp-featured-image">
                    <img class="jp-w-100" src="<?php echo esc_url($jobpress_ft_image); ?>" alt="<?php the_title(); ?>">
                </div>
                <?php endif; ?>
                <div class="jp-job-description">
                    <?php the_content(); ?>
                </div>
                <?php if(!empty($jobpress_gmap)) : ?>
                <div class="jp-job-location-googlemap">
                    <?php
                    $jobpress_gmap_allowed_html = array(
                        'iframe' => array(
                            'src' => array(),
                            'width' => array(),
                            'height' => array(),
                            'style' => array(),
                            'allowfullscreen' => array(),
                            'loading' => array(),
                        )
                    );
                    echo wp_kses( $jobpress_gmap, $jobpress_gmap_allowed_html ); ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="jp-sidebar <?php echo esc_attr( $jobpress_sidebar_order_2 ); ?>">
                <div class="jp-summary">
                    <h4 class="jp-sidebar-title"><?php esc_html_e( 'Job Summary', 'jobpress' ); ?></h4>

                    <?php if(!empty($jobpress_location)){ ?>
                    <div class="jp-job-summary-item">
                        <div class="jp-summary-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-geo-alt" viewBox="0 0 16 16">
                            <path d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A31.493 31.493 0 0 1 8 14.58a31.481 31.481 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94zM8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10z"/>
                            <path d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                            </svg>
                        </div>
                        <div class="jp-summary-text">
                            <span><?php esc_html_e( 'Location', 'jobpress' ); ?></span><br><?php echo esc_html( $jobpress_location ); ?>
                        </div>
                    </div>
                    <?php } 
                    if(!empty($job_type_str)){ ?>
                    <div class="jp-job-summary-item">
                        <div class="jp-summary-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-briefcase" viewBox="0 0 16 16">
                                <path d="M6.5 1A1.5 1.5 0 0 0 5 2.5V3H1.5A1.5 1.5 0 0 0 0 4.5v8A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-8A1.5 1.5 0 0 0 14.5 3H11v-.5A1.5 1.5 0 0 0 9.5 1h-3zm0 1h3a.5.5 0 0 1 .5.5V3H6v-.5a.5.5 0 0 1 .5-.5zm1.886 6.914L15 7.151V12.5a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5V7.15l6.614 1.764a1.5 1.5 0 0 0 .772 0zM1.5 4h13a.5.5 0 0 1 .5.5v1.616L8.129 7.948a.5.5 0 0 1-.258 0L1 6.116V4.5a.5.5 0 0 1 .5-.5z"/>
                            </svg>
                        </div>
                        <div class="jp-summary-text">
                            <span><?php esc_html_e( 'Job Type', 'jobpress' ); ?></span><br><?php echo esc_html( $job_type_str ); ?>
                        </div>
                    </div>
                    <?php } 
                    if(!empty($jobpress_deadline)){ ?>
                    <div class="jp-job-summary-item">
                        <div class="jp-summary-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2-event" viewBox="0 0 16 16">
                                <path d="M11 7.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1z"/>
                                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
                                <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
                            </svg>
                        </div>
                        <div class="jp-summary-text">
                            <span><?php esc_html_e( 'Application Deadline', 'jobpress' ); ?></span><br><?php echo esc_html( $jobpress_deadline ); ?>
                        </div>
                    </div>
                    <?php } 
                    if(!empty($jobpress_experience)){ ?>
                    <div class="jp-job-summary-item">
                        <div class="jp-summary-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-lightbulb" viewBox="0 0 16 16">
                                <path d="M2 6a6 6 0 1 1 10.174 4.31c-.203.196-.359.4-.453.619l-.762 1.769A.5.5 0 0 1 10.5 13a.5.5 0 0 1 0 1 .5.5 0 0 1 0 1l-.224.447a1 1 0 0 1-.894.553H6.618a1 1 0 0 1-.894-.553L5.5 15a.5.5 0 0 1 0-1 .5.5 0 0 1 0-1 .5.5 0 0 1-.46-.302l-.761-1.77a1.964 1.964 0 0 0-.453-.618A5.984 5.984 0 0 1 2 6zm6-5a5 5 0 0 0-3.479 8.592c.263.254.514.564.676.941L5.83 12h4.342l.632-1.467c.162-.377.413-.687.676-.941A5 5 0 0 0 8 1z"/>
                            </svg>
                        </div>
                        <div class="jp-summary-text">
                            <span><?php esc_html_e( 'Experience', 'jobpress' ); ?></span><br><?php echo esc_html( $jobpress_experience ); ?>
                        </div>
                    </div>
                    <?php } 
                    if(!empty($jobpress_work_day)){ ?>
                    <div class="jp-job-summary-item">
                        <div class="jp-summary-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar3" viewBox="0 0 16 16">
                                <path d="M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zM1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857V3.857z"/>
                                <path d="M6.5 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
                            </svg>
                        </div>
                        <div class="jp-summary-text">
                            <span><?php esc_html_e( 'Working Days', 'jobpress' ); ?></span><br><?php echo esc_html( $jobpress_work_day ); ?>
                        </div>
                    </div>
                    <?php } 
                    if(!empty($jobpress_work_hour)){ ?>
                    <div class="jp-job-summary-item">
                        <div class="jp-summary-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock" viewBox="0 0 16 16">
                                <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                                <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                            </svg>
                        </div>
                        <div class="jp-summary-text">
                            <span><?php esc_html_e( 'Working Hours', 'jobpress' ); ?></span><br><?php echo esc_html( $jobpress_work_hour ); ?>
                        </div>
                    </div>
                    <?php } 
                    if(!empty($jobpress_salary)){ ?>
                    <div class="jp-job-summary-item">
                        <div class="jp-summary-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-wallet2" viewBox="0 0 16 16">
                                <path d="M12.136.326A1.5 1.5 0 0 1 14 1.78V3h.5A1.5 1.5 0 0 1 16 4.5v9a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 13.5v-9a1.5 1.5 0 0 1 1.432-1.499L12.136.326zM5.562 3H13V1.78a.5.5 0 0 0-.621-.484L5.562 3zM1.5 4a.5.5 0 0 0-.5.5v9a.5.5 0 0 0 .5.5h13a.5.5 0 0 0 .5-.5v-9a.5.5 0 0 0-.5-.5h-13z"/>
                            </svg>
                        </div>
                        <div class="jp-summary-text">
                            <span><?php esc_html_e( 'Salary', 'jobpress' ); ?></span><br><?php echo esc_html( $jobpress_salary ); ?>
                        </div>
                    </div>
                    <?php } 
                    if(!empty($jobpress_vacancy)){ ?>
                    <div class="jp-job-summary-item">
                        <div class="jp-summary-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-building" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M14.763.075A.5.5 0 0 1 15 .5v15a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5V14h-1v1.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V10a.5.5 0 0 1 .342-.474L6 7.64V4.5a.5.5 0 0 1 .276-.447l8-4a.5.5 0 0 1 .487.022zM6 8.694 1 10.36V15h5V8.694zM7 15h2v-1.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 .5.5V15h2V1.309l-7 3.5V15z"/>
                                <path d="M2 11h1v1H2v-1zm2 0h1v1H4v-1zm-2 2h1v1H2v-1zm2 0h1v1H4v-1zm4-4h1v1H8V9zm2 0h1v1h-1V9zm-2 2h1v1H8v-1zm2 0h1v1h-1v-1zm2-2h1v1h-1V9zm0 2h1v1h-1v-1zM8 7h1v1H8V7zm2 0h1v1h-1V7zm2 0h1v1h-1V7zM8 5h1v1H8V5zm2 0h1v1h-1V5zm2 0h1v1h-1V5zm0-2h1v1h-1V3z"/>
                            </svg>
                        </div>
                        <div class="jp-summary-text">
                            <span><?php esc_html_e( 'Vacancy', 'jobpress' ); ?></span><br><?php echo esc_html( $jobpress_vacancy ); ?>
                        </div>
                    </div>
                    <?php } ?>
                    <a href="#job-apply" class="jp-btn jp-apply-now-sidebar-btn"><?php esc_html_e( 'Apply Now', 'jobpress' ); ?></a>
                </div>
            </div>
        </div>
        <div id="job-apply" class="jp-job-apply-form">
            <h3 class="jp-form-title"><?php esc_html_e( 'Apply for The Position: ', 'jobpress' ) . the_title(); ?></h3>
            <?php if(!empty($jobpress_application_collect_medium) && ($jobpress_application_collect_medium == 2) && !empty($jobpress_contact_form_7)){
                echo do_shortcode('[contact-form-7 id="'.esc_attr($jobpress_contact_form_7).'" title="'.get_the_title().'"]');
            }elseif(!empty($jobpress_application_collect_medium) && ($jobpress_application_collect_medium == 1) && !empty($jobpress_email)){
                echo '<p>' . esc_html($jobpress_email) . '</p>';
            }elseif(!empty($jobpress_resume_common_instruction) && ($jobpress_application_collect_medium == 3)){
                echo '<p>' . esc_html($jobpress_resume_common_instruction) . '</p>';
            }elseif(!empty($jobpress_resume_common_instruction)){
                echo '<p>' . esc_html($jobpress_resume_common_instruction) . '</p>';
            }else{
                echo '<p>' . esc_html__('No application process added by the recruiter.', 'jobpress') . '</p>';
            }?>
        </div>
    </div>
<?php
// endwhile;
// get_footer('jobpress');