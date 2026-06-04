<?php
/**
 * Blank Option
 *
 * @package projek-xyz/wp-blank-option
 * @copyright Copyright (c) 2026 Fery Wardiyanto <https://feryardiant.id>
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 *
 * @wordpress-plugin
 * Plugin Name: Blank Option
 * Description: Something awesome is about to come.
 * Update URI: https://projek-xyz.github.io/wp-env
 * Text Domain: blank-option
 * Domain Path: /languages
 * Version: 0.0.2
 * Tested up to: 7.0
 * Requires at least: 6.0
 * Requires PHP: 8.2
 * Author: Fery Wardiyanto
 * Author URI: https://feryardiant.id
 * License: GPLv3 or later
 */

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
Plugin::check_requirements( 'PHP', PHP_VERSION, '8.2' );

/**
 * Check if the version of WordPress in use on the site is supported.
 */
Plugin::check_requirements( 'WordPress', $GLOBALS['wp_version'], '6.0' );

if ( ! Plugin::is_met_requirements() ) {
	return;
}

/**
 * Perform actions on plugin activation.
 */
\register_activation_hook( BLANK_OPTION_FILE, array( \Blank_Option\Installer::class, 'activate' ) );

/**
 * Perform actions on plugin deactivation.
 */
\register_deactivation_hook( BLANK_OPTION_FILE, array( \Blank_Option\Installer::class, 'deactivate' ) );

/**
 * Perform actions on plugin initialization.
 */
\add_action( 'init', array( Plugin::class, 'init' ) );
