<?php
/**
 * Legacy Shortcodes
 *
 * Handles WP Ultimo 1.X Legacy Shortcodes
 *
 * @package WP_Ultimo
 * @subpackage Compat
 * @since 2.0.0
 */

namespace WP_Ultimo\Compat;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles WP Ultimo 1.X Legacy Shortcodes
 *
 * @since 2.0.0
 */
class Legacy_Shortcodes {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Control array for the template list shortcode
	 *
	 * @since 1.7.4
	 * @var array|boolean
	 */
	public $templates = false;

	/**
	 * Defines all the legacy shortcodes.
	 *
	 * @since 1.0.0 Adds Pricing Table and Paying customers.
	 * @since 1.2.1 Adds Plan Link Shortcode.
	 * @since 1.2.2 Adds Template Display.
	 * @since 1.4.0 Adds User meta getter.
	 * @since 1.5.0 Adds Restricted content.
	 *
	 * @return void
	 */
	public function init() {

		add_shortcode('wu_paying_users', array($this, 'paying_users'));

		add_shortcode('wu_user_meta', array($this, 'user_meta'));

    // add_shortcode('wu_plan_link', array($this, 'plan_link'));

		/**
		 * TODO: Convert to Elements
		 */

		// add_shortcode('wu_templates_list', array($this, 'templates_list'));
    // add_shortcode('wu_pricing_table', array($this, 'pricing_table'));
    // add_shortcode('wu_restricted_content', array($this, 'restricted_content'));
	} // end init;

	/**
	 * Return the value of a user meta on the database.
	 * This is useful to fetch data saved from custom sign-up fields during sign-up.
	 *
	 * @since 1.4.0
	 * @since 2.0.0 Search customer meta first before trying to fetch the info from the user table.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function user_meta($atts) {

		$customer_id = 0;

		$user_id = get_current_user_id();

		$site = wu_get_current_site();

		$customer = $site->get_customer();

		if ($customer) {

			$customer_id = $customer->get_id();

			$user_id = $customer->get_user_id();

		} // end if;

		$atts = shortcode_atts(array(
			'user_id'   => $user_id,
			'meta_name' => 'first_name',
			'default'   => false,
			'unique'    => true,
		), $atts, 'wu_user_meta');

		if ($customer_id) {

			$value = $customer->get_meta($atts['meta_name']);

		} else {

			$value = get_user_meta($atts['user_id'], $atts['meta_name'], $atts['unique']);

		} // end if;

		if (is_array($value)) {

			$value = implode(', ', $value);

		} // end if;

		return $value ? $value : '--';

	} // end user_meta;

	/**
	 * Returns the number of paying users on the platform.
	 *
	 * @since 1.X
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function paying_users($atts) {

		global $wpdb;

		$atts = shortcode_atts(array(), $atts, 'wu_pricing_table');

		$paying_customers = wu_get_customers(array(
			'count' => true,
		));

		return $paying_customers;

	} // end paying_users;

} // end class Legacy_Shortcodes;
