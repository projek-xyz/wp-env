<?php

declare(strict_types=1);

namespace UnitTests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Closure;
use Fixtures\PackageTestHelper;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Base test case for package-specific unit tests.
 *
 * Extends the fixture test case with WordPress-specific mocks,
 * package-aware autoloading, and convenience methods for static mocking.
 */
abstract class BaseTestCase extends PHPUnitTestCase
{
    use PackageTestHelper;

    /**
     * Whether the WP_VERSION global was set by this test instance and should be reset.
     */
    private bool $resetWpVersion = false;

    /**
     * Sets up the class before any tests run.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        defined('MINUTE_IN_SECONDS') || define('MINUTE_IN_SECONDS', 60);
        defined('HOUR_IN_SECONDS') || define('HOUR_IN_SECONDS', 60 * MINUTE_IN_SECONDS);
        defined('DAY_IN_SECONDS') || define('DAY_IN_SECONDS', 24 * HOUR_IN_SECONDS);
        defined('WEEK_IN_SECONDS') || define('WEEK_IN_SECONDS', 7 * DAY_IN_SECONDS);
        defined('MONTH_IN_SECONDS') || define('MONTH_IN_SECONDS', 30 * DAY_IN_SECONDS);
        defined('YEAR_IN_SECONDS') || define('YEAR_IN_SECONDS', 365 * DAY_IN_SECONDS);

        static::setUpBeforePackage();
    }

    /**
     * Sets up the test environment for each test.
     *
     * Mocks standard WordPress translation and escape functions, provides a stub
     * for `wp_parse_args`, ensures `WP_Error` is loaded, and defines common time constants.
     */
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();

        // Set WP version global if not available
        if (! isset($GLOBALS['wp_version'])) {
            $GLOBALS['wp_version'] = env('WP_VERSION', '7.0');

            $this->resetWpVersion = true;
        }

        if (!class_exists(\WP_Error::class)) {
            require_once ABSPATH . 'wp-includes/class-wp-error.php';
        }

        // Mock WP functions used in the main file
        Functions\stubTranslationFunctions();
        Functions\stubEscapeFunctions();

        Functions\when('wp_normalize_path')->alias(
            static fn (string $path) => str_replace('\\', '/', $path)
        );

        Functions\when('admin_url')->alias(
            static fn (string $path = '', string $scheme = 'admin') => "http://example.com/wp-admin/$path"
        );

        Functions\when('wp_parse_args')->alias(
            fn($a, $b) => array_merge($b, $a)
        );

        if ($name = static::packageName()) {
            $pkg = static::package();

            if ('theme' === $pkg['type']) {
                $this->prepareTheme($name, $pkg['path'], $pkg['url'], $pkg['version']);
            }

            if ('plugin' === $pkg['type']) {
                $this->preparePlugin($name, $pkg['path'], $pkg['url'], $pkg['version']);
            }
        }
    }

    /**
     * Tears down the test environment.
     *
     * Executes registered teardown callbacks and cleans up Brain\Monkey.
     */
    protected function tearDown(): void
    {
        if ($this->resetWpVersion) {
            unset($GLOBALS['wp_version']);
        }

        Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Creates a mock for one or more static methods using Mockery aliases.
     *
     * @template M of object
     * @template E of \Mockery\ExpectationInterface
     *
     * @param class-string<M> $className The class name to mock.
     * @param array<string, Closure(E):E> $methods Map of method names to callbacks for setting expectations.
     *
     * @return \Mockery\MockInterface&M
     */
    protected function mockStaticMethods(string $className, array $methods)
    {
        $mock = mock('alias:' . $className);

        foreach ($methods as $method => $callback) {
            $callback($mock->shouldReceive($method));
        }

        return $mock;
    }

    /**
     * Handles theme-specific environment setup.
     *
     * @param string $name Theme name.
     * @param string $path Theme path.
     * @param string|null $url Theme url.
     * @param string|null $version Theme version.
     */
    private function prepareTheme(
        string $name,
        string $path,
        ?string $url,
        ?string $version
    ): void {
        $this->preparePackage($name, $path, $url, $version);

        static::assertFileExists(
            "$path/functions.php",
            sprintf('Theme functions.php not found: %s', $name)
        );

        Functions\when('get_stylesheet')->justReturn($name);
        Functions\when('get_stylesheet_directory')->justReturn($path);
        Functions\when('get_stylesheet_directory_uri')->justReturn($url);

        Functions\when('wp_get_theme')->justReturn(new class ($name, $version) {
            public function __construct(
                public string $stylesheet,
                public string $version
            ) {
                // .
            }

            // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
            public function get_stylesheet_directory_uri()
            {
                return \get_stylesheet_directory_uri();
            }
        });
    }

    /**
     * Handles plugin-specific environment setup.
     *
     * @param string $name Plugin name.
     * @param string $path Plugin path.
     * @param string|null $url Plugin url.
     * @param string|null $version Plugin version.
     */
    private function preparePlugin(
        string $name,
        string $path,
        ?string $url,
        ?string $version
    ): void {
        $this->preparePackage($name, $path, $url, $version);

        static::assertFileExists(
            "$path/$name.php",
            sprintf('Plugin %1$s.php not found: %1$s', $name)
        );

        Functions\when('plugin_dir_path')->justReturn($path);
        Functions\when('plugin_dir_url')->justReturn($url);
        Functions\when('plugin_basename')->alias(
            static fn (string $file) => str_replace(dirname($path), '', $file)
        );
    }
}
