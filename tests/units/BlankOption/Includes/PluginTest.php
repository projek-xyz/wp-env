<?php

declare(strict_types=1);

namespace UnitTests\BlankOption\Includes;

use Blank_Option\Admin;
use Blank_Option\Plugin;
use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use Closure;
use PHPUnit\Framework\Attributes\RunClassInSeparateProcess;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use WP_Filesystem_Direct;

/**
 * Unit tests for the blank's `includes/class-plugin.php`.
 */
#[RunClassInSeparateProcess]
class PluginTest extends TestCase
{
    protected static bool $loadAutoloader = true;

    #[Test]
    public function shouldDidNothingWhenRequirementsMet()
    {
        $status = Plugin::is_met_requirements();

        Actions\expectAdded('admin_notices')->never();

        Plugin::check_requirements('Requirement', '0.0.1', '0.0.1');

        $this->assertSame($status, Plugin::is_met_requirements());
    }

    #[Test]
    public function shouldAddAdminNoticeWhenRequirementsNotMet()
    {
        Actions\expectAdded('admin_notices')->once()->whenHappen(function ($callback) {
            $this->assertInstanceOf(Closure::class, $callback);
        });

        Plugin::check_requirements('Requirement', '0.0.0', '0.0.1');
    }

    #[Test]
    public function shouldNotShowRequirementNoticeOnUndesiredScreens()
    {
        Functions\expect('get_current_screen')->once()->andReturnNull();
        Functions\expect('wp_kses')->never();

        Actions\expectAdded('admin_notices')->once()->whenHappen(function ($callback) {
            $callback();
        });

        Plugin::check_requirements('Requirement', '0.0.0', '0.0.1');
    }

    #[Test]
    public function shouldPrintNoticeOnSpecificScreens()
    {
        $this->expectOutputString(implode('', [
            '<div class="notice notice-error is-dismissible"><p>',
            'The <strong>Blank Option</strong> plugin requires at least version <strong>0.0.1</strong>',
            ' of <strong>Requirement</strong>, currently You have <strong>0.0.0</strong>.',
            '</p></div>',
        ]));

        Functions\expect('get_current_screen')->once()->andReturn((object) ['id' => 'plugins']);
        Functions\expect('wp_kses')->andReturnFirstArg();

        Actions\expectAdded('admin_notices')->once()->whenHappen(function ($callback) {
            $callback();
        });

        Plugin::check_requirements('Requirement', '0.0.0', '0.0.1');
    }

    #[Test]
    public function shouldBeInitialized()
    {
        Actions\expectAdded('wp_enqueue_scripts')->once()->whenHappen(function ($callback) {
            $this->assertIsArray($callback);

            $this->assertInstanceOf(Plugin::class, $callback[0]);
            $this->assertSame('enqueue_scripts', $callback[1]);
        });

        Actions\expectAdded('admin_enqueue_scripts')->once()->whenHappen(function ($callback) {
            $this->assertIsArray($callback);

            $this->assertInstanceOf(Admin\Blank_Page::class, $callback[0]);
            $this->assertSame('enqueue_scripts', $callback[1]);
        });

        Actions\expectAdded('admin_menu')->once()->whenHappen(function ($callback) {
            $this->assertIsArray($callback);

            $this->assertInstanceOf(Admin\Blank_Page::class, $callback[0]);
            $this->assertSame('menu', $callback[1]);
        });

        Plugin::init();
    }

    #[Test]
    public function shouldDoingActionsOnActivation()
    {
        Functions\when('get_option')->justReturn(['version' => '0.0.1']);

        Actions\expectDone('blank_option_activate')->once();
        Actions\expectAdded('blank_option_upgrade')->never();

        Plugin::activate();
    }

    #[Test]
    public function shouldDoingActionsOnDeactivation()
    {
        Actions\doing('blank_option_deactivate');

        Plugin::deactivate();
    }

    #[Test]
    public function shouldEnqueueScripts()
    {
        Functions\expect('wp_enqueue_style')->once()->andReturnUsing(
            function (string $handle, string $src, array $deps, string $version) {
                $this->assertSame('blank-option-style', $handle);
                $this->assertSame(BLANK_VERSION, $version);
                $this->assertEmpty($deps);
                $this->assertSame(
                    'http://example.com/wp-content/plugins/blank-option/assets/blank.css',
                    $src,
                );
            }
        );

        Functions\expect('wp_enqueue_script')->once()->andReturnUsing(
            function (string $handle, string $src, array $deps, string $version) {
                $this->assertSame('blank-option-script', $handle);
                $this->assertSame(BLANK_VERSION, $version);
                $this->assertEmpty($deps);
                $this->assertSame(
                    'http://example.com/wp-content/plugins/blank-option/assets/blank.js',
                    $src,
                );
            }
        );

        Plugin::instance()->enqueue_scripts();
    }

    #[Test]
    #[RunInSeparateProcess]
    public function shouldDoingNothingWhenPluginHaveNotBeenInstalled()
    {
        Functions\when('get_option')->justReturn(false);

        Actions\expectDone('blank_plugin_upgrade')->never();

        Plugin::instance()->upgrade();
    }

    #[Test]
    #[RunInSeparateProcess]
    public function shouldDoingActionsWhenHaveUpdates()
    {
        Functions\when('get_option')->justReturn(['version' => '0.0.0']);

        Actions\doing('blank_plugin_upgrade');

        Plugin::instance()->upgrade();
    }

    #[Test]
    public function shouldAbleToRetrieveAnAssetMetadataArrayFromCache()
    {
        $plugin = Plugin::instance();

        // First call
        $plugin->get_asset_url('blank.css');

        Functions\expect('plugin_dir_url')->never();

        // Second call
        $asset = $plugin->get_asset_url('blank.css');

        $this->assertIsArray($asset);
        $this->assertSame(static::packageFile('blank-option/assets/blank.css'), $asset['dir']);
        $this->assertSame('http://example.com/wp-content/plugins/blank-option/assets/blank.css', $asset['url']);
        $this->assertSame(BLANK_VERSION, $asset['version']);
    }

    #[Test]
    public function shouldAbleToRetrieveAnAssetMetadataBasedOnKey()
    {
        $asset_version = Plugin::instance()->get_asset_url('blank.css', 'version');

        $this->assertSame(BLANK_VERSION, $asset_version);
    }

    #[Test]
    #[RunInSeparateProcess]
    public function shouldAppendAssetFileTimeOnDebugMode()
    {
        $plugin = Plugin::instance();

        defined('SCRIPT_DEBUG') || define('SCRIPT_DEBUG', true);

        $asset_version = $plugin->get_asset_url('blank.css', 'version');
        $filetime = (string) filemtime($plugin->directory_path('assets/blank.css'));

        $this->assertSame(BLANK_VERSION . '-' . $filetime, $asset_version);
    }

    #[Test]
    public function shouldThrowInvalidArgumentExceptionForInvalidKey()
    {
        Functions\expect('wp_kses')->once()->andReturnFirstArg();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid key: invalid_key, expected 'dir', 'url' or 'version'");

        Plugin::instance()->get_asset_url('blank.css', 'invalid_key');
    }

    #[Test]
    public function shouldAbleToChangeAssetDirAndThrowExceptionIfNotExists()
    {
        $plugin = Plugin::instance();

        $plugin->set_asset_dir('elsewhere');

        Functions\expect('wp_kses')->once()->andReturnFirstArg();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Path not found: elsewhere/blank.css");

        $plugin->get_asset_url('blank.css');
    }

    #[Test]
    #[RunInSeparateProcess]
    public function shouldCacheFilesystemInstance()
    {
        $plugin = Plugin::instance();
        $filesystem = (new ReflectionClass(Plugin::class))->getProperty('filesystem');

        $this->assertInstanceOf(WP_Filesystem_Direct::class, $filesystem->getValue($plugin));

        Plugin::instance()->get_file_contents('assets/blank.css');
    }
}
