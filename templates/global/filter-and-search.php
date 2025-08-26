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
            <input 
                type="search" 
                id="jobpress-keyword"
                name="q" 
                value="<?php echo esc_attr( $current_keyword ); ?>" 
                placeholder="<?php esc_attr_e( 'Search by job title or description', 'jobpress' ); ?>"
            >
        </div>

        <!-- Job Category -->
        <div class="search-field">
            <select id="jobpress-category" name="jobcategory">
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
        <div class="search-field">
            <select id="jobpress-type" name="jobtype">
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
});
</script>