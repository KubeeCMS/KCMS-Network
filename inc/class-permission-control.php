<?php
/**
 * WP Ultimo Permission Control
 *
 * @package WP_Ultimo
 * @subpackage Permission Control
 * @since 2.0.0
 */

namespace WP_Ultimo;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo Permission Control
 *
 * @since 2.0.0
 */
class Permission_Control {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * List of queried support agents.
	 *
	 * Here for performance reasons.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $support_agent = array();

	/**
	 * Keeps a cache of the capabilities being test.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $cap_cache = array();

	/**
	 * Keeps a cache of the caps retrieved by user.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $agent_cap_cache = array();

	/**
	 * Keeps a second cache of the caps retrieved by user.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $agent_cap_cache_2 = array();

	/**
	 * Constructor for the logger.
	 */
	public function __construct() {

		add_filter('wu_search_models_fuctions', array($this, 'add_function_search_models'));

		add_action('wu_selectize_templates', array($this, 'add_selectize_template'));

		add_filter('map_meta_cap', array($this, 'allow_capabilities'), 10, 4);

		add_action('admin_init', array($this, 'replace_freemius_menu_caps'), 999);

	} // end __construct;

	/**
	 * Replaces the default Freemius menu caps with a custom cap that we can manage.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function replace_freemius_menu_caps() {

		global $submenu;

		if (!isset($submenu['wp-ultimo'])) {

			return;

		} // end if;

		$freemius_submenus = array(
			'wp-ultimo-account',
			'wp-ultimo-pricing'
		);

		foreach ($submenu['wp-ultimo'] as &$_submenu) {

			if (!in_array($_submenu[2], $freemius_submenus, true)) {

				continue;

			} // end if;

			$_submenu[1] = 'wu_license';

		} // end foreach;

	} // end replace_freemius_menu_caps;

	/**
	 * Add the selectize template
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_selectize_template() {

		?>

		<!-- Support Agent Template -->
			<script type="text/html" id="wu-template-support-agent">

				<div class="wu-p-4 wu-block wu-items-center">

					<div>

						{{ typeof avatar !== 'undefined' ? avatar : '' }}

					</div>

					<div>

						<span class="wu-block">{{ display_name }} (#{{ id }})</span>

						<small>{{ user_email }}</small>

					</div>

				</div>

			</script>
		<!-- /Support Agent Template -->

		<?php

	} // end add_selectize_template;

	/**
	 * Add the search function
	 *
	 * @since 2.0.0
	 *
	 * @param array $functions Search Models Functions.
	 * @return array
	 */
	public function add_function_search_models($functions) {

		$functions[] = 'wu_get_support_agents';

		return $functions;

	} // end add_function_search_models;

	/**
	 * Returns the list of registered capabilities.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function registered_capabilities() {

		$capabilities = array();

		/*
		* Dashboard
		*/
		$capabilities['dashboard'] = array(
			'title'        => __('Dashboard', 'wp-ultimo'),
			'desc'         => __('Permissions related to the WP Ultimo dashboard.', 'wp-ultimo'),
			'capabilities' => array(
				'wu_read_dashboard' => array(
					'title' => __('View Dashboard', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to see the dashboard.', 'wp-ultimo'),
				),
				'wu_read_financial' => array(
					'title' => __('View Financial Data', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to view financial data on the dashboard.', 'wp-ultimo'),
				),
			),
		);

		/*
		* Checkout Forms
		*/
		$capabilities['checkout_forms'] = array(
			'title'        => __('Checkout Forms', 'wp-ultimo'),
			'desc'         => __('Permissions related to checkout forms on the platform.', 'wp-ultimo'),
			'capabilities' => array(
				'wu_read_checkout_forms'   => array(
					'title' => __('View Checkout Forms', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to see the checkout forms on the platform.', 'wp-ultimo'),
				),
				'wu_add_checkout_forms'    => array(
					'title' => __('Add New Checkout Forms', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to add a new checkout form on the platform.', 'wp-ultimo'),
				),
				'wu_edit_checkout_forms'   => array(
					'title' => __('Edit Checkout Forms', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to edit the checkout forms on the platform.', 'wp-ultimo'),
				),
				'wu_delete_checkout_forms' => array(
					'title' => __('Delete Checkout Forms', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to delete the checkout forms on the platform.', 'wp-ultimo'),
				),
				'wu_export_checkout_forms' => array(
					'title' => __('Export Checkout Forms', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to export the checkout forms on the platform.', 'wp-ultimo'),
				),
			),
		);

		/*
		* Products
		*/
		$capabilities['products'] = array(
			'title'        => __('Products', 'wp-ultimo'),
			'desc'         => __('Permissions related to products on the platform.', 'wp-ultimo'),
			'capabilities' => array(
				'wu_read_products'   => array(
					'title' => __('View Products', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to see the products on the platform.', 'wp-ultimo'),
				),
				'wu_edit_products'   => array(
					'title' => __('Edit Products', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to edit the products on the platform.', 'wp-ultimo'),
				),
				'wu_delete_products' => array(
					'title' => __('Delete Products', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to delete the products on the platform.', 'wp-ultimo'),
				),
				'wu_export_products' => array(
					'title' => __('Export Products', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to export the products on the platform.', 'wp-ultimo'),
				),
			),
		);

		/*
		* Memberships
		*/
		$capabilities['memberships'] = array(
			'title'        => __('Memberships', 'wp-ultimo'),
			'desc'         => __('Permissions related to memberships on the platform.', 'wp-ultimo'),
			'capabilities' => array(
				'wu_read_memberships'     => array(
					'title' => __('View Memberships', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to see the memberships on the platform.', 'wp-ultimo'),
				),
				'wu_add_memberships'    => array(
					'title' => __('Add New Memberships', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to add a new membership on the platform.', 'wp-ultimo'),
				),
				'wu_edit_memberships'     => array(
					'title' => __('Edit Memberships', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to edit the memberships on the platform.', 'wp-ultimo'),
				),
				'wu_transfer_memberships' => array(
					'title' => __('Transfer Memberships', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to transfer memberships between customers.', 'wp-ultimo'),
				),
				'wu_delete_memberships'   => array(
					'title' => __('Delete Memberships', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to delete the memberships on the platform.', 'wp-ultimo'),
				),
				'wu_export_memberships'   => array(
					'title' => __('Export Memberships', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to export the memberships on the platform.', 'wp-ultimo'),
				),
			),
		);

		/*
		* Payments
		*/
		$capabilities['payments'] = array(
			'title'        => __('Payments', 'wp-ultimo'),
			'desc'         => __('Permissions related to payments on the platform.', 'wp-ultimo'),
			'capabilities' => array(
				'wu_read_payments'   => array(
					'title' => __('View Payments', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to see the payments on the platform.', 'wp-ultimo'),
				),
				'wu_add_payments'    => array(
					'title' => __('Add New Payments', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to add a new payment on the platform.', 'wp-ultimo'),
				),
				'wu_edit_payments'   => array(
					'title' => __('Edit Payments', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to edit the payments on the platform.', 'wp-ultimo'),
				),
				'wu_refund_payments' => array(
					'title' => __('Refund Payments', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to refund the payments on the platform.', 'wp-ultimo'),
				),
				'wu_delete_payments' => array(
					'title' => __('Delete Payments', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to delete the payments on the platform.', 'wp-ultimo'),
				),
				'wu_export_payments' => array(
					'title' => __('Export Payments', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to export the payments on the platform.', 'wp-ultimo'),
				),
			),
		);

		/*
		* Customers
		*/
		$capabilities['customers'] = array(
			'title'        => __('Customer', 'wp-ultimo'),
			'desc'         => __('Permissions related to customers on the platform.', 'wp-ultimo'),
			'capabilities' => array(
				'wu_read_customers'   => array(
					'title' => __('View Customer', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to see the customers on the platform.', 'wp-ultimo'),
				),
				'wu_add_customers'    => array(
					'title' => __('Add New Customer', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to add a new customer on the platform.', 'wp-ultimo'),
				),
				'wu_edit_customers'   => array(
					'title' => __('Edit Customer', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to edit the customers on the platform.', 'wp-ultimo'),
				),
				'wu_invite_customers' => array(
					'title' => __('Invite Customer', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to invite customers to the platform.', 'wp-ultimo'),
				),
				'wu_delete_customers' => array(
					'title' => __('Delete Customer', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to delete the customers on the platform.', 'wp-ultimo'),
				),
				'wu_export_customers' => array(
					'title' => __('Export Customer', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to export the customers on the platform.', 'wp-ultimo'),
				),
			),
		);

		/*
		* Support Agents
		*/
		$capabilities['support_agents'] = array(
			'title'        => __('Support Agents', 'wp-ultimo'),
			'desc'         => __('Permissions related to support agents on the platform.', 'wp-ultimo'),
			'capabilities' => array(
				'wu_read_support_agents'   => array(
					'title' => __('View Support Agents', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to see the support agents on the platform.', 'wp-ultimo'),
				),
				'wu_add_support_agents'    => array(
					'title' => __('Add new Support Agents', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to edit the support agents on the platform.', 'wp-ultimo'),
				),
				'wu_edit_support_agents'   => array(
					'title' => __('Edit Support Agents', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to edit the support agents on the platform.', 'wp-ultimo'),
				),
				'wu_delete_support_agents' => array(
					'title' => __('Delete Support Agents', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to delete the support agents on the platform.', 'wp-ultimo'),
				),
				'wu_export_support_agents' => array(
					'title' => __('Export Support Agents', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to export the support agents on the platform.', 'wp-ultimo'),
				),
			),
		);

		/*
		* Site
		*/
		$capabilities['sites'] = array(
			'title'        => __('Sites', 'wp-ultimo'),
			'desc'         => __('Permissions related to sites on the platform.', 'wp-ultimo'),
			'capabilities' => array(
				'wu_read_sites'     => array(
					'title' => __('View Sites', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to see the sites on the platform.', 'wp-ultimo'),
				),
				'wu_add_sites'      => array(
					'title' => __('Add new Sites', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to add new sites to the platform.', 'wp-ultimo'),
				),
				'wu_publish_sites'  => array(
					'title' => __('Publish Pending Sites', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to publish pending sites on the platform.', 'wp-ultimo'),
				),
				'wu_edit_sites'     => array(
					'title' => __('Edit Sites', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to edit the sites on the platform.', 'wp-ultimo'),
				),
				'wu_transfer_sites' => array(
					'title' => __('Transfer Sites', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to transfer sites between memberships.', 'wp-ultimo'),
				),
				'wu_delete_sites'   => array(
					'title' => __('Delete Sites', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to delete the sites on the platform.', 'wp-ultimo'),
				),
				'wu_export_sites'   => array(
					'title' => __('Export Sites', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to export the sites on the platform.', 'wp-ultimo'),
				),
			),
		);

		/*
		* Domains
		*/
		$capabilities['domains'] = array(
			'title'        => __('Domains', 'wp-ultimo'),
			'desc'         => __('Permissions related to domains on the platform.', 'wp-ultimo'),
			'capabilities' => array(
				'wu_read_domains'   => array(
					'title' => __('View Domains', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to see the domains on the platform.', 'wp-ultimo'),
				),
				'wu_edit_domains'   => array(
					'title' => __('Edit Domains', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to edit the domains on the platform.', 'wp-ultimo'),
				),
				'wu_delete_domains' => array(
					'title' => __('Delete Domains', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to delete the domains on the platform.', 'wp-ultimo'),
				),
				'wu_export_domains' => array(
					'title' => __('Export Domains', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to export the domains on the platform.', 'wp-ultimo'),
				),
			),
		);

		/*
		* Discount Codes
		*/
		$capabilities['discount_codes'] = array(
			'title'        => __('Discount Codes', 'wp-ultimo'),
			'desc'         => __('Permissions related to discount codes on the platform.', 'wp-ultimo'),
			'capabilities' => array(
				'wu_read_discount_codes'   => array(
					'title' => __('View Discount Codes', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to see the discount codes on the platform.', 'wp-ultimo'),
				),
				'wu_edit_discount_codes'   => array(
					'title' => __('Edit Discount Codes', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to edit the discount codes on the platform.', 'wp-ultimo'),
				),
				'wu_delete_discount_codes' => array(
					'title' => __('Delete Discount Codes', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to delete the discount codes on the platform.', 'wp-ultimo'),
				),
				'wu_export_discount_codes' => array(
					'title' => __('Export Discount Codes', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to export the discount codes on the platform.', 'wp-ultimo'),
				),
			),
		);

		/*
		* Broadcasts
		*/
		$capabilities['broadcasts'] = array(
			'title'        => __('Broadcasts', 'wp-ultimo'),
			'desc'         => __('Permissions related to broadcasts on the platform.', 'wp-ultimo'),
			'capabilities' => array(
				'wu_read_broadcasts'   => array(
					'title' => __('View Broadcasts', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to see the broadcasts on the platform.', 'wp-ultimo'),
				),
				'wu_add_broadcasts'    => array(
					'title' => __('Add new Broadcasts', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to add new broadcasts to the platform.', 'wp-ultimo'),
				),
				'wu_edit_broadcasts'   => array(
					'title' => __('Edit Broadcasts', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to edit the broadcasts on the platform.', 'wp-ultimo'),
				),
				'wu_delete_broadcasts' => array(
					'title' => __('Delete Broadcasts', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to delete the broadcasts on the platform.', 'wp-ultimo'),
				),
				'wu_export_broadcasts' => array(
					'title' => __('Export Broadcasts', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to export the broadcasts on the platform.', 'wp-ultimo'),
				),
			),
		);

		/*
		* Events
		*/
		$capabilities['events'] = array(
			'title'        => __('Events', 'wp-ultimo'),
			'desc'         => __('Permissions related to events on the platform.', 'wp-ultimo'),
			'capabilities' => array(
				'wu_read_events'   => array(
					'title' => __('View Events', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to see the events on the platform.', 'wp-ultimo'),
				),
				'wu_delete_events' => array(
					'title' => __('Delete Events', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to delete the events on the platform.', 'wp-ultimo'),
				),
				'wu_export_events' => array(
					'title' => __('Export Events', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to export the events on the platform.', 'wp-ultimo'),
				),
			),
		);

		/*
		* Webhooks
		*/
		$capabilities['webhooks'] = array(
			'title'        => __('Webhooks', 'wp-ultimo'),
			'desc'         => __('Permissions related to webhooks on the platform.', 'wp-ultimo'),
			'capabilities' => array(
				'wu_read_webhooks'   => array(
					'title' => __('View Webhooks', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to see the webhooks on the platform.', 'wp-ultimo'),
				),
				'wu_add_webhooks'    => array(
					'title' => __('Add New Webhooks', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to add a new webhook on the platform.', 'wp-ultimo'),
				),
				'wu_edit_webhooks'   => array(
					'title' => __('Edit Webhooks', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to edit the webhooks on the platform.', 'wp-ultimo'),
				),
				'wu_delete_webhooks' => array(
					'title' => __('Delete Webhooks', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to delete the webhooks on the platform.', 'wp-ultimo'),
				),
				'wu_export_webhooks' => array(
					'title' => __('Export Webhooks', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent to export the webhooks on the platform.', 'wp-ultimo'),
				),
			),
		);

		/*
		* Account
		*/
		$capabilities['account'] = array(
			'title'        => __('Account & Licensing', 'wp-ultimo'),
			'desc'         => __('Permissions related to plugin activation, account, and licensing.', 'wp-ultimo'),
			'capabilities' => array(
				'wu_license' => array(
					'title' => __('Manage Licensing', 'wp-ultimo'),
					'desc'  => __('Check to allow this agent manage WP Ultimo licensing.', 'wp-ultimo'),
				),
			),
		);

		return apply_filters('wu_registered_capabilities', $capabilities);

	} // end registered_capabilities;

	/**
	 * Add new role
	 *
	 * Call this function on init - Priority must be after the initial role definition
	 *
	 * @since 2.0.0
	 *
	 * @param string $role Role.
	 * @param string $display_name Display Name.
	 * @return void
	 */
	public function add_new_role($role, $display_name) {

		// https://developer.wordpress.org/reference/functions/add_role/
		add_role($role, $display_name);

	} // end add_new_role;

	/**
	 * Set role capabilities
	 *
	 * Call this function on init - Priority must be after the initial role definition
	 *
	 * @since 2.0.0
	 *
	 * @param string $role Role.
	 * @param string $capabilitie Capabilitie.
	 * @return void
	 */
	public function set_role_capability($role, $capabilitie) {

		// gets the simple_role role object
		$role = get_role($role);

		// add a new capability
		$role->add_cap($capabilitie, true);

	} // end set_role_capability;

	/**
	 * Set user to any role
	 *
	 * @since 2.0.0
	 *
	 * @param int    $user_id User.
	 * @param string $role Role name.
	 *
	 * @return void
	 */
	public function set_user_role($user_id, $role) {

		$user = new WP_User($user_id);

		$user->add_role($role);

	} // end set_user_role;

	/**
	 * Remover user permission controls roles
	 *
	 * @since 2.0.0
	 *
	 * @param int $user_id User id.
	 *
	 * @return void
	 */
	public function remove_user_role($user_id) {

		$user = new WP_User($user_id);

		$support_agent_caps = $this->get_support_agent_capabilities_by_id($user_id);

		foreach ($support_agent_caps as &$cap) {

			$user->remove_role($cap);

		} // end foreach;

		update_user_meta($user_id, 'wu_support_agent_capabilities', array());

	} // end remove_user_role;

	/**
	 * Get user roles by user id
	 *
	 * @param int $user_id Id User.
	 *
	 * @return array
	 * @since 2.0.0
	 */
	public function get_roles_by_user_id($user_id) {

		$user_meta = get_userdata($user_id);

		return $user_meta->allcaps; // array of roles the user is part of.

	} // end get_roles_by_user_id;

	/**
	 * Check user has permission passing id and role returns boolean
	 *
	 * @param int    $user_id Id user.
	 * @param string $role Role.
	 * @return boolean
	 * @since 2.0.0
	 */
	public function check_user_has_permission_by_role($user_id, $role) {

		return is_array($this->get_roles_by_user_id($user_id)) ? in_array($role, $this->get_roles_by_user_id($user_id)) : false;

	} // end check_user_has_permission_by_role;

	// Merged support agent class above

	/**
	 * Filter 'map_meta_cap' to give extra capabilities to trusted Deputies
	 *
	 * @param array  $required_capabilities The primitive capabilities that are required to perform the requested meta capability.
	 * @param string $requested_capability  The requested meta capability.
	 * @param int    $user_id               The user ID.
	 * @param array  $args                  Adds the context to the cap. Typically the object ID.
	 *
	 * @return array
	 */
	public function allow_capabilities($required_capabilities, $requested_capability, $user_id, $args) {

		$hash = md5(serialize(array(
			$required_capabilities,
			$requested_capability,
			$user_id,
			$args,
		)));

		$cache_value = wu_get_isset($this->cap_cache, $hash, null);

		if ($cache_value !== null) {

			return $cache_value;

		} // end if;

		/*
		 * Super admins can have it all...
		 */
		if ($requested_capability === 'exist' || !did_action('plugins_loaded') || is_super_admin()) {

			return $required_capabilities;

		} // end if;

		$support_agent = wu_get_support_agent_by_user_id($user_id);

		/*
		 * We only filter if the current user is a support agent.
		 */
		if (!$support_agent) {

			return $required_capabilities;

		} // end if;

		$allow_capability = true;

		if (in_array('do_not_allow', $required_capabilities, true)) {

			$allow_capability = false;

		} elseif (!$this->is_allowed_capability($requested_capability, $required_capabilities, $user_id)) {

			$allow_capability = false;

		} elseif (!in_array($required_capabilities[0], $this->get_support_agent_capabilities_by_id($user_id))) {

			if (array_key_exists($required_capabilities[0], $this->get_capabilities_default_wordpress())) {

				$allow_capability = false;

			} // end if;

		} // end if;

		if ($allow_capability) {

			$required_capabilities = array();

		} // end if;

		$this->cap_cache[$hash] = $required_capabilities;

		return $required_capabilities;

	} // end allow_capabilities;

	/**
	 * Determine if the given capability should be allowed.
	 *
	 * @param string $capability Capability.
	 * @param array  $dependent_capabilities Dependent Capabilities.
	 * @param int    $user_id User ID.
	 *
	 * @return bool
	 */
	public function is_allowed_capability($capability, $dependent_capabilities, $user_id) {

		$allowed = false;

		$agent_capabilities = $this->get_agent_capabilities($user_id);

		if (array_key_exists($capability, $agent_capabilities)) {

			$allowed = true;

		} else {

			foreach ($dependent_capabilities as $dependent_capability) {

				if (array_key_exists($dependent_capability, $agent_capabilities)) {

					$allowed = true;

					break;

				} // end if;

			} // end foreach;

		} // end if;

		return $allowed;

	} // end is_allowed_capability;

	/**
	 * Get the capabilities that trusted Deputies should have
	 *
	 * @since 2.0.0
	 *
	 * @param int $user_id User ID.
	 * @return array
	 */
	public function get_agent_capabilities($user_id) {

		$hash = md5($user_id);

		$cache_value = wu_get_isset($this->agent_cap_cache, $hash, null);

		if ($cache_value !== null) {

			return $cache_value;

		} // end if;

		$administrator_role = get_role('administrator');

		$capabilities = $this->get_support_agent_capabilities_by_id($user_id);

		$agent_capabilities = array_merge($capabilities, $administrator_role->capabilities);

		$this->agent_cap_cache[$hash] = $agent_capabilities;

		return $agent_capabilities;

	} // end get_agent_capabilities;

	/**
	 * Checks if user has capabilities in Support Agent user_meta.
	 *
	 * @since 2.0.0
	 *
	 * @param int $user_id User Id.
	 * @return array
	 */
	public function get_support_agent_capabilities_by_id($user_id) {

		$hash = md5($user_id);

		$cache_value = wu_get_isset($this->agent_cap_cache_2, $hash, null);

		if ($cache_value !== null) {

			return $cache_value;

		} // end if;

		$agent = wu_get_support_agent_by_user_id($user_id);

		$caps = array();

		if ($agent) {

			$caps = $agent->get_capabilities();

			$caps_wordpress = $agent->get_capabilities_wordpress();

			$caps = array_merge($caps, $caps_wordpress);

		} // end if;

		$this->agent_cap_cache_2[$hash] = $caps;

		return $caps;

	} // end get_support_agent_capabilities_by_id;

	/**
	 * This return all network capabilities default of WordPress
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public static function get_capabilities_default_wordpress() {

		$capabilities['wordpress'] = array(
			'title'        => __('WordPress Capabilities', 'wp-ultimo'),
			'desc'         => __('Default WordPress capabilities - usually attached to super admins.', 'wp-ultimo'),
			'capabilities' => array(
				'upgrade_network'        => array(
					'title' => __('Upgrade Network', 'wp-ultimo'),
					'desc'  => __('Can upgrade the WordPress database after a upgrade.', 'wp-ultimo'),
				),
				'manage_sites'           => array(
					'title' => __('Manage Sites', 'wp-ultimo'),
					'desc'  => __('Can edit sites using the default WordPress network admin pages.', 'wp-ultimo'),
				),
				'create_sites'           => array(
					'title' => __('Create Sites', 'wp-ultimo'),
					'desc'  => __('Can add new sites using the default WordPress network admin pages.', 'wp-ultimo'),
				),
				'delete_sites'           => array(
					'title' => __('Delete Sites', 'wp-ultimo'),
					'desc'  => __('Can delete sites using the default WordPress network admin pages.', 'wp-ultimo'),
				),
				'manage_network_users'   => array(
					'title' => __('Manage Network Users', 'wp-ultimo'),
					'desc'  => __('Can edit users using the default WordPress network admin pages.', 'wp-ultimo'),
				),
				'manage_network_plugins' => array(
					'title' => __('Manage Network Plugins', 'wp-ultimo'),
					'desc'  => __('Can install, activate, and remove plugins.', 'wp-ultimo'),
				),
				'manage_network_themes'  => array(
					'title' => __('Manage Network Themes', 'wp-ultimo'),
					'desc'  => __('Can install, activate, and remove themes.', 'wp-ultimo'),
				),
				'manage_network_options' => array(
					'title' => __('Manage Network Options', 'wp-ultimo'),
					'desc'  => __('Can edit network settings.', 'wp-ultimo'),
				),
			),
		);

		return apply_filters('wu_registered_default_wordpress_capabilities', $capabilities);

	} // end get_capabilities_default_wordpress;

} // end class Permission_Control;
