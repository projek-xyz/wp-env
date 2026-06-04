<?php
/**
 * Theme autoloader following WordPress coding standards.
 *
 * This follows a specific convention where namespaces are converted to directory
 * paths and class names are converted to lowercase with hyphens, prefixed with 'class-'.
 *
 * @package projek-xyz/wp-blank-option
 * @copyright Copyright (c) 2026 Fery Wardiyanto <https://feryardiant.id>
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 */

declare( strict_types = 1 );

namespace Blank_Option;

defined( 'ABSPATH' ) || exit;

spl_autoload_register(
	/**
	 * Automatically loads classes based on their namespace.
	 *
	 * @param class-string $class_name The fully-qualified class name.
	 */
	static function ( string $class_name ) {
		$namespace = __NAMESPACE__ . '\\';

		// Only handle classes within our namespace.
		if ( ! str_starts_with( $class_name, $namespace ) ) {
			return; // @codeCoverageIgnore
		}

		$class_name = substr( $class_name, strlen( $namespace ) );

		// Convert namespace separators and underscores to directory separators and hyphens.
		$pathname = str_replace(
			array( '\\', '_' ),
			array( DIRECTORY_SEPARATOR, '-' ),
			strtolower( $class_name )
		);

		$dirname  = dirname( $pathname );
		$filename = basename( $pathname );

		// Construct the final file path.
		if ( $file = realpath( __DIR__ . "/{$dirname}/class-{$filename}.php" ) ) {
			require_once $file;
		}
	}
);

( static function ( ?string $plugin_dir ) {
	if ( ! $plugin_dir ) {
		return; // @codeCoverageIgnore
	}

	$dirs = array( $plugin_dir, dirname( $plugin_dir, 2 ) );

	// Check for Composer autoloader in both the theme root and the project root.
	foreach ( $dirs as $dir ) {
		if ( $file = realpath( $dir . '/vendor/autoload.php' ) ) {
			require_once $file;
			break;
		}
	}

	if ( ! function_exists( 'get_plugin_data' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php'; // @codeCoverageIgnore
	}
} )( defined( 'BLANK_OPTION_DIR' ) ? BLANK_OPTION_DIR : null );
