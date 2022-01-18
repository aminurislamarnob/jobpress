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
        <p><?php esc_html_e(  $total_job_opens . ' open positions', 'jobpress' ); ?></p>
    </div>
    <div class="jobpress-job-lists">
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
        <div class="jp-single-job-list">
            <div class="jp-single-job-info">
                <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                <p>
                    <span><?php esc_html_e( 'Job Type: '.$job_type_str, 'jobpress' ); ?></span>
                    <span><?php esc_html_e( 'Vacancies: '.$job_vacancy, 'jobpress' ); ?></span>
                    <span><?php esc_html_e( 'Deadline: '.$job_apply_deadline, 'jobpress' ); ?></span>
                </p>
            </div>

            <?php if(!empty($jobpress_experience)){ ?>
            <div class="jp-single-job-exp">
                <div><?php esc_html_e( 'Experience', 'jobpress' ) ?></div>
                <span><?php echo esc_html( $jobpress_experience ) ?></span>
            </div>
            <?php } ?>

            <div class="jp-single-job-action">
                <a class="jp-apply-btn-radius" href="<?php the_permalink(); ?>"><?php esc_html_e('Apply', 'jobpress') ;?></a>
            </div>
        </div>
        <?php       
        endwhile;
        wp_reset_postdata();
        ?>
    </div>
</div>