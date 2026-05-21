<?php

declare(strict_types=1);

namespace UnitTests;

use Brain\Monkey\Functions;
use Closure;
use Fixtures\TestCase;

/**
 * Base Test Case for all unit tests.
 */
abstract class BaseTestCase extends TestCase
{
    /**
     * @var null|Closure($this):void
     */
    private static ?Closure $setUpCallback = null;

    protected static bool $loadAutoloader = false;

    protected static function packageName(): ?string
    {
        return defined(static::class . '::PACKAGE_NAME') ? static::PACKAGE_NAME : null;
    }

    /**
     * @template T of Closure():void
     * @param Closure(T):void $callback
     * @return void
     */
    final protected static function setUpCallback(Closure $callback): void
    {
        $next = static::$setUpCallback ?? function () {
            // noop
        };

        static::$setUpCallback = function ($newThis) use ($callback, $next) {
            if (!(new \ReflectionFunction($callback))->isStatic()) {
                $callback->bindTo($newThis);
            }

            $callback($next);
        };
    }

    /**
     * @param string $name
     * @param 'library'|'plugin'|'theme'|null $type
     * @param string|null $version
     * @return null|false
     */
    protected static function packageAutoload(string $name, ?string $type, ?string $version): ?false
    {
        static::setUpCallback(function ($next) use ($name, $type) {
            if ($type === 'theme') {
                Functions\when('get_stylesheet')->justReturn($name);
                Functions\when('get_stylesheet_directory')->justReturn(
                    static::packageFile($name)
                );
            }

            $next();
        });

        return static::$loadAutoloader ? null : false;
    }

    /**
     * Setup before any test in this class runs.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        if ($name = static::packageName()) {
            $dir = static::packageFile($name);

            [$packageJson, $composerJson] = array_map(
                static function ($file) use ($dir, $name) {
                    if (!($path = realpath($dir . $file))) {
                        throw new \RuntimeException("Could not find $file for $name package");
                    }

                    return json_decode(file_get_contents($path));
                },
                ['/package.json', '/composer.json']
            );

            $type = $composerJson->type ?? null;

            if ($type && str_contains($type, 'wordpress-')) {
                $type = substr($type, 10);
            }

            $autoload = static::packageAutoload($name, $type, $packageJson->version);

            if ($autoload !== false) {
                require_once $dir . '/includes/autoload.php';
            }
        }
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        static::$setUpCallback = null;
    }

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

        if ($callback = static::$setUpCallback) {
            $callback($this);
        }

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
