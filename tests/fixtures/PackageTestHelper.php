<?php

declare(strict_types=1);

namespace Fixtures;

/**
 * Package test helper class.
 *
 * @mixin \PHPUnit\Framework\TestCase
 */
trait PackageTestHelper
{
    /**
     * @var array{version:string, type:string, entrypoint:string, path:string, url:string|null}
     */
    private static array $packageData = [];

    /**
     * @var array<string, array{name: string, file: string, path: string}>
     */
    private static array $availablePlugins = [];

    /**
     * Gets the package name from the subclass `PACKAGE_NAME` constant.
     *
     * @return non-empty-string|null The package name or null if not defined.
     */
    final protected static function packageName(): ?string
    {
        return defined(static::class . '::PACKAGE_NAME') ? static::PACKAGE_NAME : null;
    }

    /**
     * @param null|'version'|'type'|'path'|'url'|'entrypoint' $key
     * @return ($key is null
     *          ? array{version:string, type:string, entrypoint:string, path:string, url:string|null}
     *          : string|null)
     */
    final protected static function package(?string $key = null)
    {
        if (null === $key) {
            return static::$packageData;
        }

        return static::$packageData[$key] ?? null;
    }

    /**
     * Gets the absolute path to a file within the packages directory.
     *
     * @param non-empty-string ...$paths The relative path to the file from the packages directory.
     *
     * @return non-empty-string The absolute path to the file.
     */
    final protected static function packagePath(string ...$paths): string
    {
        return static::package('path') . '/' . implode('/', $paths);
    }

    /**
     * Handles package discovery by reading package.json and composer.json,
     * determines the package type, and triggers the package-specific autoloading logic.
     *
     * @throws \PHPUnit\Framework\ExpectationFailedException If package metadata files are missing.
     */
    protected static function setUpBeforePackage(): void
    {
        if (! ($name = static::packageName())) {
            return;
        }

        self::prepareAvailablePlugins();

        $path = BASE_PATH . '/packages/' . $name;

        [$packageJson, $composerJson] = array_map(
            static function ($file) use ($path) {
                static::assertNotFalse(
                    $metadata = realpath("$path/$file.json"),
                    "Failed to locate $file.json in $path"
                );

                return json_decode(file_get_contents($metadata));
            },
            ['package', 'composer']
        );

        $type = $composerJson->type ?? null;

        if ($type && str_contains($type, 'wordpress-')) {
            $type = substr($type, 10);
        }

        static::$packageData = [
            'version' => $packageJson->version,
            'type' => $type,
            'path' => $path,
            'url' => in_array($type, ['plugin', 'theme'], true)
                ? sprintf('http://example.com/wp-content/%ss/%s', $type, $name)
                : null,
            'entrypoint' => match ($type) {
                'plugin' => "$path/$name.php",
                'theme' => "$path/functions.php",
                default => null,
            },
        ];
    }

    private static function prepareAvailablePlugins(): void
    {
        if (! empty(self::$availablePlugins)) {
            return;
        }

        self::$availablePlugins = array_reduce(
            glob(ABSPATH . 'wp-content/plugins/*', GLOB_ONLYDIR) ?: [],
            static function ($out, $path) {
                $name = basename($path);
                $plugin = [];

                foreach (glob("$path/*.php") as $filepath) {
                    if (false === ($fd = fopen($filepath, 'r'))) {
                        continue;
                    }

                    $filename = basename($filepath);
                    $header = fread($fd, 320);

                    if (1 !== preg_match('/Plugin Name: *(?<name>[^\r\n]+)/', $header, $matches)) {
                        fclose($fd);
                        continue;
                    }

                    $plugin['name'] = trim($matches['name']);
                    $plugin['file'] = "$name/$filename";
                    $plugin['path'] = $path;

                    fclose($fd);

                    break;
                }

                if (!empty($plugin)) {
                    $out[$name] = $plugin;
                }

                return $out;
            },
            []
        );
    }

    /**
     * Retrieve data from available third-party plugin.
     *
     * @param string $slug Plugin slug.
     * @param null|'name'|'file'|'path' $key Data key.
     * @return ($key is string ? string|null : array|null)
     */
    final protected function getAvailablePlugin(string $slug, ?string $key = null): string|array|null
    {
        if (! isset(self::$availablePlugins[$slug])) {
            return null;
        }

        if (null === $key) {
            return self::$availablePlugins[$slug];
        }

        return self::$availablePlugins[$slug][$key] ?? null;
    }

    /**
     * Handles common package environment setup.
     *
     * @param string $name Package name.
     * @param string $path Package path.
     * @param string|null $url Package url.
     * @param string|null $version Package version.
     */
    protected function preparePackage(string $name, string $path, ?string $url, ?string $version): void
    {
        // doing nothing.
    }

    /**
     * Define package metadata.
     *
     * @return array<string, mixed>
     */
    protected function packageMetadata(): array
    {
        return [];
    }
}
