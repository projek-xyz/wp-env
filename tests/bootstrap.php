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

Dotenv\Dotenv::createImmutable(BASE_PATH, ['.env.testing'])->safeLoad();

// Bootstrap WordPress for integration tests.
if (! in_array('Integration Tests', $_SERVER['argv'], true)) {
    return;
}

// Path to the WordPress core directory for testing.
if (! defined('WP_CORE_DIR')) {
    define('WP_CORE_DIR', ABSPATH);
}

// Find the library path automatically if not provided.
$testDir = env('WP_PHPUNIT__DIR', BASE_PATH . '/vendor/wp-phpunit/wp-phpunit');

// Path to the wp-phpunit includes directory.
if (! is_dir($testDir)) {
    return;
}

define('WP_TESTS_CONFIG_FILE_PATH', __DIR__ . '/integrations/wp-tests-config.php');

// Load the test functions.
require_once $testDir . '/includes/functions.php';

$packages = array_reduce(
    glob(BASE_PATH . '/packages/*', GLOB_ONLYDIR),
    static function ($out, string $pkg) {
        $name = basename($pkg);

        $out[$name] = [
            'name' => $name,
            'path' => $pkg,
            'type' => 'library',
        ];

        if (file_exists("$pkg/style.css") && file_exists("$pkg/functions.php")) {
            $out[$name]['type'] = 'theme';
        } elseif (file_exists("$pkg/{$name}.php")) {
            $out[$name]['type'] = 'plugin';
        }

        $out[$name]['file'] = match ($out[$name]['type']) {
            'theme' => "$pkg/functions.php",
            'plugin' => "$pkg/{$name}.php",
            default => null,
        };

        return $out;
    },
    []
);

// Register `packages` directory as `theme_root` directory.
\tests_add_filter('theme_root', fn () => BASE_PATH . '/packages');

// Load our plugins during the bootstrap process.
\tests_add_filter('muplugins_loaded', function () use ($packages) {
    foreach ($packages as $package) {
        if ($package['type'] === 'plugin') {
            require_once $package['file'];
        }
    }

    \register_theme_directory(BASE_PATH . '/packages');
});

// Start up the WP testing environment.
require $testDir . '/includes/bootstrap.php';

if (! class_exists('WP_UnitTestCase_Base')) {
    require_once $testDir . '/includes/phpunit-adapter-testcase.php';
    require_once $testDir . '/includes/abstract-testcase.php';
}
