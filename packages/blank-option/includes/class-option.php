<?php
/**
 * Option class.
 *
 * @package projek-xyz/wp-blank-option
 * @copyright Copyright (c) 2026 Fery Wardiyanto <https://feryardiant.id>
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 */

declare( strict_types = 1 );

namespace Blank_Option;

defined( 'ABSPATH' ) || exit;

/**
 * Class Option.
 */
final class Option {
	/**
	 * Cached option values.
	 *
	 * @var array
	 */
	private static array $cached = array();

	/**
	 * Option key.
	 *
	 * @var string
	 */
	private static string $key;

	/**
	 * Get an option value.
	 *
	 * @param string     $name Option name.
	 * @param mixed|null $default Default value if option is not set.
	 * @return mixed
	 */
	public static function get( string $name, mixed $default = null ): mixed {
		if ( isset( self::$cached[ $name ] ) ) {
			return self::$cached[ $name ];
		}

		$option = self::all();

		if ( ! isset( $option[ $name ] ) ) {
			return $default;
		}

		return self::$cached[ $name ] = $option[ $name ];
	}

	/**
	 * Set an option value.
	 *
	 * @param string $name Option name.
	 * @param mixed  $value Option value.
	 * @return void
	 */
	public static function set( string $name, mixed $value ): void {
		$option = self::all();

		$option[ $name ] = $value;

		if ( isset( self::$cached[ $name ] ) ) {
			unset( self::$cached[ $name ] );
		}

		\update_option( self::$key, $option );
	}

	/**
	 * Retrieve all option values.
	 *
	 * @return array
	 */
	private static function all(): array {
		$option = \get_option( self::$key, array() );

		return $option ?? array();
	}

	/**
	 * Initialize the plugin option.
	 *
	 * @param Plugin $plugin The plugin instance.
	 * @return void
	 */
	public function __construct( Plugin $plugin ) {
		self::$key = $plugin->get( 'text_domain' );
	}
}
