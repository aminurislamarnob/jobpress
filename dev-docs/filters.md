### Example Usage
```php
// Customize open positions text
add_filter('jobpress_open_positions_text', function($text, $count) {
    return sprintf('%d Available Jobs', $count);
}, 10, 2);
```