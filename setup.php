<?php
// WordPress configuration
define('DB_NAME', 'wordpress');
define('DB_USER', 'wordpress');
define('DB_PASSWORD', 'wordpress');
define('DB_HOST', 'mysql:3306');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');
define('WP_HOME', 'http://localhost:8080');
define('WP_SITEURL', 'http://localhost:8080');

// For development
define('WP_DEBUG', false);
define('WP_DEBUG_DISPLAY', false);

// Load WordPress core
require_once('/var/www/html/wp-load.php');

// Check if already installed
if (wp_cache_get('alloptions') === false) {
    // Initialize WordPress
    wp_install('My Bank Form Site', 'admin', 'admin@example.com', true, '', 'admin123');
    echo "WordPress installed successfully\n";
} else {
    echo "WordPress already installed\n";
}

// Activate the plugin
$plugin = 'bank-form-plugin/wp-plugin.php';
if (function_exists('activate_plugin')) {
    activate_plugin($plugin);
    echo "Plugin activated\n";
}
?>
