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
abstract class Admin_Page {
	/**
	 * Admin page menu slug.
	 *
	 * @var string
	 */
	protected string $menu_slug = '';

	/**
	 * Initialize the admin page.
	 *
	 * @param Plugin $plugin The plugin instance.
	 * @return void
	 */
	final public function __construct(
		protected readonly Plugin $plugin
	) {
		// .
	}

	/**
	 * Admin page load functionalities.
	 *
	 * @return void
	 */
	public function load(): void {
		$screen = \get_current_screen();

		if ( ! $screen ) {
			return;
		}

		foreach ( $this->help_tabs() as $id => $args ) {
			$screen->add_help_tab( array_merge( array( 'id' => $id ), $args ) );
		}

		if ( $sidebar_content = $this->help_sidebar() ) {
			$screen->set_help_sidebar( $sidebar_content );
		}
	}

	/**
	 * Register the admin menu.
	 *
	 * @return void
	 */
	abstract public function menu(): void;

	/**
	 * List of help tabs available for this particular admin page.
	 *
	 * The list consists of an associative array of tab IDs and their corresponding content.
	 *
	 * @see \WP_Screen::add_help_tab()
	 * @return array<string, array<string, string>>
	 */
	abstract protected function help_tabs(): array;

	/**
	 * The content that will be shown in the help sidebar.
	 *
	 * @see \WP_Screen::set_help_sidebar()
	 * @return ?string
	 */
	abstract protected function help_sidebar(): ?string;

	/**
	 * Render the admin page.
	 *
	 * @return void
	 */
	abstract public function render(): void;

	/**
	 * Admin URL for the page.
	 *
	 * @param array $query The query arguments to add to the URL.
	 */
	public function get_url( array $query = array() ): string {
		return \add_query_arg(
			$query,
			\menu_page_url( $this->menu_slug, false )
		);
	}

	/**
	 * Format array to paragraphs.
	 *
	 * @param string ...$lines The paragraphs to format.
	 * @return string
	 */
	protected function to_paragraphs( string ...$lines ): string {
		return implode(
			'',
			array_map(
				static fn ( $sentence ) => "<p>$sentence</p>",
				$lines,
			)
		);
	}
}
