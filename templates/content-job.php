<?php
/**
 * The template for displaying job content within loops
 *
 * This template can be overridden by copying it to yourtheme/jobpress/content-job.php.
 *
 * Which fields show, and the button text, follow the Listing Defaults settings.
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

$show_type       = jobpress_get_listing_setting( 'show_type' );
$show_vacancy    = jobpress_get_listing_setting( 'show_vacancy' );
$show_deadline   = jobpress_get_listing_setting( 'show_deadline' );
$show_experience = jobpress_get_listing_setting( 'show_experience' );
$button_text     = jobpress_get_listing_setting( 'button_text' );
?>
<div class="jp-single-job-list jp-listing__card">
    <div class="jp-single-job-info">
        <h4 class="jp-listing__job-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
        <?php if ( $show_type === 'yes' || $show_vacancy === 'yes' || $show_deadline === 'yes' ) : ?>
        <p class="jp-listing__meta">
            <?php if ( $show_type === 'yes' ) : ?>
            <span class="jp-listing__type">
                <?php
                // translators: %s is the job type
                printf( esc_html__( 'Job Type: %s', 'jobpress' ), esc_html( $job_type_str ) );
                ?>
            </span>
            <?php endif; ?>
            <?php if ( $show_vacancy === 'yes' ) : ?>
            <span class="jp-listing__vacancy">
                <?php
                // translators: %s is the number of job vacancies
                printf( esc_html__( 'Vacancies: %s', 'jobpress' ), esc_html( $job_vacancy ) );
                ?>
            </span>
            <?php endif; ?>
            <?php if ( $show_deadline === 'yes' ) : ?>
            <span class="jp-listing__deadline">
                <?php
                // translators: %s is the job application deadline
                printf( esc_html__( 'Deadline: %s', 'jobpress' ), esc_html( jobpress_format_date( $job_apply_deadline ) ) );
                ?>
            </span>
            <?php endif; ?>
        </p>
        <?php endif; ?>
    </div>

    <?php if( $show_experience === 'yes' && !empty($jobpress_experience)){ ?>
    <div class="jp-single-job-exp jp-listing__experience">
        <div><?php esc_html_e( 'Experience', 'jobpress' ) ?></div>
        <span><?php echo esc_html( $jobpress_experience ) ?></span>
    </div>
    <?php } ?>

    <div class="jp-single-job-action">
        <a class="jp-apply-btn-radius jp-listing__button" href="<?php the_permalink(); ?>"><?php echo esc_html( $button_text ); ?></a>
    </div>
</div>