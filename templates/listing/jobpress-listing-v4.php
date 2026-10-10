<?php
/**
 * Job listing, design v4: jobs grouped by category, with an apply button.
 *
 * This template can be overridden by copying it to yourtheme/jobpress/listing/jobpress-listing-v4.php.
 *
 * @var string  $title           Listing title.
 * @var string  $subtitle        Listing subtitle.
 * @var string  $show_type       'yes' to show each job's type.
 * @var string  $show_vacancy    'yes' to show each job's vacancies.
 * @var string  $show_deadline   'yes' to show each job's application deadline.
 * @var string  $show_experience 'yes' to show each job's experience.
 * @var string  $button_text     Apply button text.
 * @var array[] $job_groups      Category groups with jobs: 'name', 'description' and a 'query' WP_Query.
 * @var int     $total_jobs      Number of jobs matching the listing.
 * @var string  $search_form     Search form HTML (already escaped), or '' when the search bar is off.
 *
 * @version 2.2.1
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="jp-job-listing-area">
    <div class="jp-section-title jp-text-center jp-listing__header">
        <?php if( !empty( $title ) ): ?>
            <h2 class="jp-listing__title"><?php echo esc_html( $title ); ?></h2>
        <?php endif; ?>
        <?php if( !empty( $subtitle ) ): ?>
            <p class="jp-listing__subtitle"><?php echo esc_html( $subtitle ); ?></p>
        <?php endif; ?>
    </div>
    <?php echo $search_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the search template. ?>
    <?php
    foreach ( $job_groups as $jobpress_group ) {
        $jobs_query = $jobpress_group['query'];
    ?>
    <div class="jp-category-list-group jp-listing__group">
        <div class="jp-category-title jp-align-items-center jp-d-flex jp-justify-between jp-listing__group-header">
            <div class="jp-category-name">
                <h4 class="jp-listing__group-title"><?php echo esc_html( $jobpress_group['name'] ) ?></h4>
                <?php if(!empty($jobpress_group['description'])){ ?>
                <p class="jp-listing__group-description"><?php echo esc_html( $jobpress_group['description'] ) ?></p>
                <?php } ?>
            </div>
            <div class="jp-category-count jp-text-right">
                <span class="jp-label jp-listing__group-count">
                    <?php
                    // translators: %d is the number of job openings in the category
                    printf( esc_html( _n( '%d OPENING', '%d OPENINGS', $jobs_query->found_posts, 'jobpress' ) ), absint( $jobs_query->found_posts ) );
                    ?>
                </span>
            </div>
        </div>

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

            // Fields without a value are left out.
            $show_job_type     = 'yes' === $show_type && '' !== $job_type_str;
            $show_job_vacancy  = 'yes' === $show_vacancy && '' !== (string) $job_vacancy;
            $show_job_deadline = 'yes' === $show_deadline && '' !== (string) $job_apply_deadline;
            ?>
            <div class="jp-single-job-list jp-listing__card">
                <div class="jp-single-job-info">
                    <h4 class="jp-listing__job-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                    <?php if ( $show_job_type || $show_job_vacancy || $show_job_deadline ) : ?>
                    <p class="jp-listing__meta">
                        <?php if ( $show_job_type ) : ?>
                        <span class="jp-listing__type">
                            <?php
                            // translators: %s is the job type
                            printf( esc_html__( 'Job Type: %s', 'jobpress' ), esc_html( $job_type_str ) );
                            ?>
                        </span>
                        <?php endif; ?>
                        <?php if ( $show_job_vacancy ) : ?>
                        <span class="jp-listing__vacancy">
                            <?php
                            // translators: %s is the number of job vacancies
                            printf( esc_html__( 'Vacancies: %s', 'jobpress' ), esc_html( $job_vacancy ) );
                            ?>
                        </span>
                        <?php endif; ?>
                        <?php if ( $show_job_deadline ) : ?>
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
                <div class="jp-listing__experience jp-single-job-exp">
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
    <?php } ?>
</div>