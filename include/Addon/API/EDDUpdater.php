<?php namespace EmailLog\Addon\API;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	$email_log = email_log();
	require_once $email_log->get_plugin_path() . 'include/libraries/EDD_SL_Plugin_Updater.php';
}

/**
 * Update add-on using EDD API.
 *
 * @since 2.0.0
 */
class EDDUpdater extends \EDD_SL_Plugin_Updater {

	/**
	 * Add-on slug.
	 * The base class already has a slug property but it is private.
	 * So we have to create a duplicate to handle that.
	 *
	 * @var string
	 */
	protected $addon_slug;

	/**
	 * Extract add-on slug alone and then pass everything to parent.
	 *
	 * @param string     $_api_url    The URL pointing to the custom API endpoint.
	 * @param string     $_plugin_file Path to the plugin file.
	 * @param array|null $_api_data   Optional data to send with API calls.
	 */
	public function __construct( $_api_url, $_plugin_file, $_api_data = null ) {
		$this->addon_slug = basename( $_plugin_file, '.php' );

		parent::__construct( $_api_url, $_plugin_file, $_api_data );
	}

	/**
	 * Get add-on slug.
	 *
	 * @return string Add-on slug.
	 */
	public function get_slug() {
		return $this->addon_slug;
	}

	/**
	 * Get Download URL.
	 * We can't call `api_request` method directly since it is declared as private in parent class.
	 * So we call the `plugins_api_filter` method instead.
	 *
	 * @return string Download url.
	 */
	public function get_download_url() {
		$args = new \stdClass();
		$args->slug = $this->addon_slug;

		$response = $this->plugins_api_filter( null, 'plugin_information', $args );

		if ( ! $response instanceof \stdClass || ! property_exists( $response, 'package' ) ) {
			return '';
		}

		return $response->package;
	}
}
