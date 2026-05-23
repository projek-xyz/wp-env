<?php

declare(strict_types=1);

namespace UnitTests\BlankOption\Includes;

use Blank_Option\Admin;
use Blank_Option\Plugin;
use Brain\Monkey\Functions;
use PHPUnit\Framework\Attributes\RunClassInSeparateProcess;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use WP_Screen;

/**
 * Unit tests for the blank's `includes/class-admin.php`.
 */
#[RunClassInSeparateProcess]
class AdminTest extends TestCase
{
    protected static bool $loadAutoloader = true;

    #[Test]
    public function noticeShouldDoingNothingWhenNoScreenDefined()
    {
        Functions\expect('get_current_screen')->once()->andReturnNull();

        Admin::notice('unknown');
    }

    #[Test]
    public function noticeShouldDoingNothingOnUndesiredScreen()
    {
        Functions\expect('get_current_screen')->once()->andReturn((object) ['id' => 'undesired']);

        Admin::notice('unknown');
    }

    #[Test]
    public function noticeShouldDoingNothingOnUnknownType()
    {
        Functions\expect('get_current_screen')->once()->andReturn((object) ['id' => 'plugins']);
        Functions\expect('wp_kses')->never();

        Admin::notice('unknown');
    }

    #[Test]
    public function phpRequirementNoticeShouldPrintTheOutputProperly()
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        $this->expectOutputString(implode('', [
            '<div class="notice notice-error is-dismissible">',
            sprintf(
                '<p><strong>Blank WordPress Plugin</strong> requires at least version <strong>%s</strong> of <strong>PHP</strong> and has been paused.</p>',
                Plugin::MINIMUM_PHP_VERSION
            ),
            '</div>'
        ]));
        // phpcs:enable Generic.Files.LineLength.TooLong

        Functions\expect('get_current_screen')->once()->andReturn((object) ['id' => 'plugins']);
        Functions\expect('wp_kses')->once()->andReturnFirstArg();

        Admin::notice('php');
    }

    #[Test]
    public function wpRequirementNoticeShouldPrintTheOutputProperly()
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        $this->expectOutputString(implode('', [
            '<div class="notice notice-error is-dismissible">',
            sprintf(
                '<p><strong>Blank WordPress Plugin</strong> requires at least version <strong>%s</strong> of <strong>WordPress</strong> and has been paused.</p>',
                Plugin::MINIMUM_WP_VERSION
            ),
            '</div>'
        ]));
        // phpcs:enable Generic.Files.LineLength.TooLong

        Functions\expect('get_current_screen')->once()->andReturn((object) ['id' => 'plugins']);
        Functions\expect('wp_kses')->once()->andReturnFirstArg();

        Admin::notice('wp');
    }

    #[Test]
    public function shouldNotEnqueueAdminScriptsOnOtherAdminScreens()
    {
        Functions\expect('wp_enqueue_style')->never();
        Functions\expect('wp_enqueue_script')->never();

        Admin::enqueue_scripts('other-admin-screen');
    }

    #[Test]
    public function shouldAbleToEnqueueAdminScriptsOnPluginScreen()
    {
        Functions\expect('wp_enqueue_style')->once()->andReturnUsing(
            function (string $handle, string $src, array $deps, string $version) {
                $this->assertSame('blank-option-admin-style', $handle);
                $this->assertSame(Plugin::VERSION, $version);
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
                $this->assertSame(Plugin::VERSION, $version);
                $this->assertEmpty($deps);
                $this->assertSame(
                    'http://example.com/wp-content/plugins/blank-option/assets/admin.blank.js',
                    $src,
                );
            }
        );

        Admin::enqueue_scripts('plugins_page_blank-option');
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
                $this->assertSame(Admin::class, $callback[0]);
                $this->assertSame('render', $callback[1]);
            }
        );

        Admin::menu();
    }

    #[Test]
    public function actionLinksShouldReturnEmptyOnEmptyScreen()
    {
        Functions\expect('current_user_can')->once()->andReturn(false);

        $actual = Admin::action_links([], '', []);

        $this->assertSame([], $actual);
    }

    #[Test]
    public function actionLinksShouldPrependAdditionalLinks()
    {
        Functions\when('current_user_can')->justReturn(true);

        $actualLinks = Admin::action_links([], '', []);

        $this->assertSame([
            '<a href="http://example.com/wp-admin/plugins.php?page=blank-option">Settings</a>'
        ], $actualLinks);

        $actualLinks = Admin::action_links([], '', [
            'PluginURI' => 'http://example.com/support'
        ]);

        $this->assertSame([
            '<a href="http://example.com/wp-admin/plugins.php?page=blank-option">Settings</a>',
            '<a href="http://example.com/support">Supports</a>',
        ], $actualLinks);
    }

    #[Test]
    public function loadHookShouldDoingNothingOnEmptyScreen()
    {
        Functions\expect('get_current_screen')->once()->andReturnNull();
        Functions\expect('esc_url')->never();

        Admin::load();
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

        Admin::load();
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

        $this->mockStaticMethods(Plugin::class, [
            'get_file_contents' => fn ($called) => $called->once()
                ->with('composer.json')
                ->andReturn(json_encode(['support' => $support])),
        ]);

        Admin::load();
    }
}
