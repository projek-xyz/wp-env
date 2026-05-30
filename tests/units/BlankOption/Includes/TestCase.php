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

        $commonKses = [
            'align'  => true,
            'popover' => true,
            'aria-controls' => true,
            'aria-current' => true,
            'aria-describedby' => true,
            'aria-details' => true,
            'aria-expanded' => true,
            'aria-hidden' => true,
            'aria-label' => true,
            'aria-labelledby' => true,
            'aria-live' => true,
            'class' => true,
            'data-*' => true,
            'dir' => true,
            'hidden' => true,
            'id' => true,
            'lang' => true,
            'style' => true,
            'title' => true,
            'role' => true,
            'xml:lang' => true,
        ];

        Functions\when('wp_kses_allowed_html')->alias(
            static function (string $context) use ($commonKses) {
                if ($context !== 'post') {
                    return [];
                }

                return [
                    'a' => $commonKses,
                    'div' => $commonKses,
                    'h1' => $commonKses,
                    'h4' => $commonKses,
                    'img' => $commonKses,
                    'p' => $commonKses,
                    'pre' => $commonKses,
                    'span' => $commonKses,
                ];
            }
        );
    }
}
