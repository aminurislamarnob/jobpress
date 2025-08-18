<?php
namespace JobPressInc\Base;

/**
 * Elementor Integration Initialization
 */
class ElementorInit {

    /**
     * Register services.
     */
    public function register() {
        // Register Elementor widget category
        add_action( 'elementor/elements/categories_registered', array( $this, 'add_elementor_widget_category' ) );
        
        // Register Elementor widgets
        add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
    }

    /**
     * Add JobPress category to Elementor
     *
     * @param \Elementor\Elements_Manager $elements_manager Elementor elements manager.
     */
    public function add_elementor_widget_category( $elements_manager ) {
        $elements_manager->add_category(
            'jobpress',
            [
                'title' => esc_html__( 'JobPress', 'jobpress' ),
                'icon' => 'fa fa-plug',
            ]
        );
    }

    /**
     * Register Elementor widgets
     *
     * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
     */
    public function register_widgets( $widgets_manager ) {
        // Include widget file
        require_once JOBPRESS_PLUGIN_PATH . 'inc/Base/ElementorWidgets.php';
        
        // Register widget
        $widgets_manager->register( new ElementorWidgets() );
    }
}
