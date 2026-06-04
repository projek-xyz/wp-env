<?php

declare(strict_types=1);

namespace IntegrationTests\BlankOption;

use Blank_Option\Installer;
use Blank_Option\Plugin;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * Integration tests for the Plugin class.
 *
 * These tests verify the plugin's integration with WordPress hooks,
 * lifecycle events, and environment requirements.
 */
class PluginTest extends TestCase
{
    /**
     * Verifies that the plugin correctly hooks into WordPress during initialization.
     *
     * This is necessary to ensure that core plugin functionalities like asset loading,
     * menu registration, and update checking are properly wired into the WP lifecycle.
     */
    #[Test]
    #[Group('initialization')]
    public function shouldRegisterCoreHooksOnInit()
    {
        // Act: Manually trigger the init method
        Plugin::init();

        // Assert: Verify that essential filters and actions are registered.
        // We use has_filter as has_action is an alias for it.

        // Check if update checker is registered.
        $this->assertNotFalse(
            \has_filter('update_plugins_projek-xyz.github.io'),
            'Update checker filter should be registered on the custom update domain.'
        );

        // Check if frontend asset enqueuing is registered.
        $this->assertNotFalse(
            \has_action('wp_enqueue_scripts', [Plugin::instance(), 'enqueue_scripts']),
            'Plugin::enqueue_scripts should be hooked to wp_enqueue_scripts.'
        );

        // Check if admin menu registration is registered.
        // Since add_admin_page calls add_action('admin_menu', ...), we verify that.
        $this->assertNotFalse(
            \has_action('admin_menu'),
            'Admin menu registration should be hooked to admin_menu.'
        );
    }

    /**
     * Verifies that the requirement check correctly flags unmet requirements.
     *
     * This scenario is critical because running the plugin on unsupported PHP or WP versions
     * could lead to fatal errors or unexpected behavior. The plugin must gracefully
     * disable itself and notify the user.
     */
    #[Test]
    #[Group('negative-value')]
    #[Group('requirement')]
    public function shouldFlagUnmetRequirements()
    {
        // Act: Check for a requirement that is guaranteed to fail (PHP version 99.0)
        Plugin::check_requirements('PHP', '8.1', '99.0');

        // Assert: Requirements should not be met
        $this->assertFalse(
            Plugin::is_met_requirements(),
            'Plugin should flag requirements as not met when current version is lower than required.'
        );

        // Verify: Admin notice action should be registered
        $this->assertNotFalse(
            \has_action('admin_notices'),
            'An admin notice should be registered when requirements are not met.'
        );

        // Tear down: Reset the requirements flag
        Plugin::check_requirements('PHP', PHP_VERSION, PHP_VERSION);
    }

    /**
     * Verifies that activation and deactivation hooks trigger their respective actions.
     *
     * Third-party developers may rely on these hooks to perform their own setup or
     * cleanup tasks when Blank Option is activated or deactivated.
     */
    #[Test]
    #[Group('activation')]
    #[Group('deactivation')]
    public function shouldTriggerLifecycleActions()
    {
        $activated = false;
        $deactivated = false;

        \add_action('blank_option_activate', function () use (&$activated) {
            $activated = true;
        });

        \add_action('blank_option_deactivate', function () use (&$deactivated) {
            $deactivated = true;
        });

        // Act: Simulate activation
        Installer::activate();
        // Act: Simulate deactivation
        Installer::deactivate();

        // Assert: Verify actions were fired
        $this->assertTrue(
            $activated,
            'The blank_option_activate action should be triggered upon plugin activation.'
        );

        $this->assertTrue(
            $deactivated,
            'The blank_option_deactivate action should be triggered upon plugin deactivation.'
        );
    }

    /**
     * Verifies that the upgrade logic triggers the upgrade action when a version mismatch is detected.
     *
     * This is essential for handling database migrations or configuration updates
     * when the plugin is updated to a newer version.
     */
    #[Test]
    #[Group('activation')]
    public function shouldTriggerUpgradeActionOnVersionChange()
    {
        $upgrade_triggered = false;
        $old_v = '';
        $new_v = '';

        // Mock current version in DB to be older than the plugin version
        $plugin = Plugin::instance();
        $text_domain = $plugin->get('text_domain');

        \update_option($text_domain, ['version' => '0.0.0']);

        \add_action(
            'blank_option_upgrade',
            function ($old, $new) use (&$upgrade_triggered, &$old_v, &$new_v) {
                $upgrade_triggered = true;
                $old_v = $old;
                $new_v = $new;
            },
            10,
            2
        );

        // Act: Trigger upgrade logic
        Installer::upgrade($plugin);

        // Assert: Verify upgrade action was fired with correct versions
        $this->assertTrue(
            $upgrade_triggered,
            'The blank_option_upgrade action should be triggered when a version change is detected.'
        );
        $this->assertEquals(
            '0.0.0',
            $old_v,
            'The old version passed to the hook should match the value in the database.'
        );
        $this->assertEquals(
            Plugin::instance()->get('version'),
            $new_v,
            'The new version passed to the hook should match the current plugin version.'
        );
    }

    /**
     * Verifies that assets are correctly registered and enqueued with the correct version.
     *
     * Proper asset enqueuing ensures that the plugin looks and behaves correctly on the frontend,
     * and versioning prevents browser caching issues during updates.
     */
    #[Test]
    #[Group('static-asset')]
    public function shouldEnqueueFrontendAssetsWithCorrectVersion()
    {
        // Act: Trigger enqueueing
        Plugin::instance()->enqueue_scripts();

        $text_domain = Plugin::instance()->get('text_domain');
        $expected_version = Plugin::instance()->get('version');

        // Assert: Verify styles and scripts are in the enqueue queue
        $this->assertTrue(
            \wp_style_is($text_domain . '-style', 'enqueued'),
            'Plugin stylesheet should be enqueued.'
        );
        $this->assertTrue(
            \wp_script_is($text_domain . '-script', 'enqueued'),
            'Plugin script should be enqueued.'
        );

        // Verify: Check versioning
        global $wp_styles, $wp_scripts;

        $this->assertEquals(
            $expected_version,
            $wp_styles->registered[$text_domain . '-style']->ver,
            'Enqueued style should have the correct plugin version.'
        );
        $this->assertEquals(
            $expected_version,
            $wp_scripts->registered[$text_domain . '-script']->ver,
            'Enqueued script should have the correct plugin version.'
        );
    }
}
