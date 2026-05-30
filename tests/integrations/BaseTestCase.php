<?php

declare(strict_types=1);

namespace IntegrationTests;

use Fixtures\TestCase;

/**
 * Base Test Case for integration tests using real WordPress core.
 */
abstract class BaseTestCase extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected static function packageAutoload(string $name, ?string $type, ?string $version)
    {
        static::setUpCallback(function ($next) {
            $next();
        });

        return false;
    }
}
