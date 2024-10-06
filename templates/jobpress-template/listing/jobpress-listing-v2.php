<?php
    // Get list of all taxonomy terms
    $jobpress_args = array(
        'taxonomy' => 'jobpress_category',
        'orderby' => 'name',
        'order'   => 'ASC'
    );
    $jobpress_cats = get_categories($jobpress_args);
    
?>
    <div class="jp-job-listing-area jp-section-padding">
        <div class="jp-section-title jp-text-center">
            <h2><?php esc_html_e( 'Job openings', 'jobpress' ); ?></h2>
            <p><?php esc_html_e( 'Find the right job for you no matter what it is that you do.', 'jobpress' ); ?></p>
        </div>
        <?php foreach($jobpress_cats as $jobpress_cat) { ?>
        <div class="jp-category-list-group">
            <div class="jp-category-title jp-d-flex jp-justify-between">
                <div class="jp-category-name">
                    <h4><?php echo esc_html( $jobpress_cat->name ) ?></h4>
                    <?php if(!empty($jobpress_cat->description)){ ?>
                    <p><?php echo esc_html( $jobpress_cat->description) ?></p>
                    <?php } ?>
                </div>
                <div class="jp-category-count jp-text-right">
                    <span class="jp-label">
                        <?php
                        // translators: %d is the number of job openings in the category
                        printf( esc_html__( '%d OPENINGS', 'jobpress' ), esc_html( $jobpress_cat->category_count ) );
                        ?>
                    </span>
                </div>
            </div>
            <?php
                $jobs_per_page = -1;
                $jobs_query = array(
                    'posts_per_page' => $jobs_per_page,
                    'post_type' => 'jobpress',
                    'post_status' => 'publish',
                    'orderby' => 'publish_date',
                    'order' => 'DESC',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'jobpress_category',
                            'field'    => 'term_id',   
                            'terms'    => $jobpress_cat->term_id,
                        ),
                    ),
                );
                $jobs_query = new WP_Query( $jobs_query );
            ?>
            
            <div class="jobpress-job-lists">
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
            <?php       
                endwhile;
                wp_reset_postdata();
            ?>
            </div>
        </div>
        <?php } ?>
    </div>