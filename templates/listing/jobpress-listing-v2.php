<?php
/**
 * Job listing, design v2: jobs grouped by category, with an arrow link.
 *
 * This template can be overridden by copying it to yourtheme/jobpress/listing/jobpress-listing-v2.php.
 *
 * @var string  $title           Listing title.
 * @var string  $subtitle        Listing subtitle.
 * @var string  $show_type       'yes' to show each job's type.
 * @var string  $show_location   'yes' to show each job's location.
 * @var string  $show_experience 'yes' to show each job's experience.
 * @var string  $button_text     Accessible label of the arrow link to each job.
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
            <div class="jp-category-title jp-d-flex jp-justify-between jp-listing__group-header">
                <div class="jp-category-name">
                    <h4 class="jp-listing__group-title"><?php echo esc_html( $jobpress_group['name'] ) ?></h4>
                    <?php if(!empty($jobpress_group['description'])){ ?>
                    <p class="jp-listing__group-description"><?php echo esc_html( $jobpress_group['description']) ?></p>
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

                // "Type – Location", leaving out hidden or empty parts (a literal en dash, see v1).
                $job_details_str = implode( ' – ', array_filter( array(
                    $show_type === 'yes' ? $job_type_str : '',
                    $show_location === 'yes' ? $job_location : '',
                ) ) );
                ?>
                <div class="jp-single-job-list jp-listing__card">
                    <div class="jp-single-job-info jp-col-4">
                        <h4 class="jp-listing__job-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                        <?php if ( $job_details_str !== '' ) : ?>
                        <p class="jp-listing__meta"><span class="jp-job-location">
                            <?php echo esc_html( $job_details_str ); ?>
                            </span></p>
                        <?php endif; ?>
                    </div>

                    <?php if( $show_experience === 'yes' && !empty($jobpress_experience)){ ?>
                    <div class="jp-listing__experience jp-single-job-exp jp-text-center jp-col-4">
                        <div><?php esc_html_e( 'Experience', 'jobpress' ) ?></div>
                        <span><?php echo esc_html($jobpress_experience) ?></span>
                    </div>
                    <?php } ?>

                    <div class="jp-single-job-action jp-col-4 jp-text-right">
                        <a class="jp-apply-btn-inline jp-listing__button" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( $button_text . ': ' . get_the_title() ); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                            </svg>
                        </a>
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