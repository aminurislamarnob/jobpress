<?php
/**
 * JobPress Archive Filter and Search
 *
 * This template can be overridden by copying it to yourtheme/jobpress/global/filter-and-search.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Get current search values
$current_keyword = get_query_var( 'q' );
$current_jobcategory = get_query_var( 'jobcategory' );
$current_jobtype = get_query_var( 'jobtype' );

// Get jobs page URL
$jobs_page_id = get_option( 'jobpress_jobs_page_id' );
$form_action = $jobs_page_id ? get_permalink( $jobs_page_id ) : home_url( '/jobs/' );
?>

<div class="jobpress-search-form-wrapper">
    <form action="<?php echo esc_url( $form_action ); ?>" method="get" id="jobpress-search-form">
        
        <!-- Keyword Search -->
        <div class="search-field">
            <label for="jobpress-keyword">
                <span class="job-field-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24"><path d="M23.707,22.293l-5.969-5.969a10.016,10.016,0,1,0-1.414,1.414l5.969,5.969a1,1,0,0,0,1.414-1.414ZM10,18a8,8,0,1,1,8-8A8.009,8.009,0,0,1,10,18Z"/></svg>
                </span>
            </label>
            <input 
                type="search" 
                id="jobpress-keyword"
                name="q" 
                value="<?php echo esc_attr( $current_keyword ); ?>" 
                placeholder="<?php esc_attr_e( 'Search by job title or description', 'jobpress' ); ?>"
            >
        </div>

        <!-- Job Category -->
        <div class="search-field select-field">
            <label for="jobpress-category">
                <span class="job-field-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                        <path d="m13,1c0-.552.447-1,1-1h2c2.206,0,4,1.794,4,4v1.971l1.293-1.293c.391-.391,1.023-.391,1.414,0s.391,1.023,0,1.414l-2.346,2.346c-.375.375-.868.563-1.361.563s-.986-.188-1.361-.563l-2.346-2.346c-.391-.391-.391-1.023,0-1.414s1.023-.391,1.414,0l1.293,1.293v-1.971c0-1.103-.897-2-2-2h-2c-.553,0-1-.448-1-1Zm-3,21h-2c-1.103,0-2-.897-2-2v-1.971l1.293,1.293c.195.195.451.293.707.293s.512-.098.707-.293c.391-.391.391-1.023,0-1.414l-2.346-2.346c-.75-.751-1.973-.751-2.723,0l-2.346,2.346c-.391.391-.391,1.023,0,1.414s1.023.391,1.414,0l1.293-1.293v1.971c0,2.206,1.794,4,4,4h2c.553,0,1-.448,1-1s-.447-1-1-1Zm1-16.5c0,1.899-.968,3.636-2.588,4.647-.254.158-.412.468-.412.807,0,1.149-.897,2.046-2,2.046h-1c-1.103,0-2-.897-2-2,0-.386-.153-.693-.4-.846C.727,8.992-.269,6.829.065,4.643.408,2.403,2.115.588,4.314.125c1.66-.349,3.354.052,4.648,1.102,1.295,1.05,2.037,2.607,2.037,4.273Zm-2,0c0-1.06-.473-2.051-1.297-2.72-.634-.514-1.394-.781-2.196-.781-.257,0-.518.027-.78.083-1.366.288-2.471,1.465-2.685,2.863-.216,1.414.402,2.759,1.612,3.51.347.216.623.521.846.864v-2.425c-.4-.212-.734-.532-.935-.939-.218-.44.143-.954.634-.954h2.602c.491,0,.852.514.634.954-.201.407-.535.727-.935.939v2.434c.224-.349.503-.658.854-.877,1.031-.643,1.646-1.746,1.646-2.95Zm15,11c0,1.899-.968,3.636-2.588,4.647-.254.158-.412.468-.412.807,0,1.149-.897,2.046-2,2.046h-1c-1.103,0-2-.897-2-2,0-.386-.153-.693-.4-.846-1.873-1.162-2.868-3.325-2.534-5.511.343-2.239,2.05-4.055,4.249-4.518,1.66-.35,3.354.052,4.648,1.102,1.295,1.05,2.037,2.607,2.037,4.273Zm-2,0c0-1.06-.473-2.051-1.297-2.72-.634-.514-1.394-.781-2.196-.781-.257,0-.518.027-.78.083-1.366.288-2.471,1.465-2.685,2.863-.216,1.414.402,2.759,1.612,3.51.347.216.623.521.846.864v-2.425c-.4-.212-.734-.532-.935-.939-.218-.44.143-.954.634-.954h2.602c.491,0,.852.514.634.954-.201.407-.535.727-.935.939v2.434c.224-.349.503-.658.854-.877,1.031-.643,1.646-1.746,1.646-2.95Z"/>
                    </svg>
                </span>
            </label>
            <select id="jobpress-category" name="jobcategory" class="<?php echo jobpress_is_term_selected( $current_jobcategory, 'jobpress_category' ) ? 'selected' : ''; ?>">
                <option value=""><?php esc_html_e( 'Select category', 'jobpress' ); ?></option>
                <?php
                $categories = get_terms( array(
                    'taxonomy' => 'jobpress_category',
                    'hide_empty' => true,
                ) );
                
                if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
                    foreach ( $categories as $category ) {
                        $selected = ( $current_jobcategory === $category->slug ) ? 'selected' : '';
                        echo '<option value="' . esc_attr( $category->slug ) . '" ' . $selected . '>';
                        echo esc_html( $category->name );
                        echo '</option>';
                    }
                }
                ?>
            </select>
        </div>

        <!-- Job Type -->
        <div class="search-field select-field">
            <label for="jobpress-type">
                <span class="job-field-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24"><path d="M19,4H17.9A5.009,5.009,0,0,0,13,0H11A5.009,5.009,0,0,0,6.1,4H5A5.006,5.006,0,0,0,0,9V19a5.006,5.006,0,0,0,5,5H19a5.006,5.006,0,0,0,5-5V9A5.006,5.006,0,0,0,19,4ZM11,2h2a3,3,0,0,1,2.816,2H8.184A3,3,0,0,1,11,2ZM5,6H19a3,3,0,0,1,3,3v3H2V9A3,3,0,0,1,5,6ZM19,22H5a3,3,0,0,1-3-3V14h9v1a1,1,0,0,0,2,0V14h9v5A3,3,0,0,1,19,22Z"/></svg>
                </span>
            </label>
            <select id="jobpress-type" name="jobtype" class="<?php echo jobpress_is_term_selected( $current_jobtype, 'jobpress_type' ) ? 'selected' : ''; ?>">
                <option value=""><?php esc_html_e( 'Select type', 'jobpress' ); ?></option>
                <?php
                $job_types = get_terms( array(
                    'taxonomy' => 'jobpress_type',
                    'hide_empty' => true,
                ) );
                
                if ( ! empty( $job_types ) && ! is_wp_error( $job_types ) ) {
                    foreach ( $job_types as $job_type ) {
                        $selected = ( $current_jobtype === $job_type->slug ) ? 'selected' : '';
                        echo '<option value="' . esc_attr( $job_type->slug ) . '" ' . $selected . '>';
                        echo esc_html( $job_type->name );
                        echo '</option>';
                    }
                }
                ?>
            </select>
        </div>

        <!-- Submit Button -->
        <div class="search-field search-submit-field">
            <button type="submit" class="search-submit">
                <?php esc_html_e( 'Find Jobs', 'jobpress' ); ?>
            </button>
        </div>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    /**
     * Submit form on search button click
     */
    const form = document.getElementById('jobpress-search-form');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form data
        const formData = new FormData(form);
        const params = new URLSearchParams();
        
        // Only add non-empty values
        for (let [key, value] of formData.entries()) {
            if (value && value.trim() !== '') {
                params.append(key, value.trim());
            }
        }
        
        // Redirect to the search results page
        const searchUrl = '<?php echo esc_url( $form_action ); ?>' + (params.toString() ? '?' + params.toString() : '');
        window.location.href = searchUrl;
    });

    /**
     * Toggle selected class on select change
     */
    const category = document.getElementById('jobpress-category');
    const type = document.getElementById('jobpress-type');

    function toggleSelectedClass(selectEl) {
        if (selectEl.value !== '') {
        selectEl.classList.add('selected');
        } else {
        selectEl.classList.remove('selected');
        }
    }

    [category, type].forEach(select => {
        if (select) {
        // Run once on load
        toggleSelectedClass(select);

        // Update on change
        select.addEventListener('change', function () {
            toggleSelectedClass(select);
        });
        }
    });
});
</script>