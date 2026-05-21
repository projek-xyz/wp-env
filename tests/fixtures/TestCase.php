<?php

declare(strict_types=1);

namespace Fixtures;

use Brain\Monkey;
use Closure;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Base test case for all unit tests.
 *
 * Provides a framework for Brain\Monkey-based testing and supports
 * chaining setup and teardown callbacks.
 */
abstract class TestCase extends PHPUnitTestCase
{
    /**
     * @var null|Closure(static):void
     */
    private static ?Closure $setUpCallback = null;

    /**
     * @var null|Closure(static):void
     */
    private static ?Closure $tearDownCallback = null;

    /**
     * Whether the WP_VERSION global was set by this test instance and should be reset.
     */
    private bool $resetWpVersion = false;

    /**
     * Cleans up static properties after the test class has finished running.
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        static::$setUpCallback = null;
        static::$tearDownCallback = null;
    }

    /**
     * Sets up the test environment.
     *
     * Initializes Brain\Monkey, executes registered setup callbacks,
     * and ensures a default WordPress version is set in the globals.
     */
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();

        if ($callback = static::$setUpCallback) {
            $callback($this);
        }

        // Set WP version global if not available
        if (! isset($GLOBALS['wp_version'])) {
            $GLOBALS['wp_version'] = getenv('WP_VERSION') ?: '7.0';

            $this->resetWpVersion = true;
        }
    }

    /**
     * Tears down the test environment.
     *
     * Executes registered teardown callbacks and cleans up Brain\Monkey.
     */
    protected function tearDown(): void
    {
        if ($callback = static::$tearDownCallback) {
            $callback($this);
        }

        if ($this->resetWpVersion) {
            unset($GLOBALS['wp_version']);
        }

        Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Registers a setup callback to be executed during the setUp phase.
     *
     * The callback receives a `$next` closure that must be called to continue the chain.
     *
     * @param Closure(Closure():void):void $callback
     */
    final protected static function setUpCallback(Closure $callback): void
    {
        static::$setUpCallback = self::assignCallbackToCurrentInstance(
            $callback,
            static::$setUpCallback ?? static function () {
                // noop
            },
        );
    }

    /**
     * Registers a teardown callback to be executed during the tearDown phase.
     *
     * The callback receives a `$next` closure that must be called to continue the chain.
     *
     * @param Closure(Closure():void):void $callback
     */
    final protected static function tearDownCallback(Closure $callback): void
    {
        static::$tearDownCallback = self::assignCallbackToCurrentInstance(
            $callback,
            static::$tearDownCallback ?? static function () {
                // noop
            },
        );
    }

    /**
     * Assigns and binds a callback to the current test instance.
     *
     * Returns a closure that wraps the provided callback and ensures the test instance
     * is correctly bound and passed through the callback chain.
     *
     * @param Closure(Closure():void):void $callback The callback to register.
     * @param Closure(static):void $next The previous callback in the chain.
     *
     * @return Closure(static):void
     */
    private static function assignCallbackToCurrentInstance(Closure $callback, Closure $next): Closure
    {
        return static function ($newThis) use ($callback, $next) {
            $nextWithInstance = static function () use ($next, $newThis) {
                $next($newThis);
            };

            if ((new \ReflectionFunction($callback))->isStatic()) {
                $callback($nextWithInstance);
                return;
            }

            if ($boundCallback = $callback->bindTo($newThis)) {
                $boundCallback($nextWithInstance);
            }
        };
    }

    /**
     * Gets the absolute path to a file within the packages directory.
     *
     * @param string $file_path The relative path to the file from the packages directory.
     *
     * @return string The absolute path to the file.
     */
    protected static function packageFile(string $file_path): string
    {
        return BASE_PATH . '/packages/' . $file_path;
    }
}
