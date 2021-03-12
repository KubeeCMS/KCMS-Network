<?php
/**
 * WP Ultimo 1.X to 2.X migrator.
 *
 * @package WP_Ultimo
 * @subpackage Installers
 * @since 2.0.0
 */

namespace WP_Ultimo\Installers;

use \WP_Ultimo\Dependencies\Ifsnop\Mysqldump\Mysqldump as MySQLDump;
use \WP_Ultimo\UI\Template_Previewer;
use \WP_Ultimo\Models\Checkout_Form;
use \WP_Ultimo\Checkout\Legacy_Checkout;
use \WP_Ultimo\Database\Payments\Payment_Status;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo 1.X to 2.X migrator.
 *
 * @since 2.0.0
 */
class Migrator extends Base_Installer {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Holds the session object.
	 *
	 * @since 2.0.0
	 * @var \WP_Ultimo\Session
	 */
	public $session;

	/**
	 * Errors holder.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	public $errors;

	/**
	 * Legacy settings cache.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $settings;

	/**
	 * Initializes the session object to keep track of errors.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		$this->session = wu_get_session('migrator');

		$this->errors = $this->session->get('errors');

	} // end init;

	/**
	 * Check if we are running on a network that runs Ultimo 1.X
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public static function is_legacy_network() {
		/*
		 * First strategy: check if we have plans.
		 */
		$plans = get_posts(array(
			'post_type' => 'wpultimo_plan',
		));

		if (!empty($plans)) {

			return true;

		} // end if;

		/*
		 * Second strategy: check if we have subscription.
		 */

		global $wpdb;

		$wpdb->hide_errors();

		$like     = $wpdb->esc_like("{$wpdb->base_prefix}wu_subscriptions");
		$prepared = $wpdb->prepare('SHOW TABLES LIKE %s', $like);
		$result   = $wpdb->get_var($prepared); // phpcs:ignore

		if (!$result) {

			return false;

		} // end if;

		try {

			$results = $wpdb->get_results("SELECT * FROM {$wpdb->base_prefix}wu_subscriptions LIMIT 10");

		} catch (\Throwable $e) {

			// Silence is golden

		} // end try;

		$wpdb->show_errors();

		return !empty($results);

	} // end is_legacy_network;

	/**
	 * Returns the list of errors detected.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_errors() {

		return array_unique((array) $this->errors);

	} // end get_errors;

	/**
	 * Returns the list of migration steps.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_steps() {

		$dry_run = wu_request('dry-run', true);

		$steps = array();

		if (!$dry_run) {

			$steps['backup'] = array(
				'title'       => __('Prepare for Migration', 'wp-ultimo'),
				'description' => __('Verifies the data before going forward with the migration.', 'wp-ultimo'),
				'pending'     => __('Pending', 'wp-ultimo'),
				'installing'  => __('Preparing...', 'wp-ultimo'),
				'success'     => __('Success!', 'wp-ultimo'),
				'help'        => wu_get_documentation_url('migration-errors'),
				'done'        => false,
			);

		} // end if;

		$steps['settings'] = array(
			'title'       => __('Settings', 'wp-ultimo'),
			'description' => __('Migrates the settings from the older version.', 'wp-ultimo'),
			'help'        => wu_get_documentation_url('migration-errors'),
			'done'        => false,
		);

		$steps['products'] = array(
			'title'       => __('Plans to Products', 'wp-ultimo'),
			'description' => __('Coverts the old plans into products.', 'wp-ultimo'),
			'help'        => wu_get_documentation_url('migration-errors'),
			'done'        => false,
		);

		$steps['customers'] = array(
			'title'       => __('Users to Customers', 'wp-ultimo'),
			'description' => __('Creates customers based on the existing users.', 'wp-ultimo'),
			'help'        => wu_get_documentation_url('migration-errors'),
			'done'        => false,
		);

		$steps['memberships'] = array(
			'title'       => __('Subscriptions to Memberships', 'wp-ultimo'),
			'description' => __('Converts subscriptions into Memberships.', 'wp-ultimo'),
			'help'        => wu_get_documentation_url('migration-errors'),
			'done'        => false,
		);

		$steps['transactions'] = array(
			'title'       => __('Transactions to Payments & Events', 'wp-ultimo'),
			'description' => __('Converts transactions into payments and events.', 'wp-ultimo'),
			'help'        => wu_get_documentation_url('migration-errors'),
			'done'        => false,
		);

		$steps['sites'] = array(
			'title'       => __('Sites', 'wp-ultimo'),
			'description' => __('Adjusts existing sites.', 'wp-ultimo'),
			'help'        => wu_get_documentation_url('migration-errors'),
			'done'        => false,
		);

		$steps['domains'] = array(
			'title'       => __('Mapped Domains', 'wp-ultimo'),
			'description' => __('Converts mapped domains.', 'wp-ultimo'),
			'help'        => wu_get_documentation_url('migration-errors'),
			'done'        => false,
		);

		$steps['forms'] = array(
			'title'       => __('Checkout Forms', 'wp-ultimo'),
			'description' => __('Creates a checkout form based on the existing signup flow.', 'wp-ultimo'),
			'help'        => wu_get_documentation_url('migration-errors'),
			'done'        => false,
		);

		$steps['emails'] = array(
			'title'       => __('Emails & Broadcasts', 'wp-ultimo'),
			'description' => __('Converts the emails and broadcasts.', 'wp-ultimo'),
			'help'        => wu_get_documentation_url('migration-errors'),
			'done'        => false,
		);

		$steps['webhooks'] = array(
			'title'       => __('Webhooks', 'wp-ultimo'),
			'description' => __('Migrates existing webhooks.', 'wp-ultimo'),
			'help'        => wu_get_documentation_url('migration-errors'),
			'done'        => false,
		);

		$steps = array_map(function($item) {

			return wp_parse_args($item, array(
				'pending'    => __('Pending', 'wp-ultimo'),
				'installing' => __('Preparing...', 'wp-ultimo'),
				'success'    => __('Success!', 'wp-ultimo'),
			));

		}, $steps);

		/**
		 * Allow developers and add-ons to add new migration steps
		 *
		 * @since 2.0.0
		 * @param array $steps The list of steps.
		 * @param \WP_Ultimo\Migrator $this This class.
		 */
		$steps = apply_filters('wu_get_migration_steps', $steps, $this);

		return $steps;

	} // end get_steps;

	/**
	 * Handles the installer.
	 *
	 * This wraps the installer into a try catch block
	 * so we can use that to rollback on database entries.
	 *
	 * Migrator needs a different implementation to support
	 * dry runs.
	 *
	 * @since 2.0.0
	 *
	 * @param bool|\WP_Error $status Status of the installer.
	 * @param string         $installer The installer name.
	 * @param object         $wizard Wizard class.
	 * @return bool
	 */
	public function handle($status, $installer, $wizard) {

		global $wpdb;

		$callable = array($this, "_install_{$installer}");

		$callable = apply_filters("wu_installer_{$installer}_callback", $callable, $installer);

		/*
		* No installer on this class.
		*/
		if (!is_callable($callable)) {

			return $status;

		} // end if;

		try {

			wp_cache_flush();

			$wpdb->query('START TRANSACTION');

			call_user_func($callable);

		} catch (\Throwable $e) {

			$wpdb->query('ROLLBACK');

			$errors = $this->session->get('errors');

			$errors[] = $e->getMessage();

			$this->session->set('errors', $errors);

			return new \WP_Error($installer, $e->getMessage());

		} // end try;

		/*
		 * Commit or rollback depending on the status
		 */
		if (wu_request('dry-run', true)) {

			$wpdb->query('ROLLBACK');

		} else {

			$wpdb->query('COMMIT');

			wp_cache_flush();

		} // end if;

		return $status;

	} // end handle;

	/**
	 * Generate the database dump as a backup.
	 *
	 * @since 2.0.0
	 * @throws \Exception Halts the process on error.
	 * @return mixed
	 */
	public function _install_backup() {

		global $wpdb;

		ini_set('memory_limit', '2048M');

		set_time_limit(300);

		$folder = WP_Ultimo()->helper->maybe_create_folder('wu-backup');

		$file_name = $folder . date_i18n('Y-m-d-his') . '-wu-dump.sql';

		$dump = new MySQLDump(
			sprintf('mysql:dbname=%s;host=%s', DB_NAME, DB_HOST),
			DB_USER,
			DB_PASSWORD,
			array(
				'compress' => MySQLDump::GZIP,
			)
		);

		$dump->start($file_name);

	} // end _install_backup;

	/**
	 * Returns the list of legacy settings on 1.X.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_old_settings() {

		global $wpdb;

		if ($this->settings !== null) {

			return $this->settings;

		} // end if;

		$settings = $wpdb->get_var(
			"
				SELECT meta_value
				FROM
					{$wpdb->base_prefix}sitemeta
				WHERE
					meta_key = 'wp-ultimo_settings'
				LIMIT 1
			"
		);

		$this->settings = maybe_unserialize($settings);

		return $this->settings;

	} // end get_old_settings;

	/**
	 * Returns the value of a particular legacy setting.
	 *
	 * @since 2.0.0
	 *
	 * @param string  $setting The setting key.
	 * @param boolean $default Default value.
	 * @return mixed
	 */
	public function get_old_setting($setting, $default = false) {

		$settings = $this->get_old_settings();

		return wu_get_isset($settings, $setting, $default);

	} // end get_old_setting;

	/**
	 * Migrates the settings.
	 *
	 * @todo Needs implementing.
	 * @since 2.0.0
	 * @throws \Exception Halts the process on error.
	 * @return void
	 */
	protected function _install_settings() {

		$settings = $this->get_old_settings();

		$random_key = time();

		/*
		 * Save current options as backup.
		 */
		WP_Ultimo()->helper->save_option("v1_settings_{$random_key}", $settings);

		$keys_to_migrate = array(
			// General
			'currency_symbol',
			'currency_position',
			'decimal_separator',
			'thousand_separator',
			'precision',

			// Products & Legacy Pricing Table
			'default_pricing_option',
			'enable_price_1',
			'enable_price_3',
			'enable_price_12',
			'enable_multiple_domains',
			'domain_options',

			// Dev Options
			'enable_error_reporting',
			'uninstall_wipe_tables',

			// Registration
			'block_frontend',
			'obfuscate_original_login_url',
			'block_frontend_grace_period',
			'enable_multiple_sites',
			'default_role',
			'add_users_to_main_site',
			'main_site_default_role',

			// Memberships
			'move_posts_on_downgrade',
			'block_sites_on_downgrade',

			// Gateways
			'active_gateways',
			'attach_invoice_pdf',

			// PayPal
			'paypal_username',
			'paypal_pass',
			'paypal_signature',
			'paypal_sandbox',
			'paypal_standard',
			'paypal_standard_email',

			// Jumper
			'jumper_key',
			'jumper_custom_links',

			// Domain Mapping
			'enable_domain_mapping',
			'custom_domains',
			'force_admin_redirect',

			// Site Templates
			'allow_template_switching',
			'allow_own_site_as_template',
			'copy_media',

			// WordPress menus
			'menu_items_plugin',
			'add_new_users',
		);

		$to_migrate = array_intersect_key($settings, array_flip($keys_to_migrate));

		/** Additional settings to migrate */

		/*
		 * Enable Registration
		 */
		$to_migrate['enable_registration'] = $this->get_old_setting('enable_signup', true);

		/*
		 * Company Address
		 */
		$to_migrate['company_address'] = $this->get_old_setting('merchant_address', true);

		/*
		 * Gateway
		 */
		$to_migrate['active_gateways'] = array_keys($this->get_old_setting('active_gateway', array()));

		/*
		* Stripe
		*/
		$is_sandbox = strpos($this->get_old_setting('stripe_api_sk', ''), 'test') !== false;

		$to_migrate['stripe_sandbox_mode'] = $is_sandbox;

		$to_migrate['stripe_should_collect_billing_address'] = $this->get_old_setting('stripe_should_collect_billing_address', false);

		if ($is_sandbox) {

			$to_migrate['stripe_test_pk_key'] = $this->get_old_setting('stripe_api_pk', '');
			$to_migrate['stripe_test_sk_key'] = $this->get_old_setting('stripe_api_sk', '');

		} else {

			$to_migrate['stripe_live_pk_key'] = $this->get_old_setting('stripe_api_pk', '');
			$to_migrate['stripe_live_sk_key'] = $this->get_old_setting('stripe_api_sk', '');

		} // end if;

		/*
		 * PayPal
		 */
		$is_paypal_sandbox                 = $this->get_old_setting('paypal_sandbox_mode', true);
		$to_migrate['paypal_sandbox_mode'] = $is_paypal_sandbox;

		if ($is_paypal_sandbox) {

			$to_migrate['paypal_test_username']  = $this->get_old_setting('paypal_username', '');
			$to_migrate['paypal_test_password']  = $this->get_old_setting('paypal_pass', '');
			$to_migrate['paypal_test_signature'] = $this->get_old_setting('paypal_signature', '');

		} else {

			$to_migrate['paypal_live_username']  = $this->get_old_setting('paypal_username', '');
			$to_migrate['paypal_live_password']  = $this->get_old_setting('paypal_pass', '');
			$to_migrate['paypal_live_signature'] = $this->get_old_setting('paypal_signature', '');

		} // end if;

		/*
		 * API Settings
		 */
		$api_key    = wp_generate_password(24);
		$api_secret = wp_generate_password(24);

		$to_migrate['enable_api']             = $this->get_old_setting('enable-api', true);
		$to_migrate['api_key']                = $this->get_old_setting('api-key', $api_key);
		$to_migrate['api_secret']             = $this->get_old_setting('api-secret', $api_secret);
		$to_migrate['api_log_calls']          = $this->get_old_setting('api-log-calls', false);
		$to_migrate['webhook_calls_blocking'] = $this->get_old_setting('webhook-calls-blocking', false);

		$to_migrate['primary_color'] = $this->get_old_setting('primary-color', false);
		$to_migrate['accent_color']  = $this->get_old_setting('accent-color', false);

		/*
		 * Top-bar Settings
		 */
		$top_bar_settings = array(
			'enabled'                     => $this->get_old_setting('allow_template_top_bar', true),
			'preview_url_parameter'       => 'template-preview',
			'bg_color'                    => $this->get_old_setting('top-bar-bg-color', '#f9f9f9'),
			'button_bg_color'             => $this->get_old_setting('top-bar-button-bg-color', '#00a1ff'),
			'button_text'                 => $this->get_old_setting('top-bar-button-text', __('Use this Template', 'wp-ultimo')),
			'display_responsive_controls' => $this->get_old_setting('top-bar-enable-resize', true),
			'use_custom_logo'             => $this->get_old_setting('top-bar-use-logo'),
			'custom_logo'                 => $this->get_old_setting('top-bar-logo'),
		);

		$save_settings = Template_Previewer::get_instance()->save_settings($top_bar_settings);

		/*
		 * Save Migrated Settings
		 */
		$status = \WP_Ultimo\Settings::get_instance()->save_settings($to_migrate);

	} // end _install_settings;

	/**
	 * Migrates Plans.
	 *
	 * @since 2.0.0
	 * @throws \Exception Halts the process on error.
	 * @return void
	 */
	protected function _install_products() {

		global $wpdb;

		/*
		 * Load dependencies.
		 */
		require_once WP_Ultimo()->helper->path('inc/functions/product.php');

		$settings = WP_Ultimo()->helper->get_option('settings');

		$product_type_plan = 'plan';
		$duration_unit_day = 'day';
		$recurring         = true;
		$currency          = isset($settings['currency']) ? $settings['currency'] : 'USD';

		$plans = $wpdb->get_results(
			"
				SELECT
					ID,
					post_title name,
					post_name slug
				FROM
					{$wpdb->base_prefix}posts
				WHERE
					post_type = 'wpultimo_plan'
			"
		);

		$default_billing = $this->get_old_setting('default_pricing_option', 1);

		foreach ($plans as $plan) {
			/*
			 * Skip errors if a plan exists.
			 */
			if (wu_get_product_by_slug($plan->slug)) {

				continue;

			} // end if;

			$product_data                     = array();
			$product_data['type']             = 'plan';
			$product_data['migrated_from_id'] = $plan->ID;
			$product_data['name']             = $plan->name;
			$product_data['slug']             = $plan->slug;
			$product_data['recurring']        = $recurring;
			$product_data['currency']         = $currency;

			$product_data['description']   = get_post_meta($plan->ID, 'wpu_description', true);
			$product_data['setup_fee']     = get_post_meta($plan->ID, 'wpu_setup_fee', true);
			$product_data['featured_plan'] = (bool) get_post_meta($plan->ID, 'wpu_top_deal', true);
			$product_data['feature_list']  = get_post_meta($plan->ID, 'wpu_feature_list', true);
			$product_data['customer_role'] = get_post_meta($plan->ID, 'wpu_role', true);
			$product_data['list_order']    = get_post_meta($plan->ID, 'wpu_order', true);

			/*
			 * Trial
			 */
			$default_trial_value                 = $this->get_old_setting('trial', 0);
			$plan_trial_value                    = get_post_meta($plan->ID, 'wpu_trial', true);
			$product_data['trial_duration']      = $plan_trial_value ? $plan_trial_value : $default_trial_value;
			$product_data['trial_duration_unit'] = $duration_unit_day;

			$active                 = !(bool) get_post_meta($plan->ID, 'wpu_hidden', true);
			$product_data['active'] = $active;

			$is_free = get_post_meta($plan->ID, 'wpu_free', true);

			$price_variations = array();

			if ($is_free) {

				$product_data['amount']       = 0;
				$product_data['pricing_type'] = 'free';

			} else {

				$price_month = get_post_meta($plan->ID, 'wpu_price_1', true);

				if ($price_month) {

					if (absint($default_billing) === 1) {

						$product_data['amount']        = $price_month;
						$product_data['duration']      = 1;
						$product_data['duration_unit'] = 'month';

					} else {

						$price_variations[] = array(
							'amount'        => $price_month,
							'duration'      => 1,
							'duration_unit' => 'month',
						);

					} // end if;

				} // end if;

				$price_3_month = get_post_meta($plan->ID, 'wpu_price_3', true);

				if ($price_3_month) {

					if (absint($default_billing) === 3) {

						$product_data['amount']        = $price_3_month;
						$product_data['duration']      = 3;
						$product_data['duration_unit'] = 'month';

					} else {

						$price_variations[] = array(
							'amount'        => $price_3_month,
							'duration'      => 3,
							'duration_unit' => 'month',
						);

					} // end if;

				} // end if;

				$price_12_month = get_post_meta($plan->ID, 'wpu_price_12', true);

				if ($price_12_month) {

					if (absint($default_billing) === 12) {

						$product_data['amount']        = $price_12_month;
						$product_data['duration']      = 1;
						$product_data['duration_unit'] = 'year';

					} else {

						$price_variations[] = array(
							'amount'        => $price_12_month,
							'duration'      => 1,
							'duration_unit' => 'year',
						);

					} // end if;

				} // end if;

				$is_contact_us                = (bool) get_post_meta($plan->ID, 'wpu_is_contact_us', true);
				$product_data['pricing_type'] = $is_contact_us ? 'contact_us' : 'paid';

			} // end if;

			/*
			 * Set the pricing variations.
			 */
			$product_data['price_variations'] = $price_variations;

			/*
			 * Gets the rest of the meta data.
			 */
			$all_product_meta = get_post_meta($plan->ID);

			/*
			 * Fixes multiple values
			 */
			$all_product_meta = array_map('array_pop', $all_product_meta);

			/*
			 * Attaches meta to product creation
			 */
			$product_data['meta'] = $all_product_meta;

			/*
			 * Creates product
			 */
			$product = wu_create_product($product_data);

			if (is_wp_error($product)) {

				throw new \Exception($product->get_error_message());

			} // end if;

			/*
			 * Migrate Quota
			 */
			$quotas          = get_post_meta($plan->ID, 'wpu_quotas', true);
			$allowed_plugins = get_post_meta($plan->ID, 'wpu_allowed_plugins', true);
			$allowed_themes  = get_post_meta($plan->ID, 'wpu_allowed_themes', true);

			/*
			 * Treat Plugins.
			 */
			$allowed_plugins = array_map(function() {

				return 'available';

			}, array_flip((array) $allowed_plugins));

			/*
			 * Treat Themes.
			 */
			$allowed_themes = array_map(function() {

				return 'available';

			}, array_flip((array) $allowed_themes));

			$modules            = array();
			$post_type_quotas   = array();
			$allowed_post_types = array();

			$disk_space      = 0;
			$visits          = 0;
			$users           = 0;
			$unlimited_users = (bool) get_post_meta($plan->ID, 'wpu_unlimited_extra_users', true);

			foreach ($quotas as $post_type => $quota) {

				if ($post_type === 'users' && !$unlimited_users) {

					$modules['users'] = true;
					$users            = (int) $quota;

				} elseif ($post_type === 'upload') {

					$modules['disk_space'] = true;
					$disk_space            = (int) $quota;

				} elseif ($post_type === 'visits') {

					$modules['visits'] = true;
					$visits            = (int) $quota;

				} else {

					$modules['post_types']          = true;
					$post_type_quotas[$post_type]   = (int) $quota;
					$allowed_post_types[$post_type] = 1;

				} // end if;

			} // end foreach;

			$user_role_quotas = array_map(function() use ($users) {

				return $users;

			}, wu_get_roles_as_options());

			$limitations = new \WP_Ultimo\Objects\Limitations(array(
				'post_type_quotas'   => $post_type_quotas,
				'allowed_post_types' => $allowed_post_types,
				'modules'            => $modules,
				'disk_space'         => $disk_space,
				'allowed_visits'     => $visits,
 				'allowed_plugins'    => $allowed_plugins,
				'allowed_themes'     => $allowed_themes,
				'user_role_quotas'   => $user_role_quotas,
			));

			$product->update_meta('wu_limitations', $limitations);

		} // end foreach;

	} // end _install_products;

	/**
	 * Migrates Customers.
	 *
	 * @since 2.0.0
	 * @throws \Exception Halts the process on error.
	 * @return void
	 */
	protected function _install_customers() {

		global $wpdb;

		/*
		 * Load dependencies.
		 */
		require_once WP_Ultimo()->helper->path('inc/functions/customer.php');

		$users = $wpdb->get_results(
			"
				SELECT
					DISTINCT user_id
				FROM
					{$wpdb->base_prefix}wu_subscriptions
			"
		);

		foreach ($users as $user) {

			if (wu_get_customer_by_user_id($user->user_id)) {

				continue;

			} // end if;

			$customer = wu_create_customer(array(
				'user_id'            => $user->user_id,
				'vip'                => false,
				'email_verification' => 'verified',
			));

			if (is_wp_error($customer)) {

				throw new \Exception($customer->get_error_message());

			} // end if;

		} // end foreach;

	} // end _install_customers;

	/**
	 * Migrates Memberships.
	 *
	 * @since 2.0.0
	 * @throws \Exception Halts the process on error.
	 * @return void
	 */
	protected function _install_memberships() {

		global $wpdb;

		/*
		 * Load dependencies.
		 */
		require_once WP_Ultimo()->helper->path('inc/functions/customer.php');
		require_once WP_Ultimo()->helper->path('inc/functions/membership.php');

		$today = gmdate('Y-m-d H:i:s');

		$subscriptions = $wpdb->get_results(
			"
				SELECT
					ID,
					user_id,
					plan_id,
					price,
					active_until
				FROM
					{$wpdb->base_prefix}wu_subscriptions
			"
		);

		foreach ($subscriptions as $subscription) {
			/*
			 * If we have already migrated, no need to do it again.
			 */
			if (wu_get_membership_by('migrated_from_id', $subscription->ID)) {

				continue;

			} // end if;

			$customer = wu_get_customer_by_user_id($subscription->user_id);

			$plan_post = get_post($subscription->plan_id);

			$plan_slug = $plan_post->post_name;

			$product = wu_get_product_by_slug($plan_slug);

			$membership_data = array();

			if ($plan_slug && wu_request('dry-run', true)) {

				$membership_data['skip_validation'] = true;

			} // end if;

			$membership_data['migrated_from_id'] = $subscription->ID;

			$membership_data['customer_id']   = $customer ? $customer->get_id() : 0;
			$membership_data['plan_id']       = $product ? $product->get_id() : 0;
			$membership_data['amount']        = $subscription->price;
			$membership_data['disabled']      = false;
			$membership_data['signup_method'] = 'migrated';

			$membership_data['status']          = $subscription->active_until < $today ? 'expired' : 'active';
			$membership_data['date_expiration'] = $subscription->active_until;

			if (empty(wu_to_float($subscription->price))) {

				$membership_data['status'] = 'active';

			} // end if;

			$membership_data = array_merge(
				$product ? $product->to_array() : array(),
				$customer ? $customer->to_array() : array(),
				$membership_data
			);

			$membership = wu_create_membership($membership_data);

			if (is_wp_error($membership)) {

				throw new \Exception($membership->get_error_message());

			} // end if;

		} // end foreach;

	} // end _install_memberships;

	/**
	 * Migrates Transactions.
	 *
	 * @since 2.0.0
	 * @throws \Exception Halts the process on error.
	 * @return void
	 */
	protected function _install_transactions() {

		global $wpdb;

		/*
		 * Load dependencies.
		 */
		require_once WP_Ultimo()->helper->path('inc/functions/membership.php');
		require_once WP_Ultimo()->helper->path('inc/functions/payment.php');

		$transactions = $wpdb->get_results(
			"
				SELECT
					*
				FROM
					{$wpdb->base_prefix}wu_transactions
			"
		);

		/**
		 * Types to skip when migrating.
		 *
		 * In the previous version, things that were not payments were also
		 * saved as transactions, such as a recurring_setup event.
		 * We need to clean those up, skipping them.
		 */
		$types_to_skip = array(
			'recurring_setup',
			'cancel',
		);

		$map_status = array(
			'payment' => Payment_Status::COMPLETED,
			'failed'  => Payment_Status::FAILED,
			'refund'  => Payment_Status::REFUND,
			'pending' => Payment_Status::PENDING,
		);

		foreach ($transactions as $transaction) {
			/*
			 * If we have already migrated, no need to do it again.
			 */
			if (wu_get_payment_by('migrated_from_id', $transaction->id)) {

				continue;

			} // end if;

			if (in_array($transaction->type, $types_to_skip, true)) {

				continue;

			} // end if;

			$membership = wu_get_membership_by('user_id', $transaction->user_id);

			$product = wu_get_product_by('migrated_from_id', $transaction->plan_id);

			$line_item = new \WP_Ultimo\Checkout\Line_Item(array(
				'product'  => $product,
				'quantity' => 1,
			));

			$line_item->set_title($transaction->description);
			$line_item->set_description($transaction->description);

			$line_item->set_unit_price(wu_to_float($transaction->amount));
			$line_item->set_subtotal(wu_to_float($transaction->amount));
			$line_item->set_total(wu_to_float($transaction->amount));

			$line_items = array(
				$line_item->get_id() => $line_item,
			);

			$payment_data = array(
				'parent'             => 0,
				'line_items'         => $line_items,
				'status'             => wu_get_isset($map_status, $transaction->type, Payment_Status::COMPLETED),
				'customer_id'        => $membership ? $membership->get_customer_id() : false,
				'membership_id'      => $membership ? $membership->get_id() : false,
				'product_id'         => $membership ? $membership->get_plan_id() : false,
				'currency'           => $membership ? $membership->get_currency() : false,
				'discount_code'      => '',
				'subtotal'           => wu_to_float($transaction->amount),
				'discount_total'     => 0,
				'tax_total'          => 0,
				'total'              => wu_to_float($transaction->amount),
				'gateway'            => $transaction->gateway,
				'gateway_payment_id' => $transaction->reference_id,
				'migrated_from_id'   => $transaction->id,
				'date_created'       => $transaction->time,
				'date_modified'      => $transaction->time,
			);

			$payment = wu_create_payment($payment_data);

			if (is_wp_error($payment)) {

				throw new \Exception($payment->get_error_message());

			} // end if;

		} // end foreach;

	} // end _install_transactions;

	/**
	 * Migrates Sites.
	 *
	 * @since 2.0.0
	 * @throws \Exception Halts the process on error.
	 * @return void
	 */
	protected function _install_sites() {

		global $wpdb;

		/*
		 * Load dependencies.
		 */
		require_once WP_Ultimo()->helper->path('inc/functions/customer.php');
		require_once WP_Ultimo()->helper->path('inc/functions/membership.php');
		require_once WP_Ultimo()->helper->path('inc/functions/site.php');

		$site_owners = $wpdb->get_results(
			"
				SELECT
					site_id,
					user_id
				FROM
					{$wpdb->base_prefix}wu_site_owner
			"
		);

		foreach ($site_owners as $site_owner) {

			$site     = wu_get_site($site_owner->site_id);
			$customer = wu_get_customer_by_user_id($site_owner->user_id);

			$membership = wu_get_membership_by('user_id', $site_owner->user_id);

			if (!$site) {

				continue;

			} // end if;

			if ($customer) {

				$site->set_customer_id($customer->get_id());

			} // end if;

			if ($membership) {

				$site->set_membership_id($membership->get_id());

			} // end if;

			$site->set_type('customer_owned');

			$saved = $site->save();

			if (is_wp_error($saved)) {

				throw new \Exception($saved->get_error_message());

			} // end if;

		} // end foreach;

		$templates = array_flip($this->get_old_setting('templates', array()));

		foreach ($templates as $template_id) {

			$site_template = wu_get_site($template_id);

			$site_template->set_type('site_template');

			/**
			 * Get Categories
			 */
			$categories_string = get_blog_option($site_template->get_id(), 'wu_categories', false);

			$site_template->set_categories(explode(',', $categories_string));

			$saved = $site_template->save();

			if (is_wp_error($saved)) {

				throw new \Exception($saved->get_error_message());

			} // end if;

		} // end foreach;

	} // end _install_sites;

	/**
	 * Migrates domains.
	 *
	 * @since 2.0.0
	 * @throws \Exception Halts the process on error.
	 * @return void
	 */
	protected function _install_domains() {

		global $wpdb;

		/*
		 * Load dependencies.
		 */
		require_once WP_Ultimo()->helper->path('inc/functions/domain.php');

		$wpdb->suppress_errors();

		$domains = $wpdb->get_results(
			"
				SELECT
					blog_id,
					domain name,
					active
				FROM
					{$wpdb->base_prefix}domain_mapping
			"
		);

		foreach ($domains as $domain) {

			$domain = wu_create_domain(array(
				'domain'         => $domain->name,
				'stage'          => 'done',
				'blog_id'        => $domain->blog_id,
				'active'         => $domain->active,
				'primary_domain' => true,
				'secure'         => false,
			));

			if (is_wp_error($domain)) {

				throw new \Exception($domain->get_error_message());

			} // end if;

		} // end foreach;

	} // end _install_domains;

	/**
	 * Migrates Checkout Forms.
	 *
	 * @since 2.0.0
	 * @throws \Exception Halts the process on error.
	 * @return void
	 */
	protected function _install_forms() {
		/*
		 * Load dependencies.
		 */
		require_once WP_Ultimo()->helper->path('inc/deprecated/deprecated.php');
		require_once WP_Ultimo()->helper->path('inc/functions/legacy.php');
		require_once WP_Ultimo()->helper->path('inc/functions/checkout-form.php');
		require_once WP_Ultimo()->helper->path('inc/functions/site.php');

		/*
			* Skip errors if a checkout form exists.
			*/
		if (wu_get_checkout_form_by_slug('main-form')) {

			return;

		} // end if;

		$checkout_form = array(
			'name'              => __('Signup Form', 'wp-ultimo'),
			'slug'              => 'main-form',
			'allowed_countries' => $this->get_old_setting('allowed_countries', array()),
			'settings'          => array(),
		);

		$status = wu_create_checkout_form($checkout_form);

		if (is_wp_error($status)) {

			throw new \Exception($status->get_error_message());

		} else {

			$steps = Legacy_Checkout::get_instance()->get_steps();

			$steps = Checkout_Form::convert_steps_to_v2($steps, $this->get_old_settings());

			$status->set_settings($steps);

			$status->save();

		} // end if;

		$post_content = '
			<!-- wp:shortcode -->
				[wu_checkout slug="%s"]
			<!-- /wp:shortcode -->
		';

		/*
		 * Get post name based on setting for register page
		 */
		$page_slug = $this->get_old_setting('registration_url', 'register');
		$page_slug = trim($page_slug, '/');

		/*
		 * Create the page on the main site.
		 */
		$post_details = array(
			'post_name'    => $page_slug,
			'post_title'   => __('Signup', 'wp-ultimo'),
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => sprintf($post_content, $status->get_slug()),
			'post_author'  => get_current_user_id(),
		);

		$page_id = wp_insert_post($post_details);

		if (is_wp_error($page_id)) {

			throw new \Exception($page_id->get_error_message());

		} // end if;

		/*
		 * Set the legacy template.
		 */
		update_post_meta($page_id, '_wp_page_template', 'signup-main.php');

		/*
		 * Set page as the default registration page.
		 */
		wu_save_setting('default_registration_page', $page_id);

		/*
		 * Get post name based on setting for login page
		 */
		$login_page_slug = $this->get_old_setting('login_url', false);

		if (!$login_page_slug) {

			return; // Bail if no login customization.

		} // end if;

		$login_page_slug = trim($login_page_slug, '/');

		/*
		 * Create the page on the main site.
		 */
		$login_post_details = array(
			'post_name'    => $login_page_slug,
			'post_title'   => __('Login', 'wp-ultimo'),
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
			'post_author'  => get_current_user_id(),
		);

		$login_page_id = wp_insert_post($login_post_details);

		if (is_wp_error($login_page_id)) {

			throw new \Exception($login_page_id->get_error_message());

		} // end if;

		/*
		 * Set page as the default login page.
		 */
		wu_save_setting('default_login_page', $login_page_id);

		wu_save_setting('enable_custom_login_page', true);

	} // end _install_forms;

	/**
	 * Migrates Emails.
	 *
	 * @todo Needs implementing.
	 * @since 2.0.0
	 * @throws \Exception Halts the process on error.
	 * @return void
	 */
	protected function _install_emails() {

		global $wpdb;

		require_once WP_Ultimo()->helper->path('inc/functions/webhook.php');

		$broadcasts = $wpdb->get_results(
			"
				SELECT
					ID,
					post_title,
					post_content
				FROM
					{$wpdb->base_prefix}posts
				WHERE
					post_type = 'wpultimo_broadcast'
			"
		);

		foreach ($broadcasts as $broadcast) {

			$old_type = get_post_meta($broadcast->ID, 'wpu_type', true);

			$new_type = $old_type === 'message' ? 'broadcast_notice' : 'broadcast_email';

			$broadcast = wu_create_broadcast(array(
				'name'    => $broadcast->post_title,
				'content' => $broadcast->post_content,
				'type'    => $new_type,
				'style'   => get_post_meta($broadcast->ID, 'wpu_style', 'success'),
			));

			if (is_wp_error($broadcast)) {

				throw new \Exception($broadcast->get_error_message());

			} // end if;

		} // end foreach;

	} // end _install_emails;

	/**
	 * Migrates Webhooks.
	 *
	 * @since 2.0.0
	 * @throws \Exception Halts the process on error.
	 * @return void
	 */
	protected function _install_webhooks() {

		global $wpdb;

		require_once WP_Ultimo()->helper->path('inc/functions/webhook.php');

		$webhooks = $wpdb->get_results(
			"
				SELECT
					ID,
					post_title
				FROM
					{$wpdb->base_prefix}posts
				WHERE
					post_type = 'wpultimo_webhook'
			"
		);

		foreach ($webhooks as $webhook) {

			$webhook = wu_create_webhook(array(
				'name'             => $webhook->post_title,
				'migrated_from_id' => $webhook->ID,
				'webhook_url'      => get_post_meta($webhook->ID, 'wpu_url', true),
				'event'            => get_post_meta($webhook->ID, 'wpu_event', true),
				'active'           => get_post_meta($webhook->ID, 'wpu_active', true),
			));

			if (is_wp_error($webhook)) {

				throw new \Exception($webhook->get_error_message());

			} // end if;

		} // end foreach;

	} // end _install_webhooks;

} // end class Migrator;
