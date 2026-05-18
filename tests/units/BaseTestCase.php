<?php

declare(strict_types=1);

namespace UnitTests;

use Brain\Monkey\Functions;
use Fixtures\TestCase;

/**
 * Base Test Case for all unit tests.
 */
abstract class BaseTestCase extends TestCase
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Mock WP functions used in the main file
        Functions\stubTranslationFunctions();
        Functions\stubEscapeFunctions();

        Functions\when('wp_parse_args')->alias(
            fn($a, $b) => array_merge($b, $a)
        );

        Functions\when('get_stylesheet')->justReturn('custom-theme');
        Functions\when('get_stylesheet_directory')->justReturn(
            BASE_PATH . '/packages/custom-theme'
        );

        if (!class_exists(\WP_Error::class)) {
            require_once ABSPATH . 'wp-includes/class-wp-error.php';
        }

        defined('MINUTE_IN_SECONDS') || define('MINUTE_IN_SECONDS', 60);
        defined('HOUR_IN_SECONDS') || define('HOUR_IN_SECONDS', 60 * MINUTE_IN_SECONDS);
        defined('DAY_IN_SECONDS') || define('DAY_IN_SECONDS', 24 * HOUR_IN_SECONDS);
        defined('WEEK_IN_SECONDS') || define('WEEK_IN_SECONDS', 7 * DAY_IN_SECONDS);
        defined('MONTH_IN_SECONDS') || define('MONTH_IN_SECONDS', 30 * DAY_IN_SECONDS);
        defined('YEAR_IN_SECONDS') || define('YEAR_IN_SECONDS', 365 * DAY_IN_SECONDS);
    }
}
