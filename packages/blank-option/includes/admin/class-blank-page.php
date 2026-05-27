<?php
/**
 * Admin class.
 *
 * @package projek-xyz/wp-blank-option
 * @copyright Copyright (c) 2026 Fery Wardiyanto <https://feryardiant.id>
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 */

declare( strict_types = 1 );

namespace Blank_Option\Admin;

use Blank_Option\Admin_Page;

defined( 'ABSPATH' ) || exit;

/**
 * Class Admin.
 *
 * @internal
 */
final class Blank_Page extends Admin_Page {
	/**
	 * Register the admin menu.
	 *
	 * @return void
	 */
	public function menu(): void {
		$page = \add_submenu_page(
			'plugins.php',
			\__( 'Blank Options', 'blank-option' ),
			\__( 'Blank Options', 'blank-option' ),
			'activate_plugins',
			$this->plugin->get( 'text_domain' ),
			array( $this, 'render' ),
		);

		if ( ! $page ) {
			return;
		}

		\add_action( 'load-' . $page, array( $this, 'load' ) );
	}

	/**
	 * Add custom action links to the plugin row.
	 *
	 * @param array  $actions The existing action links.
	 * @param string $_ The plugin file path.
	 * @param array  $plugin_data The plugin data.
	 * @return array The modified action links.
	 */
	public function action_links( array $actions, string $_, array $plugin_data ): array {
		if ( ! \current_user_can( 'activate_plugins' ) ) {
			return $actions;
		}

		$links = array();

		$links[] = sprintf(
			'<a href="%1$s">%2$s</a>',
			$this->get_url(),
			\esc_html__( 'Settings', 'blank-option' )
		);

		if ( ! empty( $plugin_data['PluginURI'] ) ) {
			$links[] = sprintf(
				'<a href="%1$s">%2$s</a>',
				\esc_url( $plugin_data['PluginURI'] ),
				\esc_html__( 'Supports', 'blank-option' )
			);
		}

		return array_merge( $links, $actions );
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $hook The current admin page hook.
	 * @return void
	 */
	public function enqueue_scripts( string $hook ): void {
		$text_domain = $this->plugin->get( 'text_domain' );

		if ( ! str_contains( $hook, $text_domain ) ) {
			return;
		}

		$css = $this->plugin->get_asset_url( 'admin.blank.css' );
		$js  = $this->plugin->get_asset_url( 'admin.blank.js' );

		\wp_enqueue_style(
			$text_domain . '-admin-style',
			$css['url'],
			array(),
			$css['version'],
		);

		\wp_enqueue_script(
			$text_domain . '-admin-script',
			$js['url'],
			array(),
			$js['version'],
			array( 'strategy' => 'defer' )
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function render(): void {
		echo '<div class="wrap">';

		printf(
			'<h1 class="wp-heading-inline">%s</h1>',
			\wp_kses( \get_admin_page_title(), array( 'strong' => array() ) )
		);

		echo '<hr class="wp-header-end">';

		// Nothing.

		echo '<div class="clear"></div>';

		echo '</div>';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function help_tabs(): array {
		return array(
			'blank-option-help' => array(
				'title'    => \__( 'Blank Help', 'blank-option' ),
				'content'  => $this->to_paragraphs(
					\__( 'This is where I would provide tabbed help to the user on how everything in my admin panel works. Formatted HTML works fine in here too', 'blank-option' )
				),
				'callback' => false,
				'priority' => 10,
			),
		);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function help_sidebar(): ?string {
		$supports = $this->plugin->get( 'supports' ) ?: array();
		$links    = array(
			sprintf( '<strong>%s</strong>', \__( 'For more information:', 'blank-option' ) ),
		);

		foreach ( $supports as $type => $url ) {
			if ( 'email' === $type ) {
				continue;
			}

			$links[] = sprintf(
				'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
				\esc_url( $url ),
				ucfirst( $type )
			);
		}

		return $this->to_paragraphs( ...$links );
	}

	/**
	 * Get the URL for the plugin settings page.
	 *
	 * @return string
	 */
	public function get_url(): string {
		$base_name = $this->plugin->get( 'text_domain' );

		return \esc_url( \admin_url( "plugins.php?page={$base_name}" ) );
	}
}
