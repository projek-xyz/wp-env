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
#[Group('update')]
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

    private function installer(): Installer
    {
        return new Installer(Plugin::instance());
    }

    #[Test]
    #[Group('lifecycle')]
    public function shouldFireActivateAction()
    {
        $current_version = Plugin::instance()->get('version');

        Functions\expect('get_option')
            ->once()
            ->with(static::PACKAGE_NAME)
            ->andReturn(false);

        Functions\expect('update_option')
            ->once()
            ->with(static::PACKAGE_NAME, ['version' => $current_version]);

        Actions\expectDone('blank_option_activate')->once();

        Installer::activate();

        $this->assertTrue(true); // Basic assertion to avoid "no assertions" warning
    }

    #[Test]
    #[Group('lifecycle')]
    public function shouldFireDeactivateAction()
    {
        Actions\expectDone('blank_option_deactivate')->once();

        Installer::deactivate();

        $this->assertTrue(true);
    }

    #[Test]
    #[Group('upgrade')]
    public function shouldSaveVersionOnInitialInstallation()
    {
        $current_version = Plugin::instance()->get('version');

        Functions\expect('get_option')
            ->once()
            ->with(static::PACKAGE_NAME)
            ->andReturn(false);

        Functions\expect('update_option')
            ->once()
            ->with(static::PACKAGE_NAME, ['version' => $current_version]);

        $this->installer()->upgrade();
    }

    #[Test]
    #[Group('upgrade')]
    public function shouldDoNothingWhenVersionIsSame()
    {
        $current_version = Plugin::instance()->get('version');

        Functions\expect('get_option')
            ->once()
            ->with(static::PACKAGE_NAME)
            ->andReturn(['version' => $current_version]);

        Functions\expect('update_option')->never();

        $this->installer()->upgrade();
    }

    #[Test]
    #[Group('upgrade')]
    public function shouldPerformUpgradeWhenNewerVersionAvailable()
    {
        $old_version     = '0.0.1';
        $current_version = Plugin::instance()->get('version');

        Functions\expect('get_option')
            ->once()
            ->with(static::PACKAGE_NAME)
            ->andReturn(['version' => $old_version, 'some_other_option' => 'value']);

        Actions\expectDone('blank_option_upgrade')
            ->once()
            ->with($old_version, $current_version);

        Functions\expect('update_option')
            ->once()
            ->with(static::PACKAGE_NAME, ['version' => $current_version, 'some_other_option' => 'value']);

        $this->installer()->upgrade();
    }

    #[Test]
    #[Group('negative-value')]
    public function shouldReturnFalseWhenCurrentlyCheckingAnotherPlugin()
    {
        $this->assertFalse(
            $this->installer()->check_updates(
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
            $this->installer()->check_updates(
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
            $this->installer()->check_updates(
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

        $return = $this->installer()->check_updates(
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

        $return = $this->installer()->check_updates(
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
