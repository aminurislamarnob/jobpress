<?php
namespace JobPressInc\Base;

defined( 'ABSPATH' ) || exit;

/**
 * Template loader class.
 */
class TemplateLoader {

    /**
     * Hook in methods.
     */
    public function register() {
        add_filter( 'template_include', array( $this, 'template_loader' ) );
    }

    /**
     * Load a template.
     *
     * Handles template usage so that we can use our own templates instead of the theme's.
     *
     * Templates are in the 'templates' folder. JobPress looks for theme
     * overrides in /theme/jobpress/ by default.
     *
     * @param string $template Template to load.
     * @return string
     */
    public function template_loader( $template ) {
        if ( is_embed() ) {
            return $template;
        }

        $default_file = $this->get_template_loader_default_file();

        if ( $default_file ) {
            $search_files = $this->get_template_loader_files( $default_file );
            $template     = locate_template( $search_files );

            if ( ! $template ) {
                $template = JOBPRESS_PLUGIN_PATH . 'templates/jobpress-template/' . $default_file;
            }
        }

        return $template;
    }

    /**
     * Get the default filename for a template.
     *
     * @return string
     */
    private function get_template_loader_default_file() {
        if ( is_singular( 'jobpress' ) ) {
            $default_file = 'single-jobpress.php';
        } elseif ( is_post_type_archive( 'jobpress' ) || $this->is_jobs_page() ) {
            $default_file = 'archive-jobpress.php';
        } elseif ( is_tax( 'jobpress_category' ) ) {
            $default_file = 'taxonomy-jobpress_category.php';
        } elseif ( is_tax( 'jobpress_type' ) ) {
            $default_file = 'taxonomy-jobpress_type.php';
        } else {
            $default_file = '';
        }

        return apply_filters( 'jobpress_template_loader_default_file', $default_file );
    }

    /**
     * Check if current page is jobs page.
     *
     * @return bool
     */
    private function is_jobs_page() {
        $jobs_page_id = get_option( 'jobpress_jobs_page_id' );
        return $jobs_page_id && is_page( $jobs_page_id );
        return $default_file;
    }

    /**
     * Get an array of filenames to search for a given template.
     *
     * @param string $default_file The default file name.
     * @return array
     */
    private function get_template_loader_files( $default_file ) {
        $templates   = array();
        $templates[] = 'jobpress.php';

        if ( is_singular( 'jobpress' ) ) {
            $object       = get_queried_object();
            $name_decoded = urldecode( $object->post_name );
            
            if ( $name_decoded !== $object->post_name ) {
                $templates[] = "single-jobpress-{$name_decoded}.php";
            }
            $templates[] = "single-jobpress-{$object->post_name}.php";
        }

        if ( is_tax( 'jobpress_category' ) || is_tax( 'jobpress_type' ) ) {
            $object      = get_queried_object();
            $templates[] = 'taxonomy-' . $object->taxonomy . '-' . $object->slug . '.php';
            $templates[] = 'taxonomy-' . $object->taxonomy . '.php';
        }

        $templates[] = $default_file;
        $templates[] = 'jobpress/' . $default_file;

        return array_unique( $templates );
    }
}
