<?php
namespace JobPressInc\Base;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * JobPress Elementor Widget
 */
class ElementorWidgets extends Widget_Base {

    /**
     * Get widget name.
     *
     * @return string Widget name.
     */
    public function get_name() {
        return 'jobpress_jobs';
    }

    /**
     * Get widget title.
     *
     * @return string Widget title.
     */
    public function get_title() {
        return esc_html__( 'JobPress Jobs', 'jobpress' );
    }

    /**
     * Get widget icon.
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-post-list';
    }

    /**
     * Get widget categories.
     *
     * @return array Widget categories.
     */
    public function get_categories() {
        return [ 'jobpress' ];
    }

    /**
     * Register widget controls.
     */
    protected function register_controls() {
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__( 'Content', 'jobpress' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'title',
            [
                'label' => esc_html__( 'Title', 'jobpress' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__( 'Job Openings', 'jobpress' ),
                'placeholder' => esc_html__( 'Enter your title', 'jobpress' ),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'subtitle',
            [
                'label' => esc_html__( 'Subtitle', 'jobpress' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__( 'Find your dream job', 'jobpress' ),
                'placeholder' => esc_html__( 'Enter your subtitle', 'jobpress' ),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'show_positions',
            [
                'label' => esc_html__( 'Show Positions Count', 'jobpress' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'jobpress' ),
                'label_off' => esc_html__( 'Hide', 'jobpress' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output on the frontend.
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        // Pass the settings to the shortcode handler directly rather than building a
        // shortcode string, where a "]" in the title or subtitle would end the shortcode.
        $shortcode_atts = array(
            'show_positions' => $settings['show_positions'],
        );

        if (!empty($settings['title'])) {
            $shortcode_atts['title'] = $settings['title'];
        }

        if (!empty($settings['subtitle'])) {
            $shortcode_atts['subtitle'] = $settings['subtitle'];
        }

        echo ( new JobListShortcode() )->jobpress_jobs_shortcode( $shortcode_atts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the listing template.
    }
}
