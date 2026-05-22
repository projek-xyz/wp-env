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

		$data = \get_option( Plugin::BASE_NAME, array() );

		if ( false === $data || ! isset( $data[ $name ] ) ) {
			return $default;
		}

		return self::$cached[ $name ] = $data[ $name ];
	}

	/**
	 * Set an option value.
	 *
	 * @param string $name Option name.
	 * @param mixed  $value Option value.
	 * @return void
	 */
	public static function set( string $name, mixed $value ): void {
		$old_data = \get_option( Plugin::BASE_NAME, array() );

		if ( false === $old_data ) {
			$old_data = array();
		}

		$old_data[ $name ] = $value;

		unset( self::$cached[ $name ] );

		\update_option( Plugin::BASE_NAME, $old_data );
	}
}
