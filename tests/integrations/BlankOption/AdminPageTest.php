<?php

declare(strict_types=1);

namespace IntegrationTests\BlankOption;

use Blank_Option\Admin\Blank_Page;
use Blank_Option\Plugin;
use PHPUnit\Framework\Attributes\Test;

/**
 * Integration tests for the Blank_Page admin class.
 *
 * These tests verify how the plugin's admin page integrates with the
 * WordPress dashboard, including menus, help tabs, and admin-specific assets.
 */
class AdminPageTest extends TestCase
{
    /**
     * Verifies that the admin submenu page is correctly registered.
     *
     * Without correct registration, users won't be able to access the plugin settings,
     * effectively making the plugin's configuration options unreachable.
     */
    #[Test]
    public function shouldRegisterAdminSubmenu()
    {
        $plugin = Plugin::instance();
        $admin_page = new Blank_Page($plugin);

        // Act: Trigger menu registration
        $admin_page->menu();

        // Assert: Verify the submenu exists globally
        global $submenu;
        $this->assertArrayHasKey(
            'plugins.php',
            $submenu,
            'The plugins.php parent menu should exist.'
        );

        $found = false;
        foreach ($submenu['plugins.php'] as $item) {
            if ($item[2] === $plugin->get('text_domain')) {
                $found = true;
                break;
            }
        }

        $this->assertTrue(
            $found,
            'The Blank Option submenu should be registered under the Plugins menu.'
        );
    }

    /**
     * Verifies that help tabs and sidebars are added when the admin page loads.
     *
     * Contextual help is essential for a good user experience, providing immediate guidance
     * to users on how to use the specific plugin settings without leaving the dashboard.
     */
    #[Test]
    public function shouldAddHelpTabsOnPageLoad()
    {
        $plugin = Plugin::instance();
        $admin_page = new Blank_Page($plugin);

        // Act: Mock the current screen and trigger the load method
        set_current_screen('plugins_page_' . $plugin->get('text_domain'));
        $admin_page->load();

        $screen = get_current_screen();

        // Assert: Verify help tabs were added
        $help_tabs = $screen->get_help_tabs();
        $this->assertArrayHasKey(
            'blank-option-help',
            $help_tabs,
            'Contextual help tab should be added to the admin screen.'
        );
        $this->assertEquals(
            'Blank Help',
            $help_tabs['blank-option-help']['title'],
            'Help tab should have the correct title.'
        );

        // Verify: Help sidebar content is set
        // Note: get_help_sidebar() is a method on WP_Screen
        $this->assertStringContainsString(
            'For more information:',
            $screen->get_help_sidebar(),
            'Help sidebar should contain the expected guidance text.'
        );
    }

    /**
     * Verifies that admin-specific assets are enqueued only on the correct admin page.
     *
     * We must avoid "plugin pollution" by only enqueuing assets on our own pages.
     * This prevents conflicts with other plugins and keeps the WordPress admin fast.
     */
    #[Test]
    public function shouldEnqueueAdminAssetsOnlyOnPluginPage()
    {
        $plugin = Plugin::instance();
        $admin_page = new Blank_Page($plugin);
        $text_domain = $plugin->get('text_domain');

        // Act 1: Trigger on a DIFFERENT page
        $admin_page->enqueue_scripts('dashboard');

        // Assert 1: Assets should NOT be enqueued
        $this->assertFalse(
            wp_style_is($text_domain . '-admin-style', 'enqueued'),
            'Admin styles should NOT be enqueued on unrelated pages.'
        );
        $this->assertFalse(
            wp_script_is($text_domain . '-admin-script', 'enqueued'),
            'Admin scripts should NOT be enqueued on unrelated pages.'
        );

        // Act 2: Trigger on our OWN page
        $admin_page->enqueue_scripts('plugins_page_' . $text_domain);

        // Assert 2: Assets SHOULD be enqueued
        $this->assertTrue(
            wp_style_is($text_domain . '-admin-style', 'enqueued'),
            'Admin styles should be enqueued on the plugin settings page.'
        );
        $this->assertTrue(
            wp_script_is($text_domain . '-admin-script', 'enqueued'),
            'Admin scripts should be enqueued on the plugin settings page.'
        );
    }

    /**
     * Verifies that the plugin action links are correctly added to the plugins list.
     *
     * Providing a "Settings" link directly in the plugins list is a WordPress best practice,
     * as it allows users to quickly find the configuration page after activating the plugin.
     */
    #[Test]
    public function shouldAddActionLinksToPluginRow()
    {
        $plugin = Plugin::instance();
        $admin_page = new Blank_Page($plugin);
        $text_domain = $plugin->get('text_domain');

        // Act: Filter the action links
        $actions = $admin_page->action_links(
            ['deactivate' => 'Deactivate'],
            'dummy/path.php',
            ['PluginURI' => 'https://example.com']
        );

        // Assert: Verify our custom links are present
        $this->assertStringContainsString(
            'Settings',
            $actions[0],
            'A "Settings" link should be added to the plugin action links.'
        );
        $this->assertStringContainsString(
            'Supports',
            $actions[1],
            'A "Supports" link should be added when a PluginURI is present.'
        );
        $this->assertArrayHasKey(
            'deactivate',
            $actions,
            'Existing core action links should be preserved.'
        );
    }
}
