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
    private function option(): Option
    {
        return new Option(Plugin::instance());
    }

    #[Test]
    #[Group('negative-value')]
    public function shouldReturnsEmptyWhenNoOptionExistsInDatabase()
    {
        Functions\when('get_option')->justReturn(false);

        $option = $this->option();

        $this->assertTrue($option->is_empty());
    }

    #[Test]
    #[Group('negative-value')]
    public function getShouldRetunsDefaultValueIfNoOptionExists()
    {
        Functions\when('get_option')->justReturn([]);

        $actual = $this->option()->get('key_false', $expected = 'default');

        $this->assertSame($expected, $actual);
    }

    #[Test]
    #[Group('positive-value')]
    public function getShouldRetunsItsValueIfTheOptionExists()
    {
        Functions\when('get_option')->justReturn(['key' => 'value']);

        $actual = $this->option()->get('key', 'default');

        $this->assertSame('value', $actual);
    }

    #[Test]
    #[Group('cached-value')]
    public function getShouldRetunsItsValueFromCacheOnSecondCall()
    {
        Functions\expect('get_option')
            ->once()
            ->andReturn(['other' => 'value']);

        $option = $this->option();

        $first = $option->get('other', 'default');
        $second = $option->get('other', 'default');

        $this->assertSame($first, $second);
    }

    #[Test]
    #[Group('positive-value')]
    public function setShouldCleanItsCacheWhenValueIsChanged()
    {
        $cache = (new ReflectionClass(Option::class))->getProperty('cached');
        $option = $this->option();

        Functions\when('get_option')->justReturn(['key' => 'old_value']);

        $option->get('key');

        $this->assertArrayHasKey('key', $cache->getValue());

        Functions\expect('update_option')
            ->once()
            ->andReturnUsing(function (string $name, array $option) {
                $this->assertSame(static::PACKAGE_NAME, $name);
                $this->assertArrayHasKey('key', $option);
            });

        $option->set('key', 'new_value');

        $this->assertArrayNotHasKey('key', $cache->getValue());
    }
}
