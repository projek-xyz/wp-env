<?php

/**
 * PHPUnit Bootstrap
 */

declare(strict_types=1);

defined('BASE_PATH') || define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/vendor/autoload.php';

// Mock WordPress constants if needed
defined('ABSPATH') || define('ABSPATH', BASE_PATH . '/docker/volumes/wordpress/');

// Any other test initialization can go here.

// Bootstrap WordPress for integration tests.
if (! in_array('Integration Tests', $_SERVER['argv'], true)) {
    return;
}

// Path to the WordPress core directory for testing.
if (! defined('WP_CORE_DIR')) {
    define('WP_CORE_DIR', ABSPATH);
}

Dotenv\Dotenv::createImmutable(BASE_PATH, ['.env.testing', '.env'])->safeLoad();

// Find the library path automatically if not provided.
$testDir = getenv('WP_PHPUNIT__DIR') ?: BASE_PATH . '/vendor/wp-phpunit/wp-phpunit';

// Path to the wp-phpunit includes directory.
if (is_dir($testDir)) {
    define('WP_RUN_CORE_TESTS', false);
    define('WP_TESTS_CONFIG_FILE_PATH', __DIR__ . '/integrations/wp-tests-config.php');

    // Load the test functions.
    require_once $testDir . '/includes/functions.php';

    // Load our plugins during the bootstrap process.
    tests_add_filter('muplugins_loaded', function () {
        require_once BASE_PATH . '/packages/blank-option/blank-option.php';
    });

    // Start up the WP testing environment.
    require $testDir . '/includes/bootstrap.php';


    if (! class_exists('WP_UnitTestCase_Base')) {
        require_once $testDir . '/includes/phpunit-adapter-testcase.php';
        require_once $testDir . '/includes/abstract-testcase.php';
    }
}
