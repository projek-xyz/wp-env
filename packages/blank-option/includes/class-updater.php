<?php
/**
 * Updater class.
 *
 * @package projek-xyz/wp-blank-option
 * @copyright Copyright (c) 2026 Fery Wardiyanto <https://feryardiant.id>
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 */

declare( strict_types = 1 );

namespace Blank_Option;

/**
 * Class Updater.
 *
 * @internal
 */
class Updater {
	/**
	 * Initialize updater class.
	 *
	 * @param Plugin $plugin The plugin instance.
	 */
	public function __construct(
		private Plugin $plugin
	) {
		// .
	}
	/**
	 * Checks if a update is available.
	 *
	 * This method filters the update transient to include custom updates
	 * from a remote manifest file.
	 *
	 * @param array|false $update      The update transient data.
	 * @param array       $plugin_data Information about the current plugin.
	 * @param string      $plugin_file The stylesheet name of the plugin being checked.
	 *
	 * @return array|false Updated transient data with update metadata if a new version is available,
	 *                     otherwise the original transient data.
	 */
	public function check_updates(
		array|false $update,
		array $plugin_data,
		string $plugin_file,
	): array|false {
		// Only handle our custom plugin.
		if ( $this->plugin->basename !== $plugin_file ) {
			return $update;
		}

		$release = $this->get_updates();

		// Check if remote version is newer than current version.
		if ( ! $release || version_compare( $release->version, $plugin_data['Version'], '<=' ) ) {
			return $update;
		}

		// Return the update metadata for WordPress to handle the update process.
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
	 * Uses site transients to cache the results for 12 hours to minimize external HTTP requests.
	 *
	 * @return object|false The update metadata object on success, or false if the update information
	 *                      could not be retrieved or is unavailable.
	 */
	public function get_updates(): object|false {
		$cache_key   = $this->plugin->get( 'text_domain' ) . '_updates';
		$cached_data = \get_site_transient( $cache_key );

		// Return cached data if available.
		if ( ! empty( $cached_data ) ) {
			return $cached_data;
		}

		// Fetch the latest release from our release manifest.
		$response = \wp_remote_get(
			'https://projek-xyz.github.io/wp-env/release.json',
			array(
				'timeout' => 10,
				'headers' => array( 'Accept' => 'application/json' ),
			)
		);

		// Handle fetch errors or non-200 responses.
		if ( is_wp_error( $response ) || 200 !== \wp_remote_retrieve_response_code( $response ) ) {
			\set_site_transient( $cache_key, false, \HOUR_IN_SECONDS );

			return false;
		}

		$data = json_decode( \wp_remote_retrieve_body( $response ) );
		$slug = $this->plugin->get( 'text_domain' );

		// Check if our specific plugin exists in the flat manifest.
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

		// Cache the response data for 12 hours.
		\set_site_transient( $cache_key, $update, 12 * \HOUR_IN_SECONDS );

		return $update;
	}
}
