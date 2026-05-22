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
     * Whether to load the package's internal autoloader.
     */
    protected static bool $loadAutoloader = false;

    /**
     * Sets up the class before any tests run.
     *
     * Handles package discovery by reading package.json and composer.json,
     * determines the package type, and triggers the package-specific autoloading logic.
     *
     * @throws \PHPUnit\Framework\ExpectationFailedException If package metadata files are missing.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        if ($name = static::packageName()) {
            $path = static::packageFile($name);

            [$packageJson, $composerJson] = array_map(
                static function ($file) use ($path) {
                    static::assertNotFalse(
                        $metadata = realpath("$path/$file.json"),
                        "Failed to locate $file.json in $path"
                    );

                    return json_decode(file_get_contents($metadata));
                },
                ['package', 'composer']
            );

            $type = $composerJson->type ?? null;

            if ($type && str_contains($type, 'wordpress-')) {
                $type = substr($type, 10);
            }

            $autoload = static::packageAutoload($name, $type, $packageJson->version);

            if ($autoload !== false) {
                require_once $path . '/includes/autoload.php';
            }
        }
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
     * Gets the package name from the subclass PACKAGE_NAME constant.
     *
     * @return string|null The package name or null if not defined.
     */
    private static function packageName(): ?string
    {
        return defined(static::class . '::PACKAGE_NAME') ? static::PACKAGE_NAME : null;
    }

    /**
     * Handles package-specific environment setup and autoloading registration.
     *
     * Mocks common WordPress path and URL functions based on whether the package
     * is a plugin or a theme.
     *
     * @param string $name Package name.
     * @param 'library'|'plugin'|'theme'|null $type Package type.
     * @param string|null $version Package version.
     *
     * @return void|false Returns false if the internal autoloader should not be required.
     */
    protected static function packageAutoload(string $name, ?string $type, ?string $version)
    {
        static::setUpCallback(function ($next) use ($name, $type, $version) {
            $path = static::packageFile($name);

            if ($type === 'theme') {
                static::assertFileExists(
                    "$path/functions.php",
                    sprintf('Theme functions.php not found: %s', $name)
                );

                Functions\when('get_stylesheet')->justReturn($name);
                Functions\when('get_stylesheet_directory')->justReturn($path);
                Functions\when('get_stylesheet_directory_uri')->justReturn(
                    "http://example.com/wp-content/themes/$name"
                );

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
                    sprintf('Plugin %s.php not found: %s', $name, $name)
                );

                Functions\when('plugin_dir_path')->justReturn($path);
                Functions\when('plugin_dir_url')->justReturn(
                    "http://example.com/wp-content/plugins/$name"
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
