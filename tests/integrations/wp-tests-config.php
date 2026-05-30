<?php
// phpcs:ignorefile

function get_env_var(string $key, $default = null) {
    return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?? $default;
}

/**
 * Database credentials
 */
define('DB_NAME', get_env_var('DB_NAME', 'wordpress'));
define('DB_USER', get_env_var('DB_USER', 'wordpress'));
define('DB_PASSWORD', get_env_var('DB_PASS', 'secret'));
define('DB_HOST', get_env_var('DB_HOST', '127.0.0.1'));
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');

/**
 * Test Site Constants
 */
define('WP_TESTS_DOMAIN', parse_url(get_env_var('SITE_URL', 'http://localhost'), PHP_URL_HOST));
define('WP_TESTS_EMAIL', get_env_var('SITE_ADMIN_EMAIL', 'admin@example.com'));
define('WP_TESTS_TITLE', get_env_var('SITE_TITLE', 'WordPress Local'));
define('WP_PHP_BINARY', 'php');

/**
 * Table Prefix & Multisite
 */
$table_prefix = 'wp_';
define('WP_TESTS_MULTISITE', (bool) get_env_var('MULTISITE_ENABLED', 0));

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
