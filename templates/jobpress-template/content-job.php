<?php
/**
 * The template for displaying job content within loops
 *
 * This template can be overridden by copying it to yourtheme/jobpress/content-job.php.
 *
 * @package JobPress
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $post;
?>
<div <?php post_class( 'jobpress-job-item' ); ?>>
    <div class="jobpress-job-content">
        <div class="jobpress-job-header">
            <h3 class="jobpress-job-title">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h3>
        </div>
        
        <div class="jobpress-job-meta">
            <?php
            // Job type
            $job_type = get_the_terms( get_the_ID(), 'jobpress_type' );
            if ( $job_type && ! is_wp_error( $job_type ) ) {
                echo '<span class="jobpress-job-type">' . esc_html( join( ', ', wp_list_pluck( $job_type, 'name' ) ) ) . '</span>';
            }
            
            // Job vacancy
            $job_vacancy = get_post_meta( get_the_ID(), 'jobpress_vacancy', true );
            if ( ! empty( $job_vacancy ) ) {
                echo '<span class="jobpress-job-vacancy">' . esc_html( $job_vacancy ) . '</span>';
            }
            ?>
        </div>
        
        <div class="jobpress-job-excerpt">
            <?php the_excerpt(); ?>
        </div>
        
        <div class="jobpress-job-footer">
            <?php
            // Job experience
            $job_experience = get_post_meta( get_the_ID(), 'jobpress_experience', true );
            if ( ! empty( $job_experience ) ) {
                echo '<span class="jobpress-job-experience">' . esc_html( $job_experience ) . '</span>';
            }
            
            // Job deadline
            $job_deadline = get_post_meta( get_the_ID(), 'jobpress_apply_deadline', true );
            if ( ! empty( $job_deadline ) ) {
                echo '<span class="jobpress-job-deadline">' . esc_html( $job_deadline ) . '</span>';
            }
            ?>
        </div>
    </div>
</div>