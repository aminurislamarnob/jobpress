<?php
/**
 * The template for displaying job content within loops
 *
 * This template can be overridden by copying it to yourtheme/jobpress/content-job-style-v4.php.
 *
 * @version 2.2.0
 */

defined( 'ABSPATH' ) || exit;

//job type
$job_type = get_the_terms( get_the_ID(), 'jobpress_type' );
$job_type_str = '';
if ( $job_type && ! is_wp_error( $job_type ) ){
    $job_type_str = join(', ', wp_list_pluck($job_type, 'name'));
}

//job meta
$job_vacancy = get_post_meta(get_the_ID(), 'jobpress_vacancy', true);
$job_apply_deadline = get_post_meta(get_the_ID(), 'jobpress_apply_deadline', true);
$jobpress_experience = get_post_meta( get_the_ID(), 'jobpress_experience', true );
?>
<div class="jp-single-job-list">
    <div class="jp-single-job-info">
        <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
        <p>
            <span>
                <?php
                // translators: %s is the job type
                printf( esc_html__( 'Job Type: %s', 'jobpress' ), esc_html( $job_type_str ) );
                ?>
            </span>
            <span>
                <?php
                // translators: %s is the number of job vacancies
                printf( esc_html__( 'Vacancies: %d', 'jobpress' ), esc_html( $job_vacancy ) );
                ?>
            </span>
            <span>
                <?php
                // translators: %s is the job application deadline
                printf( esc_html__( 'Deadline: %s', 'jobpress' ), esc_html( $job_apply_deadline ) );
                ?>
            </span>
        </p>
    </div>

    <?php if(!empty($jobpress_experience)){ ?>
    <div class="jp-single-job-exp">
        <div><?php esc_html_e( 'Experience', 'jobpress' ) ?></div>
        <span><?php echo esc_html( $jobpress_experience ) ?></span>
    </div>
    <?php } ?>

    <div class="jp-single-job-action">
        <a class="jp-apply-btn-radius" href="<?php the_permalink(); ?>"><?php esc_html_e('Apply', 'jobpress') ;?></a>
    </div>
</div>