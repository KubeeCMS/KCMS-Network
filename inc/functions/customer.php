<?php
/**
 * Customer Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Models\Customer;

/**
 * Returns a customer.
 *
 * @since 2.0.0
 *
 * @param int $customer_id The id of the customer. This is not the user ID.
 * @return \WP_Ultimo\Models\Customer|false
 */
function wu_get_customer($customer_id) {

	return \WP_Ultimo\Models\Customer::get_by_id($customer_id);

} // end wu_get_customer;

/**
 * Returns a single customer defined by a particular column and value.
 *
 * @since 2.0.0
 *
 * @param string $column The column name.
 * @param mixed  $value The column value.
 * @return \WP_Ultimo\Models\Customer|false
 */
function wu_get_customer_by($column, $value) {

	return \WP_Ultimo\Models\Customer::get_by($column, $value);

} // end wu_get_customer_by;

/**
 * Gets a customer based on the hash.
 *
 * @since 2.0.0
 *
 * @param mixed $hash The customer hash.
 * @return \WP_Ultimo\Models\Customer|false
 */
function wu_get_customer_by_hash($hash) {

	return \WP_Ultimo\Models\Customer::get_by_hash($hash);

} // end wu_get_customer_by_hash;

/**
 * Queries customers.
 *
 * @since 2.0.0
 *
 * @param array $query Query arguments.
 * @return \WP_Ultimo\Models\Customer[]
 */
function wu_get_customers($query = array()) {

	if (!empty($query['search'])) {

		$user_ids = get_users(array(
			'blog_id' => 0,
			'number'  => -1,
			'search'  => '*' . $query['search'] . '*',
			'fields'  => 'ids',
		));

		if (!empty($user_ids)) {

			$query['user_id__in'] = $user_ids;

			unset($query['search']);

		} // end if;

	} // end if;

	/*
	 * Force search limit to customers only.
	 */
	$query['type'] = 'customer';

	return \WP_Ultimo\Models\Customer::query($query);

} // end wu_get_customers;

/**
 * Returns a customer based on user_id.
 *
 * @since 2.0.0
 *
 * @param int $user_id The ID of the WP User associated with that customer.
 * @return \WP_Ultimo\Models\Customer|false
 */
function wu_get_customer_by_user_id($user_id) {

	return \WP_Ultimo\Models\Customer::get_by('user_id', $user_id);

} // end wu_get_customer_by_user_id;

/**
 * Returns the current customer.
 *
 * @since 2.0.0
 * @return \WP_Ultimo\Models\Customer|false
 */
function wu_get_current_customer() {

	return wu_get_customer_by_user_id(get_current_user_id());

} // end wu_get_current_customer;

/**
 * Creates a new customer.
 *
 * Check the wp_parse_args below to see what parameters are necessary.
 * If the use_id is not passed, we try to create a new WP User.
 *
 * @since 2.0.0
 *
 * @param array $customer_data Customer attributes.
 * @return \WP_Error|\WP_Ultimo\Models\Customer
 */
function wu_create_customer($customer_data) {

	$customer_data = wp_parse_args($customer_data, array(
		'user_id'            => false,
		'email'              => false,
		'username'           => false,
		'password'           => false,
		'vip'                => false,
		'ip'                 => false,
		'email_verification' => 'none',
		'meta'               => array(),
		'date_registered'    => wu_get_current_time('mysql', true),
		'date_modified'      => wu_get_current_time('mysql', true),
		'last_login'         => wu_get_current_time('mysql', true),
		'signup_form'        => 'by-admin',
		'billing_address'    => array(),
	));

	$user = get_user_by('email', $customer_data['email']);

	if (!$user) {

		$user = get_user_by('ID', $customer_data['user_id']);

	} // end if;

	if (!$user) {

		if ($customer_data['password']) {

			$user_id = wpmu_create_user($customer_data['username'], $customer_data['password'], $customer_data['email']);

		} else {

			$user_id = register_new_user($customer_data['username'], $customer_data['email']);

		} // end if;

		if (is_wp_error($user_id)) {

			return $user_id;

		} // end if;

		if ($user_id === false) {

			return new \WP_Error('user', __('We were not able to create a new user with the provided username and email address combination.', 'wp-ultimo'), $customer_data);

		} // end if;

	} else {

		$user_id = $user->ID;

	} // end if;

	if (!get_userdata($user_id)) {

		return new \WP_Error('user_id', __('We were not able to find a user with the given user_id.', 'wp-ultimo'), $customer_data);

	} // end if;

	$customer = new Customer(array(
		'user_id'            => $user_id,
		'email_verification' => $customer_data['email_verification'],
		'meta'               => $customer_data['meta'],
		'date_registered'    => $customer_data['date_registered'],
		'signup_form'        => $customer_data['signup_form'],
		'billing_address'    => $customer_data['billing_address'],
		'last_login'         => $customer_data['last_login'],
		'type'               => 'customer',
	));

	$saved = $customer->save();

	return is_wp_error($saved) ? $saved : $customer;

} // end wu_create_customer;

/**
 * Get the customer meta types available.
 *
 * Meta types are basically meta data saved onto the customer,
 * and custom fields found on every form passed as a parameter.
 *
 * @since 2.0.11
 *
 * @param boolean|array $form_slugs The form slugs to search for fields.
 * @return array
 */
function wu_get_customer_meta_types($form_slugs = false) {

	$meta_keys = array();

	$query = array(
		'number' => 99999,
	);

	if (is_array($form_slugs)) {

		$query['slug__in'] = (array) $form_slugs;

	} // end if;

	$forms = wu_get_checkout_forms($query);

	foreach ($forms as $form) {

		$customer_meta_fields = $form->get_all_meta_fields('customer_meta');

		foreach ($customer_meta_fields as $customer_meta_field) {

			$meta_keys[$customer_meta_field['id']] = array(
				'type'        => $customer_meta_field['type'],
				'title'       => $customer_meta_field['name'],
				'description' => wu_get_isset($customer_meta_field, 'description', ''),
				'tooltip'     => wu_get_isset($customer_meta_field, 'tooltip', ''),
				'default'     => wu_get_isset($customer_meta_field, 'default', ''),
				'id'          => wu_get_isset($customer_meta_field, 'id', ''),
				'step'        => wu_get_isset($customer_meta_field, 'step', ''),
				'options'     => wu_get_isset($customer_meta_field, 'options', array()),
				'form'        => $form->get_slug(),
				'value'       => '',
				'exists'      => false,
			);

		} // end foreach;

	} // end foreach;

	return $meta_keys;

} // end wu_get_customer_meta_types;

/**
 * Returns all the meta data keys present on a customer.
 *
 * @since 2.0.11
 *
 * @param int     $customer_id The customer id.
 * @param boolean $include_unset If we should include fields that exist but are not set
 *                               for this particular customer.
 * @return array
 */
function wu_get_all_customer_meta($customer_id, $include_unset = true) {

	$all_meta = array();

	$customer = wu_get_customer($customer_id);

	if ($include_unset) {

		$form_slugs = $customer ? array($customer->get_signup_form()) : false;

		$all_meta = array_merge($all_meta, wu_get_customer_meta_types($form_slugs));

	} // end if;

	if (!$customer) {

		return $all_meta;

	} // end if;

	$meta_keys = $customer->get_meta('wu_custom_meta_keys');

	if ($meta_keys) {

		$meta_keys = array_map(function($item) {

			$item['exists'] = true;

			return $item;

		}, $meta_keys);

		$all_meta = wu_array_merge_recursive_distinct($all_meta, $meta_keys);

	} // end if;

	return $all_meta;

} // end wu_get_all_customer_meta;

/**
 * Returns a customer meta.
 *
 * @since 2.0.11
 *
 * @param int    $customer_id  The local (wu) customer id.
 * @param string $meta_key     The key to use on meta value.
 * @param bool   $default      The default value to be passed.
 * @param bool   $single       To return single values or not.
 * @return mixed
 */
function wu_get_customer_meta($customer_id, $meta_key, $default = false, $single = true) {

	$customer = wu_get_customer($customer_id);

	if (!$customer) {

		return $default;

	} // end if;

	return $customer->get_meta($meta_key, $default, $single);

} // end wu_get_customer_meta;

/**
 * Updates a customer meta.
 *
 * @since 2.0.11
 *
 * @param int    $customer_id  The local (wu) customer id.
 * @param string $key          The key to use on meta value.
 * @param mixed  $value        The new meta value.
 * @param string $type         The data type.
 * @param string $title        The data title.
 * @return int|bool  The new meta field ID if a field with the given
 *                   key didn't exist and was therefore added, true on
 *                   successful update, false if customer did not exist
 *                   or on failure or if the value passed to the function
 *                   is the same as the one that is already in the database.
 */
function wu_update_customer_meta($customer_id, $key, $value, $type = null, $title = null) {

	$customer = wu_get_customer($customer_id);

	if (!$customer) {

		return false;

	} // end if;

	if ($type) {

		$custom_keys = $customer->get_meta('wu_custom_meta_keys', array());

		$custom_keys = array_merge($custom_keys, array(
			$key => array(
				'type'  => $type,
				'title' => $title,
			),
		));

		$customer->update_meta('wu_custom_meta_keys', $custom_keys);

	} // end if;

	return $customer->update_meta($key, $value);

} // end wu_update_customer_meta;

/**
 * Deletes a customer meta with a custom type field.
 *
 * @since 2.0.11
 *
 * @param int    $customer_id  The local (wu) customer id.
 * @param string $meta_key     The key to use on meta value.
 * @return bool
 */
function wu_delete_customer_meta($customer_id, $meta_key) {

	$customer = wu_get_customer($customer_id);

	if (!$customer) {

		return false;

	} // end if;

	$custom_keys = $customer->get_meta('wu_custom_meta_keys', array());

	if (isset($custom_keys[$meta_key])) {

		unset($custom_keys[$meta_key]);

		$customer->update_meta('wu_custom_meta_keys', $custom_keys);

	} // end if;

	return (bool) $customer->delete_meta($meta_key);

} // end wu_delete_customer_meta;

/**
 * Searches for a existing gateway customer among the customer memberships.
 *
 * @since 2.0.0
 *
 * @param int   $customer_id The local (wu) customer id.
 * @param array $allowed_gateways The list of allowed gateways to search.
 * @return string
 */
function wu_get_customer_gateway_id($customer_id, $allowed_gateways = array()) {

	$memberships = wu_get_memberships(array(
		'customer_id'                 => absint($customer_id),
		'gateway__in'                 => $allowed_gateways,
		'number'                      => 1,
		'gateway_customer_id__not_in' => array(''),
	));

	return !empty($memberships) ? $memberships[0]->get_gateway_customer_id() : '';

} // end wu_get_customer_gateway_id;

/**
 * Create a unique username for a new customer based on the email address passed.
 *
 * This is heavily based on the WooCommerce implementation
 *
 * @see https://github.com/woocommerce/woocommerce/blob/b19500728b4b292562afb65eb3a0c0f50d5859de/includes/wc-user-functions.php#L120
 *
 * @since 2.0.0
 *
 * @param string $email New customer email address.
 * @param array  $new_user_args Array of new user args, maybe including first and last names.
 * @param string $suffix Append string to username to make it unique.
 * @return string Generated username.
 */
function wu_username_from_email($email, $new_user_args = array(), $suffix = '') {

	$username_parts = array();

	if (isset($new_user_args['first_name'])) {

		$username_parts[] = sanitize_user($new_user_args['first_name'], true);

	} // end if;

	if (isset($new_user_args['last_name'])) {

		$username_parts[] = sanitize_user($new_user_args['last_name'], true);

	} // end if;

	// Remove empty parts.
	$username_parts = array_filter($username_parts);

	// If there are no parts, e.g. name had unicode chars, or was not provided, fallback to email.
	if (empty($username_parts)) {

		$email_parts    = explode('@', $email);
		$email_username = $email_parts[0];

		$common_emails = array(
			'sales',
			'hello',
			'mail',
			'contact',
			'info',
		);

		// Exclude common prefixes.
		if (in_array($email_username, $common_emails, true)) {

			// Get the domain part, minus the dot.
			$email_username = strtok($email_parts[1], '.');

		} // end if;

		$username_parts[] = sanitize_user($email_username, true);

	} // end if;

	$username = strtolower(implode('', $username_parts));

	if ($suffix) {

		$username .= $suffix;

	} // end if;

	$illegal_logins = (array) apply_filters('illegal_user_logins', array()); // phpcs:ignore

	// Stop illegal logins and generate a new random username.
	if (in_array(strtolower($username), array_map('strtolower', $illegal_logins), true)) {

		$new_args = array();

		/**
		 * Filter generated customer username.
		 *
		 * @since 3.7.0
		 * @param string $username      Generated username.
		 * @param string $email         New customer email address.
		 * @param array  $new_user_args Array of new user args, maybe including first and last names.
		 * @param string $suffix        Append string to username to make it unique.
		 */
		$new_args['first_name'] = apply_filters(
			'wu_generated_username_from_email',
			'woo_user_' . zeroise(wp_rand(0, 9999), 4),
			$email,
			$new_user_args,
			$suffix
		);

		return wu_username_from_email($email, $new_args, $suffix);

	} // end if;

	if (username_exists($username)) {

		// Generate something unique to append to the username in case of a conflict with another user.
		$suffix = '-' . zeroise(wp_rand(0, 9999), 4);

		return wu_username_from_email( $email, $new_user_args, $suffix );

	} // end if;

	/**
	 * Filter new customer username.
	 *
	 * @since 2.0.0
	 * @param string $username      Customer username.
	 * @param string $email         New customer email address.
	 * @param array  $new_user_args Array of new user args, maybe including first and last names.
	 * @param string $suffix        Append string to username to make it unique.
	 */
	return apply_filters('wu_username_from_email', $username, $email, $new_user_args, $suffix);

} // end wu_username_from_email;
