<?php

declare(strict_types=1);

namespace UnitTests\BlankOption;

use Override;
use UnitTests\BaseTestCase;

/**
 * Base Unit Test Case for Blank Option plugin.
 */
abstract class TestCase extends BaseTestCase
{
    protected const PACKAGE_NAME = 'blank-option';

    #[Override]
    protected function packageMetadata(): array
    {
        return [
            'Name' => 'Blank Option',
            'PluginURI' => 'https://example.com/blank-option',
            'Description' => 'Something awesome is about to come.',
            'Network' => false,
            'UpdateURI' => 'https://projek-xyz.github.io/wp-env',
            'RequiresPlugins' => '',
        ];
    }
}
