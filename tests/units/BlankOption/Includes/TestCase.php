<?php

declare(strict_types=1);

namespace UnitTests\BlankOption\Includes;

use Brain\Monkey\Functions;
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

        Functions\when('get_plugin_data')->alias(
            static function (string $file) use ($version) {
                static::assertSame(BLANK_OPTION_FILE, $file);

                $data = [
                    'Name' => 'Blank Option',
                    'PluginURI' => 'https://example.com/blank-option',
                    'Version' => $version,
                    'Description' => 'Something awesome is about to come.',
                    'Author' => 'Fery Wardiyanto',
                    'AuthorURI' => 'https://feryardiant.id',
                    'TextDomain' => 'blank-option',
                    'DomainPath' => '/languages',
                    'Network' => false,
                    'RequiresWP' => '6.0',
                    'RequiresPHP' => '8.2',
                    'UpdateURI' => '',
                    'RequiresPlugins' => '',
                ];

                $data['Title'] = $data['Name'];
                $data['AuthorName'] = $data['Author'];

                return $data;
            }
        );
    }
}
