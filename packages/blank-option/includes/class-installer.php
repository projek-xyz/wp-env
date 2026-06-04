<?php
/**
 * Installer class.
 *
 * @package projek-xyz/wp-blank-option
 * @copyright Copyright (c) 2026 Fery Wardiyanto <https://feryardiant.id>
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 */

declare( strict_types = 1 );

namespace Blank_Option;

defined( 'ABSPATH' ) || exit;

/**
 * Class Installer.
 *
 * @internal
 */
class Installer {
	/**
	 * Perform actions on plugin activation.
	 *
	 * @return void
	 */
	public static function activate(): void {
		$plugin = Plugin::instance();

		self::upgrade( $plugin );

		\do_action( 'blank_option_activate' );
	}

	/**
	 * Perform actions on plugin deactivation.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		\do_action( 'blank_option_deactivate' );
	}

	/**
	 * Perform upgrade actions when necessary.
	 *
	 * @param Plugin $plugin Instance of plugin class.
	 * @return void
	 */
	public static function upgrade( Plugin $plugin ): void {
		$current_version = $plugin->get( 'version' );
		$option          = $plugin->option;

		if ( $option->is_empty() ) {
			$option->set( 'version', $current_version );
			return;
		}

		$installed_version = $option->get( 'version' );

		if ( ! version_compare( $current_version, $installed_version, '>' ) ) {
			return;
		}

		\do_action( 'blank_option_upgrade', $installed_version, $current_version );

		$option->set( 'version', $current_version );
	}

	/**
	 * Checks if a update is available.
	 *
	 * @param array|false $update      The update transient data.
	 * @param array       $plugin_data Information about the current plugin.
	 * @param string      $plugin_file The stylesheet name of the plugin being checked.
	 * @return array|false Updated transient data with update metadata.
	 */
	public static function check_updates( array|false $update, array $plugin_data, string $plugin_file ): array|false {
		$plugin = Plugin::instance();

		if ( $plugin->basename !== $plugin_file ) {
			return $update;
		}

		$release = self::get_updates( $plugin->get( 'text_domain' ) );

		if ( ! $release || version_compare( $release->version, $plugin_data['Version'], '<=' ) ) {
			return $update;
		}

		return array(
			'package'      => $release->download_url,
			'version'      => $release->version,
			'url'          => $release->info_url,
			'tested'       => $release->wp_version,
			'requires_php' => $release->php_version,
			'translations' => array(),
		);
	}

	/**
	 * Retrieves the latest update information from the remote repository.
	 *
	 * @param string $slug The plugin slug.
	 * @return object|false
	 */
	private static function get_updates( string $slug ): object|false {
		$cache_key   = $slug . '_updates';
		$cached_data = \get_site_transient( $cache_key );

		if ( ! empty( $cached_data ) ) {
			return $cached_data;
		}

		$response = \wp_remote_get(
			'https://projek-xyz.github.io/wp-env/release.json',
			array(
				'timeout' => 10,
				'headers' => array( 'Accept' => 'application/json' ),
			)
		);

		if ( \is_wp_error( $response ) || 200 !== \wp_remote_retrieve_response_code( $response ) ) {
			\set_site_transient( $cache_key, false, \HOUR_IN_SECONDS );
			return false;
		}

		$data = json_decode( \wp_remote_retrieve_body( $response ) );

		if ( ! isset( $data->$slug ) ) {
			\set_site_transient( $cache_key, false, \HOUR_IN_SECONDS );
			return false;
		}

		$update = (object) array(
			'info_url'     => $data->$slug->info_url ?? '',
			'tag_name'     => $data->$slug->tag_name ?? '',
			'version'      => $data->$slug->version ?? '',
			'download_url' => $data->$slug->download_url ?? '',
			'wp_version'   => $data->$slug->wp_version ?? '',
			'php_version'  => $data->$slug->php_version ?? '',
		);

		\set_site_transient( $cache_key, $update, 12 * \HOUR_IN_SECONDS );

		return $update;
	}
}
