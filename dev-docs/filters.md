# JobPress hooks

## Filters

### `jobpress_open_positions_text`
Filters the "N open positions" text in the listing header. Receives the text and the number of jobs matching the listing.

```php
// Customize open positions text
add_filter('jobpress_open_positions_text', function($text, $count) {
    return sprintf('%d Available Jobs', $count);
}, 10, 2);
```

### `jobpress_listing_category_groups`
Filters the groups rendered by the category-grouped listing designs (v2 and v4). Each group is an array with `name`, `description`, `slug` (category groups only) and `tax_query`. By default there is one group per non-empty job category, followed by an "Other openings" group for jobs without a category.

```php
// Hide the "Other openings" group
add_filter( 'jobpress_listing_category_groups', function( $groups ) {
    array_pop( $groups );
    return $groups;
} );
```

A listing with a `category` attribute only shows the groups of those categories (groups without a `slug` are kept, and show only if their query finds matching jobs).

### `jobpress_listing_defaults`
Filters the default attributes of every job listing (the `[jobpress]` shortcode and the JobPress Elementor widget): the global Listing Defaults settings merged over the built-in defaults, before a listing's own attributes apply. See [shortcode-attributes.md](shortcode-attributes.md) for the keys.

```php
// Show the search bar on every listing unless it sets show_search="no"
add_filter( 'jobpress_listing_defaults', function( $defaults ) {
    $defaults['show_search'] = 'yes';
    return $defaults;
} );
```

### `shortcode_atts_jobpress`
WordPress's standard `shortcode_atts_{$shortcode}` filter, applied to a listing's attributes (from the shortcode or the Elementor widget) merged with the defaults. Empty values are replaced with the defaults after this filter runs.

```php
// Never list more than 20 jobs
add_filter( 'shortcode_atts_jobpress', function( $out ) {
    if ( (int) $out['per_page'] <= 0 || (int) $out['per_page'] > 20 ) {
        $out['per_page'] = 20;
    }
    return $out;
} );
```

### `jobpress_listing_query_args`
Filters the `WP_Query` arguments of a job listing, including each category group's query in the grouped designs. Receives the arguments and the listing's resolved attributes.

```php
// Hide jobs whose application deadline has passed
add_filter( 'jobpress_listing_query_args', function( $args, $atts ) {
    $args['meta_query'] = array(
        'relation' => 'OR',
        array( 'key' => 'jobpress_apply_deadline', 'compare' => 'NOT EXISTS' ),
        array( 'key' => 'jobpress_apply_deadline', 'value' => '', 'compare' => '=' ),
        array( 'key' => 'jobpress_apply_deadline', 'value' => current_time( 'Y-m-d' ), 'compare' => '>=', 'type' => 'DATE' ),
    );
    return $args;
}, 10, 2 );
```

## Actions

### `jobpress_elementor_widget_controls`
Fires after the JobPress Elementor widget registered its controls, with the widget instance, so add-ons can add controls. Widget settings that aren't listing attributes are not passed to the shortcode; use them in your own `elementor/widget/render_content` filter or style them with `selectors`.

```php
add_action( 'jobpress_elementor_widget_controls', function( $widget ) {
    $widget->start_controls_section( 'my_section', array(
        'label' => 'My options',
        'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
    ) );
    $widget->add_control( 'my_card_outline', array(
        'label'     => 'Card outline',
        'type'      => \Elementor\Controls_Manager::COLOR,
        'selectors' => array( '{{WRAPPER}} .jp-listing__card' => 'outline: 2px solid {{VALUE}};' ),
    ) );
    $widget->end_controls_section();
} );
```
