<?php
/**
 * This follows a specific convention where namespaces are converted to directory
 * paths and class names are converted to lowercase with hyphens, prefixed with 'class-'.
 *
 * @package projek-xyz/wp-custom-theme
 * @copyright Copyright (c) 2026 Fery Wardiyanto <https://feryardiant.id>
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 */

declare( strict_types = 1 );

namespace Custom_Theme;

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
			return;
		}

		$class_name = substr( $class_name, strlen( $namespace ) );

		// Convert namespace separators and underscores to directory separators and hyphens.
		$pathname = str_replace(
			array( '\\', '_' ),
			array( '/', '-' ),
			strtolower( $class_name )
		);

		$dirname  = dirname( $pathname );
		$filename = basename( $pathname );

		// Construct the final file path.
		$file = __DIR__ . "/{$dirname}/class-{$filename}.php";

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
);

$dirs = array(
	$theme_dir = \get_stylesheet_directory(),
	dirname( dirname( $theme_dir ) ),
);

foreach ( $dirs as $dir ) {
	if ( file_exists( $dir . '/vendor/autoload.php' ) ) {
		require_once $dir . '/vendor/autoload.php';
		break;
	}
}
