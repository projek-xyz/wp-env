<?php

declare(strict_types=1);

namespace UnitTests\BlankOption\Includes\Admin;

use Blank_Option\Admin\Blank_Page;
use Blank_Option\Plugin;
use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use UnitTests\BlankOption\Includes\TestCase;
use WP_Screen;

/**
 * Unit tests for the blank's `includes/class-admin.php`.
 */
#[Group('admin-page')]
class BlankPageTest extends TestCase
{
    private function page(?Plugin $plugin = null): Blank_Page
    {
        return new Blank_Page($plugin ?? Plugin::instance());
    }

    #[Test]
    #[Group('static-asset')]
    public function shouldNotEnqueueAdminScriptsOnOtherAdminScreens()
    {
        Functions\expect('wp_enqueue_style')->never();
        Functions\expect('wp_enqueue_script')->never();

        $this->page()->enqueue_scripts('other-admin-screen');
    }

    #[Test]
    #[Group('static-asset')]
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
    #[Group('admin-menu')]
    public function shouldAbleToRegisterNewAdminPage()
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

                return 'plugins_page_blank-option';
            }
        );

        Actions\expectAdded('load-plugins_page_blank-option')->once()->whenHappen(function ($callback) {
            $this->assertIsArray($callback);

            $this->assertInstanceOf(Blank_Page::class, $callback[0]);
            $this->assertSame('load', $callback[1]);
        });

        $this->page()->menu();
    }

    #[Test]
    #[Group('admin-menu')]
    public function shouldNotAbleToRegisterAdminPageWhenCurrentUserDoesNotHaveTheRequiredCapability()
    {
        Functions\expect('add_submenu_page')->once()->andReturn(false);

        Actions\expectAdded('load-plugins_page_blank-option')->never();

        $this->page()->menu();
    }

    #[Test]
    #[Group('user-capability')]
    public function actionLinksShouldReturnEmptyOnEmptyScreen()
    {
        Functions\expect('current_user_can')->once()->andReturn(false);

        $actual = $this->page()->action_links([], '', []);

        $this->assertSame([], $actual);
    }

    #[Test]
    #[Group('user-capability')]
    public function actionLinksShouldPrependAdditionalLinks()
    {
        Functions\when('current_user_can')->justReturn(true);
        Functions\when('menu_page_url')->alias(static function () {
            return \admin_url('plugins.php?page=blank-option');
        });

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
    #[Group('admin-page')]
    public function loadHookShouldDoingNothingOnEmptyScreen()
    {
        Functions\expect('get_current_screen')->once()->andReturnNull();
        Functions\expect('esc_url')->never();

        $this->page()->load();
    }

    #[Test]
    #[Group('admin-page')]
    public function loadHookShouldRegisterContextualHelpTabs()
    {
        Functions\expect('get_current_screen')->once()->andReturnUsing(function () {
            $mock = mock(WP_Screen::class);

            $mock->shouldReceive('add_help_tab')->once();
            $mock->shouldReceive('set_help_sidebar')->once();

            return $mock;
        });

        Actions\expectAdded('admin_enqueue_scripts')->once()->whenHappen(function ($callback) {
            $this->assertIsArray($callback);

            $this->assertInstanceOf(Blank_Page::class, $callback[0]);
            $this->assertSame('enqueue_scripts', $callback[1]);
        });

        $this->page()->load();
    }

    #[Test]
    #[Group('admin-page')]
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
    #[Group('admin-page')]
    public function renderShouldPrintOutputToPluginScreen()
    {
        $this->expectOutputString(implode("\n", [
            '<div class="wrap">',
            '<h1 class="wp-heading-inline">Blank Option</h1> <!-- .wp-heading-inline -->',
            '<hr class="wp-header-end" />',
            '<div class="inner">',
            '</div> <!-- .inner -->',
            '</div> <!-- .wrap -->'
        ]));

        Functions\when('wp_kses_post')->returnArg(1);
        Functions\when('wp_kses')->returnArg(1);
        Functions\expect('get_admin_page_title')->once()->andReturn('Blank Option');

        $this->page()->render();
    }
}
