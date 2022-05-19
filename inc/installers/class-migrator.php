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
use \WP_Ultimo\Database\Memberships\Membership_Status;
use \WP_Ultimo\Database\Payments\Payment_Status;
use \WP_Ultimo\Database\Sites\Site_Type;
use \WP_Ultimo\Managers\Domain_Manager;
use \WP_Ultimo\Managers\Limitation_Manager;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo 1.X to 2.X migrator.
 *
 * @since 2.0.0
 */
class Migrator extends Base_Installer {

	use \WP_Ultimo\Traits\Singleton;

	const LOG_FILE_NAME = 'migrator-errors';

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
	 * Back traces holder.
	 *
	 * @since 2.0.7
	 * @var array
	 */
	public $back_traces;

	/**
	 * Legacy settings cache.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $settings;

	/**
	 * Dry run status.
	 *
	 * @since 2.0.7
	 * @var boolean
	 */
	protected $dry_run = true;

	/**
	 * If we should run tests on a limited set or not.
	 *
	 * @since 2.0.7
	 * @var boolean
	 */
	protected $run_tests_on_limited_set = false;

	/**
	 * List of ids of interest.
	 *
	 * @since 2.0.7
	 * @var array
	 */
	protected $ids_of_interest = array();

	/**
	 * The status of our attempts to bypass server limitations.
	 *
	 * This will return false if the bypass was never tried, true if it was successful,
	 * or a WP_Error object containing the errors.
	 *
	 * @since 2.0.7
	 * @var bool|\WP_Error
	 */
	protected $server_bypass_status = true;

	/**
	 * Initializes the session object to keep track of errors.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		if ($this->is_migration_screen()) {

			$this->session = wu_get_session('migrator');

			$this->errors = $this->session->get('errors');

			$this->back_traces = $this->session->get('back_traces');

		} // end if;

		/*
		 * Install the handler for the parallel installer.
		 */
		\WP_Ultimo\Async_Calls::register_listener('parallel_installers', array($this, 'handle_parallel_installers'));

		\WP_Ultimo\Async_Calls::install_listeners();

	} // end init;

	/**
	 * Handle the parallel installers via the Async Caller.
	 *
	 * @since 2.0.7
	 * @return array
	 */
	public function handle_parallel_installers() {

		ob_start();

		do_action('wp_ajax_wu_setup_install'); // phpcs:ignore

		$results = json_decode(ob_get_clean(), true);

		return $results;

	} // end handle_parallel_installers;

	/**
	 * Handles unexpected shutdowns of the PHP process.
	 *
	 * @since 2.0.7
	 *
	 * @param \WP_Ultimo\Session $session The session handler object.
	 * @param bool               $dry_run If we are in dry run mode or not.
	 * @param string             $installer The name of the current installer.
	 * @return void
	 */
	public function on_shutdown($session, $dry_run, $installer) {

		$message = 'The migrator process exit unexpectedly while running the "%s" migration... You might need to increase server resources to run the migrator. Below, a list of the ids of interest collected thus far:';

		wu_log_add(Migrator::LOG_FILE_NAME, sprintf($message, $installer));

		$this->log_ids_of_interest();

		$e = new \Exception('The migrator process exit unexpectedly');

		$this->handle_error_messages($e, $session, $dry_run, $installer);

	} // end on_shutdown;

	/**
	 * Checks if we are on the migration screen to prevent issues.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	protected function is_migration_screen() {

		return wu_request('page') === 'wp-ultimo-setup' && wu_request('step') === 'migration';

	} // end is_migration_screen;

	/**
	 * Checks if the migration is done.
	 *
	 * @since 2.0.7
	 * @return boolean
	 */
	public static function is_migration_done() {

		if (self::is_legacy_network() === false) {
			/*
			 * For all intents and purposes, a fresh install
			 * can be considered as "migrated", as there is nothing
			 * to migrate.
			 */
			return true;

		} // end if;

		return get_network_option(null, 'wu_is_migration_done', false);

	} // end is_migration_done;

	/**
	 * Check if we are running on a network that runs Ultimo 1.X
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public static function is_legacy_network() {

		global $wpdb;

		/*
		 * First strategy: check if we have plans.
		 */
		$plans = get_posts(array(
			'post_type'   => 'wpultimo_plan',
			'numberposts' => 1,
		));

		if (empty($plans)) {

			return false;

		} // end if;

		$wpdb->hide_errors();

		try {

			$v2_data = $wpdb->get_var("
				SELECT id FROM {$wpdb->base_prefix}wu_forms as f LIMIT 1
			");

			$wpdb->show_errors();

			return empty($v2_data);

		} catch (\Throwable $th) {

			$wpdb->show_errors();

			return false;

		} // end try;

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
	 * Returns the list of _backtraces detected.
	 *
	 * @since 2.0.7
	 * @return array
	 */
	public function get_back_traces() {

		return array_unique((array) $this->back_traces);

	} // end get_back_traces;

	/**
	 * Returns the list of migration steps.
	 *
	 * @since 2.0.0
	 * @param bool $force_all If you want to get all the steps despite of dry run status.
	 * @return array
	 */
	public function get_steps($force_all = false) {

		$steps = array();

		$dry_run = wu_request('dry-run', true);

		if ($dry_run && !$force_all) {

			$steps['dry_run_check'] = array(
				'title'       => __('Pre-Migration Check', 'wp-ultimo'),
				'description' => __('Runs all migrations in a sand-boxed environment to see if it hits an error.', 'wp-ultimo'),
				'help'        => wu_get_documentation_url('migration-errors'),
				'pending'     => __('Pending', 'wp-ultimo'),
				'installing'  => __('Checking...', 'wp-ultimo'),
				'success'     => __('Success!', 'wp-ultimo'),
				'done'        => false,
			);

			return $steps;

		} // end if;

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
			'description' => __('Converts the old plans into products.', 'wp-ultimo'),
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

		$steps['discount_codes'] = array(
			'title'       => __('Coupons to Discount Codes', 'wp-ultimo'),
			'description' => __('Converts coupons into discount codes.', 'wp-ultimo'),
			'help'        => wu_get_documentation_url('migration-errors'),
			'done'        => false,
		);

		$steps['sites'] = array(
			'title'       => __('Customer Sites', 'wp-ultimo'),
			'description' => __('Adjusts existing customer sites.', 'wp-ultimo'),
			'installing'  => __('Making Adjustments...', 'wp-ultimo'),
			'help'        => wu_get_documentation_url('migration-errors'),
			'done'        => false,
		);

		$steps['site_templates'] = array(
			'title'       => __('Sites Templates', 'wp-ultimo'),
			'description' => __('Adjusts existing site templates.', 'wp-ultimo'),
			'installing'  => __('Making Adjustments...', 'wp-ultimo'),
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

		$steps['other'] = array(
			'title'       => __('Other Migrations', 'wp-ultimo'),
			'description' => __('Other migrations that don\'t really fit anywhere else.', 'wp-ultimo'),
			'help'        => wu_get_documentation_url('migration-errors'),
			'done'        => false,
		);

		$steps = array_map(function($item) {

			return wp_parse_args($item, array(
				'pending'    => __('Pending', 'wp-ultimo'),
				'installing' => __('Migrating...', 'wp-ultimo'),
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
	 * Tries to bypass server limitations such as memory and time limits.
	 *
	 * @since 2.0.7
	 * @return void
	 */
	protected function bypass_server_limits() {

		$error = new \WP_Error;

		$message = 'Unable to set %s';

		/*
		 * Attempt to set the memory limit.
		 */
		$set_memory_limit = @ini_set('memory_limit', '-1'); // phpcs:ignore

		if ($set_memory_limit === false) {

			$error->add('memory_limit', sprintf($message, 'memory_limit'));

		} // end if;

		/*
		 * Attempt to set the time limit.
		 */
		$set_time_limit = @set_time_limit(0); // phpcs:ignore

		if ($set_time_limit === false) {

			$error->add('time_limit', sprintf($message, 'time_limit'));

		} // end if;

		/*
		 * It's interesting if we can prevent errors from showing up and breaking
		 * the ajax response, but if we can't set it, it doesn't really matter in terms
		 * of being able to execute the migrations or not.
		 *
		 * This can be gracefully handled on the front-end if necessary.
		 */
		@ini_set('display_errors', 0); // phpcs:ignore

		if ($error->has_errors()) {

			$this->server_bypass_status = $error;

			return;

		} // end if;

		return true;

	} // end bypass_server_limits;

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

		global $wpdb, $wu_migrator_current_installer;

		/*
		 * Set the dry run status, which is true by default.
		 */
		$this->dry_run = wu_request('dry-run', true);

		$callable = array($this, "_install_{$installer}");

		$callable = apply_filters("wu_installer_{$installer}_callback", $callable, $installer);

		/*
		* No installer on this class.
		*/
		if (!is_callable($callable)) {

			return $status;

		} // end if;

		/*
		 * Try to bypass the server limits such as timeouts, etc.
		 */
		$this->bypass_server_limits();

		$opening_log_message = sprintf('Migration starting for %s with dry mode %s...', $installer, $this->dry_run ? 'on' : 'off');

		if ($this->is_parallel()) {

			$opening_log_message .= sprintf(' This is a parallel request with a page attribute of %d and a per_page attribute of %d', wu_request('page', 0), wu_request('per_page', 0));

		} // end if;

		wu_log_add(Migrator::LOG_FILE_NAME, $opening_log_message);

		/*
		 * Check if we had any errors on setting the server limits.
		 * If that's the case, we only run the dry run in a limited number of items.
		 *
		 * That's no ideal in detecting possible errors, but let's face it,
		 * if some migration can break, it will probably break within the first 10-20
		 * records anyway...
		 */
		if (is_wp_error($this->server_bypass_status)) {
			/*
			 * Set the flag that tells the migrator to only run on
			 * a limited set of records for the check.
			 */
			$this->run_tests_on_limited_set = true;

			$log_message = implode(PHP_EOL, $this->server_bypass_status->get_error_messages());

			wu_log_add(Migrator::LOG_FILE_NAME, $log_message);

		} // end if;

		$session = wu_get_session('migrator');

		/*
		 * Registers a shutdown handler.
		 */
		// register_shutdown_function(array($this, 'on_shutdown'), $session, $this->dry_run, $installer);

		try {

			wp_cache_flush();

			$wpdb->query('START TRANSACTION');

			call_user_func($callable, wu_request('per_page'), wu_request('page'));

		} catch (\Throwable $e) {

			$wpdb->query('ROLLBACK');

			/*
			 * Log errors to later reference.
			 */
			wu_log_add(Migrator::LOG_FILE_NAME, $e->__toString());

			return $this->handle_error_messages($e, $session, $this->dry_run, $installer);

		} // end try;

		/*
		 * Log ids of interest.
		 */
		$this->log_ids_of_interest();

		/*
		 * Commit or rollback depending on the status
		 */
		if ($this->dry_run) {

			$wpdb->query('ROLLBACK');

		} else {

			$wpdb->query('COMMIT');

			wp_cache_flush();

		} // end if;

		if ($session) {

			$session->set('errors', array());

			$session->set('back_traces', array());

		} // end if;

		return $status;

	} // end handle;

	/**
	 * Handles error messages and exceptions so we can display them and log them.
	 *
	 * @since 2.0.7
	 *
	 * @param \Throwable|null    $e The exception thrown.
	 * @param \WP_Ultimo\Session $session THe WP Ultimo session object.
	 * @param boolean            $dry_run If we are on a dry run or not.
	 * @param string             $installer The name of the installer.
	 * @return \WP_Error
	 */
	public function handle_error_messages($e, $session, $dry_run = true, $installer = 'none') {

		global $wu_migrator_current_installer;

		$caller = $dry_run ? $wu_migrator_current_installer : $installer;

		$error_nice_message = sprintf(__('Critical error found when migrating "%s".', 'wp-ultimo'), $caller);

		if ($session) {

			$errors = (array) $session->get('errors');

			$back_traces = (array) $session->get('back_traces');

			$errors[] = $error_nice_message;

			$back_traces[] = $e->__toString();

			$session->set('errors', $errors);

			$session->set('back_traces', $back_traces);

		} // end if;

		return new \WP_Error($installer, $error_nice_message);

	} // end handle_error_messages;

	/**
	 * Dumps ids of interest on the log so we can revise them later id needed.
	 *
	 * @since 2.0.7
	 * @return void
	 */
	public function log_ids_of_interest() {

		if (!is_array($this->ids_of_interest)) {

			wu_log_add(Migrator::LOG_FILE_NAME, 'The list of IDs of interested got corrupted, this might indicate problems with one or more migrations');

			return;

		} // end if;

		foreach ($this->ids_of_interest as $key => $ids) {

			list($installer, $reason) = explode(':', $key);

			if (empty($ids)) {

				continue;

			} // end if;

			$id_list = implode(PHP_EOL, $ids);

			$message = sprintf('The following IDs where skipped from the "%s" migration due to an status of "%s": %s', $installer, $reason, PHP_EOL . $id_list);

			wu_log_add(Migrator::LOG_FILE_NAME, $message);

		} // end foreach;

	} // end log_ids_of_interest;

	/**
	 * Add an id of interest to the list.
	 *
	 * @since 2.0.7
	 *
	 * @param string|array $id_or_ids One or more ids to be added.
	 * @param string       $reason The reason why this is an id of interest.
	 * @param string       $installer The installer name.
	 * @return void
	 */
	public function add_id_of_interest($id_or_ids, $reason, $installer) {

		$id_list_key = sprintf('%s:%s', $installer, $reason);

		$id_list = wu_get_isset($this->ids_of_interest, $id_list_key, array());

		$id_or_ids = (array) $id_or_ids;

		$this->ids_of_interest[$id_list_key] = array_merge($id_list, $id_or_ids);

	} // end add_id_of_interest;

	/**
	 * Generate the database dump as a backup.
	 *
	 * @since 2.0.0
	 * @throws \Exception Halts the process on error.
	 * @return mixed
	 */
	public function _install_dry_run_check() {

		global $wpdb, $wu_migrator_current_installer;

		$all_steps = $this->get_steps(true);

		foreach ($all_steps as $installer => $step) {

			$wu_migrator_current_installer = $installer;

			$callable = array($this, "_install_{$installer}");

			call_user_func($callable);

		} // end foreach;

	} // end _install_dry_run_check;

	/**
	 * Generate the database dump as a backup.
	 *
	 * @since 2.0.0
	 * @throws \Exception Halts the process on error.
	 * @return mixed
	 */
	public function _install_backup() {

		global $wpdb;

		$folder = wu_maybe_create_folder('wu-backup');

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

		$value = wu_get_isset($settings, $setting, $default);

		return $value;

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
		wu_save_option("v1_settings_{$random_key}", $settings);

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

			// Other options we want to keep.
			'limits_and_quotas',
		);

		$to_migrate = array_intersect_key($settings, array_flip($keys_to_migrate));

		/** Additional settings to migrate */
		$to_migrate['primary_color'] = $this->get_old_setting('primary-color', '#00a1ff');
		$to_migrate['accent_color']  = $this->get_old_setting('accent-color', '#78b336');

		/*
		 * Enable Registration
		 */
		$to_migrate['enable_registration'] = $this->get_old_setting('enable_signup', true);

		/*
		 * Activate Multiple Accounts
		 *
		 * @since 2.0.7
		 */
		$to_migrate['enable_multiple_accounts'] = class_exists('\WU_Multiple_Logins');

		/*
		 * Company Address
		 */
		$to_migrate['company_address'] = $this->get_old_setting('merchant_address', true);

		/*
		 * Gateway
		 */
		$active_gateways               = array_keys($this->get_old_setting('active_gateway', array()));
		$to_migrate['active_gateways'] = $active_gateways;

		/*
		* Stripe
		*/
		$is_sandbox = strpos($this->get_old_setting('stripe_api_sk', ''), 'test') !== false;

		$to_migrate['stripe_sandbox_mode'] = $is_sandbox;

		$to_migrate['stripe_should_collect_billing_address'] = $this->get_old_setting('stripe_should_collect_billing_address', false);

		$to_migrate['stripe_test_pk_key'] = $this->get_old_setting('stripe_api_pk', '');

		$to_migrate['stripe_test_sk_key'] = $this->get_old_setting('stripe_api_sk', '');

		$to_migrate['stripe_live_pk_key'] = $this->get_old_setting('stripe_api_pk', '');

		$to_migrate['stripe_live_sk_key'] = $this->get_old_setting('stripe_api_sk', '');

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
		 * Fake registers missing settings so we can save them.
		 */
		$this->fake_register_settings($to_migrate);

		/*
		 * Save Migrated Settings.
		 */
		$status = \WP_Ultimo\Settings::get_instance()->save_settings($to_migrate);

	} // end _install_settings;

	/**
	 * Register missing setting keys so they can be saved.
	 *
	 * @since 2.0.7
	 *
	 * @param array $to_migrate The list of settings to migrate.
	 * @return array
	 */
	public function fake_register_settings($to_migrate = array()) {

		add_filter('wu_settings_section_core_fields', function($fields) use ($to_migrate) {

			$all_keys = \WP_Ultimo\Settings::get_instance()->get_all();

			$missing_keys = array_diff_key($to_migrate, $all_keys);

			foreach ($missing_keys as $field_slug => $value) {

				$fields[$field_slug] = array(
					'type'       => 'hidden',
					'setting_id' => $field_slug,
					'raw'        => true,
				);

			} // end foreach;

			return $fields;

		}, 10);

		\WP_Ultimo\Settings::get_instance()->default_sections();

		$sections = \WP_Ultimo\Settings::get_instance()->get_sections();

		return $sections;

	} // end fake_register_settings;

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
		require_once wu_path('inc/functions/product.php');

		$settings = wu_get_option('settings');

		$product_type_plan = 'plan';
		$duration_unit_day = 'month';
		$recurring         = true;
		$currency          = isset($settings['currency']) ? $settings['currency'] : 'USD';

		$plans = $wpdb->get_results(
			"
				SELECT
					ID,
					post_title name,
					post_name slug,
					post_date,
					post_modified
				FROM
					{$wpdb->base_prefix}posts
				WHERE
					post_type = 'wpultimo_plan'
				ORDER BY ID ASC
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

			/*
			 * Set the dates.
			 */
			$product_data['date_created']  = $plan->post_date;
			$product_data['date_modified'] = $plan->post_modified;

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
			$quotas              = (array) get_post_meta($plan->ID, 'wpu_quotas', true);
			$disabled_post_types = array_keys((array) get_post_meta($plan->ID, 'wpu_disabled_post_types', true));
			$allowed_plugins     = (array) get_post_meta($plan->ID, 'wpu_allowed_plugins', true);
			$allowed_themes      = (array) get_post_meta($plan->ID, 'wpu_allowed_themes', true);
			$has_custom_domain   = (bool) get_post_meta($plan->ID, 'wpu_custom_domain', true);

			$site_template_mode             = 'default';
			$has_template_selection_options = (bool) $this->get_old_setting('allow_template', true);
			$templates                      = array_filter(array_keys((array) get_post_meta($plan->ID, 'wpu_templates', true)));
			$has_custom_template_list       = (bool) get_post_meta($plan->ID, 'wpu_override_templates', true);
			$site_template_enabled          = true;
			$force_template                 = false;

			if ($has_template_selection_options === false) {

				$force_template = get_post_meta($plan->ID, 'wpu_site_template', true);

				if ($force_template && wu_get_site($force_template)) {

					$site_template_mode = 'assign_template';

				} // end if;

				$has_custom_template_list = false;

			} elseif ($has_custom_template_list) {

				$site_template_mode = 'choose_available_templates';

				if (empty($templates)) {

					$site_template_enabled = false;

				} // end if;

			} // end if;

			/*
			 * Build template list.
			 */
			$site_templates_limit = array();

			$available_site_templates = wu_get_site_templates();

			foreach ($available_site_templates as $site_template) {

				$site_template_id = absint($site_template->get_id());

				$behavior = $has_custom_template_list ? 'not_available' : 'available';

				if ($site_template_id === absint($force_template)) {

					$behavior = 'pre_selected';

				} elseif (in_array($site_template_id, $templates)) { // phpcs:ignore;

					$behavior = 'available';

				} // end if;

				$site_templates_limit[$site_template_id] = array(
					'behavior' => $behavior,
				);

			} // end foreach;

			/**
			 * Limitation modules.
			 */
			$limitations_modules = array(
				'domain_mapping' => array(
					'enabled' => $has_custom_domain,
				),
				'customer_user_role' => array(
					'enabled' => true,
					'limit'   => $product_data['customer_role'],
				),
				'site_templates' => array(
					'mode'    => $site_template_mode,
					'enabled' => $site_template_enabled,
					'limit'   => $site_templates_limit,
				),
			);

			/**
			 * Build the plugins limitations object.
			 *
			 * @since 2.0.7
			 */
			if (is_array($allowed_plugins)) {

				$allowed_plugins = $allowed_plugins;

				$plugins_limit = array();

				foreach (Limitation_Manager::get_instance()->get_all_plugins() as $plugin_path => &$plugin_data) {

					$plugins_limit[$plugin_path] = array(
						'visibility' => in_array($plugin_path, $allowed_plugins, true) ? 'visible' : 'hidden',
						'behavior'   => in_array($plugin_path, $allowed_plugins, true) ? 'available' : 'not_available',
					);

				} // end foreach;

				$limitations_modules['plugins'] = array(
					'limit' => $plugins_limit,
				);

			} // end if;

			/**
			 * Build the themes limitations object.
			 *
			 * @since 2.0.7
			 */
			if (is_array($allowed_themes)) {

				$themes_limit = array();

				foreach (Limitation_Manager::get_instance()->get_all_themes() as $stylesheet => &$theme_data) {

					$themes_limit[$stylesheet] = array(
						'visibility' => in_array($stylesheet, $allowed_themes, true) ? 'visible' : 'hidden',
						'behavior'   => in_array($stylesheet, $allowed_themes, true) ? 'available' : 'not_available',
					);

				} // end foreach;

				$limitations_modules['themes'] = array(
					'limit' => $themes_limit,
				);

			} // end if;

			$unlimited_users = (bool) get_post_meta($plan->ID, 'wpu_unlimited_extra_users', true);

			$post_types_limit = array();

			foreach ($quotas as $post_type => $quota) {

				if ($post_type === 'users' && !$unlimited_users) {

					$user_roles = get_editable_roles();

					$roles_limit = array();

					foreach ($user_roles as $role => $role_data) {

						$roles_limit[$role] = array(
							'enabled' => true,
							'number'  => $quota ? absint($quota) : '',
						);

					} // end foreach;

					$limitations_modules['users'] = array(
						'limit'   => $roles_limit,
						'enabled' => true,
					);

				} elseif ($post_type === 'upload') {

					$limitations_modules['disk_space'] = array(
						'limit'   => absint($quota),
						'enabled' => true,
					);

				} elseif ($post_type === 'visits') {

					$limitations_modules['visits'] = array(
						'limit'   => $quota ? absint($quota) : '',
						'enabled' => true,
					);

				} elseif ($post_type === 'sites') {

					$limitations_modules['sites'] = array(
						'limit'   => absint($quota),
						'enabled' => true,
					);

				} else {

					$post_types_limit[$post_type] = array(
						'enabled' => in_array($post_type, $disabled_post_types, true) === false,
						'number'  => $quota ? absint($quota) : '',
					);

				} // end if;

			} // end foreach;

			/**
			 * If there's any limitations to post types, add it.
			 */
			if ($post_types_limit) {

				$limitations_modules['post_types'] = array(
					'limit'   => $post_types_limit,
					'enabled' => true,
				);

			} // end if;

			$limitations = new \WP_Ultimo\Objects\Limitations($limitations_modules);

			$product->update_meta('wu_limitations', $limitations);

		} // end foreach;

	} // end _install_products;

	/**
	 * Verifies if we are inside a parallel request or not.
	 *
	 * @since 2.0.7
	 * @return boolean
	 */
	protected function is_parallel() {

		return wu_request('parallel') && wu_request('page') && wu_request('per_page');

	} // end is_parallel;

	/**
	 * Get the installer name based on the request.
	 *
	 * This method is useful especially inside the dry_run test,
	 * where all installers are run, but the only installer actually
	 * being called is the dry_run check. With this method,
	 * we can have access to the correct installer even on that
	 * scenario.
	 *
	 * @since 2.0.7
	 * @return string
	 */
	protected function get_installer() {

		global $wu_migrator_current_installer;

		return $this->dry_run ? $wu_migrator_current_installer : wu_request('installer', '');

	} // end get_installer;

	/**
	 * Decides if we should run this in parallel, based on the request.
	 *
	 * @since 2.0.7
	 *
	 * @param int $total_records The total number of records.
	 * @param int $threshold The threshold separating normal processing and parallel processing.
	 * @return void
	 */
	protected function maybe_run_in_parallel($total_records, $threshold = 100) {

		if ($this->dry_run) {

			return;

		} // end if;

		/*
		 * If this request is not a parallel one,
		 * we need to decide if we want to break it up
		 * based on the total number of resources.
		 */
		if ($this->is_parallel() === false) {

			if ($total_records > $threshold) {

				$args = array(
					'installer' => $this->get_installer(),
					'dry-run'   => $this->dry_run,
				);

				$result = \WP_Ultimo\Async_Calls::run('parallel_installers', $args, $total_records, $threshold, 10);

				if (is_wp_error($result)) {

					throw new \Exception($result->get_error_message());

				} // end if;

				return;

			} // end if;

		} // end if;

	} // end maybe_run_in_parallel;

	/**
	 * Builds an SQL limit clause to be used inside the installers.
	 *
	 * @since 2.0.7
	 * @return string
	 */
	protected function build_limit_clause() {
		/*
		 * Create an empty limit clause.
		 * This can be changed if we are running in parallel.
		 */
		$limit_clause = '';

		if ($this->dry_run) {

			return sprintf('LIMIT %d', 10);

		} elseif ($this->is_parallel()) {

			$page     = absint(wu_request('page'));
			$per_page = absint(wu_request('per_page'));
			$offset   = ($page - 1) * $per_page;

			$limit_clause = sprintf('LIMIT %d,%d', $offset, $per_page);

		} // end if;

		return $limit_clause;

	} // end build_limit_clause;

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
		 * We need the total number of records
		 * to decided if we must run this on the
		 * background or not.
		 */
		$total_records = (int) $wpdb->get_var("SELECT count(ID) FROM {$wpdb->base_prefix}wu_subscriptions");

		/*
		 * Pass the value to the decider,
		 * if we need to run in parallel,
		 * the method handled it gracefully
		 */
		$this->maybe_run_in_parallel($total_records);

		/*
		 * Calculates the limit clause.
		 */
		$limit_clause = $this->build_limit_clause();

		/*
		 * Load dependencies.
		 */
		require_once wu_path('inc/functions/customer.php');

		 // phpcs:disable
		$users = $wpdb->get_results(
			"
				SELECT
					user_id,
					created_at
				FROM
					{$wpdb->base_prefix}wu_subscriptions
				{$limit_clause}
			"
		);
		// phpcs:enable

		foreach ($users as $user) {

			if (wu_get_customer_by_user_id($user->user_id)) {

				continue;

			} // end if;

			$customer = wu_create_customer(array(
				'user_id'            => $user->user_id,
				'email_verification' => 'verified',
				'vip'                => false,
				'date_registered'    => $user->created_at,
				'last_login'         => $user->created_at,
			));

			if (is_wp_error($customer)) {

				if ($customer->get_error_code() !== 'empty_username') {

					throw new \Exception($customer->get_error_message());

				} else {

					$this->add_id_of_interest($user->user_id, 'not_found', 'customers');

				} // end if;

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
		 * We need the total number of records
		 * to decided if we must run this on the
		 * background or not.
		 */
		$total_records = (int) $wpdb->get_var("SELECT count(ID) FROM {$wpdb->base_prefix}wu_subscriptions");

		/*
		 * Pass the value to the decider,
		 * if we need to run in parallel,
		 * the method handled it gracefully
		 */
		$this->maybe_run_in_parallel($total_records);

		/*
		 * Calculates the limit clause.
		 */
		$limit_clause = $this->build_limit_clause();

		/*
		 * Load dependencies.
		 */
		require_once wu_path('inc/functions/date.php');
		require_once wu_path('inc/functions/customer.php');
		require_once wu_path('inc/functions/membership.php');

		$today = gmdate('Y-m-d H:i:s');

		 // phpcs:disable
		$subscriptions = $wpdb->get_results(
			"
				SELECT
					ID,
					user_id,
					plan_id,
					price,
					trial,
					active_until,
					created_at,
					gateway,
					integration_key,
					integration_status,
					last_plan_change,
					meta_object as meta
				FROM
					{$wpdb->base_prefix}wu_subscriptions
				{$limit_clause}
			"
		);
		// phpcs:enable

		foreach ($subscriptions as $subscription) {
			/*
			 * If we have already migrated, no need to do it again.
			 */
			if (wu_get_membership_by('migrated_from_id', $subscription->ID)) {

				continue;

			} // end if;

			$customer = wu_get_customer_by_user_id($subscription->user_id);

			$product = wu_get_product_by('migrated_from_id', absint($subscription->plan_id));

			$v1_subscription_meta = (object) ($subscription->meta ? maybe_unserialize($subscription->meta) : array());

			$membership_data = array();

			if (!$product) {

				$this->add_id_of_interest($subscription->plan_id, 'plan_not_migrated', 'memberships');

				$membership_data['skip_validation'] = true;

			} // end if;

			$membership_data['migrated_from_id'] = $subscription->ID;

			$membership_data['customer_id']   = $customer ? $customer->get_id() : 0;
			$membership_data['plan_id']       = $product ? $product->get_id() : 0;
			$membership_data['amount']        = $subscription->price;
			$membership_data['disabled']      = false;
			$membership_data['recurring']     = true;
			$membership_data['signup_method'] = 'migrated';

			$membership_data['status']          = $subscription->active_until < $today ? Membership_Status::EXPIRED : Membership_Status::ACTIVE;
			$membership_data['date_expiration'] = $subscription->active_until;
			$membership_data['date_created']    = $subscription->created_at;
			$membership_data['date_modified']   = $subscription->created_at;

			/**
			 * Try to fetch the last payment to use it as the last renewal date.
			 *
			 * @since 2.0.7
			 */
			$last_renewal_date_query = $wpdb->prepare("
				SELECT 
					time
				FROM
					{$wpdb->base_prefix}wu_transactions
				WHERE
					user_id = %d AND type = 'payment'
				ORDER BY id DESC
				LIMIT 1
			", $subscription->user_id);

			$last_renewal_date = $wpdb->get_var($last_renewal_date_query); // phpcs:ignore

			if (wu_validate_date($last_renewal_date)) {

				$membership_data['date_renewed'] = $last_renewal_date;

			} // end if;

			/**
			 * Try to fetch the initial amount paid.
			 *
			 * @since 2.0.7
			 */
			$initial_amount_query = $wpdb->prepare("
				SELECT 
					amount,
					original_amount
				FROM
					{$wpdb->base_prefix}wu_transactions
				WHERE
					user_id = %d AND type = 'payment'
				ORDER BY id ASC
				LIMIT 1
			", $subscription->user_id);

			$initial_amount = $wpdb->get_var($initial_amount_query); // phpcs:ignore

			if (is_numeric($initial_amount)) {

				$membership_data['initial_amount'] = (float) $initial_amount;

			} // end if;

			/*
			 * Handle the default gateway integrations: Stripe and Paypal.
			 */
			if ($subscription->gateway) {

				$membership_data['gateway'] = $subscription->gateway;

				if ($subscription->gateway === 'stripe') {

					/**
					 * Case Stripe.
					 *
					 * On Stripe, the integration key on v1 was the customer_id on
					 * Stripe. The subscription id was saved on the meta object.
					 */
					$membership_data['gateway_customer_id']     = $subscription->integration_key;
					$membership_data['gateway_subscription_id'] = $v1_subscription_meta->subscription_id;

				} elseif ($subscription->gateway === 'paypal') {

					/**
					 * Case PayPal.
					 *
					 * For PayPal, the integration key saved is the PROFILE ID.
					 * This is what we use as the subscription id on the new models.
					 */
					$membership_data['gateway_subscription_id'] = $subscription->integration_key;

				} // end if;

				if ($subscription->integration_status) {

					$membership_data['auto_renew'] = true;

				} // end if;

			} // end if;

			if ($subscription->trial > 0) {

				/**
				 * Here we need to figure out when the trial ended, if it has ended.
				 *
				 * If not, we just set the new trial date to the future and
				 * change the membership status to trialing.
				 *
				 * @since 2.0.7
				 */
				$subscription_creation_date = wu_date($subscription->created_at);

				$trial_end_date = $subscription_creation_date->addDays($subscription->trial)->endOfDay();

				$date_trial_end = $trial_end_date->format('Y-m-d 23:59:59');

				$membership_data['date_trial_end'] = $date_trial_end;

				/*
				 * Handle memberships still in trial.
				 */
				if (wu_date() < $trial_end_date) {

					$membership_data['status'] = Membership_Status::TRIALING;

				} // end if;

			} // end if;

			if (empty(wu_to_float($subscription->price)) || !$subscription->active_until) {

				$membership_data['status'] = Membership_Status::ACTIVE;

			} // end if;

			/**
			 * Count the payments made to set the billing count thus far.
			 *
			 * @since 2.0.7
			 */
			$payments_count_query = $wpdb->prepare("
				SELECT 
					count(id)
				FROM
					{$wpdb->base_prefix}wu_transactions
				WHERE
					user_id = %d AND type = 'payment'
			", $subscription->user_id);

			$payments_count = $wpdb->get_var($payments_count_query); // phpcs:ignore

			$membership_data['times_billed'] = absint($payments_count);

			$membership_data = array_merge(
				$product ? $product->to_array() : array(),
				$customer ? $customer->to_array() : array(),
				$membership_data
			);

			if (!$membership_data['customer_id']) {

				$this->add_id_of_interest($subscription->user_id, 'customer_not_migrated', 'memberships');

				$membership_data['skip_validation'] = true;

			} // end if;

			$membership = wu_create_membership($membership_data);

			if (is_wp_error($membership)) {

				throw new \Exception($membership->get_error_message());

			} // end if;

			/*
			 * Update statuses and check for other info.
			 */
			if ($membership) {

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
		 * We need the total number of records
		 * to decided if we must run this on the
		 * background or not.
		 */
		$total_records = (int) $wpdb->get_var("SELECT count(ID) FROM {$wpdb->base_prefix}wu_transactions");

		/*
		 * Pass the value to the decider,
		 * if we need to run in parallel,
		 * the method handled it gracefully
		 */
		$this->maybe_run_in_parallel($total_records);

		/*
		 * Calculates the limit clause.
		 */
		$limit_clause = $this->build_limit_clause();

		/*
		 * Load dependencies.
		 */
		require_once wu_path('inc/functions/customer.php');
		require_once wu_path('inc/functions/membership.php');
		require_once wu_path('inc/functions/tax.php');
		require_once wu_path('inc/functions/payment.php');

		// phpcs:disable
		$transactions = $wpdb->get_results(
			"
				SELECT
					*
				FROM
					{$wpdb->base_prefix}wu_transactions
				{$limit_clause}
			"
		);
		// phpcs:enable

		/**
		 * Types to skip when migrating.
		 *
		 * In the previous version, things that were not payments were also
		 * saved as transactions, such as a recurring_setup event.
		 * We need to clean those up, skipping them.
		 *
		 * @since 2.0.0
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

			$customer = wu_get_customer_by_user_id($transaction->user_id);

			$product = '';

			if (isset($transaction->plan_id)) {

				$product = wu_get_product_by('migrated_from_id', $transaction->plan_id);

			} // end if;

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
				'customer_id'        => $membership ? $customer->get_id() : false,
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

			if (!$customer) {

				$this->add_id_of_interest($subscription->user_id, 'customer_not_migrated', 'transactions');

				$payment_data['skip_validation'] = true;

			} // end if;

			if (!$membership) {

				$this->add_id_of_interest($subscription->user_id, 'membership_not_migrated', 'transactions');

				$payment_data['skip_validation'] = true;

			} // end if;

			$payment = wu_create_payment($payment_data);

			if (is_wp_error($payment)) {

				throw new \Exception($payment->get_error_message());

			} // end if;

		} // end foreach;

	} // end _install_transactions;

	/**
	 * Migrates Coupons.
	 *
	 * @since 2.0.0
	 * @throws \Exception Halts the process on error.
	 * @return void
	 */
	protected function _install_discount_codes() {

		global $wpdb;

		/*
		 * Load dependencies.
		 */
		require_once wu_path('inc/functions/discount-code.php');

		$coupons = $wpdb->get_results(
			"
				SELECT
					ID,
					post_title name,
					post_name slug,
					post_date date_create,
					post_modified
				FROM
					{$wpdb->base_prefix}posts
				WHERE
					post_type = 'wpultimo_coupon'
			"
		);

		foreach ($coupons as $coupon) {
			/*
			 * If we have already migrated, no need to do it again.
			 */
			if (wu_get_discount_code_by_code($coupon->slug)) {

				continue;

			} // end if;

			$wpu_expiring_date = get_post_meta($coupon->ID, 'wpu_expiring_date', true);

			if (!wu_validate_date($wpu_expiring_date)) {

				$wpu_expiring_date = false;

			} // end if;

			$allowed_plans = get_post_meta($coupon->ID, 'wpu_allowed_plans', true);

			$limit_products = false;

			$allowed_products = array();

			if ($allowed_plans) {

				$limit_products = true;

				foreach ($allowed_plans as $key => $plan_id) {

					$product = wu_get_product_by('migrated_from_id', $plan_id);

					if ($product) {

						$allowed_products[] = $product->get_id();

					} // end if;

				} // end foreach;

			} // end if;

			$discount_code_data = array(
				'uses'             => get_post_meta($coupon->ID, 'wpu_uses', true),
				'max_uses'         => get_post_meta($coupon->ID, 'wpu_allowed_uses', true),
				'name'             => $coupon->name,
				'description'      => get_post_meta($coupon->ID, 'wpu_description', true),
				'code'             => $coupon->slug,
				'type'             => get_post_meta($coupon->ID, 'wpu_type', true),
				'value'            => get_post_meta($coupon->ID, 'wpu_value', true),
				'setup_fee_type'   => get_post_meta($coupon->ID, 'wpu_setup_fee_discount_type', true),
				'setup_fee_value'  => get_post_meta($coupon->ID, 'wpu_setup_fee_discount_value', true),
				'limit_products'   => $limit_products,
				'allowed_products' => $allowed_products,
				'date_start'       => $coupon->date_create,
				'date_expiration'  => $wpu_expiring_date,
				'date_created'     => $coupon->date_create,
				'date_modified'    => $coupon->post_modified,
				'skip_validation'  => true,
			);

			/*
			 * Fix the type name.
			 */
			if (wu_get_isset($discount_code_data, 'type') === 'percent') {

				$discount_code_data['type'] = 'percentage';

			} // end if;

			/*
			 * Fix the type name.
			 */
			if (wu_get_isset($discount_code_data, 'setup_fee_type') === 'percent') {

				$discount_code_data['setup_fee_type'] = 'percentage';

			} // end if;

			$discount_code = wu_create_discount_code($discount_code_data);

			if (is_wp_error($discount_code)) {

				throw new \Exception($discount_code->get_error_message());

			} // end if;

		} // end foreach;

	} // end _install_discount_codes;

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
		 * We need the total number of records
		 * to decided if we must run this on the
		 * background or not.
		 */
		$total_records = (int) $wpdb->get_var("SELECT count(ID) FROM {$wpdb->base_prefix}wu_site_owner");

		/*
		 * Pass the value to the decider,
		 * if we need to run in parallel,
		 * the method handled it gracefully
		 */
		$this->maybe_run_in_parallel($total_records);

		/*
		 * Calculates the limit clause.
		 */
		$limit_clause = $this->build_limit_clause();

		/*
		 * Load dependencies.
		 */
		require_once wu_path('inc/functions/customer.php');
		require_once wu_path('inc/functions/membership.php');
		require_once wu_path('inc/functions/site.php');

		 // phpcs:disable
		$site_owners = $wpdb->get_results(
			"
				SELECT
					site_id,
					user_id
				FROM
					{$wpdb->base_prefix}wu_site_owner
				{$limit_clause}
			"
		);
		// phpcs:enable

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

	} // end _install_sites;

	/**
	 * Migrates Template Sites Sites.
	 *
	 * @since 2.0.0
	 * @throws \Exception Halts the process on error.
	 * @return void
	 */
	protected function _install_site_templates() {
		/*
		 * Load dependencies.
		 */
		require_once wu_path('inc/functions/customer.php');
		require_once wu_path('inc/functions/membership.php');
		require_once wu_path('inc/functions/site.php');

		$skip_ids = array();

		/*
		 * First thing, add the main site to the skip list.
		 */
		$skip_ids[] = wu_get_main_site_id();

		$search_arguments = array(
			'network_id'    => get_current_site()->id,
			'fields'        => 'ids',
			'site__not_in'  => $skip_ids,
			'no_found_rows' => true,
		);

		if ($this->dry_run) {

			$per_page = 10;
			$page     = 1;

			$search_arguments['number'] = $per_page;
			$search_arguments['offset'] = ($page - 1) * $per_page;

		} elseif ($this->is_parallel()) {

			$per_page = absint(wu_request('per_page', 10));
			$page     = absint(wu_request('page', 1));

			$search_arguments['number'] = $per_page;
			$search_arguments['offset'] = ($page - 1) * $per_page;

		} // end if;

		$saved_templates = get_sites($search_arguments);

		if (is_array($saved_templates)) {

			foreach ($saved_templates as $template_id) {

				$site_template = wu_get_site($template_id);

				if (!$site_template) {

					continue;

				} // end if;

				if ($site_template->get_type() === Site_Type::CUSTOMER_OWNED) {

					continue;

				} // end if;

				$site_template->set_type('site_template');

				/*
				 * Get Categories
				 */
				$categories_string = get_blog_option($site_template->get_id(), 'wu_categories', false);

				$site_template->set_categories(explode(',', $categories_string));

				/*
				 * Saved thumbnails
				 */
				$old_thumbnail_id = get_blog_option($site_template->get_id(), 'template_img', false);

				if ($old_thumbnail_id) {

					$site_template->set_featured_image_id($old_thumbnail_id);

				} else {
					/*
					 * Try to get a custom screenshot previously taken.
					 */
					$image_url = $this->maybe_get_screenshot_url($site_template->get_id());

					$attachment_id = \WP_Ultimo\Helpers\Screenshot::save_image_from_url($image_url);

					if ($attachment_id) {

						$site_template->set_featured_image_id($attachment_id);

					} else {
						/*
						 * If everything else fails, schedule a thumbnail to be saved.
						 */
						wu_enqueue_async_action('wu_async_take_screenshot', array(
							'site_id' => $site_template->get_id(),
						), 'site');

					} // end if;

				} // end if;

				$saved = $site_template->save();

				if (is_wp_error($saved)) {

					throw new \Exception($saved->get_error_message());

				} // end if;

			} // end foreach;

		} // end if;

	} // end _install_site_templates;

	/**
	 * Convert hard-coded thumbnail urls into v2 thumbnails.
	 *
	 * @since 2.0.0
	 *
	 * @param int $site_id The site template id.
	 * @return int|false
	 */
	protected function maybe_get_screenshot_url($site_id) {

		$template = get_blog_details($site_id);

		if (!$template) {

			return;

		} // end if;

		// Get filename
		$filename = sanitize_title($template->siteurl);
		$dir      = wp_upload_dir();

		// Check if exists
		$filepath = $dir['basedir'] . '/' . $filename . '.jpg';

		// Return URL
		return file_exists($filepath) && 0 !== filesize($filepath) ? $dir['baseurl'] . '/' . $filename . '.jpg' : false;

	} // end maybe_get_screenshot_url;

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
		require_once wu_path('inc/functions/domain.php');

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

		$https = $this->get_old_setting('force_mapped_https', true);

		foreach ($domains as $domain) {

			$existing_domain = wu_get_domain_by_domain($domain->name);

			if ($existing_domain) {

				continue;

			} // end if;

			$domain = wu_create_domain(array(
				'stage'          => 'done',
				'domain'         => $domain->name,
				'blog_id'        => $domain->blog_id,
				'active'         => $domain->active,
				'primary_domain' => true,
				'secure'         => $https,
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
		require_once wu_path('inc/deprecated/deprecated.php');
		require_once wu_path('inc/functions/legacy.php');
		require_once wu_path('inc/functions/checkout-form.php');
		require_once wu_path('inc/functions/site.php');

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

		require_once wu_path('inc/functions/webhook.php');
		require_once wu_path('inc/functions/broadcast.php');

		/*
			* Install the default emails.
			*/
		\WP_Ultimo\Managers\Email_Manager::get_instance()->create_all_system_emails();

		$broadcasts = $wpdb->get_results(
			"
				SELECT
					ID,
					post_title,
					post_content,
					post_date,
					post_modified
				FROM
					{$wpdb->base_prefix}posts
				WHERE
					post_type = 'wpultimo_broadcast'
			"
		);

		foreach ($broadcasts as $broadcast) {

			$existing_broadcast = wu_get_broadcast_by('migrated_from_id', $broadcast->ID);

			if ($existing_broadcast) {

				continue;

			} // end if;

			$old_type = get_post_meta($broadcast->ID, 'wpu_type', true);

			$style = get_post_meta($broadcast->ID, 'wpu_style', true);

			$new_type = $old_type === 'message' ? 'broadcast_notice' : 'broadcast_email';

			$customer_targets = (array) get_post_meta($broadcast->ID, 'wpu_target_users', true);
			$product_targets  = (array) get_post_meta($broadcast->ID, 'wpu_target_plans', true);

			$customer_targets = array_map(function($user_id) {

				$customer = wu_get_customer_by_user_id($user_id);

				if ($customer) {

					return $customer->get_id();

				} // end if;

				return false;

			}, $customer_targets);

			$product_targets = array_map(function($old_plan_id) {

				$product = wu_get_product_by('migrated_from_id', $old_plan_id);

				if ($product) {

					return $product->get_id();

				} // end if;

				return false;

			}, $product_targets);

			$targets = array(
				'customers' => array_filter($customer_targets),
				'products'  => array_filter($product_targets),
			);

			$broadcast = wu_create_broadcast(array(
				'name'             => $broadcast->post_title,
				'content'          => $broadcast->post_content,
				'type'             => $new_type,
				'style'            => $style ? $style : 'success',
				'date_created'     => $broadcast->post_date,
				'date_modified'    => $broadcast->post_modified,
				'message_targets'  => $targets,
				'migrated_from_id' => $broadcast->ID,
				'skip_validation'  => true,
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

		require_once wu_path('inc/functions/webhook.php');

		$webhooks = $wpdb->get_results(
			"
				SELECT
					ID,
					post_title,
					post_date,
					post_modified
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
				'active'           => (bool) get_post_meta($webhook->ID, 'wpu_active', true),
				'date_created'     => $webhook->post_date,
				'date_modified'    => $webhook->post_modified,
			));

			if (is_wp_error($webhook)) {

				throw new \Exception($webhook->get_error_message());

			} // end if;

		} // end foreach;

	} // end _install_webhooks;

	/**
	 * Migrates other things.
	 *
	 * @since 2.0.0
	 * @throws \Exception Halts the process on error.
	 * @return void
	 */
	protected function _install_other() {
		/*
		 * No need to run this one while checking.
		 */
		if ($this->dry_run) {

			return;

		} // end if;

		/*
		 * Loads the integrations.
		 */
		Domain_Manager::get_instance()->load_integrations();

		/*
		 * Get the list of integration classes.
		 */
		$integrations = Domain_Manager::get_instance()->get_integrations();

		foreach ($integrations as $integration_class) {
			/*
			 * Check if the class exists.
			 */
			if (class_exists($integration_class) === false) {

				continue;

			} // end if;

			/*
			 * Get the instance of the integration.
			 */
			$instance = $integration_class::get_instance();

			if ($instance && $instance->is_setup()) {

				$instance->enable();

			} // end if;

		} // end foreach;

	} // end _install_other;

} // end class Migrator;
