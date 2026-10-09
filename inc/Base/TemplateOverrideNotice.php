<?php
namespace JobPressInc\Base;

/**
 * Tell administrators when the theme overrides JobPress templates with outdated copies.
 *
 * Templates carry an "@version" header; a theme copy older than the plugin's
 * (or without one) misses newer features, e.g. listing visibility options and
 * the hook classes Elementor style controls target.
 */
class TemplateOverrideNotice
{
    const DISMISS_ACTION = 'jobpress_dismiss_template_notice';
    const DISMISSED_META = 'jobpress_dismissed_template_notice';

    public function register() {
        add_action( 'admin_notices', array( $this, 'show_notice' ) );
        add_action( 'admin_init', array( $this, 'handle_dismiss' ) );
        add_action( 'init', array( $this, 'register_dismissed_meta' ) );
    }

    /**
     * Expose the dismissal to the REST API (users/me), e.g. to reset it.
     */
    public function register_dismissed_meta() {
        register_meta(
            'user',
            self::DISMISSED_META,
            array(
                'type'          => 'string',
                'single'        => true,
                'show_in_rest'  => true,
                'auth_callback' => function ( $allowed, $meta_key, $user_id ) {
                    return current_user_can( 'edit_user', $user_id );
                },
            )
        );
    }

    /**
     * Find theme copies of JobPress templates that are older than the plugin's.
     *
     * @return array[] Each with 'file' (path relative to the themes directory),
     *                 'version' (theme copy, '' if none) and 'core_version'.
     */
    public static function get_outdated_overrides() {
        $outdated = array();
        $base     = wp_normalize_path( JOBPRESS_PLUGIN_PATH . 'templates/' );
        $iterator = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $base, \FilesystemIterator::SKIP_DOTS ) );

        foreach ( $iterator as $file ) {
            $path = wp_normalize_path( $file->getPathname() );
            if ( 'php' !== $file->getExtension() || false !== strpos( $path, '/task-pages/' ) ) {
                continue;
            }

            // Same lookup as jobpress_locate_template(): yourtheme/jobpress/{name}, then yourtheme/{name}.
            $template_name = substr( $path, strlen( $base ) );
            $theme_file    = locate_template( array( 'jobpress/' . $template_name, $template_name ) );
            if ( ! $theme_file ) {
                continue;
            }

            $core_version = self::get_template_version( $path );
            if ( '' === $core_version ) {
                continue;
            }

            $theme_version = self::get_template_version( $theme_file );
            if ( '' === $theme_version || version_compare( $theme_version, $core_version, '<' ) ) {
                $outdated[] = array(
                    'file'         => substr( wp_normalize_path( $theme_file ), strlen( wp_normalize_path( get_theme_root() ) ) + 1 ),
                    'version'      => $theme_version,
                    'core_version' => $core_version,
                );
            }
        }

        usort(
            $outdated,
            function ( $a, $b ) {
                return strcmp( $a['file'], $b['file'] );
            }
        );

        return $outdated;
    }

    /**
     * Read a template's "@version" header.
     *
     * @param string $file Template path.
     * @return string Version, or '' when there is none.
     */
    private static function get_template_version( $file ) {
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local template file.
        $contents = file_get_contents( $file, false, null, 0, 8192 );
        return $contents && preg_match( '/@version\s+([0-9][0-9a-z.\-]*)/i', $contents, $match ) ? $match[1] : '';
    }

    /**
     * A key for the current set of outdated overrides, so a dismissed notice
     * comes back when the set changes (e.g. after a plugin update).
     *
     * @param array[] $outdated Outdated overrides.
     * @return string
     */
    private static function get_notice_key( $outdated ) {
        return md5( wp_json_encode( $outdated ) );
    }

    /**
     * Output the notice.
     */
    public function show_notice() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $outdated = self::get_outdated_overrides();
        if ( ! $outdated || get_user_meta( get_current_user_id(), self::DISMISSED_META, true ) === self::get_notice_key( $outdated ) ) {
            return;
        }

        $dismiss_url = wp_nonce_url( add_query_arg( self::DISMISS_ACTION, '1' ), self::DISMISS_ACTION );
        ?>
        <div class="notice notice-warning jobpress-template-notice">
            <p>
                <strong><?php esc_html_e( 'Your theme has outdated copies of JobPress templates.', 'jobpress' ); ?></strong>
                <?php esc_html_e( 'They keep working, but miss newer JobPress features such as listing visibility options and the classes Elementor style controls use. Update them from the plugin\'s templates folder:', 'jobpress' ); ?>
            </p>
            <ul class="ul-disc">
                <?php foreach ( $outdated as $override ) : ?>
                    <li>
                        <code><?php echo esc_html( $override['file'] ); ?></code>
                        <?php
                        printf(
                            /* translators: 1: theme copy version, 2: plugin template version */
                            esc_html__( 'version %1$s, latest is %2$s', 'jobpress' ),
                            esc_html( '' !== $override['version'] ? $override['version'] : __( 'unknown', 'jobpress' ) ),
                            esc_html( $override['core_version'] )
                        );
                        ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <p><a href="<?php echo esc_url( $dismiss_url ); ?>"><?php esc_html_e( 'Dismiss this notice', 'jobpress' ); ?></a></p>
        </div>
        <?php
    }

    /**
     * Remember a dismissal for the current set of outdated overrides.
     */
    public function handle_dismiss() {
        if ( empty( $_GET[ self::DISMISS_ACTION ] ) || ! current_user_can( 'manage_options' ) ) {
            return;
        }
        check_admin_referer( self::DISMISS_ACTION );

        update_user_meta( get_current_user_id(), self::DISMISSED_META, self::get_notice_key( self::get_outdated_overrides() ) );
        wp_safe_redirect( remove_query_arg( array( self::DISMISS_ACTION, '_wpnonce' ) ) );
        exit;
    }
}
