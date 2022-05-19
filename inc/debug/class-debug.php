<?php
/**
 * Debugger Tools for developers.
 *
 * @package WP_Ultimo
 * @subpackage Debug
 * @since 2.0.0
 */

namespace WP_Ultimo\Debug;

use \WP_Ultimo\Faker;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Debugger Tools for developers.
 *
 * @since 1.9.14
 */
class Debug {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * The registry of WP Ultimo admin pages.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	private $pages = array();

	/**
	 * Initializes main hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		$this->load();

		add_action('wp_ultimo_debug', array($this, 'add_main_debug_menu'));

		add_action('wp_ultimo_debug', array($this, 'add_additional_hooks'));

		add_action('wp_ultimo_debug', array($this, 'register_forms'));

	} // end init;

	/**
	 * Adds the additional debug links.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_additional_hooks() {

		add_action('wu_header_left', array($this, 'add_debug_links'));

	} // end add_additional_hooks;

	// phpcs:disable

	/**
	 * Adds the debug links
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_debug_links() { ?>

			<a  
				href="<?php wu_network_admin_url('wp-ultimo-debug-pages'); ?>" 
				class="wu-ml-2 wu-no-underline wu-text-gray-600"
				title="<?php _e('Pages', 'wp-ultimo'); ?>"
			>
				<span class="dashicons-wu-documents"></span>
				<?php _e('Pages', 'wp-ultimo'); ?>
			</a>

			<a  
				href="<?php echo wu_get_form_url('add_debug_generator_form'); ?>" 
				class="wubox wu-ml-2 wu-no-underline wu-relative wu-text-gray-600"
				title="<?php _e('Generator', 'wp-ultimo'); ?>"
			>
				<span class="dashicons-wu-rocket"></span>
				<?php _e('Generator', 'wp-ultimo'); ?>
			</a>

			<a  
				href="<?php echo wu_get_form_url('add_debug_reset_database_form'); ?>" 
				class="wubox wu-ml-2 wu-no-underline wu-text-gray-600"
				title="<?php _e('Reset Database', 'wp-ultimo'); ?>"
			>
				<span class="dashicons-wu-back-in-time"></span>
				<?php _e('Reset Database', 'wp-ultimo'); ?>
			</a>

			<a  
				href="<?php echo wu_get_form_url('add_debug_drop_database_form'); ?>" 
				class="wubox wu-ml-2 wu-no-underline wu-text-gray-600"
				title="<?php _e('Drop Database', 'wp-ultimo'); ?>"
			>
				<span class="dashicons-wu-database"></span>
				<?php _e('Drop Database', 'wp-ultimo'); ?>
			</a>

		<?php

	} // end add_debug_links;

	// phpcs:enable

	/**
	 * Register the forms for the fakers.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms() {
		/*
		 * Add Generator Form
		 */
		wu_register_form('add_debug_generator_form', array(
			'render'  => array($this, 'render_debug_generator_form'),
			'handler' => array($this, 'handle_debug_generator_form'),
		));

		/*
		 * Adds Reset Form
		 */
		wu_register_form('add_debug_reset_database_form', array(
			'render'  => array($this, 'render_debug_reset_database_form'),
			'handler' => array($this, 'handle_debug_reset_database_form'),
		));

		/*
		 * Adds Drop Form
		 */
		wu_register_form('add_debug_drop_database_form', array(
			'render'  => array($this, 'render_debug_drop_database_form'),
			'handler' => array($this, 'handle_debug_drop_database_form'),
		));

	} // end register_forms;

	/**
	 * Adds the form to generate data.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_debug_generator_form() {

		$fields = array(
			'customers'             => array(
				'title'     => __('Customers', 'wp-ultimo'),
				'desc'      => __('Toggle to generate customers.', 'wp-ultimo'),
				'type'      => 'toggle',
				'html_attr' => array(
					'v-model' => 'customers',
				),
			),
			'customers_number'      => array(
				'title'             => __('Number of Customers', 'wp-ultimo'),
				'type'              => 'number',
				'value'             => 10,
				'wrapper_html_attr' => array(
					'v-if'    => 'customers',
					'v-cloak' => '1',
				),
			),
			'products'              => array(
				'title'     => __('Products', 'wp-ultimo'),
				'desc'      => __('Toggle to generate products.', 'wp-ultimo'),
				'type'      => 'toggle',
				'html_attr' => array(
					'v-model' => 'products',
				),
			),
			'products_number'       => array(
				'title'             => __('Number of Products', 'wp-ultimo'),
				'type'              => 'number',
				'value'             => 10,
				'wrapper_html_attr' => array(
					'v-if'    => 'products',
					'v-cloak' => '1',
				),
			),
			'memberships'           => array(
				'title'     => __('Memberships', 'wp-ultimo'),
				'desc'      => __('Toggle to generate memberships.', 'wp-ultimo'),
				'type'      => 'toggle',
				'html_attr' => array(
					'v-model' => 'memberships',
				),
			),
			'memberships_number'    => array(
				'title'             => __('Number of Memberships', 'wp-ultimo'),
				'type'              => 'number',
				'value'             => 10,
				'wrapper_html_attr' => array(
					'v-if'    => 'memberships',
					'v-cloak' => '1',
				),
			),
			'sites'                 => array(
				'title'     => __('Sites', 'wp-ultimo'),
				'desc'      => __('Toggle to generate sites.', 'wp-ultimo'),
				'type'      => 'toggle',
				'html_attr' => array(
					'v-model' => 'sites',
				),
			),
			'sites_number'          => array(
				'title'             => __('Number of Sites', 'wp-ultimo'),
				'type'              => 'number',
				'value'             => 10,
				'wrapper_html_attr' => array(
					'v-if'    => 'sites',
					'v-cloak' => '1',
				),
			),
			'domains'               => array(
				'title'     => __('Domains', 'wp-ultimo'),
				'desc'      => __('Toggle to generate domains.', 'wp-ultimo'),
				'type'      => 'toggle',
				'html_attr' => array(
					'v-model' => 'domains',
				),
			),
			'domains_number'        => array(
				'title'             => __('Number of Domains', 'wp-ultimo'),
				'type'              => 'number',
				'value'             => 10,
				'wrapper_html_attr' => array(
					'v-if'    => 'domains',
					'v-cloak' => '1',
				),
			),
			'discount_codes'        => array(
				'title'     => __('Discount Codes', 'wp-ultimo'),
				'desc'      => __('Toggle to generate discount codes.', 'wp-ultimo'),
				'type'      => 'toggle',
				'html_attr' => array(
					'v-model' => 'discount_codes',
				),
			),
			'discount_codes_number' => array(
				'title'             => __('Number of Discount Codes', 'wp-ultimo'),
				'type'              => 'number',
				'value'             => 10,
				'wrapper_html_attr' => array(
					'v-if'    => 'discount_codes',
					'v-cloak' => '1',
				),
			),
			'payments'              => array(
				'title'     => __('Payments', 'wp-ultimo'),
				'desc'      => __('Toggle to generate payments.', 'wp-ultimo'),
				'type'      => 'toggle',
				'html_attr' => array(
					'v-model' => 'payments',
				),
			),
			'payments_number'       => array(
				'title'             => __('Number of Payments', 'wp-ultimo'),
				'type'              => 'number',
				'value'             => 30,
				'wrapper_html_attr' => array(
					'v-if'    => 'payments',
					'v-cloak' => '1',
				),
			),
			'submit_button'         => array(
				'title'           => __('Generate Data &rarr;', 'wp-ultimo'),
				'type'            => 'submit',
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end',
			),
		);

		$form = new \WP_Ultimo\UI\Form('add_debug_generator_form', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'debug_generator',
				'data-state'  => json_encode(array(
					'customers'      => false,
					'products'       => false,
					'memberships'    => false,
					'sites'          => false,
					'domains'        => false,
					'discount_codes' => false,
					'webhooks'       => false,
					'payments'       => false,
				)),
			),
		));

		$form->render();

	} // end render_debug_generator_form;

	/**
	 * Handles the checkout
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_debug_generator_form() {

		ignore_user_abort(true); // You and I are gonna live forever!

		set_time_limit(0); // Seriously, this script needs to run until the end.

		global $wpdb;

		$faker = new Faker();

		$wpdb->query('START TRANSACTION');

		try {

			if (wu_request('customers')) {

				$faker->generate_fake_customers(wu_request('customers_number', 0));

			} // end if;

			if (wu_request('products')) {

				$faker->generate_fake_products(wu_request('products_number', 0));

			} // end if;

			if (wu_request('memberships')) {

				$faker->generate_fake_memberships(wu_request('memberships_number', 0));

			} // end if;

			if (wu_request('sites')) {

				$faker->generate_fake_site(wu_request('sites_number', 0));

			} // end if;

			if (wu_request('domains')) {

				$faker->generate_fake_domain(wu_request('domains_number', 0));

			} // end if;

			if (wu_request('discount_codes')) {

				$faker->generate_fake_discount_code(wu_request('discount_codes_number', 0));

			} // end if;

			if (wu_request('payments')) {

				$faker->generate_fake_payment(wu_request('payments_number', 0));

			} // end if;

		} catch (\Throwable $e) {

			$wpdb->query('ROLLBACK');

			$error = new \WP_Error($e->getCode(), $e->getMessage());

			wp_send_json_error($error);

		} // end try;

		$fake_data_generated = $faker->get_fake_data_generated();

		$fake_ids_generated = array();

		foreach ($fake_data_generated as $key => $model) {

			foreach ($model as $object) {

				$fake_ids_generated[$key][] = $object->get_id();

			} // end foreach;

		} // end foreach;

		$fake_ids_generated = array_merge_recursive($faker->get_option_debug_faker(), $fake_ids_generated);

		wu_save_option('debug_faker', $fake_ids_generated);

		$wpdb->query('COMMIT');

		wp_send_json_success(array(
			'redirect_url' => wu_network_admin_url('wp-ultimo'),
		));

	} // end handle_debug_generator_form;

	/**
	 * Reset the database form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_debug_reset_database_form() {

		$fields = array(
			'reset_only_fake_data' => array(
				'title'     => __('Only reset generated data.', 'wp-ultimo'),
				'desc'      => __('Toggle this option to only remove data that was added by the generator previously. Untoggling this option will reset ALL data in WP Ultimo tables.', 'wp-ultimo'),
				'type'      => 'toggle',
				'value'     => true,
				'html_attr' => array(
					'v-model' => 'reset_only',
				),
			),
			'submit_button'        => array(
				'title'           => __('Reset Database &rarr;', 'wp-ultimo'),
				'type'            => 'submit',
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end',
			),
		);

		$form = new \WP_Ultimo\UI\Form('debug_reset_database_form', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'debug_reset_database_form',
				'data-state'  => json_encode(array(
					'reset_only' => true,
				)),
			),
		));

		$form->render();

	} // end render_debug_reset_database_form;

	/**
	 * Handles the database reset.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_debug_reset_database_form() {

		global $wpdb;

		$wpdb->query('START TRANSACTION');

		try {

			if (wu_request('reset_only')) {

				$this->reset_fake_data();

			} else {

				$this->reset_all_data();

			} // end if;

		} catch (Exception $e) {

			$wpdb->query('ROLLBACK');

			$error = new \WP_Error($e->getCode(), $e->getMessage());

			wp_send_json_error($error);

		} // end try;

		$wpdb->query('COMMIT');

		wu_delete_option('debug_faker');

		wp_send_json_success(array(
			'redirect_url' => wu_network_admin_url('wp-ultimo-setup'),
		));

	} // end handle_debug_reset_database_form;

	/**
	 * Reset the database form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_debug_drop_database_form() {

		$fields = array(
			'reset_note'    => array(
				'type' => 'note',
				'desc' => __('This action will drop the WP Ultimo database tables and is irreversable.', 'wp-ultimo'),
			),
			'submit_button' => array(
				'title'           => __('Drop Database Tables &rarr;', 'wp-ultimo'),
				'type'            => 'submit',
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end',
			),
		);

		$form = new \WP_Ultimo\UI\Form('debug_drop_database_form', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'debug_drop_database_form',
				'data-state'  => json_encode(array(
					'reset_only' => true,
				)),
			),
		));

		$form->render();

	} // end render_debug_drop_database_form;

	/**
	 * Handles the database reset.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_debug_drop_database_form() {

		global $wpdb;

		try {

			wu_drop_tables();

		} catch (\Throwable $e) {

			$error = new \WP_Error($e->getCode(), $e->getMessage());

			wp_send_json_error($error);

		} catch (\Exception $e) {

			$error = new \WP_Error($e->getCode(), $e->getMessage());

			wp_send_json_error($error);

		} // end try;

		wp_send_json_success(array(
			'redirect_url' => wu_network_admin_url('wp-ultimo-setup&step=installation'),
		));

	} // end handle_debug_drop_database_form;

	/**
	 * Checks if we need to add the menu or not.
	 *
	 * To gain access to the debug menu, you'll need to add
	 * define('WP_ULTIMO_DEBUG', true) to your wp-config.php file.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function should_load() {

		return defined('WP_ULTIMO_DEBUG') && WP_ULTIMO_DEBUG;

	} // end should_load;

	/**
	 * Loads the debug pages and functions if we should.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function load() {

		if ($this->should_load()) {

			add_action('wu_page_added', array($this, 'add_page'));

			add_filter('wu_tour_finished', '__return_false');

			add_action('plugins_loaded', function() {

				do_action('wp_ultimo_debug');

			});

		} // end if;

	} // end load;

	/**
	 * Add a WP Ultimo page to the registry.
	 *
	 * @since 2.0.0
	 *
	 * @param string $page_id The page ID. e.g. wp-ultimo.
	 * @return void
	 */
	public function add_page($page_id) {

		$this->pages[$page_id] = wu_network_admin_url($page_id);

	} // end add_page;

	/**
	 * Returns the pages registred.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_pages() {

		return $this->pages;

	} // end get_pages;

	/**
	 * Adds the debug menu pages.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_main_debug_menu() {

		new \WP_Ultimo\Admin_Pages\Debug\Debug_Admin_Page;

	} // end add_main_debug_menu;

	/**
	 * Reset fake data.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private function reset_fake_data() {

		$fake_data_generated = wu_get_option('debug_faker', array());

		$customers_id = wu_get_isset($fake_data_generated, 'customers');

		$this->reset_customers($customers_id);

		$products_id = wu_get_isset($fake_data_generated, 'products');

		$this->reset_products($products_id);

		$memberships_id = wu_get_isset($fake_data_generated, 'memberships');

		$this->reset_memberships($memberships_id);

		$domains_id = wu_get_isset($fake_data_generated, 'domains');

		$this->reset_domains($domains_id);

		$sites_id = wu_get_isset($fake_data_generated, 'sites');

		$this->reset_sites($sites_id);

		$discount_codes_id = wu_get_isset($fake_data_generated, 'discount_codes');

		$this->reset_discount_codes($discount_codes_id);

		$payments_id = wu_get_isset($fake_data_generated, 'payments');

		$this->reset_payments($payments_id);

	} // end reset_fake_data;

	/**
	 * Reset all data.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private function reset_all_data() {

		$this->reset_customers();

		$this->reset_products();

		$this->reset_memberships();

		$this->reset_domains();

		$this->reset_sites();

		$this->reset_discount_codes();

		$this->reset_payments();

		$this->reset_checkout_forms();

		$this->reset_post_like_models();

		$this->reset_events();

		$this->reset_settings();

	} // end reset_all_data;

	/**
	 * Reset table.
	 *
	 * @since 2.0.0
	 * @param string $table The table name.
	 * @param array  $ids The ids to delete.
	 * @param string $field The name of the field to use in the WHERE clause.
	 * @return void
	 * @throws \Exception In case of failures, an exception is thrown.
	 */
	private function reset_table($table, $ids = array(), $field = 'ID') {

		global $wpdb;

		if (!empty($table)) {

			if (!empty($ids)) {

				$ids = array_filter($ids);

				$id_placeholders = implode(', ', array_fill(0, count($ids), '%d'));

				$result = $wpdb->query(
					$wpdb->prepare("DELETE FROM $table WHERE $field IN ($id_placeholders)", $ids) // phpcs:ignore
				);

			} else {

				$result = $wpdb->query(
					"DELETE FROM $table" // phpcs:ignore
				);

			} // end if;

			if ($result === false) {

				throw new \Exception("Error $table");

			} // end if;

		} // end if;

	} // end reset_table;

	/**
	 * Reset customers and customermeta table.
	 *
	 * @since 2.0.0
	 * @param array $ids The ids to delete.
	 * @return void
	 * @throws \Exception In case of failures, an exception is thrown.
	 */
	private function reset_customers($ids = array()) {
		global $wpdb;

		$customers_table = "{$wpdb->base_prefix}wu_customers";

		$customer_meta_table = "{$wpdb->base_prefix}wu_customermeta";

		if (!empty($ids)) {

			foreach ($ids as $id) {

				$customer = wu_get_customer($id);

				if ($customer) {

					$deleted = wpmu_delete_user($customer->get_user_id());

					if (!$deleted) {

						throw new \Exception('Error customer delete');

					} // end if;

				} // end if;

			} // end foreach;

		} // end if;

		$this->reset_table($customers_table, $ids);

		$this->reset_table($customer_meta_table, $ids, 'wu_customer_id');

	} // end reset_customers;

	/**
	 * Reset customers and customermeta table.
	 *
	 * @since 2.0.0
	 * @param array $ids The ids to delete.
	 * @return void
	 * @throws \Exception In case of failures, an exception is thrown.
	 */
	private function reset_sites($ids = array()) {

		if (!empty($ids)) {

			foreach ($ids as $id) {

				$site = wu_get_site($id);

				if ($site) {

					wpmu_delete_blog($site->get_id(), true);

				} // end if;

			} // end foreach;

		} // end if;

	} // end reset_sites;

	/**
	 * Reset products and productmeta table.
	 *
	 * @since 2.0.0
	 * @param array $ids The ids to delete.
	 * @return void
	 */
	private function reset_products($ids = array()) {

		global $wpdb;

		$products_table = "{$wpdb->base_prefix}wu_products";

		$product_meta_table = "{$wpdb->base_prefix}wu_productmeta";

		$this->reset_table($products_table, $ids);

		$this->reset_table($product_meta_table, $ids, 'wu_product_id');

	} // end reset_products;

	/**
	 * Reset memberships and membershipmeta table.
	 *
	 * @since 2.0.0
	 * @param array $ids The ids to delete.
	 * @return void
	 */
	private function reset_memberships($ids = array()) {

		global $wpdb;

		$memberships_table = "{$wpdb->base_prefix}wu_memberships";

		$membership_meta_table = "{$wpdb->base_prefix}wu_membershipmeta";

		$this->reset_table($memberships_table, $ids);

		$this->reset_table($membership_meta_table, $ids, 'wu_membership_id');

	} // end reset_memberships;

	/**
	 * Reset domains table.
	 *
	 * @since 2.0.0
	 * @param array $ids The ids to delete.
	 * @return void
	 */
	private function reset_domains($ids = array()) {

		global $wpdb;

		$domain_table = "{$wpdb->base_prefix}wu_domain_mappings";

		$this->reset_table($domain_table, $ids);

	} // end reset_domains;

	/**
	 * Reset discount codes table.
	 *
	 * @since 2.0.0
	 * @param array $ids The ids to delete.
	 * @return void
	 */
	private function reset_discount_codes($ids = array()) {

		global $wpdb;

		$discount_codes_table = "{$wpdb->base_prefix}wu_discount_codes";

		$this->reset_table($discount_codes_table, $ids);

	} // end reset_discount_codes;

	/**
	 * Reset webhooks table.
	 *
	 * @since 2.0.0
	 * @param array $ids The ids to delete.
	 * @return void
	 */
	private function reset_webhooks($ids = array()) {

		global $wpdb;

		$webhooks_table = "{$wpdb->base_prefix}wu_webhooks";

		$this->reset_table($webhooks_table, $ids);

	} // end reset_webhooks;

	/**
	 * Reset payments table.
	 *
	 * @since 2.0.0
	 * @param array $ids The ids to delete.
	 * @return void
	 */
	private function reset_payments($ids = array()) {

		global $wpdb;

		$payments_table = "{$wpdb->base_prefix}wu_payments";

		$this->reset_table($payments_table, $ids);

	} // end reset_payments;

	/**
	 * Reset checkout forms
	 *
	 * @since 2.0.7
	 * @param array $ids The ids to delete.
	 * @return void
	 */
	private function reset_checkout_forms($ids = array()) {

		global $wpdb;

		$forms_table = "{$wpdb->base_prefix}wu_forms";

		$form_meta_table = "{$wpdb->base_prefix}wu_formmeta";

		$this->reset_table($forms_table, $ids);

		$this->reset_table($form_meta_table, $ids, 'wu_form_id');

	} // end reset_checkout_forms;

	/**
	 * Reset custom posts.
	 *
	 * @since 2.0.7
	 * @param array $ids The ids to delete.
	 * @return void
	 */
	private function reset_post_like_models($ids = array()) {

		global $wpdb;

		$posts_table = "{$wpdb->base_prefix}wu_posts";

		$post_meta_table = "{$wpdb->base_prefix}wu_postmeta";

		$this->reset_table($posts_table, $ids);

		$this->reset_table($post_meta_table, $ids, 'wu_post_id');

	} // end reset_post_like_models;

	/**
	 * Reset events.
	 *
	 * @since 2.0.7
	 * @param array $ids The ids to delete.
	 * @return void
	 */
	private function reset_events($ids = array()) {

		global $wpdb;

		$events_table = "{$wpdb->base_prefix}wu_events";

		$this->reset_table($events_table, $ids);

	} // end reset_events;

	/**
	 * Reset the settings.
	 *
	 * @since 2.0.7
	 * @return void
	 */
	private function reset_settings() {

		$the_prefix = 'wp-ultimo_';

		/*
		 * List of WP Ultimo options.
		 * Format: $option_name => $should_use_prefix
		 */
		$options = array(
			'v2_settings'                  => true,
			'debug_faker'                  => true,
			'finished'                     => true,
			'invoice_settings'             => true,
			'template_placeholders'        => true,
			'tax_rates'                    => true,
			'top_bar_settings'             => true,
			'wu_setup_finished'            => false,
			'wu_activation'                => false,
			'wu_default_email_template'    => false,
			'wu_is_migration_done'         => false,
			'wu_host_integrations_enabled' => false,
		);

		foreach ($options as $option_name => $should_use_prefix) {

			$prefix = $should_use_prefix ? $the_prefix : '';

			delete_network_option(null, $prefix . $option_name);

		} // end foreach;

	} // end reset_settings;

} // end class Debug;
