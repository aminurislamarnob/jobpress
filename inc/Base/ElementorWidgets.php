<?php
namespace JobPressInc\Base;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * JobPress Elementor Widget
 *
 * A front end for the [jobpress] listing engine: every setting maps onto a
 * shortcode attribute, and every setting left empty inherits the global
 * JobPress settings. Style controls write Elementor CSS for the widget.
 */
class ElementorWidgets extends Widget_Base {

    /**
     * Prefix of the style control selectors. The doubled class outranks the
     * listing designs' own rules (scoped to .jp-design-v{N}).
     */
    const SCOPE = '{{WRAPPER}} .jp-listing.jp-listing';

    /**
     * Visibility settings: widget control => shortcode attribute.
     */
    const VISIBILITY_CONTROLS = array(
        'show_title'      => 'show_title',
        'show_subtitle'   => 'show_subtitle',
        'show_count'      => 'show_positions',
        'show_category'   => 'show_category',
        'show_type'       => 'show_type',
        'show_location'   => 'show_location',
        'show_experience' => 'show_experience',
        'show_vacancy'    => 'show_vacancy',
        'show_deadline'   => 'show_deadline',
        'show_search'     => 'show_search',
        'show_view_all'   => 'show_view_all',
    );

    /**
     * Settings passed to the shortcode as they are (lists are joined with commas).
     */
    const ATTRIBUTE_CONTROLS = array(
        'design',
        'title',
        'subtitle',
        'button_text',
        'view_all_text',
        'per_page',
        'category',
        'type',
        'include',
        'exclude',
        'orderby',
        'order',
    );

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
     * Get widget keywords.
     *
     * @return array Widget keywords.
     */
    public function get_keywords() {
        return [ 'jobs', 'job', 'careers', 'listing', 'jobpress' ];
    }

    /**
     * Register widget controls.
     */
    protected function register_controls() {
        $this->register_layout_controls();
        $this->register_header_controls();
        $this->register_card_controls();
        $this->register_search_controls();
        $this->register_query_controls();
        $this->register_color_style_controls();
        $this->register_header_style_controls();
        $this->register_card_style_controls();
        $this->register_job_title_style_controls();
        $this->register_meta_style_controls();
        $this->register_button_style_controls();
        $this->register_group_style_controls();
        $this->register_search_style_controls();
        $this->register_view_all_style_controls();

        /**
         * Fires after the JobPress Elementor widget registered its controls, so
         * add-ons can add their own.
         *
         * @param ElementorWidgets $widget The widget.
         */
        do_action( 'jobpress_elementor_widget_controls', $this );
    }

    /**
     * Design names, keyed by design number.
     *
     * @return string[]
     */
    private function get_design_names() {
        return array_map( 'esc_html', jobpress_get_design_names() );
    }

    /**
     * Values of the design control that render one of the given designs: the
     * design numbers, plus "Default" when the global design is one of them.
     *
     * @param int[] $designs Design numbers.
     * @return string[]
     */
    protected function get_design_condition( $designs ) {
        $values = array_map( 'strval', $designs );
        if ( in_array( jobpress_get_short_design_type(), $designs, true ) ) {
            $values[] = '';
        }
        return $values;
    }

    /**
     * Add a Default/Show/Hide control; "Default" follows the global setting.
     *
     * @param string $name      Control name.
     * @param string $label     Control label.
     * @param string $attribute Shortcode attribute holding the global default.
     * @param array  $args      Extra control arguments, e.g. a condition.
     */
    protected function add_visibility_control( $name, $label, $attribute, $args = array() ) {
        $default = 'yes' === jobpress_get_listing_setting( $attribute ) ? esc_html__( 'Show', 'jobpress' ) : esc_html__( 'Hide', 'jobpress' );

        $this->add_control(
            $name,
            array_merge(
                array(
                    'label'   => $label,
                    'type'    => Controls_Manager::SELECT,
                    'default' => '',
                    'options' => array(
                        /* translators: %s: Show or Hide, the global setting */
                        ''    => sprintf( esc_html__( 'Default (%s)', 'jobpress' ), $default ),
                        'yes' => esc_html__( 'Show', 'jobpress' ),
                        'no'  => esc_html__( 'Hide', 'jobpress' ),
                    ),
                ),
                $args
            )
        );
    }

    /**
     * Add a text control whose empty value inherits the global setting, shown as placeholder.
     *
     * @param string $name  Control name (the shortcode attribute).
     * @param string $label Control label.
     * @param array  $args  Extra control arguments.
     */
    protected function add_inherited_text_control( $name, $label, $args = array() ) {
        $this->add_control(
            $name,
            array_merge(
                array(
                    'label'       => $label,
                    'type'        => Controls_Manager::TEXT,
                    'default'     => '',
                    'placeholder' => jobpress_get_listing_setting( $name ),
                    'label_block' => true,
                    'description' => esc_html__( 'Leave empty to use the JobPress settings.', 'jobpress' ),
                ),
                $args
            )
        );
    }

    /**
     * Content tab: layout.
     */
    protected function register_layout_controls() {
        $this->start_controls_section(
            'layout_section',
            [
                'label' => esc_html__( 'Layout', 'jobpress' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $design_names = $this->get_design_names();
        /* translators: %s: name of the design selected in the JobPress settings */
        $default_design = sprintf( esc_html__( 'Default (%s)', 'jobpress' ), $design_names[ (string) jobpress_get_short_design_type() ] );
        $this->add_control(
            'design',
            [
                'label' => esc_html__( 'Design', 'jobpress' ),
                'type' => Controls_Manager::SELECT,
                'default' => '',
                'options' => array( '' => $default_design ) + $design_names,
                // Options with numeric keys are listed before the others, so group the
                // designs to keep "Default" first.
                'groups' => array(
                    '' => $default_design,
                    'designs' => array(
                        'label' => esc_html__( 'Designs', 'jobpress' ),
                        'options' => $design_names,
                    ),
                ),
            ]
        );

        $this->add_responsive_control(
            'columns',
            [
                'label' => esc_html__( 'Columns', 'jobpress' ),
                'type' => Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 6,
                'default' => '',
                'description' => esc_html__( 'Most cards per row. Fewer are shown where cards would get too narrow.', 'jobpress' ),
                'selectors' => [
                    // A maximum: the grid drops columns where cards would be narrower than 180px,
                    // since the desktop value also applies to tablets and phones unless they set their own.
                    self::SCOPE . ' .jobpress-job-grids .jp-row' => 'display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, max(180px, calc(100% / {{VALUE}} - var(--jp-grid-gap, 24px)))), 1fr));',
                ],
                'condition' => [ 'design' => $this->get_design_condition( array( 5 ) ) ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Content tab: header.
     */
    protected function register_header_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__( 'Header', 'jobpress' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_visibility_control( 'show_title', esc_html__( 'Title', 'jobpress' ), 'show_title' );
        $this->add_inherited_text_control( 'title', esc_html__( 'Title text', 'jobpress' ), [
            'condition' => [ 'show_title' => $this->get_inherited_condition( 'show_title' ) ],
        ] );

        $this->add_visibility_control( 'show_subtitle', esc_html__( 'Subtitle', 'jobpress' ), 'show_subtitle' );
        $this->add_inherited_text_control( 'subtitle', esc_html__( 'Subtitle text', 'jobpress' ), [
            'condition' => [ 'show_subtitle' => $this->get_inherited_condition( 'show_subtitle' ) ],
        ] );

        $this->add_visibility_control( 'show_count', esc_html__( 'Open positions count', 'jobpress' ), 'show_positions', [
            'condition' => [ 'design' => $this->get_design_condition( array( 1, 3, 5 ) ) ],
        ] );

        $this->end_controls_section();
    }

    /**
     * Values of a visibility control that show the element: "Show", plus
     * "Default" when the global setting shows it.
     *
     * @param string $attribute Shortcode attribute holding the global default.
     * @return string[]
     */
    protected function get_inherited_condition( $attribute ) {
        $values = array( 'yes' );
        if ( 'yes' === jobpress_get_listing_setting( $attribute ) ) {
            $values[] = '';
        }
        return $values;
    }

    /**
     * Content tab: job card fields.
     */
    protected function register_card_controls() {
        $this->start_controls_section(
            'card_section',
            [
                'label' => esc_html__( 'Job Card', 'jobpress' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $fields = jobpress_get_listing_design_fields();
        $labels = array(
            'category'   => esc_html__( 'Category', 'jobpress' ),
            'type'       => esc_html__( 'Job type', 'jobpress' ),
            'location'   => esc_html__( 'Location', 'jobpress' ),
            'experience' => esc_html__( 'Experience', 'jobpress' ),
            'vacancy'    => esc_html__( 'Vacancies', 'jobpress' ),
            'deadline'   => esc_html__( 'Deadline', 'jobpress' ),
        );
        foreach ( $labels as $field => $label ) {
            $this->add_visibility_control( 'show_' . $field, $label, 'show_' . $field, [
                'condition' => [ 'design' => $this->get_design_condition( $fields[ $field ] ) ],
            ] );
        }

        $this->add_inherited_text_control( 'button_text', esc_html__( 'Button text', 'jobpress' ), [
            'condition' => [ 'design' => $this->get_design_condition( $fields['button'] ) ],
        ] );

        $this->end_controls_section();
    }

    /**
     * Content tab: search bar and "View all jobs" link.
     */
    protected function register_search_controls() {
        $this->start_controls_section(
            'search_section',
            [
                'label' => esc_html__( 'Search & Links', 'jobpress' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_visibility_control( 'show_search', esc_html__( 'Search bar', 'jobpress' ), 'show_search', [
            'description' => esc_html__( 'Searches open the Jobs Page results.', 'jobpress' ),
        ] );

        $this->add_visibility_control( 'show_view_all', esc_html__( '"View all jobs" link', 'jobpress' ), 'show_view_all', [
            'separator' => 'before',
        ] );
        $this->add_inherited_text_control( 'view_all_text', esc_html__( 'Link text', 'jobpress' ), [
            'condition' => [ 'show_view_all' => $this->get_inherited_condition( 'show_view_all' ) ],
        ] );

        $this->end_controls_section();
    }

    /**
     * Content tab: which jobs to list.
     */
    protected function register_query_controls() {
        $this->start_controls_section(
            'query_section',
            [
                'label' => esc_html__( 'Query', 'jobpress' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'per_page',
            [
                'label' => esc_html__( 'Number of jobs', 'jobpress' ),
                'type' => Controls_Manager::NUMBER,
                'min' => 1,
                'default' => '',
                'description' => esc_html__( 'Leave empty to show all jobs. Grouped designs show this many per category.', 'jobpress' ),
            ]
        );

        $this->add_control(
            'category',
            [
                'label' => esc_html__( 'Categories', 'jobpress' ),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'label_block' => true,
                'options' => $this->get_term_options( 'jobpress_category' ),
                'description' => esc_html__( 'Show jobs in any of these categories. Leave empty for all.', 'jobpress' ),
            ]
        );

        $this->add_control(
            'type',
            [
                'label' => esc_html__( 'Job types', 'jobpress' ),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'label_block' => true,
                'options' => $this->get_term_options( 'jobpress_type' ),
                'description' => esc_html__( 'Show jobs of any of these types. Leave empty for all.', 'jobpress' ),
            ]
        );

        $job_options = $this->get_job_options();
        $this->add_control(
            'include',
            [
                'label' => esc_html__( 'Only these jobs', 'jobpress' ),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'label_block' => true,
                'options' => $job_options,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'exclude',
            [
                'label' => esc_html__( 'Exclude jobs', 'jobpress' ),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'label_block' => true,
                'options' => $job_options,
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label' => esc_html__( 'Order by', 'jobpress' ),
                'type' => Controls_Manager::SELECT,
                'default' => 'date',
                'options' => [
                    'date' => esc_html__( 'Date', 'jobpress' ),
                    'title' => esc_html__( 'Title', 'jobpress' ),
                    'menu_order' => esc_html__( 'Menu order', 'jobpress' ),
                    'rand' => esc_html__( 'Random', 'jobpress' ),
                ],
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'order',
            [
                'label' => esc_html__( 'Order', 'jobpress' ),
                'type' => Controls_Manager::SELECT,
                'default' => 'DESC',
                'options' => [
                    'DESC' => esc_html__( 'Descending', 'jobpress' ),
                    'ASC' => esc_html__( 'Ascending', 'jobpress' ),
                ],
                'condition' => [ 'orderby!' => 'rand' ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Add color, typography and bottom spacing controls for a text element.
     *
     * @param string $prefix   Control name prefix.
     * @param string $selector Element selector, relative to SCOPE.
     * @param array  $args     Extra arguments for every control, e.g. a condition.
     */
    protected function add_text_style_controls( $prefix, $selector, $args = array() ) {
        $this->add_control(
            $prefix . '_color',
            array_merge( [
                'label' => esc_html__( 'Color', 'jobpress' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [ self::SCOPE . ' ' . $selector => 'color: {{VALUE}};' ],
            ], $args )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array_merge( [
                'name' => $prefix . '_typography',
                'selector' => self::SCOPE . ' ' . $selector,
            ], $args )
        );

        $this->add_responsive_control(
            $prefix . '_spacing',
            array_merge( [
                'label' => esc_html__( 'Spacing', 'jobpress' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'range' => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
                'selectors' => [ self::SCOPE . ' ' . $selector => 'margin-bottom: {{SIZE}}{{UNIT}};' ],
            ], $args )
        );
    }

    /**
     * Style tab: the six JobPress colors, which every design is built from.
     */
    protected function register_color_style_controls() {
        $this->start_controls_section(
            'colors_style_section',
            [
                'label' => esc_html__( 'Colors', 'jobpress' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'colors_description',
            [
                'type' => Controls_Manager::RAW_HTML,
                'raw' => esc_html__( 'Override the JobPress Appearance colors for this listing. The sections below style individual elements.', 'jobpress' ),
                'content_classes' => 'elementor-descriptor',
            ]
        );

        $labels = array(
            'brand_color'     => esc_html__( 'Brand', 'jobpress' ),
            'hover_color'     => esc_html__( 'Hover', 'jobpress' ),
            'heading_color'   => esc_html__( 'Headings', 'jobpress' ),
            'secondary_color' => esc_html__( 'Secondary text', 'jobpress' ),
            'content_color'   => esc_html__( 'Content text', 'jobpress' ),
            'border_color'    => esc_html__( 'Borders', 'jobpress' ),
        );
        $colors = PublicEnqueue::get_colors();
        foreach ( $labels as $name => $label ) {
            $this->add_control(
                $name,
                [
                    'label' => $label,
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [ '{{WRAPPER}} .jp-listing' => $colors[ $name ][0] . ': {{VALUE}};' ],
                ]
            );
        }

        $this->end_controls_section();
    }

    /**
     * Style tab: header.
     */
    protected function register_header_style_controls() {
        $this->start_controls_section(
            'header_style_section',
            [
                'label' => esc_html__( 'Header', 'jobpress' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'header_align',
            [
                'label' => esc_html__( 'Alignment', 'jobpress' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [ 'title' => esc_html__( 'Left', 'jobpress' ), 'icon' => 'eicon-text-align-left' ],
                    'center' => [ 'title' => esc_html__( 'Center', 'jobpress' ), 'icon' => 'eicon-text-align-center' ],
                    'right' => [ 'title' => esc_html__( 'Right', 'jobpress' ), 'icon' => 'eicon-text-align-right' ],
                ],
                'selectors' => [ self::SCOPE . ' .jp-listing__header' => 'text-align: {{VALUE}};' ],
            ]
        );

        $this->add_responsive_control(
            'header_spacing',
            [
                'label' => esc_html__( 'Space below header', 'jobpress' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'range' => [ 'px' => [ 'min' => 0, 'max' => 150 ] ],
                'selectors' => [ self::SCOPE . ' .jp-listing__header' => 'margin-bottom: {{SIZE}}{{UNIT}};' ],
            ]
        );

        $this->add_control( 'title_style_heading', [ 'label' => esc_html__( 'Title', 'jobpress' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ] );
        $this->add_text_style_controls( 'title', '.jp-listing__title' );

        $this->add_control( 'subtitle_style_heading', [ 'label' => esc_html__( 'Subtitle', 'jobpress' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ] );
        $this->add_text_style_controls( 'subtitle', '.jp-listing__subtitle' );

        $count_condition = [ 'condition' => [ 'design' => $this->get_design_condition( array( 1, 3, 5 ) ) ] ];
        $this->add_control( 'count_style_heading', array_merge( [ 'label' => esc_html__( 'Open positions count', 'jobpress' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ], $count_condition ) );
        $this->add_text_style_controls( 'count', '.jp-listing__count', $count_condition );

        $this->end_controls_section();
    }

    /**
     * Style tab: job cards.
     */
    protected function register_card_style_controls() {
        $this->start_controls_section(
            'card_style_section',
            [
                'label' => esc_html__( 'Job Card', 'jobpress' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $card = self::SCOPE . ' .jp-listing__jobs .jp-listing__card';

        $this->add_responsive_control(
            'card_padding',
            [
                'label' => esc_html__( 'Padding', 'jobpress' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', 'rem', '%' ],
                'selectors' => [ $card => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
            ]
        );

        $this->add_responsive_control(
            'card_gap',
            [
                'label' => esc_html__( 'Space between cards', 'jobpress' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'range' => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
                'selectors' => [
                    self::SCOPE . ' .jobpress-job-lists.jp-listing__jobs' => 'display: flex; flex-direction: column; gap: {{SIZE}}{{UNIT}};',
                    self::SCOPE . ' .jobpress-job-grids .jp-row' => 'gap: {{SIZE}}{{UNIT}}; --jp-grid-gap: {{SIZE}}{{UNIT}};',
                    $card => 'margin-bottom: 0;',
                ],
            ]
        );

        $this->add_control(
            'card_radius',
            [
                'label' => esc_html__( 'Border radius', 'jobpress' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [ $card => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
            ]
        );

        $this->start_controls_tabs( 'card_style_tabs' );

        $this->start_controls_tab( 'card_style_normal', [ 'label' => esc_html__( 'Normal', 'jobpress' ) ] );
        $this->add_control(
            'card_background',
            [
                'label' => esc_html__( 'Background', 'jobpress' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [ $card => 'background-color: {{VALUE}};' ],
            ]
        );
        $this->add_group_control( Group_Control_Border::get_type(), [ 'name' => 'card_border', 'selector' => $card ] );
        $this->add_group_control( Group_Control_Box_Shadow::get_type(), [ 'name' => 'card_shadow', 'selector' => $card ] );
        $this->end_controls_tab();

        $this->start_controls_tab( 'card_style_hover', [ 'label' => esc_html__( 'Hover', 'jobpress' ) ] );
        $this->add_control(
            'card_background_hover',
            [
                'label' => esc_html__( 'Background', 'jobpress' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [ $card . ':hover' => 'background-color: {{VALUE}};' ],
            ]
        );
        $this->add_control(
            'card_border_color_hover',
            [
                'label' => esc_html__( 'Border color', 'jobpress' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [ $card . ':hover' => 'border-color: {{VALUE}};' ],
            ]
        );
        $this->add_group_control( Group_Control_Box_Shadow::get_type(), [ 'name' => 'card_shadow_hover', 'selector' => $card . ':hover' ] );
        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    /**
     * Style tab: job titles.
     */
    protected function register_job_title_style_controls() {
        $this->start_controls_section(
            'job_title_style_section',
            [
                'label' => esc_html__( 'Job Title', 'jobpress' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $title = self::SCOPE . ' .jp-listing__job-title';

        $this->add_control(
            'job_title_color',
            [
                'label' => esc_html__( 'Color', 'jobpress' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [ "$title, $title a" => 'color: {{VALUE}};' ],
            ]
        );

        $this->add_control(
            'job_title_hover_color',
            [
                'label' => esc_html__( 'Hover color', 'jobpress' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    // In the grid design the whole card is the link.
                    "$title a:hover, " . self::SCOPE . ' .jp-listing__card:hover .jp-listing__job-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'job_title_typography',
                'selector' => $title,
            ]
        );

        $this->add_responsive_control(
            'job_title_spacing',
            [
                'label' => esc_html__( 'Spacing', 'jobpress' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'range' => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
                'selectors' => [ $title => 'margin-bottom: {{SIZE}}{{UNIT}};' ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Style tab: job details (category, type, location, vacancies, deadline, experience).
     */
    protected function register_meta_style_controls() {
        $this->start_controls_section(
            'meta_style_section',
            [
                'label' => esc_html__( 'Job Details', 'jobpress' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $meta = array(
            self::SCOPE . ' .jp-listing__meta',
            self::SCOPE . ' .jp-listing__meta span',
            self::SCOPE . ' .jp-listing__experience',
            self::SCOPE . ' .jp-listing__experience span',
        );

        $this->add_control(
            'meta_color',
            [
                'label' => esc_html__( 'Color', 'jobpress' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [ implode( ', ', $meta ) => 'color: {{VALUE}};' ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'meta_typography',
                'selector' => implode( ', ', $meta ),
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Add typography, padding, radius, border and Normal/Hover color controls for a button.
     *
     * @param string $prefix   Control name prefix.
     * @param string $selector Button selector, relative to SCOPE.
     * @param array  $extra    Extra selectors (relative to SCOPE) per state for the
     *                         text color: 'normal' and 'hover', e.g. an icon's fill.
     */
    protected function add_button_style_controls( $prefix, $selector, $extra = array() ) {
        $button = self::SCOPE . ' ' . $selector;

        $this->add_group_control( Group_Control_Typography::get_type(), [ 'name' => $prefix . '_typography', 'selector' => $button ] );

        $this->add_responsive_control(
            $prefix . '_padding',
            [
                'label' => esc_html__( 'Padding', 'jobpress' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors' => [ $button => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
            ]
        );

        $this->add_control(
            $prefix . '_radius',
            [
                'label' => esc_html__( 'Border radius', 'jobpress' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [ $button => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
            ]
        );

        $this->add_group_control( Group_Control_Border::get_type(), [ 'name' => $prefix . '_border', 'selector' => $button ] );

        $this->start_controls_tabs( $prefix . '_tabs' );
        foreach ( array( 'normal' => esc_html__( 'Normal', 'jobpress' ), 'hover' => esc_html__( 'Hover', 'jobpress' ) ) as $state => $label ) {
            $suffix = 'hover' === $state ? '_hover' : '';
            $target = 'hover' === $state ? "$button:hover, $button:focus" : $button;

            $this->start_controls_tab( $prefix . '_tab_' . $state, [ 'label' => $label ] );

            // The designs set the button text color with !important.
            $color_selectors = [ $target => 'color: {{VALUE}} !important;' ];
            if ( ! empty( $extra[ $state ] ) ) {
                $color_selectors[ self::SCOPE . ' ' . $extra[ $state ] ] = 'fill: {{VALUE}};';
            }
            $this->add_control(
                $prefix . '_color' . $suffix,
                [
                    'label' => esc_html__( 'Text color', 'jobpress' ),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => $color_selectors,
                ]
            );
            $this->add_control(
                $prefix . '_background' . $suffix,
                [
                    'label' => esc_html__( 'Background', 'jobpress' ),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [ $target => 'background-color: {{VALUE}};' ],
                ]
            );
            if ( 'hover' === $state ) {
                $this->add_control(
                    $prefix . '_border_color_hover',
                    [
                        'label' => esc_html__( 'Border color', 'jobpress' ),
                        'type' => Controls_Manager::COLOR,
                        'selectors' => [ $target => 'border-color: {{VALUE}};' ],
                    ]
                );
            }

            $this->end_controls_tab();
        }
        $this->end_controls_tabs();
    }

    /**
     * Style tab: the apply button (the arrow link in design v2).
     */
    protected function register_button_style_controls() {
        $this->start_controls_section(
            'button_style_section',
            [
                'label' => esc_html__( 'Apply Button', 'jobpress' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [ 'design' => $this->get_design_condition( array( 1, 2, 3, 4 ) ) ],
            ]
        );

        $this->add_button_style_controls( 'button', '.jp-listing__button', [
            'normal' => '.jp-listing__button svg',
            'hover'  => '.jp-listing__button:hover svg',
        ] );

        $this->end_controls_section();
    }

    /**
     * Style tab: category group headers of the grouped designs.
     */
    protected function register_group_style_controls() {
        $this->start_controls_section(
            'group_style_section',
            [
                'label' => esc_html__( 'Category Group Header', 'jobpress' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [ 'design' => $this->get_design_condition( array( 2, 4 ) ) ],
            ]
        );

        $header = self::SCOPE . ' .jp-listing__group-header';

        $this->add_control(
            'group_background',
            [
                'label' => esc_html__( 'Background', 'jobpress' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [ $header => 'background-color: {{VALUE}};' ],
            ]
        );

        $this->add_responsive_control(
            'group_padding',
            [
                'label' => esc_html__( 'Padding', 'jobpress' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors' => [ $header => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
            ]
        );

        $this->add_control(
            'group_radius',
            [
                'label' => esc_html__( 'Border radius', 'jobpress' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [ $header => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
            ]
        );

        $this->add_responsive_control(
            'group_spacing',
            [
                'label' => esc_html__( 'Space below header', 'jobpress' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'range' => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
                'selectors' => [ $header => 'margin-bottom: {{SIZE}}{{UNIT}};' ],
            ]
        );

        $this->add_control( 'group_title_heading', [ 'label' => esc_html__( 'Category name', 'jobpress' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ] );
        $this->add_control(
            'group_title_color',
            [
                'label' => esc_html__( 'Color', 'jobpress' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [ self::SCOPE . ' .jp-listing__group-title' => 'color: {{VALUE}};' ],
            ]
        );
        $this->add_group_control( Group_Control_Typography::get_type(), [ 'name' => 'group_title_typography', 'selector' => self::SCOPE . ' .jp-listing__group-title' ] );

        $this->add_control(
            'group_description_color',
            [
                'label' => esc_html__( 'Description color', 'jobpress' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [ self::SCOPE . ' .jp-listing__group-description' => 'color: {{VALUE}};' ],
            ]
        );

        $badge = self::SCOPE . ' .jp-listing__group-count';
        $this->add_control( 'group_count_heading', [ 'label' => esc_html__( 'Openings badge', 'jobpress' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ] );
        $this->add_control(
            'group_count_color',
            [
                'label' => esc_html__( 'Color', 'jobpress' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [ $badge => 'color: {{VALUE}};' ],
            ]
        );
        $this->add_control(
            'group_count_background',
            [
                'label' => esc_html__( 'Background', 'jobpress' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [ $badge => 'background-color: {{VALUE}};' ],
            ]
        );
        $this->add_group_control( Group_Control_Typography::get_type(), [ 'name' => 'group_count_typography', 'selector' => $badge ] );

        $this->end_controls_section();
    }

    /**
     * Style tab: search bar.
     */
    protected function register_search_style_controls() {
        $this->start_controls_section(
            'search_style_section',
            [
                'label' => esc_html__( 'Search Bar', 'jobpress' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [ 'show_search' => $this->get_inherited_condition( 'show_search' ) ],
            ]
        );

        $bar = self::SCOPE . ' .jobpress-search-form-wrapper';

        $this->add_control(
            'search_background',
            [
                'label' => esc_html__( 'Background', 'jobpress' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [ $bar => 'background-color: {{VALUE}};' ],
            ]
        );

        $this->add_group_control( Group_Control_Border::get_type(), [ 'name' => 'search_border', 'selector' => $bar ] );

        $this->add_control(
            'search_radius',
            [
                'label' => esc_html__( 'Border radius', 'jobpress' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [ $bar => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
            ]
        );

        $this->add_group_control( Group_Control_Box_Shadow::get_type(), [ 'name' => 'search_shadow', 'selector' => $bar ] );

        $this->add_control(
            'search_text_color',
            [
                'label' => esc_html__( 'Text color', 'jobpress' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    "$bar input, $bar select" => 'color: {{VALUE}};',
                    "$bar input::placeholder" => 'color: {{VALUE}}; opacity: .7;',
                ],
            ]
        );

        $this->add_control(
            'search_divider_color',
            [
                'label' => esc_html__( 'Divider color', 'jobpress' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [ "$bar .search-field" => 'border-color: {{VALUE}};' ],
            ]
        );

        $this->add_control( 'search_button_heading', [ 'label' => esc_html__( 'Button', 'jobpress' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ] );
        $this->add_button_style_controls( 'search_button', '.jobpress-search-form-wrapper .search-submit' );

        $this->end_controls_section();
    }

    /**
     * Style tab: "View all jobs" link.
     */
    protected function register_view_all_style_controls() {
        $this->start_controls_section(
            'view_all_style_section',
            [
                'label' => esc_html__( 'View All Jobs Link', 'jobpress' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [ 'show_view_all' => $this->get_inherited_condition( 'show_view_all' ) ],
            ]
        );

        $this->add_responsive_control(
            'view_all_align',
            [
                'label' => esc_html__( 'Alignment', 'jobpress' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [ 'title' => esc_html__( 'Left', 'jobpress' ), 'icon' => 'eicon-text-align-left' ],
                    'center' => [ 'title' => esc_html__( 'Center', 'jobpress' ), 'icon' => 'eicon-text-align-center' ],
                    'right' => [ 'title' => esc_html__( 'Right', 'jobpress' ), 'icon' => 'eicon-text-align-right' ],
                ],
                'selectors' => [ self::SCOPE . ' .jp-listing__footer' => 'text-align: {{VALUE}};' ],
            ]
        );

        $this->add_responsive_control(
            'view_all_spacing',
            [
                'label' => esc_html__( 'Space above', 'jobpress' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'range' => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
                'selectors' => [ self::SCOPE . ' .jp-listing__footer' => 'margin-top: {{SIZE}}{{UNIT}};' ],
            ]
        );

        $this->add_button_style_controls( 'view_all', '.jp-listing__view-all' );

        $this->end_controls_section();
    }

    /**
     * Terms of a taxonomy as select options, keyed by slug.
     *
     * @param string $taxonomy Taxonomy.
     * @return string[]
     */
    private function get_term_options( $taxonomy ) {
        $terms = get_terms( array(
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
        ) );

        return is_wp_error( $terms ) ? array() : wp_list_pluck( $terms, 'name', 'slug' );
    }

    /**
     * Published jobs as select options, keyed by ID.
     *
     * @return string[]
     */
    private function get_job_options() {
        $jobs = get_posts( array(
            'post_type'      => 'jobpress',
            'post_status'    => 'publish',
            'posts_per_page' => 500,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );

        $options = array();
        foreach ( $jobs as $job ) {
            $options[ (string) $job->ID ] = get_the_title( $job );
        }
        return $options;
    }

    /**
     * Map the widget settings onto shortcode attributes. Empty settings are left
     * out, so the shortcode falls back to the global JobPress settings.
     *
     * @param array $settings Widget settings.
     * @return array
     */
    public static function get_shortcode_atts( $settings ) {
        $atts = array();

        foreach ( self::ATTRIBUTE_CONTROLS as $control ) {
            $value = isset( $settings[ $control ] ) ? $settings[ $control ] : '';
            if ( is_array( $value ) ) {
                $value = implode( ',', $value );
            }
            if ( '' !== trim( (string) $value ) ) {
                $atts[ $control ] = (string) $value;
            }
        }

        foreach ( self::VISIBILITY_CONTROLS as $control => $attribute ) {
            if ( isset( $settings[ $control ] ) && in_array( $settings[ $control ], array( 'yes', 'no' ), true ) ) {
                $atts[ $attribute ] = $settings[ $control ];
            }
        }

        // Widgets saved before 2.2.0 had a show_positions switcher, which saved '' when switched off.
        if ( ! isset( $atts['show_positions'] ) && isset( $settings['show_positions'] ) && '' === $settings['show_positions'] ) {
            $atts['show_positions'] = 'no';
        }

        return $atts;
    }

    /**
     * Render widget output on the frontend.
     */
    protected function render() {
        // Pass the settings to the shortcode handler directly rather than building a
        // shortcode string, where a "]" in the title or subtitle would end the shortcode.
        $atts = self::get_shortcode_atts( $this->get_settings_for_display() );

        echo ( new JobListShortcode() )->jobpress_jobs_shortcode( $atts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the listing template.
    }
}
