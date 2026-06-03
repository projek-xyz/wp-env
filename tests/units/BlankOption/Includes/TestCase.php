<?php

declare(strict_types=1);

namespace UnitTests\BlankOption\Includes;

use Brain\Monkey\Functions;
use Override;
use UnitTests\BlankOption\TestCase as BaseTestCase;

/**
 * Base Unit Test Case for Blank Option plugin.
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * {@inheritdoc}
     */
    public static function setUpBeforePackage(): void
    {
        parent::setUpBeforePackage();

        defined('BLANK_VERSION') || define('BLANK_VERSION', static::package('version'));
        defined('BLANK_OPTION_DIR') || define('BLANK_OPTION_DIR', static::package('path'));
        defined('BLANK_OPTION_FILE') || define('BLANK_OPTION_FILE', static::package('entrypoint'));
    }

    #[Override]
    protected function preparePackage(string $name, string $path, ?string $url, ?string $version): void
    {
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

        require_once "$path/includes/autoload.php";
    }
}
