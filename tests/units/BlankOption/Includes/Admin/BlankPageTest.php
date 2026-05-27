<?php

declare(strict_types=1);

namespace UnitTests\BlankOption\Includes\Admin;

use Blank_Option\Admin\Blank_Page;
use Blank_Option\Plugin;
use Brain\Monkey\Functions;
use PHPUnit\Framework\Attributes\RunClassInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use UnitTests\BlankOption\Includes\TestCase;
use WP_Screen;

/**
 * Unit tests for the blank's `includes/class-admin.php`.
 */
#[RunClassInSeparateProcess]
class BlankPageTest extends TestCase
{
    protected static bool $loadAutoloader = true;

    private function page(?Plugin $plugin = null): Blank_Page
    {
        return new Blank_Page($plugin ?? Plugin::instance());
    }

    #[Test]
    public function shouldNotEnqueueAdminScriptsOnOtherAdminScreens()
    {
        Functions\expect('wp_enqueue_style')->never();
        Functions\expect('wp_enqueue_script')->never();

        $this->page()->enqueue_scripts('other-admin-screen');
    }

    #[Test]
    public function shouldAbleToEnqueueAdminScriptsOnPluginScreen()
    {
        Functions\expect('wp_enqueue_style')->once()->andReturnUsing(
            function (string $handle, string $src, array $deps, string $version) {
                $this->assertSame('blank-option-admin-style', $handle);
                $this->assertSame(BLANK_VERSION, $version);
                $this->assertEmpty($deps);
                $this->assertSame(
                    'http://example.com/wp-content/plugins/blank-option/assets/admin.blank.css',
                    $src,
                );
            }
        );

        Functions\expect('wp_enqueue_script')->once()->andReturnUsing(
            function (string $handle, string $src, array $deps, string $version) {
                $this->assertSame('blank-option-admin-script', $handle);
                $this->assertSame(BLANK_VERSION, $version);
                $this->assertEmpty($deps);
                $this->assertSame(
                    'http://example.com/wp-content/plugins/blank-option/assets/admin.blank.js',
                    $src,
                );
            }
        );

        $this->page()->enqueue_scripts('plugins_page_blank-option');
    }

    #[Test]
    public function registerNewAdminMenu()
    {
        Functions\expect('add_submenu_page')->once()->andReturnUsing(
            function (
                string $parent,
                string $page_title,
                string $menu_title,
                string $capability,
                string $menu_slug,
                callable $callback
            ) {
                $this->assertSame('plugins.php', $parent);
                $this->assertSame('Blank Options', $page_title);
                $this->assertSame('Blank Options', $menu_title);
                $this->assertSame('activate_plugins', $capability);
                $this->assertSame('blank-option', $menu_slug);

                $this->assertIsArray($callback);
                $this->assertInstanceOf(Blank_Page::class, $callback[0]);
                $this->assertSame('render', $callback[1]);
            }
        );

        $this->page()->menu();
    }

    #[Test]
    public function actionLinksShouldReturnEmptyOnEmptyScreen()
    {
        Functions\expect('current_user_can')->once()->andReturn(false);

        $actual = $this->page()->action_links([], '', []);

        $this->assertSame([], $actual);
    }

    #[Test]
    public function actionLinksShouldPrependAdditionalLinks()
    {
        Functions\when('current_user_can')->justReturn(true);

        $this->assertSame(
            [
                '<a href="http://example.com/wp-admin/plugins.php?page=blank-option">Settings</a>'
            ],
            $this->page()->action_links([], '', [])
        );

        $this->assertSame(
            [
                '<a href="http://example.com/wp-admin/plugins.php?page=blank-option">Settings</a>',
                '<a href="http://example.com/support">Supports</a>',
            ],
            $this->page()->action_links([], '', [
                'PluginURI' => 'http://example.com/support'
            ])
        );
    }

    #[Test]
    public function loadHookShouldDoingNothingOnEmptyScreen()
    {
        Functions\expect('get_current_screen')->once()->andReturnNull();
        Functions\expect('esc_url')->never();

        $this->page()->load();
    }

    #[Test]
    public function loadHookShouldRegisterContextualHelpTabs()
    {
        Functions\expect('get_current_screen')->once()->andReturnUsing(function () {
            $mock = mock(WP_Screen::class);

            $mock->shouldReceive('add_help_tab')->once();
            $mock->shouldReceive('set_help_sidebar')->once();

            return $mock;
        });

        $this->page()->load();
    }

    #[Test]
    public function loadHookShouldExcludeEmailFromHelpSidebar()
    {
        Functions\expect('get_current_screen')->once()->andReturnUsing(function () {
            $mock = mock(WP_Screen::class);

            $mock->shouldReceive('add_help_tab')->once();
            $mock->shouldReceive('set_help_sidebar')->once();

            return $mock;
        });

        $support = [
            'email' => 'john@example.com',
            'docs' => 'https://example.com/docs'
        ];

        $plugin = mock(Plugin::class);

        $plugin->shouldReceive('get')
            ->once()
            ->with('supports')
            ->andReturn($support);

        $this->page($plugin)->load();
    }

    #[Test]
    public function renderShouldPrintOutputToPluginScreen()
    {
        $this->expectOutputString(implode('', [
            '<div class="wrap">',
            '<h1 class="wp-heading-inline">Blank Option</h1><hr class="wp-header-end">',
            '<div class="clear"></div></div>'
        ]));

        Functions\expect('wp_kses')->once()->andReturnFirstArg();
        Functions\expect('get_admin_page_title')->once()->andReturn('Blank Option');

        $this->page()->render();
    }
}
