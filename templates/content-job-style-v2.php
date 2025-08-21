<?php
/**
 * The template for displaying job content within loops
 *
 * This template can be overridden by copying it to yourtheme/jobpress/content-job-style-v2.php.
 *
 * @version 2.2.0
 */

defined( 'ABSPATH' ) || exit;

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

//job meta
$job_location = get_post_meta(get_the_ID(), 'jobpress_location', true);
$jobpress_experience = get_post_meta( get_the_ID(), 'jobpress_experience', true );
?>
<div class="jp-single-job-list">
    <div class="jp-single-job-info jp-col-4">
        <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
        <p><span class="jp-job-location">
            <?php 
                echo esc_html(!empty($job_type_str) ? $job_type_str : '');
                echo esc_html(!empty($job_location) ? ' - '.$job_location : '');
            ?>
            </span></p>
    </div>

    <?php if(!empty($jobpress_experience)){ ?>
    <div class="jp-single-job-exp jp-text-center jp-col-4">
        <div><?php esc_html_e( 'Experience', 'jobpress' ) ?></div>
        <span><?php echo esc_html($jobpress_experience) ?></span>
    </div>
    <?php } ?>

    <div class="jp-single-job-action jp-col-4 jp-text-right">
        <a class="jp-apply-btn-inline" href="<?php the_permalink(); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
            </svg>
        </a>
    </div>
</div>