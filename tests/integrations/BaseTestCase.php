<?php

declare(strict_types=1);

namespace IntegrationTests;

use Fixtures\PackageTestHelper;
use Override;

/**
 * Base Test Case for integration tests using real WordPress core.
 */
abstract class BaseTestCase extends \WP_UnitTestCase_Base
{
    use PackageTestHelper;

    /**
     * List of activated third-party plugins during tests.
     *
     * @var string[]
     */
    private array $activatedPlugins = [];

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function expectDeprecated()
    {
        // This method is just overriding parent's one
        // by removing the use of `PHPUnit\Util\Test::parseTestMethodAnnotations`
        // And keep the rest

        \add_action('deprecated_function_run', [$this, 'deprecated_function_run'], 10, 3);
        \add_action('deprecated_argument_run', [$this, 'deprecated_function_run'], 10, 3);
        \add_action('deprecated_class_run', [$this, 'deprecated_function_run'], 10, 3);
        \add_action('deprecated_file_included', [$this, 'deprecated_function_run'], 10, 4);
        \add_action('deprecated_hook_run', [$this, 'deprecated_function_run'], 10, 4);
        \add_action('doing_it_wrong_run', [$this, 'doing_it_wrong_run'], 10, 3);

        \add_action('deprecated_function_trigger_error', '__return_false');
        \add_action('deprecated_argument_trigger_error', '__return_false');
        \add_action('deprecated_class_trigger_error', '__return_false');
        \add_action('deprecated_file_trigger_error', '__return_false');
        \add_action('deprecated_hook_trigger_error', '__return_false');
        \add_action('doing_it_wrong_trigger_error', '__return_false');
    }

    public static function set_up_before_class(): void // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    {
        parent::set_up_before_class();

        static::setUpBeforePackage();
    }

    /**
     * {@inheritdoc}
     */
    public function set_up(): void // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    {
        parent::set_up();

        \wp_set_current_user(1);

        if ($name = static::packageName()) {
            $pkg = static::package();

            if ('theme' === $pkg['type']) {
                $this->prepareTheme($name, $pkg['path'], $pkg['url'], $pkg['version']);
            }

            if ('plugin' === $pkg['type']) {
                $this->preparePlugin($name, $pkg['path'], $pkg['url'], $pkg['version']);
            }
        }
    }

    /**
    * {@inheritdoc}
    */
    public function tear_down(): void // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    {
        parent::tear_down();

        $plugins = array_map(
            fn ($name) => $this->getAvailablePlugin($name, 'file'),
            $this->activatedPlugins
        );

        \deactivate_plugins(array_filter($plugins));
    }

    /**
     * Handles theme-specific environment setup.
     *
     * @param string $name Theme name.
     * @param string $path Theme path.
     * @param string|null $url Theme url.
     * @param string|null $version Theme version.
     */
    private function prepareTheme(
        string $name,
        string $path,
        ?string $url,
        ?string $version
    ): void {
        static::assertFileExists(
            "$path/functions.php",
            sprintf('Theme functions.php not found: %s', $name)
        );

        $this->preparePackage($name, $path, $url, $version);
    }

    /**
     * Handles plugin-specific environment setup.
     *
     * @param string $name Plugin name.
     * @param string $path Plugin path.
     * @param string|null $url Plugin url.
     * @param string|null $version Plugin version.
     */
    private function preparePlugin(
        string $name,
        string $path,
        ?string $url,
        ?string $version
    ): void {
        static::assertFileExists(
            "$path/$name.php",
            sprintf('Plugin %1$s.php not found: %1$s', $name)
        );

        $this->preparePackage($name, $path, $url, $version);
    }

    /**
     * Activate third-party plugin.
     *
     * @param string $name
     * @return void
     * @throws \RuntimeException
     */
    final protected function activatePlugin(string $name): void
    {
        if (! $plugin = $this->getAvailablePlugin($name, 'file')) {
            return;
        }

        $resuls = \activate_plugin($plugin);

        if ($resuls instanceof \WP_Error) {
            $code = array_key_first($resuls->errors);
            $message = $resuls->errors[$code][0];

            throw new \RuntimeException(
                sprintf('Unable to activate plugin "%s" due to %s: %s', $name, $code, $message)
            );
        }

        $this->activatedPlugins[] = $name;
    }
}
