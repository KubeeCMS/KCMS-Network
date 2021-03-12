<?php
/**
 * Customer Manager
 *
 * Handles processes related to Customers.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Customer_Manager
 * @since 2.0.0
 */

namespace WP_Ultimo\Managers;

use WP_Ultimo\Managers\Base_Manager;
use WP_Ultimo\Models\Customer;
use WP_Ultimo\Logger;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles processes related to webhooks.
 *
 * @since 2.0.0
 */
class Customer_Manager extends Base_Manager {

	use \WP_Ultimo\Apis\Rest_Api, \WP_Ultimo\Apis\WP_CLI, \WP_Ultimo\Traits\Singleton;

	/**
	 * The manager slug.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $slug = 'customer';

	/**
	 * The model class associated to this manager.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $model_class = '\\WP_Ultimo\\Models\\Customer';

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		$this->enable_rest_api();

		$this->enable_wp_cli();

		add_action('wp_login', array($this, 'log_ip_and_last_login'), 10, 2);

		add_filter('heartbeat_send', array($this, 'on_heartbeat_send'));

	} // end init;

	/**
	 * Handle heartbeat response sent.
	 *
	 * @since 2.0.0
	 *
	 * @param array $response The Heartbeat response.
	 * @return array $response The Heartbeat response
	 */
	public function on_heartbeat_send($response) {

		$this->log_ip_and_last_login(wp_get_current_user());

		return $response;

	} // end on_heartbeat_send;

	/**
	 * Saves the IP address and last_login date onto the user.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_User $user The WP User object of the user that logged in.
	 * @return void
	 */
	function log_ip_and_last_login($user) {

		if (!is_a($user, '\WP_User')) {

			$user = get_user_by('login', $user);

		} // end if;

		if (!$user) {

			return;

		} // end if;

		$customer = wu_get_customer_by_user_id($user->ID);

		if (!$customer) {

			return;

		} // end if;

		$geolocation = \WP_Ultimo\Geolocation::geolocate_ip('', true);

		$customer->add_ip($geolocation['ip']);

		$customer->update_meta('ip_country', $geolocation['country']);
		$customer->update_meta('ip_state', $geolocation['state']);

		$customer->attributes(array(
			'last_login' => current_time('mysql'),
		));

		$customer->save();

	} // end log_ip_and_last_login;

} // end class Customer_Manager;
