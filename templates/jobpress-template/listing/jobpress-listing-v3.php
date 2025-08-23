<div class="jp-job-listing-area jp-section-padding">
    <?php
        // Get jobs per page from options or default to 10
        $jobs_per_page = get_option( 'jobpress_jobs_per_page', 10 );
        
        // Get current page for pagination
        $paged = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
        
        $jobs_query = array(
            'posts_per_page' => $jobs_per_page,
            'post_type' => 'jobpress',
            'post_status' => 'publish',
            'orderby' => 'publish_date',
            'order' => 'DESC',
            'paged' => $paged,
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
        <?php if( !empty( $title ) ): ?>
            <h2><?php echo esc_html( $title ); ?></h2>
        <?php endif; ?>
        <?php if( !empty( $subtitle ) ): ?>
            <p><?php echo esc_html( $subtitle ); ?></p>
        <?php endif; ?>
        <?php if( $show_positions === 'yes' ): ?>
        <p>
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
            echo apply_filters( 'jobpress_open_positions_text', $jobpress_open_positions_text, $total_job_opens );
            ?>
        </p>
        <?php endif; ?>
    </div>
    <div class="jobpress-job-lists">
        <?php
        if ( $jobs_query->have_posts() ) :
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
            <div class="jp-single-job-list">
                <div class="jp-job-list-content">
                    <div class="jp-job-list-header">
                        <div class="jp-job-list-title">
                            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        </div>
                        <div class="jp-job-list-meta">
                            <?php if ( !empty( $job_type_str ) ): ?>
                                <span class="jp-job-type"><?php echo esc_html( $job_type_str ); ?></span>
                            <?php endif; ?>
                            <?php if ( !empty( $job_vacancy ) ): ?>
                                <span class="jp-job-vacancy"><?php echo esc_html( $job_vacancy ); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="jp-job-list-excerpt">
                        <?php the_excerpt(); ?>
                    </div>
                    <div class="jp-job-list-footer">
                        <?php if ( !empty( $jobpress_experience ) ): ?>
                            <span class="jp-job-experience"><?php echo esc_html( $jobpress_experience ); ?></span>
                        <?php endif; ?>
                        <?php if ( !empty( $job_apply_deadline ) ): ?>
                            <span class="jp-job-deadline"><?php echo esc_html( $job_apply_deadline ); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php
            endwhile;
            
            // Display pagination
            if ( $jobs_query->max_num_pages > 1 ) {
                jobpress_pagination( $jobs_query );
            }
            
            // Reset post data
            wp_reset_postdata();
        else:
            echo '<p>' . esc_html__( 'No jobs found.', 'jobpress' ) . '</p>';
        endif;
        ?>
    </div>
</div>