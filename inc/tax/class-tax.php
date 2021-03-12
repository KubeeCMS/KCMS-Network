<?php
/**
 * WP Ultimo helper methods for including and rendering files, assets, etc
 *
 * @package WP_Ultimo
 * @subpackage Tax
 * @since 2.0.0
 */

namespace WP_Ultimo\Tax;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Tax\EU_Vat;

/**
 * WP Ultimo helper methods for including and rendering files, assets, etc
 *
 * @since 2.0.0
 */
class Tax {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Adds hooks to be added at the original instantiations.
	 *
	 * @since 1.9.0
	 */
	public function init() {

		if ($this->is_enabled()) {

			add_action('wp_ultimo_admin_pages', array($this, 'add_admin_page'));

			add_action('wp_ajax_wu_get_tax_rates', array($this, 'serve_taxes_rates_via_ajax'));

			add_action('wp_ajax_wu_save_tax_rates', array($this, 'save_taxes_rates'));

			add_action('wp_ultimo_load', array($this, 'maybe_load_eu_vat'));

		} // end if;

	} // end init;

	/**
	 * Checks if this functionality is available and should be loaded.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_enabled() {

		$is_enabled = wu_get_setting('enable_taxes', false);

		return apply_filters('wu_enable_taxes', $is_enabled);

	} // end is_enabled;

	/**
	 * Adds the Tax Rate edit admin screen.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_admin_page() {

		new \WP_Ultimo\Admin_Pages\Tax_Rates_Admin_Page;

	} // end add_admin_page;

	/**
	 * Loads the EU Vat class that handles European Union Taxes.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function maybe_load_eu_vat() {

		EU_Vat::get_instance();

	} // end maybe_load_eu_vat;

	/**
	 * Returns the Tax Rate Types available in the platform; Filterable
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_tax_rate_types() {

		return apply_filters('wu_get_tax_rate_types', array(
			'regular' => __('Regular', 'wp-ultimo')
		));

	} // end get_tax_rate_types;

	/**
	 * Returns the default elements of a tax rate.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_tax_rate_defaults() {

		$defaults = array(
			'id'         => uniqid(),
			'title'      => __('Tax Rate', 'wp-ultimo'),
			'country'    => '*',
			'state'      => '*',
			'tax_type'   => 'percentage',
			'tax_amount' => 5,
			'priority'   => 10,
			'compound'   => false,
			'type'       => 'regular',
		);

		return apply_filters('wu_get_tax_rate_defaults', $defaults);

	} // end get_tax_rate_defaults;

	/**
	 * Returns the registered tax rates.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_tax_rates() {

		$tax_rates = WP_Ultimo()->helper->get_option('tax_rates', array(
			'default' => array(
				'name'  => __('Default', 'wp-ultimo'),
				'rates' => array(),
			),
		));

		$tax_rates = array_map(function($item) {

			return wp_parse_args($item, $this->get_tax_rate_defaults());

		}, $tax_rates);

		return apply_filters('wu_get_tax_rates', $tax_rates);

	} // end get_tax_rates;

	/**
	 * Retrieves the tax rates to serve via ajax.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function serve_taxes_rates_via_ajax() {

		$tax_rates = array();

		if (current_user_can('read_tax_rates')) {

			$tax_rates = $this->get_tax_rates();

		} // end if;

		wp_send_json_success($tax_rates);

	} // end serve_taxes_rates_via_ajax;

	/**
	 * Handles the saving of new tax rates.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function save_taxes_rates() {

		if (!check_ajax_referer('wu_tax_editing')) {

			wp_send_json(array(
				'code'    => 'not-enough-permissions',
				'message' => __('You don\'t have permission to alter tax rates', 'wp-ultimo')
			));

		} // end if;

		$data = json_decode(file_get_contents('php://input'), true);

		$tax_rates = isset($data['tax_rates']) ? $data['tax_rates'] : false;

		if (!$tax_rates) {

			wp_send_json(array(
				'code'    => 'tax-rates-not-found',
				'message' => __('No tax rates present in the request', 'wp-ultimo')
			));

		} // end if;

		$treated_tax_rates = array();

		foreach ($tax_rates as $tax_rate_slug => $tax_rate) {

			if (!isset($tax_rate['rates'])) {

				continue;

			} // end if;

			$tax_rate['rates'] = array_map(function($item) {

				unset($item['selected']);

				return $item;

			}, $tax_rate['rates']);

			$treated_tax_rates[strtolower(sanitize_title($tax_rate_slug))] = $tax_rate;

		} // end foreach;

		WP_Ultimo()->helper->save_option('tax_rates', $treated_tax_rates);

		wp_send_json(array(
			'code'         => 'success',
			'message'      => __('Tax Rates successfully updated!', 'wp-ultimo'),
			'tax_category' => strtolower(sanitize_title(wu_get_isset($data, 'tax_category', 'default'))),
		));

	} // end save_taxes_rates;

} // end class Tax;
