<?php
/**
 * Local custom theme
 *
 * @package projek-xyz/wp-custom-theme
 * @copyright Copyright (c) 2026 Fery Wardiyanto <https://feryardiant.id>
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 */

namespace Custom_Theme;

defined( 'CUSTOM_THEME_VERSION' ) || define( 'CUSTOM_THEME_VERSION', '0.0.1' );

require_once __DIR__ . '/includes/autoload.php';

add_action( 'wp_enqueue_scripts', array( Theme::class, 'enqueue_scripts' ) );

/**
 * Trigger custom theme activation hook.
 */
add_action( 'after_switch_theme', array( Theme::class, 'activation' ) );

/**
 * Trigger custom theme deactivation hook.
 */
add_action( 'switch_theme', array( Theme::class, 'deactivation' ) );

/**
 * Check if theme update is available.
 */
add_filter(
	'update_themes_projek-xyz.github.io',
	array( Theme::class, 'check_updates' ),
	10,
	3
);
