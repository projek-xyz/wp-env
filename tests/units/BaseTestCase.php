<?php

declare(strict_types=1);

namespace UnitTests;

use Brain\Monkey\Functions;
use Closure;
use Fixtures\TestCase;
use ReturnTypeWillChange;

/**
 * Base test case for package-specific unit tests.
 *
 * Extends the fixture test case with WordPress-specific mocks,
 * package-aware autoloading, and convenience methods for static mocking.
 */
abstract class BaseTestCase extends TestCase
{
    /**
     * Sets up the test environment for each test.
     *
     * Mocks standard WordPress translation and escape functions, provides a stub
     * for `wp_parse_args`, ensures `WP_Error` is loaded, and defines common time constants.
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

    /**
     * {@inheritdoc}
     */
    protected static function packageAutoload(string $name, ?string $type, ?string $version)
    {
        static::setUpCallback(function ($next) use ($name, $type, $version) {
            $path = static::packageFile($name);
            $dir_url = in_array($type, ['plugin', 'theme'], true)
                ? sprintf('http://example.com/wp-content/%ss/%s', $type, $name)
                : null;

            Functions\when('wp_normalize_path')->alias(
                static fn (string $path) => str_replace('\\', '/', $path)
            );
            Functions\when('admin_url')->alias(
                static fn (string $path = '', string $scheme = 'admin') => "http://example.com/wp-admin/$path"
            );

            if ($type === 'theme') {
                static::assertFileExists(
                    "$path/functions.php",
                    sprintf('Theme functions.php not found: %s', $name)
                );

                Functions\when('get_stylesheet')->justReturn($name);
                Functions\when('get_stylesheet_directory')->justReturn($path);
                Functions\when('get_stylesheet_directory_uri')->justReturn($dir_url);

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

            if ($type === 'plugin') {
                static::assertFileExists(
                    "$path/$name.php",
                    sprintf('Plugin %1$s.php not found: %1$s', $name)
                );

                Functions\when('plugin_dir_path')->justReturn($path);
                Functions\when('plugin_dir_url')->justReturn($dir_url);
                Functions\when('plugin_basename')->alias(
                    static fn (string $file) => str_replace(dirname($path), '', $file)
                );
            }

            $next();
        });

        if (!static::$loadAutoloader) {
            return false;
        }
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
}
