<?php
/**
 * Custom Table Loader
 *
 * Registers our custom tables.
 *
 * @package WP_Ultimo
 * @subpackage Loaders
 * @since 2.0.0
 */

namespace WP_Ultimo\Loaders;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Registers our custom tables.
 *
 * @since 2.0.0
 */
class Table_Loader {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Loads the table objects for our custom tables.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {
		/*
		 * Loads the Domain Mappings Table
		 */
		$this->domain_table = new \WP_Ultimo\Database\Domains\Domains_Table();

		/*
		 * Loads the Products (and Meta) Tables
		 */
		$this->product_table     = new \WP_Ultimo\Database\Products\Products_Table();
		$this->productmeta_table = new \WP_Ultimo\Database\Products\Products_Meta_Table();

		/*
		 * Loads the Discount Codes Table
		 */
		$this->discount_code_table     = new \WP_Ultimo\Database\Discount_Codes\Discount_Codes_Table();
		$this->discount_codemeta_table = new \WP_Ultimo\Database\Discount_Codes\Discount_Codes_Meta_Table();

		/*
		 * Loads the Sites (and Meta) Tables
		 */
		$this->site_table     = new \WP_Ultimo\Database\Sites\Sites_Table();
		$this->sitemeta_table = new \WP_Ultimo\Database\Sites\Sites_Meta_Table();

		/*
		 * Loads the Customer Table
		 */
		$this->customer_table     = new \WP_Ultimo\Database\Customers\Customers_Table();
		$this->customermeta_table = new \WP_Ultimo\Database\Customers\Customers_Meta_Table();

		/*
		 * Loads the Memberships Table
		 */
		$this->membership_table     = new \WP_Ultimo\Database\Memberships\Memberships_Table();
		$this->membershipmeta_table = new \WP_Ultimo\Database\Memberships\Memberships_Meta_Table();

		/*
		 * Loads the Payments Table
		 */
		$this->payment_table     = new \WP_Ultimo\Database\Payments\Payments_Table();
		$this->paymentmeta_table = new \WP_Ultimo\Database\Payments\Payments_Meta_Table();

		/*
		 * Loads the Posts (and Meta) Tables
		 */
		$this->post_table     = new \WP_Ultimo\Database\Posts\Posts_Table();
		$this->postmeta_table = new \WP_Ultimo\Database\Posts\Posts_Meta_Table();

		/*
		 * Loads the Webhook Table
		 */
		$this->webhook_table = new \WP_Ultimo\Database\Webhooks\Webhooks_Table();

		/*
		 * Loads the Webhook Table
		 */
		$this->event_table = new \WP_Ultimo\Database\Events\Events_Table();

		/*
		 * Loads the Checkout Forms Table
		 */
		$this->checkout_form_table = new \WP_Ultimo\Database\Checkout_Forms\Checkout_Forms_Table();
		$this->checkout_formmeta_table = new \WP_Ultimo\Database\Checkout_Forms\Checkout_Forms_Meta_Table();

	} // end init;

	/**
	 * Returns all the table objects.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_tables() {

		return get_object_vars($this);

	} // end get_tables;

	/**
	 * Checks if we have all the tables installed.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_installed() {

		$all_installed = true;

		$tables = $this->get_tables();

		foreach ($tables as $table) {

			if (!$table->exists()) {

				$all_installed = false;

			} // end if;

		} // end foreach;

		return $all_installed;

	} // end is_installed;

} // end class Table_Loader;
