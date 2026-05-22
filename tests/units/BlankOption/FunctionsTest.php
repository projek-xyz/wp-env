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
    public function shouldDefinePluginConstants()
    {
        Functions\expect('register_activation_hook')
            ->once()
            ->andReturnUsing(function ($_, $callback) {
                $this->assertIsArray($callback);
                $this->assertIsCallable($callback);
            });

        Functions\expect('register_deactivation_hook')
            ->once()
            ->andReturnUsing(function ($_, $callback) {
                $this->assertIsArray($callback);
                $this->assertIsCallable($callback);
            });

        $this->mockStaticMethods(Plugin::class, [
            'is_unmet_php_requirements' => fn ($method) => $method->withNoArgs()->once()->andReturnFalse(),
            'is_unmet_wp_requirements' => fn ($method) => $method->withNoArgs()->once()->andReturnFalse(),
        ]);

        require $this->packageFile(static::PACKAGE_NAME . '/' . static::PACKAGE_NAME . '.php');

        $this->assertTrue(defined('BLANK_VERSION'));
        $this->assertTrue(defined('BLANK_OPTION_DIR'));
        $this->assertTrue(defined('BLANK_OPTION_FILE'));
    }

    #[Test]
    public function shouldTriggersInitAction()
    {
        Functions\expect('register_activation_hook')->once();
        Functions\expect('register_deactivation_hook')->once();

        Actions\expectAdded('init')->once()->whenHappen(function ($callback) {
            $this->assertIsArray($callback);

            $this->assertSame(Plugin::class, $callback[0]);
            $this->assertSame('init', $callback[1]);
        });

        require $this->packageFile(static::PACKAGE_NAME . '/' . static::PACKAGE_NAME . '.php');
    }

    #[Test]
    public function shouldAddAdminNoticeWhenPhpVerionUnmet()
    {
        $this->mockStaticMethods(Plugin::class, [
            'is_unmet_php_requirements' => fn ($called) => $called->once()->withNoArgs()->andReturnTrue(),
            'is_unmet_wp_requirements' => fn ($called) => $called->never()->withNoArgs(),
        ]);

        Functions\expect('register_activation_hook')->never();
        Functions\expect('register_deactivation_hook')->never();

        Actions\expectAdded('admin_notices')->once()->whenHappen(function ($callback) {
            $this->assertIsCallable($callback);
        });

        require $this->packageFile(static::PACKAGE_NAME . '/' . static::PACKAGE_NAME . '.php');
    }

    #[Test]
    public function shouldAddAdminNoticeWhenWordpressVerionUnmet()
    {
        $this->mockStaticMethods(Plugin::class, [
            'is_unmet_php_requirements' => fn ($method) => $method->withNoArgs()->once()->andReturnFalse(),
            'is_unmet_wp_requirements' => fn ($method) => $method->withNoArgs()->once()->andReturnTrue(),
        ]);

        Functions\expect('register_activation_hook')->never();
        Functions\expect('register_deactivation_hook')->never();

        Actions\expectAdded('admin_notices')->once()->whenHappen(function ($callback) {
            $this->assertIsCallable($callback);
        });

        require $this->packageFile(static::PACKAGE_NAME . '/' . static::PACKAGE_NAME . '.php');
    }
}
