### Example Usage
```php
// Customize open positions text
add_filter('jobpress_open_positions_text', function($text, $count) {
    return sprintf('%d Available Jobs', $count);
}, 10, 2);
```

### `jobpress_listing_category_groups`
Filters the groups rendered by the category-grouped listing designs (v2 and v4). Each group is an array with `name`, `description` and `tax_query`. By default there is one group per non-empty job category, followed by an "Other openings" group for jobs without a category.

```php
// Hide the "Other openings" group
add_filter( 'jobpress_listing_category_groups', function( $groups ) {
    array_pop( $groups );
    return $groups;
} );
```
