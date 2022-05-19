<?php
/**
 * The Customer model.
 *
 * @package WP_Ultimo
 * @subpackage Models
 * @since 2.0.0
 */

namespace WP_Ultimo\Models;

use WP_Ultimo\Models\Base_Model;
use WP_Ultimo\Models\Membership;
use WP_Ultimo\Models\Site;
use WP_Ultimo\Models\Payment;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Customer model class. Implements the Base Model.
 *
 * @since 2.0.0
 */
class Customer extends Base_Model {

	use Traits\Billable, Traits\Notable;

	/**
	 * User ID of the associated user.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $user_id;

	/**
	 * The type of customer.
	 *
	 * Almost a 100% of the time this will be 'customer'
	 * but since we use this table to store support-agents as well
	 * this can be 'support-agent'.
	 *
	 * @see \WP_Ultimo\Models\Support_Agent
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $type;

	/**
	 * Date when the customer was created.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $date_registered;

	/**
	 * Email verification status - either `none`, `pending`, or `verified`.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $email_verification;

	/**
	 * Date this customer last logged in.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $last_login;

	/**
	 * Whether or not the customer has trialed before.
	 *
	 * @since 2.0.0
	 * @var null|bool
	 */
	protected $has_trialed;

	/**
	 * If this customer is a VIP customer or not.
	 *
	 * @since 2.0.0
	 * @var bool
	 */
	protected $vip = false;

	/**
	 * List of IP addresses used by this customer.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $ips;

	/**
	 * The form used to signup.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $signup_form;

	/**
	 * Extra information about this customer.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $extra_information;

	/**
	 * Query Class to the static query methods.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = '\\WP_Ultimo\\Database\\Customers\\Customer_Query';

	/**
	 * Prepare data before it is stored into the database.
	 *
	 * @since 2.0.0
	 */
	public function prepare_extra_information_to_save() {

		$name_array  = wu_request('name');
		$value_array = wu_request('value');

		if (!empty($name_array) && !empty($value_array)) {

			$length_extra_information = max(count($name_array), count($value_array));
			$extra_information        = array();

			for ($i = 0; $i < $length_extra_information; $i++) {

				$extra_information[] = array(
					'name'  => $name_array[$i],
					'value' => $value_array[$i]
				);

			} // end for;

			$this->set_extra_information($extra_information);

		} // end if;

	} // end prepare_extra_information_to_save;

	/**
	 * Save (create or update) the model on the database.
	 *
	 * @since 2.0.0
	 */
	public function save() {

		$this->prepare_extra_information_to_save();

		return parent::save();

	} // end save;

	/**
	 * Save the extra information of the customer
	 *
	 * @since 2.0.0
	 *
	 * @param array $data The object data that will be stored.
	 * @return void
	 */
	public function save_extra_information($data) {

		update_user_meta($data['user_id'], 'wu_customer_extra_information', $data['extra_information']);

	} // end save_extra_information;

	/**
	 * Set the validation rules for this particular model.
	 *
	 * To see how to setup rules, check the documentation of the
	 * validation library we are using: https://github.com/rakit/validation
	 *
	 * @since 2.0.0
	 * @link https://github.com/rakit/validation
	 * @return array
	 */
	public function validation_rules() {

		$id = $this->get_id();

		return array(
			'user_id'            => "required|integer|unique:\WP_Ultimo\Models\Customer,user_id,{$id}",
			'email_verification' => 'required|in:none,pending,verified',
			'type'               => 'required|in:customer',
			'last_login'         => 'default:',
			'has_trialed'        => 'boolean|default:0',
			'vip'                => 'boolean|default:0',
			'ips'                => 'array',
			'extra_information'  => 'default:',
			'signup_form'        => 'default:',
		);

	} // end validation_rules;

	/**
	 * Get user ID of the associated user.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_user_id() {

		return absint($this->user_id);

	} // end get_user_id;

	/**
	 * Set user ID of the associated user.
	 *
	 * @since 2.0.0
	 * @param int $user_id The WordPress user ID attached to this customer.
	 * @return void
	 */
	public function set_user_id($user_id) {

		$this->user_id = $user_id;

	} // end set_user_id;

	/**
	 * Returns the user associated with this customer.
	 *
	 * @since 2.0.0
	 * @return WP_User
	 */
	public function get_user() {

		return get_user_by('id', $this->get_user_id());

	} // end get_user;

	/**
	 * Returns the customer's display name.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_display_name() {

		$user = $this->get_user();

		if (empty($user)) {

			return __('User Deleted', 'wp-ultimo');

		} // end if;

		return $user->display_name;

	} // end get_display_name;

	/**
	 * Returns the default billing address.
	 *
	 * Classes that implement this trait need to implement
	 * this method.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Objects\Billing_Address
	 */
	public function get_default_billing_address() {

		return new \WP_Ultimo\Objects\Billing_Address(array(
			'company_name'    => $this->get_display_name(),
			'billing_email'   => $this->get_email_address(),
			'billing_country' => $this->get_meta('ip_country'),
		));

	} // end get_default_billing_address;

	/**
	 * Returns the customer country.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_country() {

		$billing_address = $this->get_billing_address();

		$country = $billing_address->billing_country;

		if (!$country) {

			return $this->get_meta('ip_country');

		} // end if;

		return $country;

	} // end get_country;

	/**
	 * Returns the customer's username.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_username() {

		$user = $this->get_user();

		if (empty($user)) {

			return __('none', 'wp-ultimo');

		} // end if;

		return $user->user_login;

	} // end get_username;

	/**
	 * Returns the customer's email address.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_email_address() {

		$user = $this->get_user();

		if (empty($user)) {

			return __('none', 'wp-ultimo');

		} // end if;

		return $user->user_email;

	} // end get_email_address;

	/**
	 * Get date when the customer was created.
	 *
	 * @since 2.0.0
	 * @param bool $formatted To format or not.
	 * @return string
	 */
	public function get_date_registered($formatted = true) {

		return $this->date_registered;

	} // end get_date_registered;

	/**
	 * Set date when the customer was created.
	 *
	 * @since 2.0.0
	 * @param string $date_registered Date when the customer was created.
	 * @return void
	 */
	public function set_date_registered($date_registered) {

		$this->date_registered = $date_registered;

	} // end set_date_registered;

	/**
	 * Get email verification status - either `none`, `pending`, or `verified`.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_email_verification() {

		return $this->email_verification;

	} // end get_email_verification;

	/**
	 * Set email verification status - either `none`, `pending`, or `verified`.
	 *
	 * @since 2.0.0
	 * @param string $email_verification Email verification status - either `none`, `pending`, or `verified`.
	 * @return void
	 */
	public function set_email_verification($email_verification) {

		$this->email_verification = $email_verification;

	} // end set_email_verification;

	/**
	 * Get date this customer last logged in.
	 *
	 * @since 2.0.0
	 * @param bool $formatted To format or not.
	 * @return string
	 */
	public function get_last_login($formatted = true) {

		return $this->last_login;

	} // end get_last_login;

	/**
	 * Set date this customer last logged in.
	 *
	 * @since 2.0.0
	 * @param string $last_login Date this customer last logged in.
	 * @return void
	 */
	public function set_last_login($last_login) {

		$this->last_login = $last_login;

	} // end set_last_login;

	/**
	 * Get whether or not the customer has trialed before.
	 *
	 * @since 2.0.0
	 * @return null|bool
	 */
	public function has_trialed() {

		return (bool) $this->has_trialed;

	} // end has_trialed;

	/**
	 * Set whether or not the customer has trialed before.
	 *
	 * @since 2.0.0
	 * @param bool $has_trialed Whether or not the customer has trialed before.
	 * @return void
	 */
	public function set_has_trialed($has_trialed) {

		$this->has_trialed = $has_trialed;

	} // end set_has_trialed;

	/**
	 * Get if this customer is a VIP customer or not.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function is_vip() {

		return (bool) $this->vip;

	} // end is_vip;

	/**
	 * Set if this customer is a VIP customer or not.
	 *
	 * @since 2.0.0
	 * @param bool $vip If this customer is a VIP customer or not.
	 * @return void
	 */
	public function set_vip($vip) {

		$this->vip = $vip;

	} // end set_vip;

	/**
	 * Get list of IP addresses used by this customer.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_ips() {

		if (empty($this->ips)) {

			return array();

		} // end if;

		if (is_string($this->ips)) {

			$this->ips = maybe_unserialize($this->ips);

		} // end if;

		return $this->ips;

	} // end get_ips;

	/**
	 * Returns the last IP address recorded for the customer.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_last_ip() {

		$ips = $this->get_ips();

		return array_pop($ips);

	} // end get_last_ip;

	/**
	 * Set list of IP addresses used by this customer.
	 *
	 * @since 2.0.0
	 * @param array $ips List of IP addresses used by this customer.
	 * @return void
	 */
	public function set_ips($ips) {

		if (is_string($ips)) {

			$ips = maybe_unserialize($ips);

		} // end if;

		$this->ips = $ips;

	} // end set_ips;

	/**
	 * Adds a new IP to the IP list.
	 *
	 * @since 2.0.0
	 *
	 * @param string $ip New IP address to add.
	 * @return void
	 */
	public function add_ip($ip) {

		$ips = $this->get_ips();

		if (!is_array($ips)) {

			$ips = array();

		} // end if;

		/*
		 * IP already exists.
		 */
		if (in_array($ip, $ips, true)) {

			return;

		} // end if;

		$ips[] = sanitize_text_field($ip);

		$this->set_ips($ips);

	} // end add_ip;

	/**
	 * Updates the last login, as well as the ip and country if necessary.
	 *
	 * @since 2.0.0
	 *
	 * @param boolean $update_ip If we want to update the IP address.
	 * @param boolean $update_country_and_state If we want to update country and state.
	 * @return boolean
	 */
	public function update_last_login($update_ip = true, $update_country_and_state = false) {

		$this->attributes(array(
			'last_login' => wu_get_current_time('mysql', true),
		));

		$geolocation = $update_ip || $update_country_and_state ? \WP_Ultimo\Geolocation::geolocate_ip('', true) : false;

		if ($update_ip) {

			$this->add_ip($geolocation['ip']);

		} // end if;

		if ($update_country_and_state) {

			$this->update_meta('ip_country', $geolocation['country']);
			$this->update_meta('ip_state', $geolocation['state']);

		} // end if;

		return $this->save();

	} // end update_last_login;

	/**
	 * Get extra information.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_extra_information() {

		if ($this->extra_information === null) {

			return $this->get_meta('wu_customer_extra_information');

		} // end if;

		return $this->extra_information;

	} // end get_extra_information;

	/**
	 * Set featured extra information.
	 *
	 * @since 2.0.0
	 * @param string $extra_information Any extra information related to this customer.
	 * @return void
	 */
	public function set_extra_information($extra_information) {

		$this->meta['wu_customer_extra_information'] = $extra_information;

	} // end set_extra_information;

	/**
	 * Returns the subscriptions attached to this customer.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_memberships() {

		return Membership::query(array(
			'customer_id' => $this->get_id(),
		));

	} // end get_memberships;

	/**
	 * Returns the sites attached to this customer.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_sites() {

		return Site::query(array(
			'meta_query' => array(
				'customer_id' => array(
					'key'   => 'wu_customer_id',
					'value' => $this->get_id(),
				),
			),
		));

	} // end get_sites;

	/**
	 * Returns all pending sites associated with a customer.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_pending_sites() {

		$pending_sites = array();

		$memberships = $this->get_memberships();

		foreach ($memberships as $membership) {

			$pending_site = $membership->get_pending_site();

			if ($pending_site) {

				$pending_sites[] = $pending_site;

			} // end if;

		} // end foreach;

		return $pending_sites;

	} // end get_pending_sites;

	/**
	 * The the primary site ID if available.
	 *
	 * In cases where none is set, we:
	 * - return the id of the first site on the list off sites
	 * belonging to this customer;
	 * - or return the main site id.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_primary_site_id() {

		$primary_site_id = get_user_option('primary_blog', $this->get_user_id());

		if (!$primary_site_id) {

			$sites = $this->get_sites();

			$primary_site_id = $sites ? $sites[0]->get_id() : wu_get_main_site_id();

		} // end if;

		return $primary_site_id;

	} // end get_primary_site_id;

	/**
	 * Returns the payments attached to this customer.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_payments() {

		return Payment::query(array(
			'customer_id' => $this->get_id(),
		));

	} // end get_payments;

	/**
	 * By default, we just use the to_array method, but you can rewrite this.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function to_search_results() {

		$user = get_userdata($this->get_user_id());

		if (isset($this->_user)) {

			$user = $this->_user; // Allows for injection, which is useful for mocking.

			unset($this->_user);

		} // end if;

		$search_result = $this->to_array();

		if ($user) {

			$user->data->avatar = get_avatar($user->data->user_email, 40, 'identicon', '', array(
				'force_display' => true,
				'class'         => 'wu-rounded-full wu-mr-3',
			));

			$search_result = array_merge((array) $user->data, $search_result);

		} // end if;

		$search_result['billing_address_data'] = $this->get_billing_address()->to_array();
		$search_result['billing_address']      = $this->get_billing_address()->to_string();

		return $search_result;

	} // end to_search_results;

	/**
	 * Get the customer type.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_type() {

		return $this->type;

	} // end get_type;

	/**
	 * Get the customer type.
	 *
	 * @since 2.0.0
	 * @param string $type The customer type. Can be 'customer'.
	 * @options customer
	 * @return void
	 */
	public function set_type($type) {

		$this->type = $type;

	} // end set_type;

	/**
	 * Gets the total grossed by the customer so far.
	 *
	 * @since 2.0.0
	 * @return float
	 */
	public function get_total_grossed() {

		global $wpdb;

		static $sum;

		if ($sum === null) {

			$sum = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT SUM(total) FROM {$wpdb->base_prefix}wu_payments WHERE parent_id = 0 AND customer_id = %d",
					$this->get_id()
				)
			);

		} // end if;

		return $sum;

	} // end get_total_grossed;

	/**
	 * Get if the customer is online or not.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_online() {

		if ($this->get_last_login() === '0000-00-00 00:00:00') {

			return false;

		} // end if;

		$last_login_date = new \DateTime($this->get_last_login());
		$now             = new \DateTime('now');

		$interval          = $last_login_date->diff($now);
		$minutes_interval  = $interval->days * 24 * 60;
		$minutes_interval += $interval->h * 60;
		$minutes_interval += $interval->i;

		return $minutes_interval <= apply_filters('wu_is_online_minutes_interval', 3) ? true : false;

	} // end is_online;

	/**
	 * Saves a verification key.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function generate_verification_key() {

		$seed = time();

		$hash = \WP_Ultimo\Helpers\Hash::encode($seed, 'verification-key');

		return $this->update_meta('wu_verification_key', $hash);

	} // end generate_verification_key;

	/**
	 * Returns the saved verification key.
	 *
	 * @since 2.0.0
	 * @return string|bool
	 */
	public function get_verification_key() {

		return $this->get_meta('wu_verification_key', false);

	} // end get_verification_key;

	/**
	 * Disabled the verification by setting the key to false.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function disable_verification_key() {

		return $this->update_meta('wu_verification_key', false);

	} // end disable_verification_key;

	/**
	 * Returns the link of the email verification endpoint.
	 *
	 * @since 2.0.0
	 * @return string|bool
	 */
	public function get_verification_url() {

		$key = $this->get_verification_key();

		if (!$key) {

			return get_site_url(wu_get_main_site_id());

		} // end if;

		return add_query_arg(array(
			'email-verification-key' => $key,
			'customer'               => $this->get_hash(),
		), get_site_url(wu_get_main_site_id()));

	} // end get_verification_url;

	/**
	 * Send verification email.
	 *
	 * @since 2.0.4
	 * @return void
	 */
	public function send_verification_email() {

		$this->generate_verification_key();

		$payload = array_merge(
			array('verification_link' => $this->get_verification_url()),
			wu_generate_event_payload('customer', $this)
		);

		wu_do_event('confirm_email_address', $payload);

	} // end send_verification_email;

	/**
	 * Get the form used to signup.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_signup_form() {

		return $this->signup_form;

	} // end get_signup_form;

	/**
	 * Set the form used to signup.
	 *
	 * @since 2.0.0
	 * @param string $signup_form The form used to signup.
	 * @return void
	 */
	public function set_signup_form($signup_form) {

		$this->signup_form = $signup_form;

	} // end set_signup_form;

} // end class Customer;
