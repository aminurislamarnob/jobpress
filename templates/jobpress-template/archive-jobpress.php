<?php
/**
 * The Template for displaying job archives
 *
 * This template can be overridden by copying it to yourtheme/jobpress/archive-jobpress.php.
 *
 * @package JobPress
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

get_header( 'jobpress' );

/**
 * Hook: jobpress_before_main_content.
 */
do_action( 'jobpress_before_main_content' );
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">  
        <?php
        /**
         * Hook: jobpress_job_loop_header.
         */
        do_action( 'jobpress_job_loop_header' );
        ?>
        <?php if ( have_posts() ) : ?>

            <?php
            // Use the default listing template
            echo do_shortcode('[jobpress]');
            ?>

        <?php else : ?>
            <p><?php esc_html_e( 'No jobs found', 'jobpress' ); ?></p>
        <?php endif; ?>
    </main>
</div>
<?php
/**
 * Hook: jobpress_after_main_content.
 */
do_action( 'jobpress_after_main_content' );

get_footer( 'jobpress' );
