<?php
/**
 * Blank Option for Nothing
 *
 * @package projek-xyz/wp-blank-option
 * @copyright Copyright (c) 2026 Fery Wardiyanto <https://feryardiant.id>
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 *
 * @wordpress-plugin
 * Plugin Name: Blank Option for Nothing
 * Description: Something awesome is about to come.
 * Text Domain: blank-option
 * Domain Path: /languages
 * Version: 0.0.1
 * Tested up to: 7.0
 * Requires at least: 6.0
 * Requires PHP: 8.2
 * Author: Fery Wardiyanto
 * Author URI: https://feryardiant.id
 * License: GPLv3 or later
 */

use Blank_Option\Admin;
use Blank_Option\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin version.
 *
 * @var string
 */
define( 'BLANK_VERSION', '0.0.1' );

/**
 * Plugin directory path.
 *
 * @var string
 */
define( 'BLANK_OPTION_DIR', __DIR__ );

/**
 * Plugin file path.
 *
 * @var string
 */
define( 'BLANK_OPTION_FILE', __FILE__ );

require_once BLANK_OPTION_DIR . '/includes/autoload.php';

/**
 * Check if the version of PHP in use on the site is supported.
 */
if ( Plugin::is_unmet_php_requirements() ) {
	/**
	 * Display an admin notice if the PHP version is too low.
	 */
	\add_action( 'admin_notices', static fn () => Admin::notice( 'php' ) );

	return;
}

/**
 * Check if the version of WordPress in use on the site is supported.
 */
if ( Plugin::is_unmet_wp_requirements() ) {
	/**
	 * Display an admin notice if the WordPress version is too low.
	 */
	\add_action( 'admin_notices', static fn () => Admin::notice( 'wp' ) );

	return;
}

/**
 * Perform actions on plugin activation.
 */
\register_activation_hook( BLANK_OPTION_FILE, array( Plugin::class, 'activate' ) );

/**
 * Perform actions on plugin deactivation.
 */
\register_deactivation_hook( BLANK_OPTION_FILE, array( Plugin::class, 'deactivate' ) );

/**
 * Perform actions on plugin initialization.
 */
\add_action( 'init', array( Plugin::class, 'init' ) );
