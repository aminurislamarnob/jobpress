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
                <p><?php !empty($job_category_str) ? esc_html_e( $job_category_str, 'jobpress' ) : ''; ?><span class="jp-job-location">
                    <?php 
                        !empty($job_type_str) ? esc_html_e( ' - '.$job_type_str, 'jobpress' ) : ''; 
                        !empty($job_location) ? esc_html_e( ' - '.$job_location, 'jobpress' ) : ''; 
                    ?>
                    </span></p>
            </div>
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