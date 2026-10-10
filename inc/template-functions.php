<?php
/**
 * JobPress Template Functions
 *
 * Functions used in the template files to output content.
 *
 * @package JobPress
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'jobpress_get_job_loop_header' ) ) {
    /**
     * Output the start of a job loop. By default this is a UL.
     */
    function jobpress_get_job_loop_header() {
        jobpress_get_template( 'loop/header.php' );
    }
}

if ( ! function_exists( 'jobpress_page_title' ) ) {
    /**
     * Page Title function.
     *
     * @param bool $echo Echo the string or return it.
     * @return string
     */
    function jobpress_page_title( $echo = true ) {
        if ( is_search() ) {
            /* translators: %s: search query */
            $page_title = sprintf( __( 'Search results: %s', 'jobpress' ), get_search_query() );

            if ( get_query_var( 'paged' ) ) {
                /* translators: %s: page number */
                $page_title .= sprintf( __( '&nbsp;&ndash; Page %s', 'jobpress' ), get_query_var( 'paged' ) );
            }
        } elseif ( is_tax() ) {
            $page_title = single_term_title( '', false );
        } else {
            $jobs_page_id = get_option( 'jobpress_jobs_page_id' );
            $page_title   = get_the_title( $jobs_page_id );
        }

        /**
         * Filter the page title.
         *
         * @since 2.1.0
         * @param string $page_title Page title.
         */
        $page_title = apply_filters( 'jobpress_page_title', $page_title );

        if ( $echo ) {
            echo esc_html( $page_title );
        } else {
            return $page_title;
        }
    }
}

if ( ! function_exists( 'jobpress_jobs_loop' ) ) {

    /**
     * Should the JobPress jobs exists?
     * @return bool
     */
    function jobpress_jobs_loop() {
        return have_posts();
    }
}

if ( ! function_exists( 'jobpress_jobs_loop_start' ) ) {

	/**
	 * Output the start of a job loop.
	 *
	 * @param bool $echo Should echo?.
	 * @return string
	 */
	function jobpress_jobs_loop_start( $echo = true ) {
		ob_start();

		jobpress_get_template( 'loop/loop-start.php' );

		$loop_start = apply_filters( 'jobpress_jobs_loop_start', ob_get_clean() );

		if ( $echo ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $loop_start;
		} else {
			return $loop_start;
		}
	}
}

if ( ! function_exists( 'jobpress_jobs_loop_end' ) ) {

	/**
	 * Output the end of a job loop.
	 *
	 * @param bool $echo Should echo?.
	 * @return string
	 */
	function jobpress_jobs_loop_end( $echo = true ) {
		ob_start();

		jobpress_get_template( 'loop/loop-end.php' );

		$loop_end = apply_filters( 'jobpress_jobs_loop_end', ob_get_clean() );

		if ( $echo ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $loop_end;
		} else {
			return $loop_end;
		}
	}
}

/**
 * Get a slug identifying the current activated theme.
 *
 * @return string
 */
function jobpress_get_theme_slug_for_templates() {
	return apply_filters( 'jobpress_theme_slug_for_templates', get_option( 'template' ) );
}

/**
 * Global
 */

 if ( ! function_exists( 'jobpress_output_content_wrapper' ) ) {

	/**
	 * Output the start of the page wrapper.
	 */
	function jobpress_output_content_wrapper() {
		jobpress_get_template( 'global/wrapper-start.php' );
	}
}
if ( ! function_exists( 'jobpress_output_content_wrapper_end' ) ) {

	/**
	 * Output the end of the page wrapper.
	 */
	function jobpress_output_content_wrapper_end() {
		jobpress_get_template( 'global/wrapper-end.php' );
	}
}

if ( ! function_exists( 'jobpress_archive_job_filter_and_search' ) ) {

	/**
	 * Output the filter and search form.
	 */
	function jobpress_archive_job_filter_and_search() {
		jobpress_get_template( 'global/filter-and-search.php' );
	}
}

if ( ! function_exists( 'jobpress_no_jobs_found' ) ) {

	/**
	 * Handles the loop when no jobs were found/no job exist.
	 */
	function jobpress_no_jobs_found() {
		jobpress_get_template( 'loop/no-jobs-found.php' );
	}
}

if ( ! function_exists( 'jobpress_get_block_template_part' ) ) {

	/**
	 * Render a block theme template part ("header" or "footer") as a landmark element.
	 *
	 * Parts are rendered once and cached, and jobpress_get_header() renders both
	 * before wp_head(), like WordPress's own template canvas does: rendering blocks
	 * is what enqueues their layout styles, so doing it later would leave the theme
	 * header and footer without their layout CSS.
	 *
	 * @param string $slug Template part slug: 'header' or 'footer'.
	 * @return string
	 */
	function jobpress_get_block_template_part( $slug ) {
		static $parts = array();

		if ( ! isset( $parts[ $slug ] ) ) {
			$parts[ $slug ] = do_blocks( '<!-- wp:template-part {"slug":"' . $slug . '","tagName":"' . $slug . '"} /-->' );
		}

		return $parts[ $slug ];
	}
}

if ( ! function_exists( 'jobpress_get_header' ) ) {

	/**
	 * Output the site header for JobPress pages.
	 *
	 * Classic themes use their header.php (header-jobpress.php if present). Block
	 * themes have no header.php, so open the document here and render the theme's
	 * "header" template part instead; jobpress_get_footer() closes it again.
	 */
	function jobpress_get_header() {
		if ( ! wp_is_block_theme() ) {
			get_header( 'jobpress' );
			return;
		}

		// Render before wp_head() so the parts' styles are enqueued in time.
		$header = jobpress_get_block_template_part( 'header' );
		jobpress_get_block_template_part( 'footer' );
		?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="wp-site-blocks">
		<?php
		echo $header; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered blocks.
	}
}

if ( ! function_exists( 'jobpress_get_footer' ) ) {

	/**
	 * Output the site footer for JobPress pages. Counterpart of jobpress_get_header().
	 */
	function jobpress_get_footer() {
		if ( ! wp_is_block_theme() ) {
			get_footer( 'jobpress' );
			return;
		}

		echo jobpress_get_block_template_part( 'footer' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered blocks.
		?>
</div>
		<?php
		wp_footer();
		?>
</body>
</html>
		<?php
	}
}
