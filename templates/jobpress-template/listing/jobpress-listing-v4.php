<div class="jobpress-cover jp-text-center">
    <figure class="jp-img-full-width">
        <img src="https://images.unsplash.com/photo-1557426272-fc759fdf7a8d?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=2100&q=80" alt="">
    </figure>
    <div class="jp-cover-text">
        <h1><?php esc_html_e( 'Work at Truecaller', 'jobpress' ); ?></h1>
        <p><?php esc_html_e( 'Ready to boost your career to new heights?', 'jobpress' ); ?></p>
    </div>
</div>
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
        <div class="jp-category-title jp-align-items-center jp-d-flex jp-justify-between">
            <div class="jp-category-name">
                <h4><?php esc_html_e( $jobpress_cat->name, 'jobpress' ) ?></h4>
                <?php if(!empty($jobpress_cat->description)){ ?>
                <p><?php esc_html_e( $jobpress_cat->description, 'jobpress' ) ?></p>
                <?php } ?>
            </div>
            <div class="jp-category-count jp-text-right"><span class="jp-label"><?php esc_html_e( $jobpress_cat->category_count.' OPENINGS', 'jobpress' ); ?></span></div>
        </div>
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
        ?>
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
                    <span><?php esc_html_e( $jobpress_experience, 'jobpress' ) ?></span>
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
    <?php } ?>
</div>