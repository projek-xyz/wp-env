<?php
/**
 * Plugin class.
 *
 * @package projek-xyz/wp-blank-option
 * @copyright Copyright (c) 2026 Fery Wardiyanto <https://feryardiant.id>
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 */

declare( strict_types = 1 );

namespace Blank_Option;

defined( 'ABSPATH' ) || exit;

class Installer {
	public function __construct( private Plugin $plugin ) {}

	public static function activate(): void {
		$plugin = Plugin::instance();
		$self   = new self( $plugin );
		$self->upgrade();
		new Option( $plugin );
		\do_action( 'blank_option_activate' );
	}

	public static function deactivate(): void {
		\do_action( 'blank_option_deactivate' );
	}

	public function upgrade(): void {
		$domain          = $this->plugin->get( 'text_domain' );
		$option          = \get_option( $domain );
		$current_version = $this->plugin->get( 'version' );

		if ( ! $option || ! is_array( $option ) ) {
			\update_option( $domain, array( 'version' => $current_version ) );
			return;
		}

		if ( ! version_compare( $current_version, $option['version'], '>' ) ) {
			return;
		}

		\do_action( 'blank_option_upgrade', $option['version'], $current_version );

		$option['version'] = $current_version;
		\update_option( $domain, $option );
	}

	public function check_updates( array|false $update, array $plugin_data, string $plugin_file ): array|false {
		if ( $this->plugin->basename !== $plugin_file ) {
			return $update;
		}
		$release = $this->get_updates();
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

	public function get_updates(): object|false {
		$cache_key   = $this->plugin->get( 'text_domain' ) . '_updates';
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
		$slug = $this->plugin->get( 'text_domain' );
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
