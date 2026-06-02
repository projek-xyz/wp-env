<?php

declare(strict_types=1);

namespace UnitTests\BlankOption\Includes;

use Blank_Option\Plugin;
use Blank_Option\Updater;
use Brain\Monkey\Functions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use WP_Error;

/**
 * Unit tests for the blank's `includes/class-plugin.php`.
 */
#[Group('update')]
class UpdaterTest extends TestCase
{
    private array $updates = [
        'info_url' => '',
        'tag_name' => '',
        'version' => '0.0.2',
        'download_url' => '',
        'wp_version' => '6.9',
        'php_version' => '8.1',
    ];

    private function updater(): Updater
    {
        return new Updater(Plugin::instance());
    }

    #[Test]
    #[Group('negative-value')]
    public function shouldReturnFalseWhenCurrentlyCheckingAnotherPlugin()
    {
        $this->assertFalse(
            $this->updater()->check_updates(
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
            $this->updater()->check_updates(
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
            $this->updater()->check_updates(
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

        $return = $this->updater()->check_updates(
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

        $return = $this->updater()->check_updates(
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
