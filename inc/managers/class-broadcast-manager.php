<?php
/**
 * Broadcast Manager
 *
 * Handles processes related to products.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Broadcast_Manager
 * @since 2.0.0
 */

namespace WP_Ultimo\Managers;

use WP_Ultimo\Managers\Base_Manager;
use WP_Ultimo\Models\Broadcast;
use WP_Ultimo\Helpers\Sender;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles processes related to products.
 *
 * @since 2.0.0
 */
class Broadcast_Manager extends Base_Manager {

	use \WP_Ultimo\Apis\Rest_Api, \WP_Ultimo\Apis\WP_CLI, \WP_Ultimo\Traits\Singleton;

	/**
	 * The manager slug.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $slug = 'broadcast';

	/**
	 * The model class associated to this manager.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $model_class = '\\WP_Ultimo\\Models\\Broadcast';

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		$this->enable_rest_api();

		$this->enable_wp_cli();

		/**
		 * Add unseen broadcast notices to the panel.
		 */
		if (!is_network_admin() && !is_main_site()) {

			add_action('init', array($this, 'add_unseen_broadcast_notices'));

		} // end if;

	} // end init;

	/**
	 * Add unseen broadcast messages.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_unseen_broadcast_notices() {

		$current_customer = wu_get_current_customer();

		if (!$current_customer) {

			return;

		} // end if;

		$all_broadcasts = Broadcast::query(array(
			'number'   => 10,
			'order'    => 'DESC',
			'order_by' => 'id',
			'type__in' => array('broadcast_notice'),
		));

		if (isset($all_broadcasts)) {

			foreach ($all_broadcasts as $key => $broadcast) {

				if (isset($broadcast)) {

					$targets = $this->get_all_notice_customer_targets($broadcast->get_id());

					if (!is_array($targets)) {

						$targets = array($targets);

					} // end if;

					$dismissed = get_user_meta(get_current_user_id(), 'wu_dismissed_admin_notices');

					if (in_array($current_customer->get_id(), $targets, true) && !in_array($broadcast->get_id(), $dismissed, true)) {

						$notice = '<span><strong>' . $broadcast->get_title() . '</strong> ' . $broadcast->get_content() . '</span>';

						WP_Ultimo()->notices->add($notice, $broadcast->get_notice_type(), 'admin', strval($broadcast->get_id()));

						WP_Ultimo()->notices->add($notice, $broadcast->get_notice_type(), 'user', strval($broadcast->get_id()));

					} // end if;

				} // end if;

			} // end foreach;

		} // end if;

	} // end add_unseen_broadcast_notices;

	/**
	 * Handles the broadcast message send via modal.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function handle_broadcast() {

		$args = $_POST;

		$target_customers = wu_request('target_customers', '');

		$target_products = wu_request('target_products', '');

		if (!$target_customers && !$target_products) {

			wp_send_json_error(new \WP_Error('error', __('No product or customer target was selected.', 'wp-ultimo')));

		} // end if;

		$broadcast_type = wu_request('type', 'broadcast_notice');

		$args['type'] = $broadcast_type;

		if ($broadcast_type === 'broadcast_notice') {

			$targets = array(
				'customers' => $target_customers,
				'products'  => $target_products
			);

			$args['targets'] = $targets;

			// then we save with the message status (success, fail)
			$saved = $this->save_broadcast($args);

			if (is_wp_error($saved)) {

				wp_send_json_error($saved);

			} // end if;

			wp_send_json_success(array(
				'redirect_url' => add_query_arg('id', $saved->get_id(), wu_network_admin_url('wp-ultimo-edit-broadcast'))
			));

		} // end if;

		if ($args['type'] === 'broadcast_email') {

			$to = array();

			$bcc = array();

			$targets = array();

			if ($args['target_customers']) {

				$customers = explode(',', $args['target_customers']);

				$targets = array_merge($targets, $customers);

			} // end if;

			if ($args['target_products']) {

				$product_targets = explode(',', $args['target_products']);

				$targets = array_merge($targets, $product_targets);

			} // end if;

			$targets = array_unique($targets);

			/**
			 * Get name and email based on user id
			 */
			foreach ($targets as $target) {

				$customer = wu_get_customer($target);

				if ($customer) {

					$to[] = array(
						'name'  => $customer->get_display_name(),
						'email' => $customer->get_email_address(),
					);

				} // end if;

			} // end foreach;

			if (!isset($args['custom_sender'])) {

				$from = array(
					'name'  => wu_get_setting('from_name', get_network_option(null, 'site_name')),
					'email' => wu_get_setting('from_email', get_network_option(null, 'admin_email')),
				);

			} else {

				$from = array(
					'name'  => $args['custom_sender']['from_name'],
					'email' => $args['custom_sender']['from_email'],
				);

			} // end if;

			$template_type = wu_get_setting('email_template_type', 'html');

			$template_type = $template_type ? $template_type : 'html';

			$send_args = array(
				'site_name' => get_network_option(null, 'site_name'),
				'site_url'  => get_site_url(),
				'type'      => $template_type,
				'subject'   => $args['subject'],
				'content'   => $args['content'],
			);

			try {

				$status = Sender::send_mail($from, $to, $send_args);

			} catch (\Throwable $e) {

				$error = new \WP_Error($e->getCode(), $e->getMessage());

				wp_send_json_error($error);

			} // end try;

			if ($status) {

				$args['targets'] = array(
					'customers' => $args['target_customers'],
					'products'  => $args['target_products'],
				);

				// then we save with the message status (success, fail)
				$this->save_broadcast($args);

				wp_send_json_success(array(
					'redirect_url' => wu_network_admin_url('wp-ultimo-broadcasts')
				));

			} // end if;

		} // end if;

		$error = new \WP_Error('mail-error', __('Something wrong happened.', 'wp-ultimo'));

		wp_send_json_error($error);

	} // end handle_broadcast;

	/**
	 * Saves the broadcast message in the database
	 *
	 * @since 2.0.0
	 *
	 * @param array $args With the message arguments.
	 * @return Broadcast|\WP_Error
	 */
	public function save_broadcast($args) {

		$broadcast_data = array(
			'type'    => $args['type'],
			'name'    => $args['subject'],
			'content' => $args['content'],
			'status'  => 'publish',
		);

		$broadcast = new Broadcast($broadcast_data);

		if ($args['type'] === 'broadcast_notice') {

			$broadcast->set_notice_type($args['notice_type']);

		} // end if;

		$broadcast->set_message_targets($args['targets']);

		$saved = $broadcast->save();

		return is_wp_error($saved) ? $saved : $broadcast;

	} // end save_broadcast;

	/**
	 * Returns targets for a specific broadcast.
	 *
	 * @since 2.0.0
	 *
	 * @param string $object_id The broadcast object id.
	 * @param string $type The broadcast target type.
	 * @return string Return the broadcast targets for the specific type.
	 */
	public function get_broadcast_targets($object_id, $type) {

		$broadcast = Broadcast::get_by_id($object_id);

		$targets = $broadcast->get_message_targets();

		if (isset($targets[$type][0])) {

			return $targets[$type][0];

		} // end if;

	} // end get_broadcast_targets;

	/**
	 * Returns all customer from targets.
	 *
	 * @since 2.0.0
	 *
	 * @param string $object_id The broadcast object id.
	 * @return array Return the broadcast targets for the specific type.
	 */
	public function get_all_notice_customer_targets($object_id) {

		$customers_targets = explode(',', $this->get_broadcast_targets($object_id, 'customers'));

		$products = explode(',', $this->get_broadcast_targets($object_id, 'products'));

		$product_customers = array();

		if (is_array($products) && $products[0]) {

			foreach ($products as $product_key => $product) {

				$membership_customers = wu_get_membership_customers($product);

				if ($membership_customers) {

					if (is_array($membership_customers)) {

						$product_customers = array_merge($membership_customers, $product_customers);

					} else {

						array_push($product_customers, $membership_customers);

					} // end if;

				} // end if;

			} // end foreach;

		} // end if;

		if (isset($product_customers) ) {

			$targets = array_merge($product_customers, $customers_targets);

		} else {

			$targets = $customers_targets;

		} // end if;

		return array_map('absint', array_filter(array_unique($targets)));

	} // end get_all_notice_customer_targets;

} // end class Broadcast_Manager;
