<?php
/**
 * The template for displaying job content within loops
 *
 * This template can be overridden by copying it to yourtheme/jobpress/jobpress-listing-v5.php.
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
$job_vacancy = get_post_meta(get_the_ID(), 'jobpress_vacancy', true);
$job_apply_deadline = get_post_meta(get_the_ID(), 'jobpress_apply_deadline', true);
$jobpress_experience = get_post_meta( get_the_ID(), 'jobpress_experience', true );
?>
<div class="jp-grid-col">
    <a href="<?php the_permalink(); ?>" class="jp-single-job-grid">
        <div>
            <div class="jp-category"><?php echo esc_html(!empty($job_category_str) ? $job_category_str : ''); ?></div>
            <div class="jp-single-job-info">
                <h4><?php the_title(); ?></h4>
                <?php if ( has_excerpt() ) {
                    the_excerpt();
                } ?>
            </div>
        </div>
        <div class="jp-job-type"><?php echo esc_html($job_type_str); ?></div>
    </a>
</div>