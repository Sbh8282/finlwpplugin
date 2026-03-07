#!/bin/bash

# Wait for database to be ready
echo "Waiting for database to be ready..."
sleep 5

# Run WordPress installation setup
docker exec wp-wordpress bash -c 'cd /var/www/html && php -d error_reporting=0 << "EOF"
<?php
// Suppress these warnings as WordPress config is already defined
if (!\$_SERVER["HTTP_HOST"]) {
    \$_SERVER["HTTP_HOST"] = "localhost:8080";
}

define("WP_CLI_STRICT_ARGS_MODE", false);
require_once("wp-load.php");

// Check if WordPress is installed
\$installed = get_option("siteurl");
if (!\$installed) {
    // Install WordPress
    wp_install(
        "Bank Form Plugin", 
        "admin", 
        "admin@test.com", 
        true, 
        "", 
        "admin123"
    );
    echo "✓ WordPress installed\n";
} else {
    echo "✓ WordPress already installed\n";
}

// Activate plugin
\$plugin_file = "bank-form-plugin/wp-plugin.php";
if (file_exists("wp-content/plugins/bank-form-plugin/wp-plugin.php")) {
    activate_plugin(\$plugin_file);
    echo "✓ Plugin activated\n";
} else {
    echo "✗ Plugin file not found\n";
}
EOF
'

echo ""
echo "✓ Setup complete!"
echo ""
echo "WordPress is running at: http://localhost:8080"
echo "Admin login: admin / admin123"
