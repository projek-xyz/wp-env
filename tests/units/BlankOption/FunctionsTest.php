<?php

declare(strict_types=1);

namespace UnitTests\BlankOption;

use Blank_Option\Plugin;
use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PHPUnit\Framework\Attributes\RunClassInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;

/**
 * Unit tests for the blank's `blank-option.php`.
 */
#[RunClassInSeparateProcess]
class FunctionsTest extends TestCase
{
    #[Test]
    public function shouldBeInitializedWhenRequirementsMet()
    {
        Functions\expect('register_activation_hook')->once()->andReturnUsing(function ($_, $callback) {
            $this->assertIsArray($callback);
            $this->assertIsCallable($callback);
        });

        Functions\expect('register_deactivation_hook')->once()->andReturnUsing(function ($_, $callback) {
            $this->assertIsArray($callback);
            $this->assertIsCallable($callback);
        });

        Actions\expectAdded('init')->once()->whenHappen(function ($callback) {
            $this->assertIsArray($callback);

            $this->assertSame(Plugin::class, $callback[0]);
            $this->assertSame('init', $callback[1]);
        });

        require static::package('entrypoint');

        $this->assertTrue(defined('BLANK_VERSION'));
        $this->assertTrue(defined('BLANK_OPTION_DIR'));
        $this->assertTrue(defined('BLANK_OPTION_FILE'));
    }

    #[Test]
    public function shouldNotBeInitializedWhenRequirementsNotMet()
    {
        Functions\expect('register_activation_hook')->never();
        Functions\expect('register_deactivation_hook')->never();

        Actions\expectAdded('init')->never();

        $this->mockStaticMethods(Plugin::class, [
            'check_requirements' => fn ($mock) => $mock->twice(),
            'is_met_requirements' => fn ($mock) => $mock->andReturnFalse(),
        ]);

        require static::package('entrypoint');
    }
}
