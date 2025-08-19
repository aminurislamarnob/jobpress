<?php
namespace JobPressInc\Base;

/**
 * Add post states for JobPress pages in the page list table.
 */
class PostStates {

    /**
     * Register hooks
     */
    public function register() {
        add_filter( 'display_post_states', array( $this, 'add_post_states' ), 10, 2 );
    }

    /**
     * Add post states for JobPress pages in the page list table.
     *
     * @param array   $post_states An array of post display states.
     * @param WP_Post $post        The current post object.
     */
    public function add_post_states( $post_states, $post ) {
        if ( ! $post || 'page' !== $post->post_type ) {
            return $post_states;
        }

        $jobs_page_id = get_option( 'jobpress_jobs_page_id' );

        if ( $jobs_page_id && $post->ID === (int) $jobs_page_id ) {
            $post_states['jobpress_page_for_jobs'] = __( 'Jobs Page', 'jobpress' );
        }

        return $post_states;
    }
}
