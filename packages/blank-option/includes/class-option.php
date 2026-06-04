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
	 * Option domain.
	 *
	 * @var string
	 */
	private string $domain;

	/**
	 * Initialize the plugin option.
	 *
	 * @param Plugin $plugin The plugin instance.
	 * @return void
	 */
	public function __construct( Plugin $plugin ) {
		$this->domain = $plugin->get( 'text_domain' );
	}

	/**
	 * Returns debug information for the HTML instance.
	 *
	 * @codeCoverageIgnore
	 */
	public function __debugInfo(): array {
		return $this->all();
	}

	/**
	 * Get an option value.
	 *
	 * @param string     $name Option name.
	 * @param mixed|null $default Default value if option is not set.
	 * @return mixed
	 */
	public function get( string $name, mixed $default = null ): mixed {
		if ( isset( self::$cached[ $name ] ) ) {
			return self::$cached[ $name ];
		}

		$options = $this->all();

		if ( ! isset( $options[ $name ] ) ) {
			return $default;
		}

		return self::$cached[ $name ] = $options[ $name ];
	}

	/**
	 * Set an option value.
	 *
	 * @param string $name Option name.
	 * @param mixed  $value Option value.
	 * @return void
	 */
	public function set( string $name, mixed $value ): void {
		$options = $this->all();

		$options[ $name ] = $value;

		if ( isset( self::$cached[ $name ] ) ) {
			unset( self::$cached[ $name ] );
		}

		\update_option( $this->domain, $options );
	}

	/**
	 * Whether the plugin option is not exists in database.
	 *
	 * @return bool
	 */
	public function is_empty(): bool {
		return empty( $this->all() );
	}

	/**
	 * Retrieve all option values.
	 *
	 * @return array
	 */
	private function all(): array {
		$option = \get_option( $this->domain, array() );

		return $option ?? array();
	}
}
