View
====

## Installation

```
composer require wplibs/view
```

## Usage

```php
<?php

use WPLibs\View\Factory;

$view_factory = Factory::create([
    'paths' => [
        get_stylesheet_directory() . '/custom-templates',
        get_template_directory() . '/custom-templates',
        '/path-to-plugin-dir/templates',
    ]
]);

echo $view_factory->make('welcome.php', ['data' => 'value']);
```
