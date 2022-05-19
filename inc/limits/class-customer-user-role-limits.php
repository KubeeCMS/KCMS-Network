<?php
/**
 * Handles limitations to the customer user role.
 *
 * @todo We need to move posts on downgrade.
 * @package WP_Ultimo
 * @subpackage Limits
 * @since 2.0.10
 */

namespace WP_Ultimo\Limits;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles limitations to the customer user role.
 *
 * @since 2.0.0
 */
class Customer_User_Role_Limits {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Runs on the first and only instantiation.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		add_action('wu_async_after_membership_update_products', array($this, 'update_site_user_roles'));

		add_filter('editable_roles', array($this, 'filter_editable_roles'));

		if (!wu_get_current_site()->has_module_limitation('customer_user_role')) {

			return;

		} // end if;

	} // end init;

	/**
	 * Filters editable roles offered as options on limitations.
	 *
	 * @since 2.0.10
	 *
	 * @param array $roles The list of available roles.
	 * @return array
	 */
	public function filter_editable_roles($roles) {

		if (!wu_get_current_site()->has_module_limitation('users')) {

			return $roles;

		} // end if;

		foreach ($roles as $role => $details) {

			if (!wu_get_current_site()->get_limitations()->users->{$role}->enabled) {

				unset($roles[$role]);

			} // end if;

		} // end foreach;

		return $roles;

	} // end filter_editable_roles;

	/**
	 * Updates the site user roles after a up/downgrade.
	 *
	 * @since 2.0.10
	 *
	 * @param int $membership_id The membership upgraded or downgraded.
	 * @return void
	 */
	public function update_site_user_roles($membership_id) {

		$membership = wu_get_membership($membership_id);

		if ($membership) {

			$customer = $membership->get_customer();

			if (!$customer) {

				return;

			} // end if;

			$sites = $membership->get_sites();

			$role = $membership->get_limitations()->customer_user_role->get_limit();

			foreach ($sites as $site) {

				add_user_to_blog($site->get_id(), $customer->get_user_id(), $role);

			} // end foreach;

		} // end if;

	} // end update_site_user_roles;

} // end class Customer_User_Role_Limits;
