<?php
/**
 * Job listing, design v3: a list of job cards with type, vacancies, deadline and experience.
 *
 * This template can be overridden by copying it to yourtheme/jobpress/listing/jobpress-listing-v3.php.
 *
 * @var string   $title           Listing title.
 * @var string   $subtitle        Listing subtitle.
 * @var string   $show_positions  'yes' to show the open positions count.
 * @var string   $show_type       'yes' to show each job's type.
 * @var string   $show_vacancy    'yes' to show each job's vacancies.
 * @var string   $show_deadline   'yes' to show each job's application deadline.
 * @var string   $show_experience 'yes' to show each job's experience.
 * @var string   $button_text     Apply button text.
 * @var WP_Query $jobs_query      Jobs to list.
 * @var int      $total_jobs      Number of jobs matching the listing.
 * @var string   $search_form     Search form HTML (already escaped), or '' when the search bar is off.
 *
 * @version 2.2.0
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
    <?php echo $search_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the search template. ?>
    <div class="jobpress-job-lists jp-listing__jobs">
        <?php
        while($jobs_query->have_posts()) : $jobs_query->the_post();

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
        <?php       
        endwhile;
        wp_reset_postdata();
        ?>
    </div>
</div>