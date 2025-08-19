<?php
namespace JobPressInc\Base;

/**
 * Add body classes for JobPress pages.
 */
class BodyClasses {

    /**
     * Register hooks
     */
    public function register() {
        add_filter( 'body_class', array( $this, 'add_body_classes' ) );
    }

    /**
     * Add classes to the body tag.
     *
     * @param array $classes Array of body classes.
     * @return array
     */
    public function add_body_classes( $classes ) {
        if ( is_post_type_archive( 'jobpress' ) || jobpress_is_jobs_page() ) {
            $classes[] = 'jobpress-archive';
            $classes[] = 'jobpress-page';
            $classes[] = 'archive';
            $classes[] = 'post-type-archive';
            $classes[] = 'post-type-archive-jobpress';
        } elseif ( is_singular( 'jobpress' ) ) {
            $classes[] = 'jobpress-page';
            $classes[] = 'jobpress-single';
            $classes[] = 'single-jobpress';
        } elseif ( is_tax( array( 'jobpress_category', 'jobpress_type' ) ) ) {
            $classes[] = 'jobpress-archive';
            $classes[] = 'jobpress-page';
            $classes[] = 'jobpress-tax';
            $classes[] = 'tax-' . get_queried_object()->taxonomy;
        }

        return $classes;
    }
}
