<div class="jp-job-listing-area jp-section-padding">
    <?php
        $jobs_per_page = -1;
        $jobs_query = array(
            'posts_per_page' => $jobs_per_page,
            'post_type' => 'jobpress',
            'post_status' => 'publish',
            'orderby' => 'publish_date',
            'order' => 'DESC',
        );
        $jobs_query = new WP_Query( $jobs_query );

        //get total jobs
        if($jobs_query->found_posts < 10){
            $total_job_opens = '0'.$jobs_query->found_posts;
        }else{
            $total_job_opens = $jobs_query->found_posts;
        }
    ?>
    <div class="jp-section-title jp-text-center">
        <h2><?php esc_html_e( 'Job openings', 'jobpress' ); ?></h2>
        <p>
            <?php 
            // translators: %d is the number of open job positions
            printf( esc_html__( '%d open positions', 'jobpress' ), esc_html($total_job_opens) );
            ?>
        </p>
    </div>
    <div class="jobpress-job-grids">
        <div class="jp-row">
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
            <?php       
            endwhile;
            wp_reset_postdata();
            ?>
        </div>
    </div>
</div>