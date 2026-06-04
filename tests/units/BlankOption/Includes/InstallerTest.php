<?php

declare(strict_types=1);

namespace UnitTests\BlankOption\Includes;

use Blank_Option\Plugin;
use Blank_Option\Installer;
use Brain\Monkey\Functions;
use Brain\Monkey\Actions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use WP_Error;

/**
 * Unit tests for the blank's `includes/class-installer.php`.
 */
#[Group('installer')]
class InstallerTest extends TestCase
{
    private array $updates = [
        'info_url' => '',
        'tag_name' => '',
        'version' => '0.0.2',
        'download_url' => '',
        'wp_version' => '6.9',
        'php_version' => '8.1',
    ];

    #[Test]
    #[Group('lifecycle')]
    public function shouldFireActivateAction()
    {
        Functions\when('get_option')->justReturn(false);
        Functions\when('update_option')->justReturn();

        Actions\expectDone('blank_option_activate')->once();

        Installer::activate();

        $this->assertTrue(true); // Basic assertion to avoid "no assertions" warning
    }

    #[Test]
    #[Group('lifecycle')]
    public function shouldFireDeactivateAction()
    {
        Functions\expect('delete_site_transient')->once();

        Actions\expectDone('blank_option_deactivate')->once();

        Installer::deactivate();

        $this->assertTrue(true);
    }

    #[Test]
    #[Group('upgrade')]
    public function shouldSaveVersionOnInitialInstallation()
    {
        $plugin = Plugin::instance();

        Functions\when('get_option')->justReturn(false);
        Functions\expect('update_option')
            ->once()
            ->with(static::PACKAGE_NAME, \Mockery::on(function ($options) use ($plugin) {
                return isset($options['version']) && $options['version'] === $plugin->get('version');
            }));

        Installer::upgrade($plugin);
    }

    #[Test]
    #[Group('upgrade')]
    public function shouldDoNothingWhenVersionIsSame()
    {
        $plugin = Plugin::instance();
        $current_version = $plugin->get('version');

        Functions\when('get_option')->justReturn(['version' => $current_version]);
        Functions\expect('update_option')->never();

        Installer::upgrade($plugin);
    }

    #[Test]
    #[Group('upgrade')]
    public function shouldPerformUpgradeWhenNewerVersionAvailable()
    {
        $plugin = Plugin::instance();
        $current_version = $plugin->get('version');
        $old_version = '0.0.1';

        Functions\when('get_option')->justReturn([
            'version' => $old_version,
            'some_other_option' => 'value'
        ]);

        Functions\expect('update_option')
            ->once()
            ->with(static::PACKAGE_NAME, ['version' => $current_version, 'some_other_option' => 'value']);

        $plugin->option->set('version', $old_version); // Ensure to clear out the option cache.

        Actions\expectDone('blank_option_upgrade')
            ->once()
            ->with($old_version, $current_version);

        Installer::upgrade($plugin);
    }

    #[Test]
    #[Group('negative-value')]
    public function shouldReturnFalseWhenCurrentlyCheckingAnotherPlugin()
    {
        $this->assertFalse(
            Installer::check_updates(
                false,
                [],
                plugin_basename('other-plugin/other-plugin.php')
            )
        );
    }

    #[Test]
    #[Group('negative-value')]
    public function shouldReturnsFalseWhenTheresErrorWhileCheckingUpdates()
    {
        Functions\when('get_site_transient')->justReturn(false);
        Functions\when('set_site_transient')->justReturn();
        Functions\when('wp_remote_retrieve_response_code')->justReturn(500);
        Functions\when('wp_remote_get')->justReturn(new WP_Error());

        $this->assertFalse(
            Installer::check_updates(
                false,
                [],
                plugin_basename(BLANK_OPTION_FILE)
            )
        );
    }

    #[Test]
    #[Group('negative-value')]
    public function shouldReturnsFalseWhenTheresNoNewReleaseAvailable()
    {
        Functions\when('get_site_transient')->justReturn(false);
        Functions\when('set_site_transient')->justReturn();
        Functions\when('wp_remote_get')->justReturn([]);
        Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        Functions\when('wp_remote_retrieve_body')->justReturn(json_encode((object) [
            'other-theme' => (object) $this->updates,
        ]));

        $this->assertFalse(
            Installer::check_updates(
                false,
                [],
                plugin_basename(BLANK_OPTION_FILE)
            )
        );
    }

    #[Test]
    #[Group('cached-value')]
    public function shouldReturnsArrayWhenTheresSiteTransient()
    {
        Functions\when('get_site_transient')->justReturn((object) $this->updates);

        $return = Installer::check_updates(
            false,
            ['Version' => '0.0.1'],
            plugin_basename(BLANK_OPTION_FILE)
        );

        $this->assertArrayHasKey('package', $return);
        $this->assertArrayHasKey('version', $return);
        $this->assertArrayHasKey('url', $return);
        $this->assertArrayHasKey('tested', $return);
        $this->assertArrayHasKey('requires_php', $return);
        $this->assertArrayHasKey('translations', $return);
    }

    #[Test]
    #[Group('positive-value')]
    public function shouldReturnsArrayWhenTheresAnUpdateAvailable()
    {
        Functions\when('get_site_transient')->justReturn(false);
        Functions\when('set_site_transient')->justReturn();
        Functions\when('wp_remote_get')->justReturn([]);
        Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        Functions\when('wp_remote_retrieve_body')->justReturn(json_encode([
            static::PACKAGE_NAME => (object) $this->updates,
        ]));

        $return = Installer::check_updates(
            false,
            ['Version' => '0.0.1'],
            plugin_basename(BLANK_OPTION_FILE)
        );

        $this->assertArrayHasKey('package', $return);
        $this->assertArrayHasKey('version', $return);
        $this->assertArrayHasKey('url', $return);
        $this->assertArrayHasKey('tested', $return);
        $this->assertArrayHasKey('requires_php', $return);
        $this->assertArrayHasKey('translations', $return);
    }
}
