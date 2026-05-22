<?php

declare(strict_types=1);

namespace UnitTests\BlankOption\Includes;

use Blank_Option\Option;
use Brain\Monkey\Functions;
use PHPUnit\Framework\Attributes\RunClassInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;

/**
 * Unit tests for the blank's `includes/class-option.php`.
 */
#[RunClassInSeparateProcess]
class OptionTest extends TestCase
{
    protected static bool $loadAutoloader = true;

    #[Test]
    public function setShouldRetunsDefaultValueIfNoOptionExists()
    {
        Functions\when('get_option')->justReturn(false);

        Functions\expect('update_option')
            ->once()
            ->andReturnUsing(function (string $name, array $option) {
                $this->assertSame(static::PACKAGE_NAME, $name);
                $this->assertSame(['key' => 'value'], $option);
            });

        Option::set('key', 'value');
    }

    #[Test]
    public function getShouldRetunsDefaultValueIfNoOptionExists()
    {
        $stub = Functions\when('get_option');

        $stub->justReturn(false);

        $actual = Option::get('key_false', $expected = 'default');

        $this->assertSame($expected, $actual);

        $stub->justReturn([]);

        $actual = Option::get('key_empty', $expected = 'default');

        $this->assertSame($expected, $actual);
    }

    #[Test]
    public function getShouldRetunsItsValueIfTheOptionExists()
    {
        $stub = Functions\when('get_option');

        $stub->justReturn(['key' => 'value']);

        $actual = Option::get('key', 'default');

        $this->assertSame('value', $actual);
    }

    #[Test]
    public function getShouldRetunsItsValueFromCacheOnSecondCall()
    {
        Functions\expect('get_option')
            ->once()
            ->andReturn(['other' => 'value']);

        $first = Option::get('other', 'default');
        $second = Option::get('other', 'default');

        $this->assertSame($first, $second);
    }
}
