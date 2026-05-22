<?php
/**
 * Admin class.
 *
 * @package projek-xyz/wp-blank-option
 * @copyright Copyright (c) 2026 Fery Wardiyanto <https://feryardiant.id>
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 */

declare( strict_types = 1 );

namespace Blank_Option;

defined( 'ABSPATH' ) || exit;

/**
 * Class Admin.
 *
 * @internal
 */
final class Admin {
	private const SCREEN_IDS = array(
		'plugins',
		'plugins-network',
		'update-core',
		'update-core-network',
	);

	/**
	 * Display an admin notice for the given type.
	 *
	 * @param 'wp'|'php' $type The type of notice to display.
	 * @return void
	 */
	public static function notice( string $type ): void {
		if ( ! Plugin::is_within_screens( ...self::SCREEN_IDS ) ) {
			return;
		}

		$notices = array(
			'php' => sprintf(
				/* translators: %s: version of PHP required by Blank WordPress Plugin. */
				\__( '<strong>Blank WordPress Plugin</strong> requires at least version <strong>%s</strong> of <strong>PHP</strong> and has been paused.', 'blank-option' ),
				Plugin::MINIMUM_PHP_VERSION
			),
			'wp'  => sprintf(
				/* translators: %s: version of PHP required by Blank WordPress Plugin. */
				\__( '<strong>Blank WordPress Plugin</strong> requires at least version <strong>%s</strong> of <strong>WordPress</strong> and has been paused.', 'blank-option' ),
				Plugin::MINIMUM_WP_VERSION
			),
		);

		if ( ! isset( $notices[ $type ] ) ) {
			return;
		}

		echo '<div class="notice notice-error is-dismissible"><p>';

		echo \wp_kses( $notices[ $type ], array( 'strong' => array() ) );

		echo '</p></div>';
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @return void
	 */
	public static function enqueue_scripts(): void {
		$css = Plugin::asset( 'admin.blank.css' );
		$js  = Plugin::asset( 'admin.blank.js' );

		\wp_enqueue_style(
			Plugin::BASE_NAME . '-admin-style',
			$css['url'],
			array(),
			$css['version'],
		);

		\wp_enqueue_script(
			Plugin::BASE_NAME . '-admin-script',
			$js['url'],
			array(),
			$js['version'],
			array( 'strategy' => 'defer' )
		);
	}

	/**
	 * Register the admin menu.
	 *
	 * @return void
	 */
	public static function menu(): void {
		$page = \add_submenu_page(
			'plugins.php',
			__( 'Blank Options', 'blank-option' ),
			__( 'Blank Options', 'blank-option' ),
			'activate_plugins',
			'blank-option',
			array( self::class, 'render' ),
		);

		\add_action( 'load-' . $page, array( self::class, 'load' ) );
	}

	/**
	 * Admin page load functionalities.
	 *
	 * @return void
	 */
	public static function load(): void {
		$screen = get_current_screen();

		if ( ! $screen ) {
			return;
		}

		$screen->add_help_tab(
			array(
				'id'       => 'blank-option-help',
				'title'    => \__( 'Blank Help', 'blank-option' ),
				'content'  => self::to_paragraphs(
					\__( 'This is where I would provide tabbed help to the user on how everything in my admin panel works. Formatted HTML works fine in here too', 'blank-option' )
				),
				'callback' => false,
				'priority' => 10,
			)
		);

		$json = Plugin::get_file_contents( 'composer.json' );
		$data = json_decode( $json, true );

		$links = array(
			sprintf( '<strong>%s</strong>', \__( 'For more information:', 'blank-option' ) ),
		);

		foreach ( $data['support'] as $type => $url ) {
			if ( 'email' === $type ) {
				continue;
			}

			$links[] = sprintf(
				'<a href="%s" target="_blank">%s</a>',
				\esc_url( $url ),
				ucfirst( $type )
			);
		}

		$screen->set_help_sidebar( self::to_paragraphs( ...$links ) );
	}

	/**
	 * Render the admin page.
	 *
	 * @codeCoverageIgnore Not implemented just yet.
	 * @return void
	 */
	public static function render(): void {
		echo '<div class="wrap">';

		printf(
			'<h1 class="wp-heading-inline">%s</h1>',
			\wp_kses(
				get_admin_page_title(),
				array( 'strong' => array() )
			),
		);

		echo '<hr class="wp-header-end">';

		// Nothing.

		echo '<div class="clear"></div>';

		echo '</div>';
	}

	/**
	 * Format array to paragraphs.
	 *
	 * @param string ...$lines The paragraphs to format.
	 * @return string
	 */
	private static function to_paragraphs( string ...$lines ): string {
		return implode(
			'',
			array_map(
				static fn ( $sentence ) => "<p>$sentence</p>",
				$lines,
			)
		);
	}
}
