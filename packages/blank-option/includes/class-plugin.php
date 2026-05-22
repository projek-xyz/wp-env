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
	 * Plugin base name.
	 *
	 * @var string
	 */
	public const BASE_NAME = 'blank-option';

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public const VERSION = \BLANK_VERSION;

	/**
	 * Minimum required PHP version.
	 *
	 * @var string
	 */
	public const MINIMUM_PHP_VERSION = '8.1';

	/**
	 * Minimum required WordPress version.
	 *
	 * @var string
	 */
	public const MINIMUM_WP_VERSION = '6.0';

	/**
	 * Map of asset paths to their URL and version.
	 *
	 * @var array
	 */
	private static array $asset_map = array();

	/**
	 * Instance of the WordPress filesystem.
	 *
	 * @var WP_Filesystem_Direct|null
	 */
	private static ?WP_Filesystem_Direct $filesystem = null;

	/**
	 * Perform actions on plugin activation.
	 *
	 * @return void
	 */
	public static function activate(): void {
		static::upgrade();

		do_action( 'blank_option_activate' );
	}

	/**
	 * Perform actions on plugin deactivation.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		do_action( 'blank_option_deactivate' );

		// Clear any cached data that has been removed.
		wp_cache_flush();
	}

	/**
	 * Perform actions on plugin initialization.
	 *
	 * @return void
	 */
	public static function init(): void {
		/**
		 * Enqueue scripts and styles.
		 */
		add_action( 'enqueue_scripts', array( self::class, 'enqueue_scripts' ) );

		/**
		 * Enqueue admin scripts and styles.
		 */
		add_action( 'admin_enqueue_scripts', array( Admin::class, 'enqueue_scripts' ) );

		/**
		 * Register the admin menu.
		 */
		\add_action( 'admin_menu', array( Admin::class, 'menu' ) );
	}

	/**
	 * Perform upgrade actions when necessary.
	 *
	 * @return void
	 */
	public static function upgrade(): void {
		$old_version = Option::get( 'version', '0.0.0' );
		$new_version = self::VERSION;

		if ( ! version_compare( $new_version, $old_version, '>' ) ) {
			return;
		}

		\do_action( 'blank_option_upgrade', $old_version, $new_version );
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @return void
	 */
	public static function enqueue_scripts(): void {
		$css = static::asset( 'blank.css' );
		$js  = static::asset( 'blank.js' );

		\wp_enqueue_style(
			self::BASE_NAME . '-style',
			$css['url'],
			array(),
			$css['version'],
		);

		\wp_enqueue_script(
			self::BASE_NAME . '-script',
			$js['url'],
			array(),
			$js['version'],
			array( 'strategy' => 'defer' )
		);
	}

	/**
	 * Get the plugin directory path.
	 *
	 * @param string ...$paths Path segments to append.
	 * @return string
	 */
	public static function dir( string ...$paths ): string {
		$paths = array_merge( array( \BLANK_OPTION_DIR ), $paths );

		return implode( DIRECTORY_SEPARATOR, array_filter( $paths ) );
	}

	/**
	 * Get the plugin URL.
	 *
	 * @param string ...$paths Path segments to append.
	 * @return string
	 */
	public static function url( string ...$paths ): string {
		$paths = array_merge( array( \plugin_dir_url( \BLANK_OPTION_FILE ) ), $paths );

		return implode( '/', array_filter( $paths ) );
	}

	/**
	 * Retrieve the URL for a plugin asset.
	 *
	 * @param string                     $path Path to the asset.
	 * @param 'dir'|'url'|'version'|null $key  Optional. The key to retrieve from the asset array.
	 * @return ($key is string ? string : array)
	 * @throws \InvalidArgumentException If an invalid key is provided.
	 */
	public static function asset( string $path, ?string $key = null ): string|array {
		$asset = self::$asset_map[ $path ] ?? array();

		if ( ! empty( $asset ) ) {
			if ( $key ) {
				if ( ! in_array( $key, array( 'dir', 'url', 'version' ), true ) ) {
					throw new \InvalidArgumentException(
						\wp_kses( "Invalid key: $key, expected 'dir', 'url', or 'version'", array() )
					);
				}

				return $asset[ $key ];
			}

			return $asset;
		}

		$asset = array(
			'dir'     => self::dir( 'assets', $path ),
			'url'     => self::url( 'assets', $path ),
			'version' => self::VERSION,
		);

		if ( self::is_debug() ) {
			$filetime = (string) filemtime( $asset['dir'] );

			$asset['version'] .= '-' . $filetime;
		}

		self::$asset_map[ $path ] = $asset;

		return static::asset( $path, $key );
	}

	/**
	 * Get the content of a file from the plugin directory.
	 *
	 * @param string $path The path to the file relative to the plugin directory.
	 * @return string The content of the file.
	 */
	public static function get_file_contents( string $path ): string {
		return static::filesystem()->get_contents( static::dir( $path ) );
	}

	/**
	 * Retrieve the filesystem instance.
	 *
	 * @internal
	 * @return WP_Filesystem_Direct
	 */
	private static function filesystem(): WP_Filesystem_Direct {
		if ( self::$filesystem ) {
			return self::$filesystem;
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

		return self::$filesystem = new WP_Filesystem_Direct( 1 );
	}

	/**
	 * Check if the version of PHP in use on the site is supported.
	 *
	 * @return bool
	 */
	public static function is_unmet_php_requirements(): bool {
		return version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' );
	}

	/**
	 * Check if the version of WordPress in use on the site is supported.
	 *
	 * @return bool
	 */
	public static function is_unmet_wp_requirements(): bool {
		return version_compare( $GLOBALS['wp_version'], self::MINIMUM_WP_VERSION, '<' );
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
	 * @return bool
	 */
	public static function is_debug(): bool {
		return defined( 'WP_DEBUG' ) && boolval( WP_DEBUG );
	}
}
