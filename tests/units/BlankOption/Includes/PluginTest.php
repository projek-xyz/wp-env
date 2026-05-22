<?php

declare(strict_types=1);

namespace UnitTests\BlankOption\Includes;

use Blank_Option\Admin;
use Blank_Option\Plugin;
use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
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
    public function shouldBeInitialized()
    {
        Actions\expectAdded('enqueue_scripts')->once()->whenHappen(function ($callback) {
            $this->assertIsArray($callback);

            $this->assertSame(Plugin::class, $callback[0]);
            $this->assertSame('enqueue_scripts', $callback[1]);
        });

        Actions\expectAdded('admin_enqueue_scripts')->once()->whenHappen(function ($callback) {
            $this->assertIsArray($callback);

            $this->assertSame(Admin::class, $callback[0]);
            $this->assertSame('enqueue_scripts', $callback[1]);
        });

        Actions\expectAdded('admin_menu')->once()->whenHappen(function ($callback) {
            $this->assertIsArray($callback);

            $this->assertSame(Admin::class, $callback[0]);
            $this->assertSame('menu', $callback[1]);
        });

        Plugin::init();
    }

    #[Test]
    public function shouldEnqueueScripts()
    {
        Functions\expect('wp_enqueue_style')->once()->andReturnUsing(
            function (string $handle, string $src, array $deps, string $version) {
                $this->assertSame('blank-option-style', $handle);
                $this->assertSame(Plugin::VERSION, $version);
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
                $this->assertSame(Plugin::VERSION, $version);
                $this->assertEmpty($deps);
                $this->assertSame(
                    'http://example.com/wp-content/plugins/blank-option/assets/blank.js',
                    $src,
                );
            }
        );

        Plugin::enqueue_scripts();
    }

    #[Test]
    public function shouldDoingActionsOnActivation()
    {
        Actions\doing('blank_plugin_activate');

        Actions\expectAdded('blank_plugin_upgrade')->never();

        Functions\when('get_option')->justReturn(['version' => '0.0.1']);

        Plugin::activate();
    }

    #[Test]
    public function shouldDoingActionsOnDeactivation()
    {
        Actions\doing('blank_plugin_deactivate');

        Functions\expect('wp_cache_flush')->once();

        Plugin::deactivate();
    }

    #[Test]
    #[RunInSeparateProcess]
    public function shouldDoingActionsWhenHaveUpdates()
    {
        Functions\when('get_option')->justReturn(['version' => '0.0.0']);

        Actions\doing('blank_plugin_upgrade');

        Plugin::upgrade();
    }

    #[Test]
    public function shouldAbleToRetrieveAnAssetMetadataArray()
    {
        $asset = Plugin::asset('blank.css');

        $this->assertIsArray($asset);
        $this->assertSame(static::packageFile('blank-option/assets/blank.css'), $asset['dir']);
        $this->assertSame('http://example.com/wp-content/plugins/blank-option/assets/blank.css', $asset['url']);
        $this->assertSame(Plugin::VERSION, $asset['version']);
    }

    #[Test]
    public function shouldAbleToRetrieveAnAssetMetadataArrayFromCache()
    {
        // First call
        Plugin::asset('blank.css');

        Functions\expect('plugin_dir_url')->never();

        // Second call
        $asset = Plugin::asset('blank.css');

        $this->assertIsArray($asset);
        $this->assertSame(static::packageFile('blank-option/assets/blank.css'), $asset['dir']);
        $this->assertSame('http://example.com/wp-content/plugins/blank-option/assets/blank.css', $asset['url']);
        $this->assertSame(Plugin::VERSION, $asset['version']);
    }

    #[Test]
    public function shouldAbleToRetrieveAnAssetMetadataBasedOnKey()
    {
        $asset_version = Plugin::asset('blank.css', 'version');

        $this->assertSame(Plugin::VERSION, $asset_version);
    }

    #[Test]
    public function shouldThrowInvalidArgumentExceptionForInvalidKey()
    {
        Functions\expect('wp_kses')->once()->andReturnFirstArg();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid key: invalid_key, expected 'dir', 'url', or 'version'");

        Plugin::asset('blank.css', 'invalid_key');
    }

    #[Test]
    #[RunInSeparateProcess]
    public function shouldCacheFilesystemInstance()
    {
        $filesystem = (new ReflectionClass(Plugin::class))->getProperty('filesystem');

        $this->assertNull($filesystem->getValue());

        Plugin::get_file_contents('assets/blank.css');

        $this->assertInstanceOf(WP_Filesystem_Direct::class, $filesystem->getValue());

        Plugin::get_file_contents('assets/blank.css');
    }

    #[Test]
    #[RunInSeparateProcess]
    public function shouldAppendAssetFileTimeOnDebugMode()
    {
        defined('WP_DEBUG') || define('WP_DEBUG', true);

        $asset_version = Plugin::asset('blank.css', 'version');
        $filetime = (string) filemtime(Plugin::dir('assets/blank.css'));

        $this->assertSame(Plugin::VERSION . '-' . $filetime, $asset_version);
    }
}
