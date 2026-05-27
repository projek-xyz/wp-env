<?php
/**
 * Plugin class.
 *
 * @package projek-xyz/wp-blank-option
 * @copyright Copyright (c) 2026 Fery Wardiyanto <https://feryardiant.id>
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 */

declare( strict_types = 1 );

namespace Blank_Option;

use Blank_Option\Admin\Blank_Page;
use WP_Filesystem_Base;
use WP_Filesystem_Direct;

defined( 'ABSPATH' ) || exit;

/**
 * Class Plugin.
 *
 * @internal
 */
class Plugin {
	/**
	 * Plugin basename.
	 *
	 * @var string
	 */
	public readonly string $basename;

	/**
	 * Plugin directory path.
	 *
	 * @var string
	 */
	public readonly string $directory_path;

	/**
	 * Plugin directory URL.
	 *
	 * @var string
	 */
	public readonly string $directory_url;

	/**
	 * List of registered admin pages.
	 *
	 * @var array
	 */
	private array $admin_pages = array();

	/**
	 * Plugin data.
	 *
	 * @var array<string, string>
	 */
	private array $data = array();

	/**
	 * Map of asset paths to their URL and version.
	 *
	 * @var array<string, array<string, string>>
	 */
	private array $asset_map = array();

	/**
	 * Plugin assets directory relative to the plugin directory, default: 'assets'.
	 *
	 * @var string
	 */
	private string $asset_dir = 'assets';

	/**
	 * Instance of the WordPress filesystem.
	 *
	 * @var WP_Filesystem_Direct|null
	 */
	private ?WP_Filesystem_Direct $filesystem = null;

	/**
	 * Whether the plugin meets the required version of during initiation.
	 *
	 * @var bool
	 */
	private static bool $is_met_requirements = true;

	/**
	 * Singleton instance of the Plugin.
	 *
	 * @var Plugin|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * Whether the plugin meets the required version of during initiation.
	 *
	 * @return bool
	 */
	public static function is_met_requirements(): bool {
		return self::$is_met_requirements;
	}

	/**
	 * Checks if the plugin meets the required version of during initiation.
	 *
	 * @param 'PHP'|'WordPress' $requirement The requirement to check.
	 * @param string            $current     The current version of the plugin.
	 * @param string            $required The required version of the plugin.
	 * @return void
	 */
	public static function check_requirements( string $requirement, string $current, string $required ) {
		if ( version_compare( $current, $required, '<' ) ) {
			self::$is_met_requirements = false;

			\add_action(
				'admin_notices',
				static function () use ( $current, $required, $requirement ) {
					$screens = array( 'plugins', 'plugins-network', 'update-core', 'update-core-network' );

					if ( ! self::is_within_screens( ...$screens ) ) {
						return;
					}

					$plugin = get_plugin_data( BLANK_OPTION_FILE );

					$message = sprintf(
						// Translators: %1$s is the plugin name, %2$s is the requirement, %3$s is the required version, %4$s is the current version.
						\__( 'The <strong>%1$s</strong> plugin requires at least version <strong>%3$s</strong> of <strong>%2$s</strong>, currently you have <strong>%4$s</strong>.', 'blank-option' ),
						$plugin['Name'],
						$requirement,
						$required,
						$current,
					);

					printf(
						'<div class="notice notice-error is-dismissible"><p>%s</p></div>',
						\wp_kses( $message, array( 'strong' => array() ) )
					);
				}
			);
		}
	}

	/**
	 * Perform actions on plugin activation.
	 *
	 * @return void
	 */
	public static function activate(): void {
		$plugin = static::instance();

		$plugin->upgrade();

		new Option( $plugin );

		do_action( 'blank_option_activate' );
	}

	/**
	 * Perform actions on plugin deactivation.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		do_action( 'blank_option_deactivate' );
	}

	/**
	 * Perform actions on plugin initialization.
	 *
	 * @return void
	 */
	public static function init(): void {
		$plugin = new Plugin( BLANK_OPTION_FILE );

		/**
		 * Enqueue scripts and styles.
		 */
		\add_action( 'wp_enqueue_scripts', array( $plugin, 'enqueue_scripts' ) );

		/**
		 * Register the admin menu.
		 */
		$plugin->add_admin_page( new Blank_Page( $plugin ) );
	}

	/**
	 * Retrieves the singleton instance of the Plugin class.
	 *
	 * @return static
	 */
	final public static function instance(): static {
		if ( ! self::$instance ) {
			self::$instance = new Plugin( BLANK_OPTION_FILE );
		}

		return self::$instance;
	}

	/**
	 * Constructs the Plugin instance.
	 *
	 * @param string $file The path to the plugin file.
	 * @return void
	 */
	public function __construct(
		public readonly string $file,
	) {
		$this->basename       = \plugin_basename( $file );
		$this->directory_path = \plugin_dir_path( $file );
		$this->directory_url  = \plugin_dir_url( $file );

		/**
		 * The key is what provided by `get_plugin_data()`.
		 *
		 * @see \get_plugin_data()
		 */
		$data_map = array(
			'Name'            => 'name',
			'PluginURI'       => 'plugin_uri',
			'Version'         => 'version',
			'Description'     => 'description',
			'Author'          => 'author',
			'AuthorURI'       => 'author_uri',
			'TextDomain'      => 'text_domain',
			'DomainPath'      => 'domain_path',
			'Network'         => 'network',
			'RequiresWP'      => 'requires_wp',
			'RequiresPHP'     => 'requires_php',
			'UpdateURI'       => 'update_uri',
			'RequiresPlugins' => 'requires_plugins',
		);

		foreach ( \get_plugin_data( $file ) as $key => $value ) {
			if ( $map = $data_map[ $key ] ?? null ) {
				$this->data[ $map ] = $value;
			}
		}

		$json = $this->get_file_contents( 'composer.json' );
		$data = $json ? json_decode( $json ?: array(), true ) : array();

		$this->data['supports'] = $data['support'] ?? array();

		self::$instance = $this;
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @return void
	 */
	public function enqueue_scripts(): void {
		$css = $this->get_asset_url( 'blank.css' );
		$js  = $this->get_asset_url( 'blank.js' );

		$domain = $this->get( 'text_domain' );

		\wp_enqueue_style( $domain . '-style', $css['url'], array(), $css['version'] );
		\wp_enqueue_script( $domain . '-script', $js['url'], array(), $js['version'], array( 'strategy' => 'defer' ) );
	}

	/**
	 * Retrieve plugin metadata based on given key.
	 *
	 * @param 'name'|'plugin_uri'|'version'|'description'|'text_domain'|'domain_path'|'network'|'requires_wp'|'requires_php'|'update_uri'|'requires_plugins'|'supports' $key Plugin metadata key.
	 * @return ($key is 'supports' ? array : non-empty-string)
	 */
	public function get( string $key ): array|string {
		if ( ! isset( $this->data[ $key ] ) || empty( $this->data[ $key ] ) ) {
			throw new \InvalidArgumentException(
				\wp_kses( "Unknown plugin metadata: $key", array() )
			);
		}

		return $this->data[ $key ];
	}

	/**
	 * Perform upgrade actions when necessary.
	 *
	 * @return void
	 */
	public function upgrade(): void {
		$option = \get_option( $this->get( 'text_domain' ) );

		if ( ! $option || ! is_array( $option ) ) {
			// Not installed, skipping.
			return;
		}

		$new_version = $this->get( 'version' );

		if ( ! version_compare( $new_version, $option['version'], '>' ) ) {
			return;
		}

		\do_action( 'blank_option_upgrade', $option['version'], $new_version );
	}

	/**
	 * Register an admin page.
	 *
	 * @param Admin_Page $page The admin page instance.
	 * @return void
	 */
	public function add_admin_page( Admin_Page $page ): void {
		/**
		 * Register the admin menu.
		 */
		\add_action( 'admin_menu', array( $page, 'menu' ) );

		if ( method_exists( $page, 'action_links' ) ) {
			/**
			 * Register the action links.
			 */
			\add_filter( 'plugin_action_links_' . $this->basename, array( $page, 'action_links' ), 10, 4 );
		}

		$this->admin_pages[ $page::class ] = $page;
	}

	/**
	 * Get the plugin directory path.
	 *
	 * @param string ...$paths Path segments to append.
	 * @return string
	 * @throws \InvalidArgumentException If the path is not found.
	 */
	public function directory_path( string ...$paths ): string {
		$rel_path = implode( '/', array_filter( $paths ) );
		$realpath = realpath( "$this->directory_path/$rel_path" );

		if ( $realpath ) {
			return \wp_normalize_path( $realpath );
		}

		throw new \InvalidArgumentException(
			\wp_kses( "Path not found: $rel_path", array() )
		);
	}

	/**
	 * Get the plugin directory URL.
	 *
	 * @param string ...$paths Path segments to append.
	 * @return string
	 */
	public function directory_url( string ...$paths ): string {
		$paths = array_merge( array( $this->directory_url ), $paths );

		return implode( '/', array_filter( $paths ) );
	}

	/**
	 * Set new assets directory relative to the plugin directory.
	 *
	 * @param string $dir The assets directory path.
	 * @return void
	 */
	public function set_asset_dir( string $dir ): void {
		$this->asset_dir = $dir;
	}

	/**
	 * Retrieve the URL for a plugin asset.
	 *
	 * @param string                     $path Path to the asset.
	 * @param 'dir'|'url'|'version'|null $key  Optional. The key to retrieve from the asset array.
	 * @return ($key is string ? string : array)
	 * @throws \InvalidArgumentException If an invalid key is provided.
	 */
	public function get_asset_url( string $path, ?string $key = null ): string|array {
		$asset = $this->asset_map[ $path ] ?? array();

		if ( ! empty( $asset ) ) {
			if ( $key ) {
				if ( ! in_array( $key, array( 'dir', 'url', 'version' ), true ) ) {
					throw new \InvalidArgumentException(
						\wp_kses( "Invalid key: $key, expected 'dir', 'url' or 'version'", array() )
					);
				}

				return $asset[ $key ];
			}

			return $asset;
		}

		$asset = array(
			'dir'     => $this->directory_path( $this->asset_dir, $path ),
			'url'     => $this->directory_url( $this->asset_dir, $path ),
			'version' => $this->get( 'version' ),
		);

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$asset['version'] .= '-' . ( (string) filemtime( $asset['dir'] ) );
		}

		$this->asset_map[ $path ] = $asset;

		return $this->get_asset_url( $path, $key );
	}

	/**
	 * Get the content of a file from the plugin directory.
	 *
	 * @param string $path The path to the file relative to the plugin directory.
	 * @return string|false The content of the file.
	 */
	public function get_file_contents( string $path ): string|false {
		return $this->filesystem()->get_contents( $this->directory_path( $path ) );
	}

	/**
	 * Check the current screen.
	 *
	 * @param string ...$screens The desired screen IDs to check against.
	 * @return bool
	 */
	public static function is_within_screens( string ...$screens ): bool {
		if ( ! $screen = \get_current_screen() ) {
			return false;
		}

		return in_array( $screen->id, $screens, true );
	}

	/**
	 * Check if the version of WordPress in under debug mode.
	 *
	 * @codeCoverageIgnore
	 * @return bool
	 */
	public static function is_debug(): bool {
		return defined( 'WP_DEBUG' ) && boolval( WP_DEBUG );
	}

	/**
	 * Retrieve the filesystem instance.
	 *
	 * @internal
	 * @return WP_Filesystem_Direct
	 */
	private function filesystem(): WP_Filesystem_Direct {
		if ( $this->filesystem ) {
			return $this->filesystem;
		}

		$fs_classes = array(
			WP_Filesystem_Base::class   => 'class-wp-filesystem-base.php',
			WP_Filesystem_Direct::class => 'class-wp-filesystem-direct.php',
		);

		foreach ( $fs_classes as $class => $file ) {
			if ( ! class_exists( $class ) ) {
				require_once ABSPATH . "wp-admin/includes/$file";
			}
		}

		return $this->filesystem = new WP_Filesystem_Direct( 1 );
	}
}
