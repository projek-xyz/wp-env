<?php

declare(strict_types=1);

namespace IntegrationTests\CustomTheme;

use Custom_Theme\Theme;
use IntegrationTests\BaseTestCase;

/**
 * Base Test Case for Custom Theme integration tests.
 */
abstract class TestCase extends BaseTestCase
{
    protected const PACKAGE_NAME = 'custom-theme';

    protected function preparePackage(string $name, string $path, ?string $url, ?string $version): void
    {
        $this->activatePlugin('blocksy-companion');

        \switch_theme($name);

        if (!class_exists(Theme::class)) {
            require_once $path . '/functions.php';
        }
    }
}
