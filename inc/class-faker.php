<?php
/**
 * WP Ultimo faker
 *
 * @package WP_Ultimo
 * @subpackage Helper
 * @since 2.0.0
 */

namespace WP_Ultimo;

// require_once __DIR__ . '/../vendor/fzaninotto/faker/src/autoload.php';

use Faker as Lib_Faker;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo faker
 *
 * @since 2.0.0
 */
class Faker {
	/**
	 * Hold the fake data
	 *
	 * @since 2.0.0
	 * @var array
	 */
	private $fake_data_generated;

	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$this->faker = Lib_Faker\Factory::create();

	} // end __construct;

	/**
	 * Get the faker generator.
	 *
	 * @since 2.0.0
	 * @return Faker faker object.
	 */
	private function get_faker() {

		return $this->faker;

	} // end get_faker;

	/**
	 * Get the faker generator.
	 *
	 * @since 2.0.0
	 * @return Faker faker object.
	 */
	public function generate() {

		return $this->get_faker();

	} // end generate;

	/**
	 * Get the fake data generated.
	 *
	 * @since 2.0.0
	 * @param string $model The model name to get.
	 * @return array The fake data generated.
	 */
	public function get_fake_data_generated($model = '') {

		if (empty($this->fake_data_generated)) {

			$this->fake_data_generated = array(
				'customers'      => array(),
				'products'       => array(),
				'memberships'    => array(),
				'domains'        => array(),
				'discount_codes' => array(),
				'webhooks'       => array(),
				'payments'       => array(),
				'sites'          => array(),
			);

		} // end if;

		if (empty($model)) {

			return $this->fake_data_generated;

		} // end if;

		if (isset($this->fake_data_generated[$model])) {

			return $this->fake_data_generated[$model];

		} else {

			return array();

		} // end if;
	} // end get_fake_data_generated;

	/**
	 * Set the fake data generated.
	 *
	 * @since 2.0.0
	 * @param string $model The model name.
	 * @param string $value The value to identify the fake data generated.
	 */
	public function set_fake_data_generated($model, $value) {

		$this->get_fake_data_generated();

		$this->fake_data_generated[$model][] = $value;

	} // end set_fake_data_generated;

	/**
	 * Get the option "debug_faker" with the data generated by faker.
	 *
	 * @since 2.0.0
	 * @return array The ids of the fake data generated.
	 */
	public function get_option_debug_faker() {

		return WP_Ultimo()->helper->get_option('debug_faker', array(
			'customers'      => array(),
			'products'       => array(),
			'memberships'    => array(),
			'domains'        => array(),
			'discount_codes' => array(),
			'webhooks'       => array(),
			'payments'       => array(),
			'sites'          => array(),
		));

	} // end get_option_debug_faker;

	/**
	 * Generate a faker customer.
	 *
	 * @since 2.0.0
	 * @param int $number The number of fake data that will be generated.
	 * @throws \Exception In case of failures, an exception is thrown.
	 */
	public function generate_fake_customers($number = 1) {

		for ($i = 0; $i < $number; $i++) {

			$user_name  = $this->get_faker()->userName;
			$user_email = $this->get_faker()->safeEmail;

			if (!username_exists($user_name) && !email_exists($user_email)) {

				$password = wp_generate_password( 12, false );

				$user_id = wp_create_user($user_name, $password, $user_email);

				remove_user_from_blog($user_id);

				$customer = wu_create_customer(array(
					'user_id'            => $user_id,
					'vip'                => $this->get_faker()->boolean,
					'date_registered'    => $this->get_faker()->dateTimeThisYear()->format('Y-m-d H:i:s'),
					'email_verification' => $this->get_faker()->randomElement(array(
						'none',
						'pending',
						'verified'
					)),
					'meta'               => array(
						'ip_country' => $this->get_faker()->countryCode,
					),
				));

				if (is_wp_error($customer)) {

					throw new \Exception('Error customer');

				} else {

					$this->set_fake_data_generated('customers', $customer);

				} // end if;

			} // end if;
		} // end for;
	} // end generate_fake_customers;

	/**
	 * Generate a faker product.
	 *
	 * @since 2.0.0
	 * @param int $number The number of fake data that will be generated.
	 * @throws \Exception In case of failures, an exception is thrown.
	 */
	public function generate_fake_products($number = 1) {

		$faker                 = $this->get_faker();
		$product_type_options  = array(
			'plan',
			'package',
			'service'
		);
		$pricing_type_options  = array(
			'paid',
			'free',
			'contact_us'
		);
		$duration_unit_options = array(
			'day',
			'week',
			'month',
			'year'
		);

		for ($i = 0; $i < $number; $i++) {

			$product_data = array();

			$type         = $faker->optional(0.5, $product_type_options[0])->randomElement($product_type_options);
			$pricing_type = $faker->optional(0.2, $pricing_type_options[0])->randomElement($pricing_type_options);
			$amount       = 'free' === $pricing_type ? 0 : $faker->numberBetween(10, 55);
			$name         = $faker->unique()->word;

			$product_data['type']                = $type;
			$product_data['name']                = ucwords($type . ' ' . $name);
			$product_data['description']         = $faker->sentence();
			$product_data['pricing_type']        = $pricing_type;
			$product_data['amount']              = $amount;
			$product_data['trial_duration']      = $faker->numberBetween(0, 5);
			$product_data['trial_duration_unit'] = $faker->randomElement($duration_unit_options);
			$product_data['duration']            = $faker->numberBetween(1, 3);
			$product_data['duration_unit']       = $faker->randomElement($duration_unit_options);
			$product_data['active']              = $faker->boolean(75);
			$product_data['currency']            = 'USD';
			$product_data['slug']                = $type . '-' . $name;
			$product_data['recurring']           = $faker->boolean(75);

			$product = wu_create_product($product_data);

			if (is_wp_error($product)) {

				throw new \Exception('Error product');

			} else {

				$this->set_fake_data_generated('products', $product);

			} // end if;

		} // end for;

	} // end generate_fake_products;

	/**
	 * Get random data.
	 *
	 * @since 2.0.0
	 * @param string $model The name of model.
	 * @return number The id of the data.
	 */
	private function get_random_data($model) {

		if ($model) {

			$faker = $this->get_faker();

			$data_saved = wu_get_isset($this->get_option_debug_faker(), $model, array());

			$data_in_memory = $this->get_fake_data_generated($model);

			if (!empty($data_saved) && !empty($data_in_memory)) {

				$data_saved_or_in_memory = $faker->randomElement(array('data_saved', 'data_in_memory'));

				$data_index = $faker->numberBetween(0, count(${$data_saved_or_in_memory}) - 1);

				return ${$data_saved_or_in_memory}[$data_index];

			} elseif (!empty($data_saved)) {

				$data_index = $faker->numberBetween(0, count($data_saved) - 1);

				return $data_saved[$data_index];

			} elseif (!empty($data_in_memory)) {

				$data_index = $faker->numberBetween(0, count($data_in_memory) - 1);

				return $data_in_memory[$data_index];

			} else {

				return false;

			} // end if;

		} // end if;

	} // end get_random_data;

	/**
	 * Get random customer.
	 *
	 * @since 2.0.0
	 * @param boolean $create_if_not_exist Create the data if there's none.
	 * @return object The customer object.
	 */
	private function get_random_customer($create_if_not_exist = false) {

		$faker = $this->get_faker();

		$customer = $this->get_random_data('customers');

		if (!$customer) {

			if ($create_if_not_exist) {

				$this->generate_fake_customers();

				$customer = $this->get_random_data('customers');

			} else {

				return false;

			} // end if;

		} // end if;

		if (is_object($customer)) {

			return $customer;

		} else {

			return wu_get_customer($customer);

		} // end if;
	} // end get_random_customer;

	/**
	 * Get random product.
	 *
	 * @since 2.0.0
	 * @param boolean $create_if_not_exist Create the data if there's none.
	 * @return object The product object.
	 */
	private function get_random_product($create_if_not_exist = false) {

		$faker = $this->get_faker();

		$product = $this->get_random_data('products');

		if (!$product) {

			if ($create_if_not_exist) {

				$this->generate_fake_products();

				$product = $this->get_random_data('products');

			} else {

				return false;

			} // end if;

		} // end if;

		if (is_object($product)) {

			return $product;

		} else {

			return wu_get_product($product);

		} // end if;

	} // end get_random_product;

	/**
	 * Get random membership.
	 *
	 * @since 2.0.0
	 * @return object The membership object.
	 */
	private function get_random_membership() {

		$faker = $this->get_faker();

		$membership = $this->get_random_data('memberships');

		if (!$membership) {

			return false;

		} // end if;

		if (is_object($membership)) {

			return $membership;

		} else {

			return wu_get_membership($membership);

		} // end if;

	} // end get_random_membership;

	/**
	 * Get random site.
	 *
	 * @since 2.0.0
	 * @return object The site object.
	 */
	private function get_random_site() {

		$faker = $this->get_faker();

		$site = $this->get_random_data('sites');

		if (!$site) {

			return false;

		} // end if;

		if (is_object($site)) {

			return $site;

		} else {

			return wu_get_site($site);

		} // end if;

	} // end get_random_site;

	/**
	 * Generate a faker membership.
	 *
	 * @since 2.0.0
	 * @param int $number The number of fake data that will be generated.
	 * @throws \Exception In case of failures, an exception is thrown.
	 */
	public function generate_fake_memberships($number = 1) {

		$faker = $this->get_faker();

		for ($i = 0; $i < $number; $i++) {

			$customer = $this->get_random_customer(true);
			$product  = $this->get_random_product(true);

			$status_options = array(
				'pending',
				'active',
				'expired',
				'canceled'
			);

			$membership_data = array();

			$membership_data['customer_id']   = $customer ? $customer->get_id() : 0;
			$membership_data['plan_id']       = $product ? $product->get_id() : 0;
			$membership_data['amount']        = $product ? $product->get_amount() : 0;
			$membership_data['status']        = $faker->optional(0.6, $status_options[1])->randomElement($status_options);
			$membership_data['disabled']      = $faker->boolean(75);
			$membership_data['signup_method'] = 'network_admin';
			$membership_data['date_created']  = $this->get_faker()->dateTimeThisYear()->format('Y-m-d H:i:s');

			$membership_data = array_merge(
				$membership_data,
				$product ? $product->to_array() : array(),
				$customer ? $customer->to_array() : array()
			);

			$membership = wu_create_membership($membership_data);

			if (is_wp_error($membership)) {

				throw new \Exception('Error membership');

			} else {

				$this->set_fake_data_generated('memberships', $membership);

			} // end if;

		} // end for;

	} // end generate_fake_memberships;

	/**
	 * Generate a faker product.
	 *
	 * @since 2.0.0
	 * @param int $number The number of fake data that will be generated.
	 * @throws \Exception In case of failures, an exception is thrown.
	 */
	public function generate_fake_site($number = 1) {

		$faker = $this->get_faker();

		$type_options        = array(
			'default',
			'site_template',
			'customer_owned',
		);
		$type_customer_owned = $type_options[2];

		for ($i = 0; $i < $number; $i++) {
			$site_data = array();

			$title = rtrim($faker->sentence(2), '.');
			$path  = strtolower(implode('-', explode(' ', $title)));
			$type  = $faker->optional(0.2, $type_customer_owned)->randomElement($type_options);

			$site_data['title']  = $title;
			$site_data['path']   = $path;
			$site_data['type']   = $type;
			$site_data['public'] = $faker->boolean(75);

			if ($type_customer_owned === $type) {

				$membership = $this->get_random_membership();

				if ($membership) {

					$site_data['customer_id']   = $membership->get_customer_id();
					$site_data['membership_id'] = $membership->get_id();

				} // end if;
			} // end if;

			$site = wu_create_site($site_data);

			if (is_wp_error($site)) {

				throw new \Exception('Error site');

			} else {

				$this->set_fake_data_generated('sites', $site);

			} // end if;

		} // end for;

	} // end generate_fake_site;

	/**
	 * Generate a fake payment.
	 *
	 * @since 2.0.0
	 * @param int $number The number of fake data that will be generated.
	 * @throws \Exception In case of failures, an exception is thrown.
	 */
	public function generate_fake_payment($number = 1) {

		$faker          = $this->get_faker();
		$type_options   = array(
			'percentage',
			'absolute',
		);
		$status_options = array(
			'pending',
			'completed',
			'refund',
			'partial',
			'failed',
		);

		$type_options_percentage = $type_options[0];
		$status_options_pending  = $status_options[0];
		$memberships             = $this->get_fake_data_generated('memberships');

		for ($i = 0; $i < $number; $i++) {

			$membership = $this->get_random_membership();

			$payment_data = array(
				'description'        => $faker->sentence(),
				'parent_id'          => 0,
				'status'             => $faker->randomElement($status_options),
				'customer_id'        => $membership ? $membership->get_customer_id() : false,
				'membership_id'      => $membership ? $membership->get_id() : false,
				'product_id'         => $membership ? $membership->get_plan_id() : false,
				'currency'           => $membership ? $membership->get_currency() : false,
				'quantity'           => 1,
				'unit_price'         => $membership ? $membership->get_amount() : false,
				'tax_type'           => $faker->optional(0.2, $type_options_percentage)->randomElement($type_options),
				'tax'                => 0.00,
				'credits'            => 0.00,
				'fees'               => 0.00,
				'discounts'          => 0.00,
				'discount_code'      => '',
				'gateway'            => '',
				'gateway_payment_id' => '',
				'date_created'       => $this->get_faker()->dateTimeThisYear()->format('Y-m-d H:i:s'),
			);

			$payment = wu_create_payment($payment_data);

			if (is_wp_error($payment)) {

				throw new \Exception('Error payment');

			} else {

				$payment->recalculate_totals()->save();

				$this->set_fake_data_generated('payments', $payment);

			} // end if;

		} // end for;

	} // end generate_fake_payment;

	/**
	 * Generate a fake domain.
	 *
	 * @since 2.0.0
	 * @param int $number The number of fake data that will be generated.
	 * @throws \Exception In case of failures, an exception is thrown.
	 */
	public function generate_fake_domain($number = 1) {

		$faker         = $this->get_faker();
		$stage_options = array(
			'checking-dns',
			'checking-ssl-cert',
			'done'
		);

		$stage_checking_dns = $stage_options[0];

		$stage = $faker->optional(0.35, $stage_checking_dns)->randomElement($stage_options);

		for ($i = 0; $i < $number; $i++) {

			$site = $this->get_random_site();

			$domain = wu_create_domain(array(
				'domain'         => $faker->domainName, // phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				'stage'          => $stage,
				'blog_id'        => $site ? $site->get_blog_id() : 0,
				'primary_domain' => $faker->boolean(25),
				'active'         => $faker->boolean(75),
				'secure'         => $faker->boolean(25)
			));

			if (is_wp_error($domain)) {

				throw new \Exception('Error domain');

			} else {

				$this->set_fake_data_generated('domains', $domain);

			} // end if;

		} // end for;

	} // end generate_fake_domain;

	/**
	 * Generate a fake discount code.
	 *
	 * @since 2.0.0
	 * @param int $number The number of fake data that will be generated.
	 * @throws \Exception In case of failures, an exception is thrown.
	 */
	public function generate_fake_discount_code($number = 1) {

		$faker                   = $this->get_faker();
		$type_options            = array(
			'percentage',
			'absolute',
		);
		$type_options_percentage = $type_options[0];

		for ($i = 0; $i < $number; $i++) {

			$name            = rtrim($faker->sentence(2), '.');
			$value           = $faker->numberBetween(1, 25);
			$code            = strtoupper(substr(implode('', explode(' ', $name)), 0, 15)) . $value . 'OFF';
			$type            = $faker->optional(0.2, $type_options_percentage)->randomElement($type_options);
			$setup_fee_type  = $faker->optional(0.2, $type_options_percentage)->randomElement($type_options);
			$start_date      = $faker->dateTimeBetween('-1 weeks', 'now', 'UTC');
			$expiration_date = $faker->dateTimeBetween('now', '+4 weeks', 'UTC');

			$discount_code = wu_create_discount_code(array(
				'name'            => $name,
				'description'     => $faker->sentence(),
				'code'            => $code,
				'max_uses'        => $faker->numberBetween(1, 50),
				'type'            => $type,
				'value'           => $value,
				'setup_fee_type'  => $setup_fee_type,
				'setup_fee_value' => $faker->numberBetween(1, 20),
				'date_start'      => $start_date->format('Y-m-d H:i:s'),
				'date_expiration' => $expiration_date->format('Y-m-d H:i:s'),
				'active'          => true
			));

			if (is_wp_error($discount_code)) {

				throw new \Exception('Error discount code');

			} else {

				$this->set_fake_data_generated('discount_codes', $discount_code);

			} // end if;

		} // end for;

	} // end generate_fake_discount_code;

	/**
	 * Generate a fake webhook.
	 *
	 * @since 2.0.0
	 * @param int $number The number of fake data that will be generated.
	 * @throws \Exception In case of failures, an exception is thrown.
	 */
	public function generate_fake_webhook($number = 1) {

		$faker         = $this->get_faker();
		$event_options = array(
			'account_created',
			'account_deleted',
			'new_domain_mapping',
			'payment_received',
			'payment_successful',
			'payment_failed',
			'refund_issued',
			'plan_change',
		);

		for ($i = 0; $i < $number; $i++) {

			$webhook = wu_create_webhook(array(
				'name'        => rtrim($faker->sentence(2), '.'),
				'webhook_url' => 'https://' . $faker->domainName,
				'event'       => $faker->randomElement($event_options),
				'active'      => $faker->boolean(75)
			));

			if (is_wp_error($webhook)) {

				throw new \Exception('Error webhook');

			} else {

				$this->set_fake_data_generated('webhooks', $webhook);

			} // end if;

		} // end for;

	} // end generate_fake_webhook;

} // end class Faker;
