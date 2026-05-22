<?php

declare(strict_types=1);

namespace UnitTests\CustomTheme;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Custom_Theme\Theme;
use Mockery;
use WP_Error;

/**
 * Unit tests for the custom theme's functions.php.
 */
class FunctionsTest extends TestCase
{
    /**
     * Verifies that the 'ct_activation' action is fired when the 'after_switch_theme' hook is triggered.
     *
     * @return void
     */
    public function testThemeShouldQueueACustomScripts()
    {
        Functions\when('wp_register_script')->justReturn();
        Functions\when('wp_enqueue_script')->justReturn();

        Actions\expectAdded('wp_enqueue_scripts')
            ->once()
            ->whenHappen(function ($callback) {
                $callback();

                $this->addToAssertionCount(2);
            });

        require $this->packageFile('custom-theme/functions.php');
    }
    /**
     * Verifies that the 'ct_activation' action is fired when the 'after_switch_theme' hook is triggered.
     *
     * @return void
     */
    public function testThemeActivationTriggeredOnAfterSwitchTheme()
    {
        Actions\expectAdded('after_switch_theme')
            ->once()
            ->whenHappen(function ($callback) {
                Actions\expectDone('ct_activation')->once();

                $callback();

                $this->addToAssertionCount(2);
            });

        require $this->packageFile('custom-theme/functions.php');
    }

    /**
     * Verifies that the 'ct_deactivation' action is fired when the 'switch_theme' hook is triggered.
     *
     * @return void
     */
    public function testThemeDeactivationTriggeredOnSwitchTheme()
    {
        Actions\expectAdded('switch_theme')
            ->once()
            ->whenHappen(function ($callback) {
                Actions\expectDone('ct_deactivation')->once();

                $callback();

                $this->addToAssertionCount(2);
            });

        require $this->packageFile('custom-theme/functions.php');
    }

    public function testReturnFalseWhenCurrentlyCheckingAnotherTheme()
    {
        $spy = Mockery::spy(Theme::class)->makePartial();

        Filters\expectAdded('update_themes_projek-xyz.github.io')
            ->once()
            ->whenHappen(function ($callback) {
                $return = $callback(false, [], 'other-theme');

                $this->assertFalse($return);
            });

        $spy->shouldNotReceive('get_updates');

        require $this->packageFile('custom-theme/functions.php');
    }

    public function testReturnFalseWhenTheresErrorWhileCheckingUpdates()
    {
        $spy = Mockery::spy(Theme::class)->makePartial();

        Functions\when('get_site_transient')->justReturn(false);
        Functions\when('set_site_transient')->justReturn();
        Functions\when('wp_remote_retrieve_response_code')->justReturn(500);
        Functions\when('wp_remote_get')->justReturn(new WP_Error());

        Filters\expectAdded('update_themes_projek-xyz.github.io')
            ->once()
            ->whenHappen(function ($callback) {
                $return = $callback(false, [], 'custom-theme');

                $this->assertFalse($return);
            });

        $spy->shouldReceive('get_updates')->andReturn(false);

        require $this->packageFile('custom-theme/functions.php');
    }

    public function testReturnFalseWhenTheresNoReleaseForCurrentTheme()
    {
        $spy = Mockery::spy(Theme::class)->makePartial();
        $release = (object) [
            'other-theme' => (object) [
                'info_url' => '',
                'tag_name' => '',
                'version' => '0.0.2',
                'download_url' => '',
                'wp_version' => '6.9',
                'php_version' => '8.1',
            ],
        ];

        Functions\when('get_site_transient')->justReturn(false);
        Functions\when('set_site_transient')->justReturn();
        Functions\when('wp_remote_get')->justReturn([]);
        Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        Functions\when('wp_remote_retrieve_body')->justReturn(json_encode($release));

        Filters\expectAdded('update_themes_projek-xyz.github.io')
            ->once()
            ->whenHappen(function ($callback) {
                $return = $callback(false, [], 'custom-theme');

                $this->assertFalse($return);
            });

        $spy->shouldReceive('get_updates')->andReturn($release);

        require $this->packageFile('custom-theme/functions.php');
    }

    public function testReturnArrayWhenTheresSiteTransient()
    {
        $spy = Mockery::spy(Theme::class)->makePartial();
        $updates = [
            'info_url' => '',
            'tag_name' => '',
            'version' => '0.0.2',
            'download_url' => '',
            'wp_version' => '6.9',
            'php_version' => '8.1',
        ];

        Functions\when('get_site_transient')->justReturn((object) $updates);

        Filters\expectAdded('update_themes_projek-xyz.github.io')
            ->once()
            ->whenHappen(function ($callback) {
                $return = $callback(false, ['Version' => '0.0.1'], 'custom-theme');

                // $this->assertArrayHasKey('theme', $return);
                $this->assertArrayHasKey('package', $return);
                $this->assertArrayHasKey('version', $return);
                $this->assertArrayHasKey('url', $return);
                $this->assertArrayHasKey('tested', $return);
                $this->assertArrayHasKey('requires_php', $return);
                $this->assertArrayHasKey('translations', $return);
            });

        $spy->shouldReceive('get_updates')->andReturn((object) ['custom-theme' => (object) $updates]);

        require $this->packageFile('custom-theme/functions.php');
    }

    public function testReturnArrayWhenTheresAnUpdateAvailable()
    {
        $spy = Mockery::spy(Theme::class)->makePartial();
        $release = (object) [
            'custom-theme' => (object) [
                'info_url' => '',
                'tag_name' => '',
                'version' => '0.0.2',
                'download_url' => '',
                'wp_version' => '6.9',
                'php_version' => '8.1',
            ],
        ];

        Functions\when('get_site_transient')->justReturn(false);
        Functions\when('set_site_transient')->justReturn();
        Functions\when('wp_remote_get')->justReturn([]);
        Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        Functions\when('wp_remote_retrieve_body')->justReturn(json_encode($release));

        Filters\expectAdded('update_themes_projek-xyz.github.io')
            ->once()
            ->whenHappen(function ($callback) {
                $return = $callback(false, ['Version' => '0.0.1'], 'custom-theme');

                // $this->assertArrayHasKey('theme', $return);
                $this->assertArrayHasKey('package', $return);
                $this->assertArrayHasKey('version', $return);
                $this->assertArrayHasKey('url', $return);
                $this->assertArrayHasKey('tested', $return);
                $this->assertArrayHasKey('requires_php', $return);
                $this->assertArrayHasKey('translations', $return);
            });

        $spy->shouldReceive('get_updates')->andReturn($release);

        require $this->packageFile('custom-theme/functions.php');
    }
}
