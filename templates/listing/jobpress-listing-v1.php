<?php
/**
 * Job listing, design v1: a bordered list of jobs.
 *
 * This template can be overridden by copying it to yourtheme/jobpress/listing/jobpress-listing-v1.php.
 *
 * @var string   $title          Listing title.
 * @var string   $subtitle       Listing subtitle.
 * @var string   $show_positions 'yes' to show the open positions count.
 * @var string   $show_category  'yes' to show each job's category.
 * @var string   $show_type      'yes' to show each job's type.
 * @var string   $show_location  'yes' to show each job's location.
 * @var string   $button_text    Apply button text.
 * @var WP_Query $jobs_query     Jobs to list.
 * @var int      $total_jobs     Number of jobs matching the listing.
 *
 * @version 2.3.0
 */

defined( 'ABSPATH' ) || exit;

//get total jobs
if($total_jobs < 10){
    $total_job_opens = '0'.$total_jobs;
}else{
    $total_job_opens = $total_jobs;
}
?>
<div class="jp-job-listing-area">
    <div class="jp-section-title jp-text-center jp-listing__header">
        <?php if( !empty( $title ) ): ?>
            <h2 class="jp-listing__title"><?php echo esc_html( $title ); ?></h2>
        <?php endif; ?>
        <?php if( !empty( $subtitle ) ): ?>
            <p class="jp-listing__subtitle"><?php echo esc_html( $subtitle ); ?></p>
        <?php endif; ?>
        <?php if( $show_positions === 'yes' ): ?>
        <p class="jp-listing__count">
            <?php
            // translators: %d is the number of open job positions
            $jobpress_open_positions_text = sprintf(
                esc_html__( '%d open positions', 'jobpress' ),
                esc_html( $total_job_opens )
            );

            /**
             * Filter the text that displays total open job positions.
             *
             * @param string $jobpress_open_positions_text The full text (e.g., "10 open positions").
             * @param int    $total_job_opens              The number of open positions.
             */
            echo wp_kses_post( apply_filters( 'jobpress_open_positions_text', $jobpress_open_positions_text, $total_job_opens ) );
            ?>
        </p>
        <?php endif; ?>
    </div>
    <div class="jobpress-job-lists jp-listing__jobs">
        <?php
        while($jobs_query->have_posts()) : $jobs_query->the_post();

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

        // "Category - Type - Location", leaving out hidden or empty parts.
        $job_category_str = $show_category === 'yes' ? $job_category_str : '';
        $job_details_str  = implode( ' - ', array_filter( array(
            $show_type === 'yes' ? $job_type_str : '',
            $show_location === 'yes' ? $job_location : '',
        ) ) );
        if ( $job_category_str !== '' && $job_details_str !== '' ) {
            $job_details_str = ' - ' . $job_details_str;
        }
        ?>
        <div class="jp-single-job-list jp-listing__card">
            <div class="jp-single-job-info">
                <h4 class="jp-listing__job-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                <?php if ( $job_category_str !== '' || $job_details_str !== '' ) : ?>
                <p class="jp-listing__meta"><?php echo esc_html( $job_category_str ); ?><span class="jp-job-location">
                    <?php echo esc_html( $job_details_str ); ?></span></p>
                <?php endif; ?>
            </div>
            <div class="jp-single-job-action">
                <a class="jp-apply-btn-radius jp-listing__button" href="<?php the_permalink(); ?>"><?php echo esc_html( $button_text ); ?></a>
            </div>
        </div>
        <?php
        endwhile;
        wp_reset_postdata();
        ?>
    </div>
</div>
