<?php
namespace JobPressInc\Base;

class CustomPostType
{

	public function register() 
	{
        add_action( 'init', array( $this, 'jobpress_jobs_cpt' ) );
        add_action( 'init', array( $this, 'jobpress_jobs_category_taxonomies' ) );
        add_action( 'init', array( $this, 'jobpress_jobs_type_taxonomies' ) );
	}

    /**
	 * Jobs custom post type.
	 */
    function jobpress_jobs_cpt() {
        $labels = array(
            'name'                  => __( 'Jobs', 'jobpress' ),
            'singular_name'         => __( 'Job', 'jobpress' ),
            'menu_name'             => __( 'JobPress', 'jobpress' ),
            'name_admin_bar'        => __( 'Job', 'jobpress' ),
            'add_new'               => __( 'Add New', 'jobpress' ),
            'add_new_item'          => __( 'Add New Job', 'jobpress' ),
            'new_item'              => __( 'New Job', 'jobpress' ),
            'edit_item'             => __( 'Edit Job', 'jobpress' ),
            'view_item'             => __( 'View Job', 'jobpress' ),
            'all_items'             => __( 'All Jobs', 'jobpress' ),
            'search_items'          => __( 'Search Jobs', 'jobpress' ),
            'parent_item_colon'     => __( 'Parent Jobs:', 'jobpress' ),
            'not_found'             => __( 'No Jobs found.', 'jobpress' ),
            'not_found_in_trash'    => __( 'No Jobs found in Trash.', 'jobpress' ),
            'featured_image'        => __( 'Job Cover Image', 'jobpress' ),
            'set_featured_image'    => __( 'Set cover image', 'jobpress' ),
            'remove_featured_image' => __( 'Remove cover image', 'jobpress' ),
            'use_featured_image'    => __( 'Use as cover image', 'jobpress' ),
            'archives'              => __( 'Job archives', 'jobpress' ),
            'insert_into_item'      => __( 'Insert into Job', 'jobpress' ),
            'uploaded_to_this_item' => __( 'Uploaded to this Job', 'jobpress' ),
            'filter_items_list'     => __( 'Filter Jobs list', 'jobpress' ),
            'items_list_navigation' => __( 'Jobs list navigation', 'jobpress' ),
            'items_list'            => __( 'Jobs list', 'jobpress' ),
        );
     
        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'menu_icon'          => 'dashicons-clipboard',
            'rewrite'            => array( 'slug' => 'jobs' ),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt' ),
            'show_in_rest'       => true
        );
     
        register_post_type( 'jobpress', $args );
    }


    /**
     * Job Category taxonomies
     */
    function jobpress_jobs_category_taxonomies() {
        $labels = array(
            'name'              => __( 'Job Category', 'jobpress' ),
            'singular_name'     => __( 'Job Category', 'jobpress' ),
            'search_items'      => __( 'Search Job Categories', 'jobpress' ),
            'all_items'         => __( 'All Job Categories', 'jobpress' ),
            'parent_item'       => __( 'Parent Job Category', 'jobpress' ),
            'parent_item_colon' => __( 'Parent Job Category:', 'jobpress' ),
            'edit_item'         => __( 'Edit Job Category', 'jobpress' ),
            'update_item'       => __( 'Update Job Category', 'jobpress' ),
            'add_new_item'      => __( 'Add New Job Category', 'jobpress' ),
            'new_item_name'     => __( 'New Job Category Name', 'jobpress' ),
            'menu_name'         => __( 'Job Categories', 'jobpress' ),
        );
     
        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'jobpress_category' ),
            'show_in_rest'      => true
        );
        register_taxonomy( 'jobpress_category', array( 'jobpress' ), $args );
    }

    /**
     * Job Type taxonomies
     */
    function jobpress_jobs_type_taxonomies() {
        $labels = array(
            'name'              => __( 'Job Type', 'jobpress' ),
            'singular_name'     => __( 'Job Type', 'jobpress' ),
            'search_items'      => __( 'Search Job Types', 'jobpress' ),
            'all_items'         => __( 'All Job Types', 'jobpress' ),
            'parent_item'       => __( 'Parent Job Type', 'jobpress' ),
            'parent_item_colon' => __( 'Parent Job Type:', 'jobpress' ),
            'edit_item'         => __( 'Edit Job Type', 'jobpress' ),
            'update_item'       => __( 'Update Job Type', 'jobpress' ),
            'add_new_item'      => __( 'Add New Job Type', 'jobpress' ),
            'new_item_name'     => __( 'New Job Type Name', 'jobpress' ),
            'menu_name'         => __( 'Job Types', 'jobpress' ),
        );
     
        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'jobpress_type' ),
            'show_in_rest'      => true
        );
        register_taxonomy( 'jobpress_type', array( 'jobpress' ), $args );
    }
}