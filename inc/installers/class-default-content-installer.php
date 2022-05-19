<?php
/**
 * Installs default content during the setup install.
 *
 * @package WP_Ultimo
 * @subpackage Installers/Default_Content_Installer
 * @since 2.0.0
 */

namespace WP_Ultimo\Installers;

use \WP_Ultimo\Logger;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Installs default content during the setup install.
 *
 * @since 2.0.0
 */
class Default_Content_Installer extends Base_Installer {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Loads dependencies for when WP Ultimo is not yet loaded.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		require_once wu_path('inc/functions/email.php');

		require_once wu_path('inc/functions/tax.php');

		require_once wu_path('inc/functions/site.php');

		require_once wu_path('inc/functions/customer.php');

		require_once wu_path('inc/functions/product.php');

		require_once wu_path('inc/functions/checkout-form.php');

	} // end init;

	/**
	 * Checks if we already created a template site.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	protected function done_creating_template_site() {

		$current_site = get_current_site();

		$d = wu_get_site_domain_and_path('template');

		return domain_exists($d->domain, $d->path, get_current_network_id());

	} // end done_creating_template_site;

	/**
	 * Checks if we already created the base products.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	protected function done_creating_products() {
		/*
		 * Check for tables before
		 */
		$has_tables_installed = \WP_Ultimo\Loaders\Table_Loader::get_instance()->is_installed();

		if (!$has_tables_installed) {

			return false;

		} // end if;

		return !empty(wu_get_plans());

	} // end done_creating_products;

	/**
	 * Checks if we already created the base checkout form.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	protected function done_creating_checkout_forms() {
		/*
		 * Check for tables before
		 */
		$has_tables_installed = \WP_Ultimo\Loaders\Table_Loader::get_instance()->is_installed();

		if (!$has_tables_installed) {

			return false;

		} // end if;

		return !empty(wu_get_checkout_forms());

	} // end done_creating_checkout_forms;

	/**
	 * Checks if we already created the system emails and the template email.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	protected function done_creating_emails() {
		/*
		 * Check for tables before
		 */
		$has_tables_installed = \WP_Ultimo\Loaders\Table_Loader::get_instance()->is_installed();

		if (!$has_tables_installed) {

			return false;

		} // end if;

		return !empty(wu_get_all_system_emails());

	} // end done_creating_emails;

	/**
	 * Checks if we already created the custom login page.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	protected function done_creating_login_page() {

		$page_id = wu_get_setting('default_login_page');

		if (!$page_id) {

			return false;

		} // end if;

		$page = get_post($page_id);

		return !empty($page);

	} // end done_creating_login_page;

	/**
	 * Returns the list of migration steps.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_steps() {

		$steps = array();

		$steps['create_template_site'] = array(
			'done'        => $this->done_creating_template_site(),
			'title'       => __('Create Example Template Site', 'wp-ultimo'),
			'description' => __('This will create a template site on your network that you can use as a starting point.', 'wp-ultimo'),
			'pending'     => __('Pending', 'wp-ultimo'),
			'installing'  => __('Creating Template Site...', 'wp-ultimo'),
			'success'     => __('Success!', 'wp-ultimo'),
			'help'        => wu_get_documentation_url('installation-errors'),
		);

		$steps['create_products'] = array(
			'done'        => $this->done_creating_products(),
			'title'       => __('Create Example Products', 'wp-ultimo'),
			'description' => __('This action will create example products (plans, packages, and services), so you have an starting point.', 'wp-ultimo'),
			'pending'     => __('Pending', 'wp-ultimo'),
			'installing'  => __('Creating Products...', 'wp-ultimo'),
			'success'     => __('Success!', 'wp-ultimo'),
			'help'        => wu_get_documentation_url('installation-errors'),
		);

		$steps['create_checkout'] = array(
			'done'        => $this->done_creating_checkout_forms(),
			'title'       => __('Create a Checkout Form', 'wp-ultimo'),
			'description' => __('This action will create a single-step checkout form that your customers will use to place purchases, as well as the page that goes with it.', 'wp-ultimo'),
			'pending'     => __('Pending', 'wp-ultimo'),
			'installing'  => __('Creating Checkout Form and Registration Page...', 'wp-ultimo'),
			'success'     => __('Success!', 'wp-ultimo'),
			'help'        => wu_get_documentation_url('installation-errors'),
		);

		$steps['create_emails'] = array(
			'done'        => $this->done_creating_emails(),
			'title'       => __('Create the System Emails', 'wp-ultimo'),
			'description' => __('This action will create all emails sent by WP Ultimo.', 'wp-ultimo'),
			'pending'     => __('Pending', 'wp-ultimo'),
			'installing'  => __('Creating System Emails...', 'wp-ultimo'),
			'success'     => __('Success!', 'wp-ultimo'),
			'help'        => wu_get_documentation_url('installation-errors'),
		);

		$steps['create_login_page'] = array(
			'done'        => $this->done_creating_login_page(),
			'title'       => __('Create Custom Login Page', 'wp-ultimo'),
			'description' => __('This action will create a custom login page and replace the default one.', 'wp-ultimo'),
			'pending'     => __('Pending', 'wp-ultimo'),
			'installing'  => __('Creating Custom Login Page...', 'wp-ultimo'),
			'success'     => __('Success!', 'wp-ultimo'),
			'help'        => wu_get_documentation_url('installation-errors'),
		);

		return $steps;

	} // end get_steps;

	// Default_Content_Installers start below

	/**
	 * Creates a new template site as an example.
	 *
	 * @since 2.0.0
	 * @throws \Exception When a site with the /template path already exists.
	 * @return void
	 */
	public function _install_create_template_site() {

		$d = wu_get_site_domain_and_path('template');

		$template_site = array(
			'domain' => $d->domain,
			'path'   => $d->path,
			'title'  => __('Template Site', 'wp-ultimo'),
			'type'   => 'site_template',
		);

		$status = wu_create_site($template_site);

		if (is_wp_error($status)) {

			throw new \Exception($status->get_error_message());

		} // end if;

		if (!$status) {

			$error_message = __('Template Site was not created. Maybe a site with the /template path already exists?', 'wp-ultimo');

			throw new \Exception($error_message);

		} // end if;

	} // end _install_create_template_site;

	/**
	 * Creates a example products.
	 *
	 * @since 2.0.0
	 * @throws \Exception When the network already has products.
	 * @return void
	 */
	public function _install_create_products() {
		/*
		 * Saves Images
		 */
		$images = array(
			'free'    => wu_get_asset('free.png', 'img/wizards'),
			'premium' => wu_get_asset('premium.png', 'img/wizards'),
			'seo'     => wu_get_asset('seo.png', 'img/wizards'),
		);

		$images = array_map(array('\\WP_Ultimo\\Helpers\\Screenshot', 'save_image_from_url'), $images);

		$products = array();

		/*
		 * Free Plan
		 */
		$products[] = array(
			'name'           => __('Free', 'wp-ultimo'),
			'description'    => __('This is an example of a free plan.', 'wp-ultimo'),
			'currency'       => wu_get_setting('currency_symbol', 'USD'),
			'pricing_type'   => 'free',
			'duration'       => 1,
			'duration_unit'  => 'month',
			'slug'           => 'free',
			'type'           => 'plan',
			'setup_fee'      => 0,
			'recurring'      => false,
			'amount'         => 0,
			'billing_cycles' => false,
			'list_order'     => false,
			'active'         => 1,
		);

		/*
		 * Premium Plan
		 */
		$products[] = array(
			'name'           => __('Premium', 'wp-ultimo'),
			'description'    => __('This is an example of a paid plan.', 'wp-ultimo'),
			'currency'       => wu_get_setting('currency_symbol', 'USD'),
			'pricing_type'   => 'paid',
			'type'           => 'plan',
			'slug'           => 'premium',
			'setup_fee'      => 99,
			'recurring'      => true,
			'duration'       => 1,
			'duration_unit'  => 'month',
			'amount'         => 29.99,
			'billing_cycles' => false,
			'list_order'     => false,
			'active'         => 1,
		);

		/*
		 * Service
		 */
		$products[] = array(
			'name'           => __('SEO Consulting', 'wp-ultimo'),
			'description'    => __('This is an example of a service that you can create and charge customers for.', 'wp-ultimo'),
			'currency'       => wu_get_setting('currency_symbol', 'USD'),
			'pricing_type'   => 'paid',
			'type'           => 'service',
			'slug'           => 'seo',
			'setup_fee'      => 0,
			'recurring'      => true,
			'duration'       => 1,
			'duration_unit'  => 'month',
			'amount'         => 9.99,
			'billing_cycles' => false,
			'list_order'     => false,
			'active'         => 1,
		);

		foreach ($products as $product_data) {

			$status = wu_create_product($product_data);

			if (is_wp_error($status)) {

				throw new \Exception($status->get_error_message());

			} // end if;

			$status->set_featured_image_id($images[$product_data['slug']]);

			$status->save();

		} // end foreach;

	} // end _install_create_products;

	/**
	 * Creates a new checkout form as an example.
	 *
	 * @since 2.0.0
	 * @throws \Exception When a checkout form is already present.
	 * @return void
	 */
	public function _install_create_checkout() {

		$checkout_form = array(
			'name'     => __('Registration Form', 'wp-ultimo'),
			'slug'     => 'main-form',
			'settings' => array(),
		);

		$status = wu_create_checkout_form($checkout_form);

		if (is_wp_error($status)) {

			throw new \Exception($status->get_error_message());

		} else {

			$status->use_template('single-step');

			$status->save();

		} // end if;

		$post_content = '
			<!-- wp:shortcode -->
				[wu_checkout slug="%s"]
			<!-- /wp:shortcode -->
		';

		/*
		 * Create the page on the main site.
		 */
		$post_details = array(
			'post_name'    => 'register',
			'post_title'   => __('Register', 'wp-ultimo'),
			'post_content' => sprintf($post_content, $status->get_slug()),
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_author'  => get_current_user_id(),
		);

		$page_id = wp_insert_post($post_details);

		if (is_wp_error($page_id)) {

			throw new \Exception($page_id->get_error_message());

		} // end if;

		/*
		 * Set page as the default registration page.
		 */
		wu_save_setting('default_registration_page', $page_id);

	} // end _install_create_checkout;

	/**
	 * Creates the template email, invoice template and system emails.
	 *
	 * @since 2.0.0
	 * @throws \Exception When the content is already present.
	 * @return void
	 */
	public function _install_create_emails() {

		\WP_Ultimo\Managers\Email_Manager::get_instance()->create_all_system_emails();

	} // end _install_create_emails;

	/**
	 * Creates custom login page.
	 *
	 * @since 2.0.0
	 * @throws \Exception When the content is already present.
	 * @return void
	 */
	public function _install_create_login_page() {

		$page_content = '
		<!-- wp:shortcode -->
			[wu_login_form]
		<!-- /wp:shortcode -->
	  ';

		$page_args = array(
			'post_title'   => __('Login', 'wp-ultimo'),
			'post_content' => $page_content,
			'post_status'  => 'publish',
			'post_author'  => get_current_user_id(),
			'post_type'    => 'page',
		);

		$page_id = wp_insert_post($page_args);

		if (is_wp_error($page_id)) {

			throw new \Exception($page_id->get_error_message());

		} // end if;

		/*
		 * Enable a custom login page.
		 */
		wu_save_setting('enable_custom_login_page', true);

		/*
		 * Set page as the default login page.
		 */
		wu_save_setting('default_login_page', $page_id);

	} // end _install_create_login_page;

} // end class Default_Content_Installer;
