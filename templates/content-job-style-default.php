<?php
/**
 * The template for displaying job content within loops
 *
 * This template can be overridden by copying it to yourtheme/jobpress/content-job-style-default.php.
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
?>
<div class="jp-single-job-list">
    <div class="jp-single-job-info">
        <h4><?php the_title(); ?></h4>
        <p><?php echo esc_html(!empty($job_category_str) ? $job_category_str : ''); ?>
            <span class="jp-job-location">
                <?php
                    echo esc_html(!empty($job_type_str) ? ' - '.$job_type_str : '');
                    echo esc_html(!empty($job_location) ? ' - '.$job_location : '');
                ?>
            </span>
        </p>
    </div>
    <div class="jp-single-job-action">
        <a class="jp-apply-btn-radius" href="<?php the_permalink(); ?>"><?php esc_html_e('Apply', 'jobpress') ;?></a>
    </div>
</div>