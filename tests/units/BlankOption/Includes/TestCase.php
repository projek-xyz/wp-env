<?php

declare(strict_types=1);

namespace UnitTests\BlankOption\Includes;

use UnitTests\BlankOption\TestCase as BaseTestCase;

/**
 * Base Test Case for CF7 Entry Manager unit tests.
 */
abstract class TestCase extends BaseTestCase
{
    protected static function packageAutoload(string $name, ?string $type, ?string $version)
    {
        parent::packageAutoload($name, $type, $version);

        $dir = static::packageFile($name);

        defined('BLANK_VERSION') || define('BLANK_VERSION', $version);
        defined('BLANK_OPTION_DIR') || define('BLANK_OPTION_DIR', $dir);
        defined('BLANK_OPTION_FILE') || define('BLANK_OPTION_FILE', "$dir/$name.php");
    }
}
