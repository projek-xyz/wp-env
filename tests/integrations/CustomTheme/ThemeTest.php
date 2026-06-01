<?php

declare(strict_types=1);

namespace IntegrationTests\CustomTheme;

use Custom_Theme\Theme;
use PHPUnit\Framework\Attributes\Test;

/**
 * Integration tests for the Theme class.
 *
 * These tests verify the theme's interaction with WordPress hooks,
 * asset management, and custom update mechanisms.
 */
class ThemeTest extends TestCase
{
    /**
     * Diagnostic test to check if the theme is actually active.
     */
    #[Test]
    public function isThemeActive()
    {
        $this->assertEquals(
            static::PACKAGE_NAME,
            \get_stylesheet(),
            'The custom-theme should be the active stylesheet.'
        );
    }

    /**
     * Verifies that the theme triggers custom actions upon activation and deactivation.
     *
     * Custom lifecycle hooks allow other components or child themes to perform
     * setup/cleanup tasks specifically for this theme without modifying the theme core.
     */
    #[Test]
    public function shouldTriggerThemeLifecycleActions()
    {
        $activated = false;
        $deactivated = false;

        \add_action('ct_activation', function () use (&$activated) {
            $activated = true;
        });

        \add_action('ct_deactivation', function () use (&$deactivated) {
            $deactivated = true;
        });

        // Act: Simulate theme switch to this theme
        Theme::activate();
        // Act: Simulate theme switch away from this theme
        Theme::deactivate();

        // Assert: Verify actions were fired
        $this->assertTrue(
            $activated,
            'The ct_activation action should be triggered on after_switch_theme.'
        );
        $this->assertTrue(
            $deactivated,
            'The ct_deactivation action should be triggered on switch_theme.'
        );
    }

    /**
     * Verifies that the theme's custom scripts are correctly registered and enqueued.
     *
     * Enqueuing scripts properly ensures that the theme's interactive features
     * work as expected and follow WordPress standards for dependency management.
     */
    #[Test]
    public function shouldEnqueueThemeScripts()
    {
        // Act: Trigger script enqueuing
        Theme::enqueue_scripts();

        $theme = \wp_get_theme();
        $handle = $theme->stylesheet;

        // Assert: Verify script is enqueued
        $this->assertTrue(
            \wp_script_is($handle, 'enqueued'),
            'The theme-specific script should be enqueued using the theme handle.'
        );

        // Verify: Check registration details
        global $wp_scripts;

        $this->assertStringContainsString(
            '/assets/custom.js',
            $wp_scripts->registered[$handle]->src,
            'Enqueued script should point to the correct path in the theme assets.'
        );
        $this->assertEquals(
            $theme->version,
            $wp_scripts->registered[$handle]->ver,
            'Enqueued script should use the theme version.'
        );
    }

    /**
     * Verifies that the update logic correctly identifies if an update is available.
     *
     * This scenario is necessary to ensure the custom theme update mechanism
     * properly interfaces with WordPress's internal update transient system.
     */
    #[Test]
    public function shouldHandleUpdateChecksCorrectly()
    {
        \add_filter('http_response', static function ($response) {
            $body = json_decode($response['body'], true);

            // Mock release data with a newer version
            $body[static::PACKAGE_NAME]['version'] = '0.0.2';

            $response['body'] = json_encode($body);

            return $response;
        });

        // Act: Call get_updates
        $update = Theme::check_updates(false, ['Version' => '0.0.1'], \get_stylesheet());

        $this->assertNotFalse($update);
    }

    /**
     * Verifies that update information is cached in a site transient.
     *
     * Caching remote API responses is crucial for performance and to avoid hitting
     * API rate limits during standard WordPress administration.
     */
    #[Test]
    public function shouldCacheUpdateInformationInTransient()
    {
        $cache_key = 'custom-theme_updates';

        // Ensure cache is empty
        \delete_site_transient($cache_key);

        // Note: Real network requests are discouraged in integration tests.
        // This scenario highlights the necessity of testing the caching mechanism.
        // A full implementation would mock wp_remote_get.

        $mock_update = (object) ['version' => '1.2.3'];
        \set_site_transient($cache_key, $mock_update, HOUR_IN_SECONDS);

        // Act: Call get_updates
        $result = Theme::get_updates();

        // Assert: Verify it returns the cached data
        $this->assertEquals(
            $mock_update,
            $result,
            'Theme::get_updates should return cached data if available in transients.'
        );
    }
}
