<?php
// phpcs:ignorefile

/**
 * Database credentials
 */
define('DB_NAME', getenv('WORDPRESS_DB_NAME') ?: 'wordpress');
define('DB_USER', getenv('WORDPRESS_DB_USER') ?: 'wordpress');
define('DB_PASSWORD', getenv('WORDPRESS_DB_PASSWORD') ?: 'secret');
define('DB_HOST', getenv('WORDPRESS_DB_HOST') ?: '127.0.0.1');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');

/**
 * Test Site Constants
 */
define('WP_TESTS_DOMAIN', parse_url(getenv('SITE_URL') ?: 'http://localhost', PHP_URL_HOST));
define('WP_TESTS_EMAIL', getenv('SITE_ADMIN_EMAIL') ?: 'admin@example.com');
define('WP_TESTS_TITLE', getenv('SITE_TITLE') ?: 'WordPress Local');
define('WP_PHP_BINARY', 'php');

/**
 * Table Prefix & Multisite
 */
$table_prefix = 'wp_';
define('WP_TESTS_MULTISITE', (bool) getenv('MULTISITE_ENABLED'));

/**
 * Authentication Unique Keys and Salts.
 * (These can be anything for testing)
 */
define('AUTH_KEY', 'testing_salt');
define('SECURE_AUTH_KEY', 'testing_salt');
define('LOGGED_IN_KEY', 'testing_salt');
define('NONCE_KEY', 'testing_salt');
define('AUTH_SALT', 'testing_salt');
define('SECURE_AUTH_SALT', 'testing_salt');
define('LOGGED_IN_SALT', 'testing_salt');
define('NONCE_SALT', 'testing_salt');

/**
 * Developer Settings
 */
define('WP_DEBUG', true);
define('WP_DEBUG_DISPLAY', true);
