<?php
/**
 * Membership Manager
 *
 * Handles processes related to memberships.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Membership_Manager
 * @since 2.0.0
 */

namespace WP_Ultimo\Managers;

use WP_Ultimo\Managers\Base_Manager;
use WP_Ultimo\Logger;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles processes related to memberships.
 *
 * @since 2.0.0
 */
class Membership_Manager extends Base_Manager {

	use \WP_Ultimo\Apis\Rest_Api, \WP_Ultimo\Apis\WP_CLI, \WP_Ultimo\Traits\Singleton;

	/**
	 * The manager slug.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $slug = 'membership';

	/**
	 * The model class associated to this manager.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $model_class = '\\WP_Ultimo\\Models\\Membership';

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		$this->enable_rest_api();

		$this->enable_wp_cli();

		add_action('wu_async_transfer_membership', array($this, 'async_transfer_membership'), 10, 2);
		add_action('wu_async_delete_membership', array($this, 'async_delete_membership'), 10);

		/**
		 * Transitions
		 */
		add_action('wu_transition_membership_status', array($this, 'mark_cancelled_date'), 10, 3);

		add_action('wu_transition_membership_status', array($this, 'transition_membership_status'), 10, 3);

	} // end init;

	/**
	 * Watches the change in payment status to take action when needed.
	 *
	 * @todo Publishing sites should be done in async.
	 *
	 * @since 2.0.0
	 *
	 * @param string  $old_status The old status of the membership.
	 * @param string  $new_status The new status of the membership.
	 * @param integer $membership_id Payment ID.
	 * @return void
	 */
	public function transition_membership_status($old_status, $new_status, $membership_id) {

		$allowed_previous_status = array(
			'pending',
			'on-hold',
		);

		if (!in_array($old_status, $allowed_previous_status, true)) {

			return;

		} // end if;

		if ($new_status !== 'active') {

			return;

		} // end if;

		/*
		 * Create pending sites.
		 */
		$membership = wu_get_membership($membership_id);

		$pending_site = $membership->get_pending_site();

		if (is_a($pending_site, '\WP_Ultimo\Models\Site')) {

			$saved = $pending_site->save();

			if (!is_wp_error($saved)) {

				$membership->delete_pending_site();

				/*
				 * Trigger event that marks the publication of a site.
				 */
				do_action('wu_pending_site_published', $pending_site, $membership);

			} // end if;

		} // end if;

	} // end transition_membership_status;

	/**
	 * Mark the membership date of cancellation.
	 *
	 * @since 2.0.0
	 *
	 * @param string $old_value Old status value.
	 * @param string $new_value New status value.
	 * @param int    $item_id The membership id.
	 * @return void
	 */
	public function mark_cancelled_date($old_value, $new_value, $item_id) {

		if ($new_value === 'cancelled' && $new_value !== $old_value) {

			$membership = wu_get_membership($item_id);

			$membership->set_date_cancellation(current_time('mysql'));

			$membership->save();

		} // end if;

	} // end mark_cancelled_date;

	/**
	 * Transfer a membership from a user to another.
	 *
	 * @since 2.0.0
	 *
	 * @param int $membership_id The ID of the membership being transfered.
	 * @param int $target_customer_id The new owner.
	 * @return mixed
	 */
	public function async_transfer_membership($membership_id, $target_customer_id) {

		global $wpdb;

		$membership = wu_get_membership($membership_id);

		$target_customer = wu_get_customer($target_customer_id);

		if (!$membership || !$target_customer || $membership->get_customer_id() == $target_customer->get_id()) {

			return new \WP_Error('error', __('An unexpected error happened.', 'wp-ultimo'));

		} // end if;

		$wpdb->query('START TRANSACTION');

		try {
			/*
			 * Get Sites and move them over.
			 */
			$sites = wu_get_sites(array(
				'meta_query' => array(
					'membership_id' => array(
						'key'   => 'wu_membership_id',
						'value' => $membership->get_id(),
					),
				),
			));

			foreach ($sites as $site) {

				$site->set_customer_id($target_customer_id);

				$saved = $site->save();

				if (is_wp_error($saved)) {

					$wpdb->query('ROLLBACK');

					return $saved;

				} // end if;

			} // end foreach;

			/*
			 * Change the membership
			 */
			$membership->set_customer_id($target_customer_id);

			$saved = $membership->save();

			if (is_wp_error($saved)) {

				$wpdb->query('ROLLBACK');

				return $saved;

			} // end if;

		} catch (\Throwable $e) {

			$wpdb->query('ROLLBACK');

			return new \WP_Error('exception', $e->getMessage());

		} // end try;

		$wpdb->query('COMMIT');

		return true;

	} // end async_transfer_membership;

	/**
	 * Delete a membership.
	 *
	 * @since 2.0.0
	 *
	 * @param int $membership_id The ID of the membership being deleted.
	 * @return mixed
	 */
	public function async_delete_membership($membership_id) {

		global $wpdb;

		$membership = wu_get_membership($membership_id);

		if (!$membership) {

			return new \WP_Error('error', __('An unexpected error happened.', 'wp-ultimo'));

		} // end if;

		$wpdb->query('START TRANSACTION');

		try {
			/*
			 * Get Sites and delete them.
			 */
			$sites = wu_get_sites(array(
				'meta_query' => array(
					'membership_id' => array(
						'key'   => 'wu_membership_id',
						'value' => $membership->get_id(),
					),
				),
			));

			foreach ($sites as $site) {

				$saved = $site->delete();

				if (is_wp_error($saved)) {

					$wpdb->query('ROLLBACK');

					return $saved;

				} // end if;

			} // end foreach;

			/*
			 * Delete the membership
			 */
			$saved = $membership->delete();

			if (is_wp_error($saved)) {

				$wpdb->query('ROLLBACK');

				return $saved;

			} // end if;

		} catch (\Throwable $e) {

			$wpdb->query('ROLLBACK');

			return new \WP_Error('exception', $e->getMessage());

		} // end try;

		$wpdb->query('COMMIT');

		return true;

	} // end async_delete_membership;

} // end class Membership_Manager;
