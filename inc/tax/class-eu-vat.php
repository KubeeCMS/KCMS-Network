<?php
/**
 * WP Ultimo helper methods for including and rendering files, assets, etc
 *
 * @package WP_Ultimo
 * @subpackage Tax
 * @since 2.0.0
 */

namespace WP_Ultimo\Tax;

use \WP_Ultimo\Tax\Vat_Validation;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo helper methods for including and rendering files, assets, etc
 *
 * @since 2.0.0
 */
class EU_Vat {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Cache key to save EU Vat Rates
	 */
	const EU_VAT_RATES_CACHE_KEY = 'wu_eu_vat_rates';

	/**
	 * Cache key to save VAT validation data about a VAT number.
	 */
	const EU_VAT_CHECK_CACHE_KEY = 'wu_eu_vat_check';

	/**
	 * Adds hooks to be added at the original instantiations.
	 *
	 * @since 1.9.0
	 */
	public function init() {

		if ($this->is_enabled()) {

			add_action('wu_load_tax_rates_list_page', array($this, 'register_scripts'));

			// Add EU VAT Field to the Address or Account Step, if necessary
			add_filter('wu_checkout_payment_billing_address_fields', array($this, 'maybe_add_eu_vat_field_to_signup'));

			// Serve EU VAT Tax Rates via Ajax
			add_action('wp_ajax_wu_get_eu_vat_tax_rates', array($this, 'serve_eu_vat_taxes_rates_via_ajax'));

			// Render EU VAT Helper buttons on the actions bar
			add_action('wu_tax_rates_screen_additional_actions', array($this, 'print_eu_vat_actions'));

			// Add the EU VAT TAx Rate Type
			add_filter('wu_get_tax_rate_types', array($this, 'add_eu_vat_tax_rate_type'));

			// If the user selected that option, we should exempt the user from the base country from paying the VAT
			add_filter('wu_get_tax_rates_for_country_code', array($this, 'maybe_exempt_user_on_base_country'), 5, 2);

			add_action('wu_checkout_serve_order_summary', array($this, 'validate_vat_number_on_summary'));

		} // end if;

	} // end init;

	/**
	 * Checks if this functionality is enabled.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_enabled() {

		$is_enabled = wu_get_setting('enable_vat', false);

		return apply_filters('wu_enable_vat', $is_enabled);

	} // end is_enabled;

	/**
	 * Validates the VAT number entered during checkout. Could use some refactoring.
	 *
	 * @since 0.0.1
	 * @param WU_Checkout $checkout Passed by reference.
	 * @return void
	 */
	public function validate_vat_number_on_summary(&$checkout) {

		// TODO: We need to refactor this here
		if (isset($_POST['country_code'])) {

			$checkout->gateway->vat_status = false;

			$checkout->gateway->country_code = $_POST['country_code'];

			if (isset($_POST['vat_number']) && $_POST['vat_number']) {

				$checkout->vat_number = $_POST['vat_number'];

				$invalid_request = WU_Tax_EU_VAT()->is_request_invalid($checkout->gateway->country_code, $_POST['vat_number']);

				if ($invalid_request) {

				} // end if;

				/** Validates the VAT Number */
				$parsed_number = WU_Tax_EU_VAT()->parse_vat_number($_POST['vat_number']);

				$valid_vat_number = WU_Tax_EU_VAT()->validate_vat_number($checkout->gateway->country_code, $_POST['vat_number']);

				if ($valid_vat_number) {

					$checkout->gateway->vat_status = true;

					add_filter('wu_get_tax_rates_for_country_code', function() {

						return array();

					});

				} // end if;

			} // end if;

			$checkout->gateway->tax_rates = wu_get_tax_rates_for_country_code($checkout->gateway->country_code);

		} // end if;

	}  // end validate_vat_number_on_summary;

	/**
	 * Add additional methods for the fetching Tax Rates functions.
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function register_scripts() {

		wp_enqueue_script('wu-vat', wu_get_asset('tax-rates-vat.js', 'js'), array('jquery', 'wu-tax-rates'), wu_get_version());

	} // end register_scripts;

	/**
	 * Adds the EU VAT tax rate type to the types list.
	 *
	 * @since 2.0.0
	 * @param array $types Current tax rate types.
	 * @return array
	 */
	public function add_eu_vat_tax_rate_type($types) {

		$types['eu-vat'] = __('EU VAT', 'wp-ultimo');

		return $types;

	} // end add_eu_vat_tax_rate_type;

	/**
	 * Checks wether or not the current costumer is located on the base country based on Geolocation status
	 * Returns false if no base country is set for the Network.
	 *
	 * @since 2.0.0
	 * @param string $country Country code for the customer.
	 * @return boolean
	 */
	public function is_customer_on_base_country($country) {

		$base_country = wu_get_setting('company_country', false);

		if (!$base_country) {

			return false;

		} // end if;

		return $country === $base_country;

	} // end is_customer_on_base_country;

	/**
	 * Returns a list of all EU Countries
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_eu_countries() {

		$eu_country_codes = array(
			'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DE',
			'DK', 'EE', 'EL', 'ES', 'FI', 'FR', 'GB',
			'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT',
			'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK',
			'MC', 'IM',
		);

		return apply_filters('wu_eu_country_codes', $eu_country_codes);

	} // end get_eu_countries;

	/**
	 * Checks if the current customer is on a EU member country
	 *
	 * @since 2.0.0
	 * @param string $country Country code.
	 * @return boolean
	 */
	public function is_customer_on_eu_country($country) {

		$eu_country_codes = $this->get_eu_countries();

		return in_array($country, $eu_country_codes, true);

	} // end is_customer_on_eu_country;

	/**
	 * Checks if a given country needs to be validated.
	 *
	 * @since 2.0.0
	 * @param string $country Country code.
	 * @return boolean
	 */
	public function needs_eu_vat_validation($country) {

		if (!$country) {

			return false;

		} // end if;

		return true;

	} // end needs_eu_vat_validation;

	/**
	 * Checks if we need to exempt a given a country from taxes
	 *
	 * @since 2.0.0
	 * @param array  $applicable_tax_rates List of applicable taxes.
	 * @param string $country_code The country code.
	 * @return boolean
	 */
	public function maybe_exempt_user_on_base_country($applicable_tax_rates, $country_code) {

		if (WU_Settings::get_setting('deduct_when_base_country') && $this->is_customer_on_base_country($country_code)) {

			return array_filter($applicable_tax_rates, function($tax_rate) use ($country_code) {

				return $tax_rate->country !== $country_code || $tax_rate->type != 'eu-vat';

			});

		} // end if;

		return $applicable_tax_rates;

	} // end maybe_exempt_user_on_base_country;

	/**
	 * Maybe adds the EU VAT Field to the Address step or Account Step during sign-up
	 *
	 * @since 2.0.0
	 * @param array $fields The fields.
	 * @return array
	 */
	public function maybe_add_eu_vat_field_to_signup($fields) {

		// Gets costumer country
		$customer_country = wu_get_current_subscription()->get_customer_billing_country();

		// Should this field be optional?
		$field_display_option = WU_Settings::get_setting('eu_vat_field');

		// Do not show if hidden
		if ($field_display_option == 'hidden') {

			return $fields;

		} // end if;

		// Do not show if the user is on the base country and that option is not checked
		if (!WU_Settings::get_setting('show_when_base_country') && $this->is_customer_on_base_country($customer_country)) {

			return $fields;

		} // end if;

		// Builds the fields attributes
		$field_attributes = array(
			'name'               => WU_Settings::get_setting('eu_vat_field_label'),
			'tooltip'            => WU_Settings::get_setting('eu_vat_field_tooltip'),
			'type'               => 'text',
			'placeholder'        => '',
			'wrapper_attributes' => array(
				'class' => 'wu-col-sm-12'
			),
			'attributes'         => array(
				'v-model.lazy' => 'vat_number'
			),
		);

		// We need to check if we need to force it as required when the customer is from base country
		$force_required = ($field_display_option == 'required_eu' && $this->is_customer_on_eu_country($customer_country)) || $field_display_option == 'always_required';

		/**
		 * VAT validation field
		 */
		$fields['vat_number_status'] = array(
			'type'               => 'html',
			'content'            => '<p v-cloak v-if="vat_number" class="wu-vat-status" v-bind:class="{ valid: vat_status }">{{ vat_status ? "Valid VAT number" : "Invalid VAT number" }}</p>',
			'order'              => 120,
			'wrapper_attributes' => array(
				'class' => 'wu-col-sm-12'
			),
		);

		/**
		 * Decides if we need to show the optional field
		 */
		if ($field_display_option == 'optional' || !$force_required) {

			$fields['has_vat_number'] = array(
				'order'              => 100,
				'name'               => __('Do you have an VAT Number?', 'wp-ultimo'),
				'type'               => 'checkbox',
				'check_if'           => 'vat_number', // Check if the input with this name is selected
				// 'checked'       => false,
				'wrapper_attributes' => array(
					'class' => 'wu-col-sm-12'
				),
			);

			// Adds the requires parameter in case of optional
			$field_attributes['requires']            = array('has_vat_number' => true);
			$fields['vat_number_status']['requires'] = array('has_vat_number' => true);

		} // end if;

		$field_attributes['order'] = 110;

		/**
		 * Adds the Fields
		 */

		$fields['vat_number'] = $field_attributes;

		return $fields;

	} // end maybe_add_eu_vat_field_to_signup;

	/**
	 * Retrieves the list of VAT rates and returns them.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function serve_eu_vat_taxes_rates_via_ajax() {

		$rate_type = wu_request('rate_type', 'standard_rate');

		$eu_vat_tax_rates = $this->get_eu_vat_rates();

		/**
		 * Loop the results and return the array with contents
		 */
		$results = array();

		foreach ($eu_vat_tax_rates['rates'] as $country_code => $item) {

			$rate = (float) isset($item[$rate_type]) && $item[$rate_type] ? $item[$rate_type] : $item['standard_rate'];

			$results[] = array(
				'title'    => sprintf(__('%1$s VAT', 'wp-ultimo'), $item['country']),
				'country'  => $country_code,
				'tax_rate' => $rate,
				'state'    => '',
				'priority' => '1',
				'type'     => 'eu-vat',
				'compound' => false,
				'selected' => true,
				'ID'       => null,
			);

		} // end foreach;

		$vat_rates = apply_filters('wu_get_eu_vat_tax_rates', $results);

		wp_send_json_success($vat_rates);

	} // end serve_eu_vat_taxes_rates_via_ajax;

	/**
	 * Adds the additional settings need for EU VAT support.
	 *
	 * @param array $fields The fields to amend.
	 * @return array
	 */
	public function add_settings($fields) {

		$eu_vat_settings = array(
			'enable_eu_vat'            => array(
				'title'   => __('Enable EU VAT Support', 'wp-ultimo'),
				'desc'    => __('WP ultimo offers a set of tools to help your network achieve EU VAT compliance. Activate this options if You need to support EU VAT.', 'wp-ultimo'),
				'tooltip' => '',
				'type'    => 'checkbox',
				'default' => false,
				'require' => array('enable_tax' => 1),
			),

			'eu_vat'                   => array(
				'title'   => __('EU VAT Options', 'wp-ultimo'),
				'desc'    => __('Use the options below add support to EU VAT requirements to your network.', 'wp-ultimo'),
				'type'    => 'heading',
				'require' => array('enable_tax' => 1, 'enable_eu_vat' => 1),
			),

			// 'eu_vat_field'             => array(
			// 'title'   => __('EU VAT Field', 'wp-ultimo'),
			// 'desc'    => __('Choose how you would like to display the EU VAT field during the sign-up flow. The field will be added to the Account Step, if the customer location is determined by geolocation, or in the Address Step, if the costumer has to enter his or her address.', 'wp-ultimo'),
			// 'type'    => 'select',
			// 'default' => 'optional',
			// 'tooltip' => '',
			// 'options' => array(
			// 'optional'        => __('Optional', 'wp-ultimo'),
			// 'always_required' => __('Always Required', 'wp-ultimo'),
			// 'required_eu'     => __('Required for EU Addresses', 'wp-ultimo'),
			// 'hidden'          => __('Hidden', 'wp-ultimo'),
			// ),
			// 'require' => array('enable_tax' => 1, 'enable_eu_vat' => 1),
			// ),
			// 'eu_vat_field_note'        => array(
			// 'title'   => '',
			// 'type'    => 'note',
			// 'require' => array('enable_tax' => 1, 'enable_eu_vat' => 1),
			// 'desc'    => implode('<br><br>', array(
			// __('<strong>Optional</strong> - Customers can enter a EU VAT number to get VAT exemption.', 'wp-ultimo'),
			// __('<strong>Always required</strong> - Customers must enter a valid EU VAT number to complete a purchase. This means that only B2B sales with EU businesses can be completed.', 'wp-ultimo'),
			// __('<strong>Required only for EU addresses</strong> - Customers who select a billing country that is part of the EU must enter a valid EU VAT number to complete a purchase. Customer who select a non-EU country can proceed without entering the VAT number.', 'wp-ultimo'),
			// __('<strong>Hidden</strong> - Customers will not be able to enter a EU VAT number. This option is useful if you do not plan to sell to EU businesses.', 'wp-ultimo'),
			// ))
			// ),
			'eu_vat_field_label'       => array(
				'title'       => __('EU VAT Field Label', 'wp-ultimo'),
				'desc'        => __('Customize the label of the VAT number field.', 'wp-ultimo'),
				'tooltip'     => '',
				'type'        => 'text',
				'default'     => __('VAT Number', 'wp-ultimo'),
				'placeholder' => __('VAT Number', 'wp-ultimo'),
				'require'     => array('enable_tax' => 1, 'enable_eu_vat' => 1),
			),

			'eu_vat_field_tooltip'     => array(
				'title'       => __('EU VAT Field Tooltip', 'wp-ultimo'),
				'desc'        => __('You can enter a helper text to be displayed on a tooltip for the EU VAT Field. Leave blank if you do not want to display a tooltip.', 'wp-ultimo'),
				'tooltip'     => '',
				'type'        => 'text',
				'default'     => __('Enter your EU VAT Number (if any). Country prefix is not required.', 'wp-ultimo'),
				'placeholder' => __('Enter your EU VAT Number (if any). Country prefix is not required.', 'wp-ultimo'),
				'require'     => array('enable_tax' => 1, 'enable_eu_vat' => 1),
			),

			'show_when_base_country'   => array(
				'title'   => __('Show EU VAT Field when customer is located in base country', 'wp-ultimo'),
				'desc'    => __("Show the EU VAT field when customer address is located in any European country, including your shop's base country. If this option is not selected, the EU VAT field will be hidden when the customer is from the same country specified as your shop's base country.", 'wp-ultimo'),
				'tooltip' => '',
				'type'    => 'checkbox',
				'default' => true,
				'require' => array('enable_tax' => 1, 'enable_eu_vat' => 1),
			),

			'deduct_when_base_country' => array(
				'title'   => __('Deduct VAT if customer is located in base country', 'wp-ultimo'),
				'desc'    => __("Enable this option to deduct VAT from subscriptions of customers who are located in your shop's base country, if they enter a valid EU VAT number.", 'wp-ultimo'),
				'tooltip' => '',
				'type'    => 'checkbox',
				'default' => false,
				'require' => array('enable_tax' => 1, 'enable_eu_vat' => 1),
			),

			'accept_vat_when_busy'     => array(
				'title'   => __('Accept VAT numbers that cannot be validated due to server busy', 'wp-ultimo'),
				'desc'    => __("VAT numbers are validates using EU VIES service. Occasionally, the VIES server may return a 'busy' response, which means that the VAT number was not processed and its validity is unknown. If you enable this option, a VAT number that cannot be validated due to the VIES server being busy will be accepted, and the customer will be considered exempt from VAT.", 'wp-ultimo'),
				'tooltip' => '',
				'type'    => 'checkbox',
				'default' => true,
				'require' => array('enable_tax' => 1, 'enable_eu_vat' => 1),
			),
		);

		return array_merge($fields, $eu_vat_settings);

	} // end add_settings;

	/**
	 * Checks if the VAT rates retrieved by the EU VAT Assistant are valid. Rates
	 * are valid when, for each country, they contain at least a standard rate
	 * (invalid rates often have a "null" object associated to them).
	 *
	 * @param array $vat_rates An array containing the VAT rates for all EU countries.
	 * @return bool
	 */
	protected function valid_eu_vat_rates($vat_rates) {

		foreach ($vat_rates as $country_code => $rates) {

			if (empty($rates['standard_rate']) || !is_numeric($rates['standard_rate'])) {

				return false;

			} // end if;

		} // end foreach;

		return true;

	} // end valid_eu_vat_rates;

	/**
	 * Retrieves the EU VAT rats from https://euvatrates.com website.
	 *
	 * @return array|null An array with the details of VAT rates, or null on failure.
	 * @link https://euvatrates.com
	 */
	public function get_eu_vat_rates() {

		$vat_rates = get_site_transient(self::EU_VAT_RATES_CACHE_KEY);

		if (!empty($vat_rates) && is_array($vat_rates)) {

			return $vat_rates;

		} // end if;

		$eu_vat_source_urls = array(
			// Forked Github repository at https://github.com/aelia-co/euvatrates.com
			// @since 1.7.16.171215
			'https://raw.githubusercontent.com/aelia-co/euvatrates.com/master/rates.json',
			// Original VAT rate service. It should work, although it might contain
			// obsolete VAT rates
			'http://euvatrates.com/rates.json',
		);

		// Go through the available URLs to get the rates
		foreach ($eu_vat_source_urls as $eu_vat_url) {

			$eu_vat_response = wp_remote_get($eu_vat_url, array(
				'timeout' => 5,
			));

			// Stop as soon as we get a valid response (i.e. NOT a WP Error)
			if (!is_wp_error($eu_vat_response)) {

				// We got a valid response. Now ensure that the VAT rates are in the
				// correct format
				$vat_rates = json_decode(wp_remote_retrieve_body($eu_vat_response), true);

				if (($vat_rates === null) || !is_array($vat_rates) || !isset($vat_rates['rates'])) {

					$vat_rates = null;

				} else {

					// If we reach this point, we got a valid response and the VAT rates
					// are most likely in the correct format. We can stop the loop here
					break;

				} // end if;
			} else {

				// TODO: LOG ERROR

			} // end if;

		} // end foreach;

		// If we reach this point and the VAT rates are still null, then the rates
		// could not be retrieved
		// @since 1.7.16.171215
		if ($vat_rates === null) {

			return null;

		} // end if;

		// Add rates for countries that use other countries' tax rates
		// Monaco uses French VAT
		$vat_rates['rates']['MC']            = $vat_rates['rates']['FR'];
		$vat_rates['rates']['MC']['country'] = 'Monaco';

		// Isle of Man uses UK's VAT
		$vat_rates['rates']['IM']            = $vat_rates['rates']['UK'];
		$vat_rates['rates']['IM']['country'] = 'Isle of Man';

		// Fix the country codes received from the feed. Some country codes are
		// actually the VAT country code. We need the ISO Code instead.
		$country_codes_to_fix = array(
			'EL' => 'GR',
			'UK' => 'GB',
		);

		foreach ($country_codes_to_fix as $code => $correct_code) {

			$vat_rates['rates'][$correct_code] = $vat_rates['rates'][$code];

			unset($vat_rates['rates'][$code]);

		} // end foreach;

		/*
		 * Fix the VAT rates for countries that don't have a reduced VAT rate. For
		 * those countries, the standard rate should be used as the "reduced" rate.
		 */
		foreach ($vat_rates['rates'] as $country_code => $rates) {

			if (!is_numeric($rates['reduced_rate'])) {

				$rates['reduced_rate'] = $rates['standard_rate'];

			} // end if;

			$vat_rates['rates'][$country_code] = $rates;

		} // end foreach;

		ksort($vat_rates['rates']);

		// Ensure that the VAT rates are valid before caching them
		if ($this->valid_eu_vat_rates($vat_rates['rates'])) {

			// Cache the VAT rates, to prevent unnecessary calls to the remote site
			set_site_transient(self::EU_VAT_RATES_CACHE_KEY, $vat_rates, 3 * HOUR_IN_SECONDS);

		} // end if;

		return $vat_rates;

	} // end get_eu_vat_rates;

	/**
	 * Returns sn associative array of country code => EU VAT prefix pairs.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function get_eu_vat_country_prefixes() {

		$eu_vat_country_prefixes = array();

		foreach ($this->get_eu_countries() as $country_code) {

			$eu_vat_country_prefixes[$country_code] = $country_code;

		} // end foreach;

		// Correct vat prefixes that don't match the country code and add some
		// extra ones
		// Greece
		$eu_vat_country_prefixes['GR'] = 'EL';
		// Isle of Man
		$eu_vat_country_prefixes['IM'] = 'GB';
		// Monaco
		$eu_vat_country_prefixes['MC'] = 'FR';

		return apply_filters('wu_get_eu_vat_country_prefixes', $eu_vat_country_prefixes);

	}  // end get_eu_vat_country_prefixes;

	/**
	 * Parses a VAT number, removing special characters and the country prefix, if
	 * any.
	 *
	 * @since 2.0.0
	 *
	 * @param string $vat_number The VAT number to parse.
	 * @return string
	 */
	public function parse_vat_number($vat_number) {

		// Remove special characters
		$vat_number = strtoupper(str_replace(array(' ', '-', '_', '.'), '', $vat_number));

		// Remove country code if set at the beginning
		$prefix = substr($vat_number, 0, 2);

		$eu_countries = $this->get_eu_vat_country_prefixes();

		if (in_array($prefix, array_values($eu_countries), true)) {

			$vat_number = substr($vat_number, 2);

		} // end if;

		if (empty($vat_number)) {

			return false;

		} // end if;

		return $vat_number;

	} // end parse_vat_number;

	/**
	 * Returns the VAT prefix used by a specific country.
	 *
	 * @param string $country An ISO country code.
	 * @return string|false
	 */
	public function get_vat_prefix($country) {

		$country_prefixes = $this->get_eu_vat_country_prefixes();

		return in_array($country, $country_prefixes, true) ? $country_prefixes[$country] : false;

	} // end get_vat_prefix;

	/**
	 * Validates the argument passed for validation, transforming a country code
	 * into a VAT prefix and checking the VAT number before it's used for a VIES
	 * request.
	 *
	 * @param string $country_code A country code. It will be used to determine the VAT number prefix.
	 * @param string $vat_number A VAT number.
	 * @return bool
	 */
	public function is_request_invalid($country_code, $vat_number) {

		// Some preliminary formal validation, to prevent unnecessary requests with
		// clearly invalid data
		$vat_number = $this->parse_vat_number($vat_number);

		if ($vat_number === false) {

			// return sprintf(__('An empty or invalid VAT number was passed for validation. The VAT number should contain several digits, without the country prefix. Received VAT number: "%s".', 'wp-ultimo'), $vat_number);

		} // end if;

		$vat_prefix = $this->get_vat_prefix($country_code);

		if (empty($vat_prefix)) {

			return sprintf(__('A VAT prefix could not be found for the specified country. Received country code: "%s".', 'wp-ultimo'), $country_code);

		} // end if;

		return false;

	}  // end is_request_invalid;

	/**
	 * Caches the results of a particular check
	 *
	 * @since 2.0.0
	 * @param string $country_code The country code.
	 * @param string $vat_number The VAT number for the company or individual.
	 * @param bool   $valid If it was valid or not.
	 * @param array  $info The information about the company or individual.
	 * @return bool
	 */
	public function set_cache_vat_result($country_code, $vat_number, $valid, $info) {

		return set_site_transient(self::EU_VAT_CHECK_CACHE_KEY . '_' . $country_code . $vat_number, array(
			'valid'        => $valid,
			'country_code' => $country_code,
			'vat_number'   => $vat_number,
			'info'         => $info,
		), HOUR_IN_SECONDS);

	}  // end set_cache_vat_result;

	/**
	 * Return the caches value of a check result.
	 *
	 * @param string $country_code The country code.
	 * @param string $vat_number The VAT number.
	 * @return array|bool
	 */
	function get_cache_vat_result($country_code, $vat_number) {

		return get_site_transient(self::EU_VAT_CHECK_CACHE_KEY . '_' . $country_code . $vat_number);

	} // end get_cache_vat_result;

	/**
	 * Validates a given VAT Number.
	 *
	 * @param string $country_code The country code.
	 * @param string $vat_number The VAT number.
	 * @return bool
	 */
	public function validate_vat_number($country_code, $vat_number) {

		// Checks of PHP has SOAP enabled
		if (!class_exists('SoapClient')) {

			return false;

		} // end if;

		$cache = $this->get_cache_vat_result($country_code, $vat_number);

		// Check if we had it in cache
		if ($cache && $cache['valid']) {

			return $cache['valid'];

		} else {

			require_once WP_Ultimo_VAT()->path('inc/vat-validation.class.php');

			$vat_validation = new Vat_Validation(array('debug' => false));

			try {

				$valid = $vat_validation->check($country_code, $vat_number);

			} catch (Exception $e) {

				$valid = false;

			} // end try;

			// Saves Cache
			$info = $valid ? array(
				'denomination' => $vat_validation->getDenomination(),
				'name'         => $vat_validation->getName(),
				'address'      => $vat_validation->getAddress()
			) : array();

			$this->set_cache_vat_result($country_code, $vat_number, $valid, $info);

			return $valid;

		} // end if;

	} // end validate_vat_number;

	/**
	 * Prints the Update EU VAT action buttons
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function print_eu_vat_actions() { ?>

		<button v-on:click="pull_vat_data" class="button">
		<?php _e('Update EU VAT Rates'); ?>
		</button>

		<select v-model="rate_type">

			<option value="standard_rate">
		<?php echo _e('Use Standard Rates', 'wp-ultimo'); ?>
			</option>

			<option value="reduced_rate">
		<?php echo _e('Use Reduced Rates', 'wp-ultimo'); ?>
			</option>

			<option value="reduced_rate_alt">
		<?php echo _e('Use Reduced Rates (Alternative)', 'wp-ultimo'); ?>
			</option>

			<option value="super_reduced_rates">
		<?php echo _e('Use Super Reduced Rates', 'wp-ultimo'); ?>
			</option>

			<option value="parking_rate">
		<?php echo _e('Use Parking Rates', 'wp-ultimo'); ?>
			</option>

		</select>

		<span style="line-height: 30px;" title='<?php _e('Please, keep in mind that if alternative rates are not found for some country, the standard rate will be used instead.', 'wp-ultimo'); ?>' class='wu-tooltip-vue dashicons dashicons-editor-help'></span>

		<?php

	} // end print_eu_vat_actions;

} // end class EU_Vat;
