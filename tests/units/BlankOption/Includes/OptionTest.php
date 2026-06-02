<?php

declare(strict_types=1);

namespace UnitTests\BlankOption\Includes;

use Blank_Option\Option;
use Blank_Option\Plugin;
use Brain\Monkey\Functions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;

/**
 * Unit tests for the blank's `includes/class-option.php`.
 */
#[Group('option')]
class OptionTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        new Option(Plugin::instance());
    }

    #[Test]
    #[Group('negative-value')]
    public function getShouldRetunsDefaultValueIfNoOptionExists()
    {
        Functions\when('get_option')->justReturn([]);

        $actual = Option::get('key_false', $expected = 'default');

        $this->assertSame($expected, $actual);

        $actual = Option::get('key_empty', $expected = 'default');

        $this->assertSame($expected, $actual);
    }

    #[Test]
    #[Group('positive-value')]
    public function getShouldRetunsItsValueIfTheOptionExists()
    {
        Functions\when('get_option')->justReturn(['key' => 'value']);

        $actual = Option::get('key', 'default');

        $this->assertSame('value', $actual);
    }

    #[Test]
    #[Group('cached-value')]
    public function getShouldRetunsItsValueFromCacheOnSecondCall()
    {
        Functions\expect('get_option')
            ->once()
            ->andReturn(['other' => 'value']);

        $first = Option::get('other', 'default');
        $second = Option::get('other', 'default');

        $this->assertSame($first, $second);
    }

    #[Test]
    #[Group('positive-value')]
    public function setShouldCleanItsCacheWhenValueIsChanged()
    {
        $cache = (new ReflectionClass(Option::class))->getProperty('cached');

        Functions\when('get_option')->justReturn(['key' => 'old_value']);

        Option::get('key');

        $this->assertArrayHasKey('key', $cache->getValue());

        Functions\expect('update_option')
            ->once()
            ->andReturnUsing(function (string $name, array $option) {
                $this->assertSame(static::PACKAGE_NAME, $name);
                $this->assertArrayHasKey('key', $option);
            });

        Option::set('key', 'new_value');

        $this->assertArrayNotHasKey('key', $cache->getValue());
    }
}
