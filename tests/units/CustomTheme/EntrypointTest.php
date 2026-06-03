<?php

declare(strict_types=1);

namespace UnitTests\CustomTheme;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Custom_Theme\Theme;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use WP_Error;

/**
 * Unit tests for the custom theme's functions.php.
 */
#[Group('entrypoint')]
class EntrypointTest extends TestCase
{
    /**
     * Verifies that the 'wp_enqueue_scripts' action is added when the plugin is initialized.
     *
     * @return void
     */
    #[Test]
    #[Group('static-asset')]
    public function shouldEnqueueACustomScripts()
    {
        Functions\when('wp_register_script')->justReturn();
        Functions\when('wp_enqueue_script')->justReturn();

        Actions\expectAdded('wp_enqueue_scripts')
            ->once()
            ->whenHappen(function ($callback) {
                $callback();

                $this->addToAssertionCount(2);
            });

        require static::package('entrypoint');
    }
    /**
     * Verifies that the 'ct_activation' action is fired when the 'after_switch_theme' hook is triggered.
     *
     * @return void
     */
    #[Test]
    #[Group('activation')]
    public function activationShouldBeTriggeredAfterSwitchTheme()
    {
        Actions\expectAdded('after_switch_theme')
            ->once()
            ->whenHappen(function ($callback) {
                Actions\expectDone('ct_activation')->once();

                $callback();

                $this->addToAssertionCount(2);
            });

        require static::package('entrypoint');
    }

    /**
     * Verifies that the 'ct_deactivation' action is fired when the 'switch_theme' hook is triggered.
     *
     * @return void
     */
    #[Test]
    #[Group('deactivation')]
    public function deactivationShouldBeTriggeredOnSwitchTheme()
    {
        Actions\expectAdded('switch_theme')
            ->once()
            ->whenHappen(function ($callback) {
                Actions\expectDone('ct_deactivation')->once();

                $callback();

                $this->addToAssertionCount(2);
            });

        require static::package('entrypoint');
    }

    #[Test]
    #[Group('negative-value')]
    #[Group('update')]
    public function shouldReturnFalseWhenCurrentlyCheckingAnotherTheme()
    {
        $spy = Mockery::spy(Theme::class)->makePartial();

        Filters\expectAdded('update_themes_projek-xyz.github.io')
            ->once()
            ->whenHappen(function ($callback) {
                $return = $callback(false, [], 'other-theme');

                $this->assertFalse($return);
            });

        $spy->shouldNotReceive('get_updates');

        require static::package('entrypoint');
    }

    #[Test]
    #[Group('negative-value')]
    #[Group('update')]
    public function shouldReturnsFalseWhenTheresErrorWhileCheckingUpdates()
    {
        $spy = Mockery::spy(Theme::class)->makePartial();

        Functions\when('get_site_transient')->justReturn(false);
        Functions\when('set_site_transient')->justReturn();
        Functions\when('wp_remote_retrieve_response_code')->justReturn(500);
        Functions\when('wp_remote_get')->justReturn(new WP_Error());

        Filters\expectAdded('update_themes_projek-xyz.github.io')
            ->once()
            ->whenHappen(function ($callback) {
                $return = $callback(false, [], static::PACKAGE_NAME);

                $this->assertFalse($return);
            });

        $spy->shouldReceive('get_updates')->andReturn(false);

        require static::package('entrypoint');
    }

    #[Test]
    #[Group('negative-value')]
    #[Group('update')]
    public function shouldReturnsFalseWhenTheresNoReleaseForCurrentTheme()
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
                $return = $callback(false, [], static::PACKAGE_NAME);

                $this->assertFalse($return);
            });

        $spy->shouldReceive('get_updates')->andReturn($release);

        require static::package('entrypoint');
    }

    #[Test]
    #[Group('cached-value')]
    #[Group('update')]
    public function shouldReturnsArrayWhenTheresSiteTransient()
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
                $return = $callback(false, ['Version' => '0.0.1'], static::PACKAGE_NAME);

                // $this->assertArrayHasKey('theme', $return);
                $this->assertArrayHasKey('package', $return);
                $this->assertArrayHasKey('version', $return);
                $this->assertArrayHasKey('url', $return);
                $this->assertArrayHasKey('tested', $return);
                $this->assertArrayHasKey('requires_php', $return);
                $this->assertArrayHasKey('translations', $return);
            });

        $spy->shouldReceive('get_updates')->andReturn((object) [
            static::PACKAGE_NAME => (object) $updates,
        ]);

        require static::package('entrypoint');
    }

    #[Test]
    #[Group('positive-value')]
    #[Group('update')]
    public function shouldReturnsArrayWhenTheresAnUpdateAvailable()
    {
        $spy = Mockery::spy(Theme::class)->makePartial();
        $release = (object) [
            static::PACKAGE_NAME => (object) [
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
                $return = $callback(false, ['Version' => '0.0.1'], static::PACKAGE_NAME);

                // $this->assertArrayHasKey('theme', $return);
                $this->assertArrayHasKey('package', $return);
                $this->assertArrayHasKey('version', $return);
                $this->assertArrayHasKey('url', $return);
                $this->assertArrayHasKey('tested', $return);
                $this->assertArrayHasKey('requires_php', $return);
                $this->assertArrayHasKey('translations', $return);
            });

        $spy->shouldReceive('get_updates')->andReturn($release);

        require static::package('entrypoint');
    }
}
