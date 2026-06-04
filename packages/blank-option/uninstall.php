<?php
/**
 * Uninstaller file.
 *
 * @package projek-xyz/wp-blank-option
 * @copyright Copyright (c) 2026 Fery Wardiyanto <https://feryardiant.id>
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit; // @codeCoverageIgnoreStart

( static function (): void {
	if ( defined( 'BLANK_VERSION' ) ) {
		return;
	}

	\delete_option( 'blank-option' );
} )(); // @codeCoverageIgnoreEnd
