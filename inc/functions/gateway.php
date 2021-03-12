<?php
/**
 * Gateway Functions
 *
 * Public APIs to load and deal with WP Ultimo gateways.
 *
 * @author      Arindo Duque
 * @category    Admin
 * @package     WP_Ultimo/Gateway
 * @version     2.0.0
 */

use WP_Ultimo\Managers\Gateway_Manager;

/**
 * Adds a new Gateway to the System. Used by gateways to make themselves visible.
 *
 * @since 2.0.0
 *
 * @param string $id ID of the gateway. This is how we will identify the gateway in the system.
 * @param string $title Name of the gateway.
 * @param string $desc A description of the gateway to help super admins understand what services they integrate with.
 * @param string $class_name Gateway class name.
 * @param bool   $hidden If we need to hide this gateway publicly.
 * @return bool
 */
function wu_register_gateway($id, $title, $desc, $class_name, $hidden = false) {

	if (!did_action('wu_register_gateways')) {

		_doing_it_wrong(__FUNCTION__, __('You should not register new payment gateways before the wu_register_gateways hook.', 'wp-ultimo'), '2.0.0');

	} // end if;

	return Gateway_Manager::get_instance()->register_gateway($id, $title, $desc, $class_name, $hidden);

} // end wu_register_gateway;

/**
 * Returns the currently registered gateways.
 *
 * @since 2.0.0
 *
 * @return array
 */
function wu_get_gateways() {

	return Gateway_Manager::get_instance()->get_registered_gateways();

} // end wu_get_gateways;

/**
 * Returns the currently registered and active gateways.
 *
 * @since 2.0.0
 * @return array
 */
function wu_get_active_gateways() {

	$gateways = array();

	$active_gateways = (array) wu_get_setting('active_gateways', array());

	foreach ($active_gateways as $active_gateway) {

		if (Gateway_Manager::get_instance()->is_gateway_registered($active_gateway)) {

			$gateways[$active_gateway] = Gateway_Manager::get_instance()->get_gateway($active_gateway);

		} // end if;

	} // end foreach;

	return apply_filters('wu_get_active_gateways', $gateways);

} // end wu_get_active_gateways;

/**
 * Returns a gateway class if it exists.
 *
 * @since 2.0.0
 *
 * @param string $id Gateway ID.
 * @param string $subscription Subscription object to load into the gateway.
 * @return mixed Gateway class.
 */
function wu_get_gateway($id, $subscription = null) {

	$gateway = Gateway_Manager::get_instance()->get_gateway($id);

	if (!$gateway) {

		return false;

	} // end if;

	$gateway_class = new $gateway['class_name'];

	$gateway_class->title = $gateway['title'];

	return $gateway_class;

} // end wu_get_gateway;

/**
 * Returns the list of available gateways as key => name.
 *
 * @since 2.0.0
 * @return array
 */
function wu_get_gateway_as_options() {

	$options = array();

	foreach (wu_get_gateways() as $gateway_slug => $gateway) {

		$instance = class_exists($gateway['class_name']) ? new $gateway['class_name'] : false;

		if ($instance === false || $gateway['hidden']) {

			continue;

		} // end if;

		$options[$gateway_slug] = $gateway['title'];

	} // end foreach;

	return $options;

} // end wu_get_gateway_as_options;

/**
 * Get the active gateways.
 *
 * @since 2.0.0
 * @return array
 */
function wu_get_active_gateway_as_options() {

	$options = array();

	foreach (wu_get_active_gateways() as $gateway_slug => $gateway) {

		$instance = class_exists($gateway['class_name']) ? new $gateway['class_name'] : false;

		if ($instance === false || $gateway['hidden']) {

			continue;

		} // end if;

		$title = $instance->get_public_title() ? $instance->get_public_title() : $gateway['title'];

		$options[$gateway_slug] = apply_filters("wu_gateway_{$gateway_slug}_as_option_title", $title, $gateway);

	} // end foreach;

	return $options;

} // end wu_get_active_gateway_as_options;

/**
 * Checks if the current post is a registration page.
 *
 * @since 2.0.0
 * @return boolean
 */
function wu_is_registration_page() {

	global $post;

	if (!is_main_site()) {

		return false;

	} // end if;

	if (!is_a($post, '\WP_Post')) {

		return false;

	} // end if;

	return abs(wu_get_setting('default_registration_page', 0)) === $post->ID;

} // end wu_is_registration_page;
