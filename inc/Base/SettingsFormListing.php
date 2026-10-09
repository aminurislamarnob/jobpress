<?php
namespace JobPressInc\Base;

/**
 * Listing Defaults settings, on the Shortcodes settings screen: the global
 * defaults of every [jobpress] shortcode, JobPress Jobs block and JobPress Elementor widget.
 */
class SettingsFormListing
{

	public function register()
	{
        add_action( 'admin_init', array( $this, 'jobpress_listing_settings' ) );
	}

    /**
     * Register the section, its fields and settings.
     */
    function jobpress_listing_settings() {
        add_settings_section(
            'jobpress_listing_settings_section',
            __( 'Listing Defaults', 'jobpress' ),
            array( $this, 'jobpress_listing_section_text' ),
            'jobpress_shortcode_section'
        );

        foreach ( jobpress_get_listing_settings() as $key => $setting ) {
            $label = $setting['label'];
            if ( ! empty( $setting['archive'] ) ) {
                $label .= ' ' . __( '(also on the jobs archive)', 'jobpress' );
            }

            add_settings_field(
                $setting['option'],
                esc_html( $label ),
                array( $this, 'jobpress_listing_field_callback' ),
                'jobpress_shortcode_section',
                'jobpress_listing_settings_section',
                array(
                    'key'       => $key,
                    'setting'   => $setting,
                    'label_for' => 'checkbox' === $setting['type'] ? null : $setting['option'],
                )
            );

            register_setting(
                'jobpress_shortcode_settings_section',
                $setting['option'],
                array(
                    'sanitize_callback' => 'checkbox' === $setting['type'] ? array( $this, 'sanitize_checkbox' ) : 'sanitize_text_field',
                )
            );
        }
    }

    /**
     * Render a listing setting field.
     *
     * @param array $args Field arguments: 'key' and 'setting'.
     */
    function jobpress_listing_field_callback( $args ) {
        $setting = $args['setting'];
        $value   = jobpress_get_listing_setting( $args['key'] );

        if ( 'checkbox' === $setting['type'] ) {
            printf(
                '<label><input type="checkbox" name="%1$s" id="%1$s" value="yes"%2$s> %3$s</label>',
                esc_attr( $setting['option'] ),
                checked( 'yes', $value, false ),
                esc_html( $setting['label'] )
            );
            return;
        }

        printf(
            '<input type="text" class="regular-text" name="%1$s" id="%1$s" value="%2$s" placeholder="%3$s">',
            esc_attr( $setting['option'] ),
            esc_attr( get_option( $setting['option'], '' ) ),
            esc_attr( $setting['default'] )
        );
    }

    /**
     * Sanitize a checkbox: unchecked boxes aren't submitted, so anything but 'yes' is 'no'.
     *
     * @param mixed $value Submitted value.
     * @return string 'yes' or 'no'.
     */
    function sanitize_checkbox( $value ) {
        return 'yes' === $value ? 'yes' : 'no';
    }

    //Section text
    function jobpress_listing_section_text() {
        printf(
            '<p>%s</p>',
            esc_html__( 'Defaults for every job listing shown with the [jobpress] shortcode, the JobPress Jobs block or the JobPress Elementor widget. Each listing can override them with shortcode attributes or block and widget settings. Leave a text field empty to use the built-in text.', 'jobpress' )
        );
    }
}
