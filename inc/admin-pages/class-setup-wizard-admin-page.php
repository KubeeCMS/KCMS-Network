<?php
/**
 * WP Ultimo Dashboard Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\License;
use \WP_Ultimo\Installers\Migrator;
use \WP_Ultimo\Installers\Core_Installer;
use \WP_Ultimo\Installers\Default_Content_Installer;
use \WP_Ultimo\Logger;

/**
 * WP Ultimo Dashboard Admin Page.
 */
class Setup_Wizard_Admin_Page extends Wizard_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-setup';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $type = 'submenu';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $parent = 'none';

	/**
	 * This page has no parent, so we need to highlight another sub-menu.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $highlight_menu_slug = 'wp-ultimo-settings';

	/**
	 * If this number is greater than 0, a badge with the number will be displayed alongside the menu title
	 *
	 * @since 1.8.2
	 * @var integer
	 */
	protected $badge_count = 0;

	/**
	 * Holds the admin panels where this page should be displayed, as well as which capability to require.
	 *
	 * To add a page to the regular admin (wp-admin/), use: 'admin_menu' => 'capability_here'
	 * To add a page to the network admin (wp-admin/network), use: 'network_admin_menu' => 'capability_here'
	 * To add a page to the user (wp-admin/user) admin, use: 'user_admin_menu' => 'capability_here'
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $supported_panels = array(
		'network_admin_menu' => 'manage_network',
	);

	/**
	 * The customer license, if it exists.
	 *
	 * @since 2.0.0
	 * @var \FS_License
	 */
	public $license;

	/**
	 * The customer object, if it exists.
	 *
	 * @since 2.0.0
	 * @var \FS_User
	 */
	public $customer;

	/**
	 * Is this an old install migrating.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	private $is_migration;

	/**
	 * Overrides original construct method.
	 *
	 * We need to override the construct method to make sure
	 * we make the necessary changes to the Wizard page when it's
	 * being run for the first time.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function __construct() {

		if (!$this->is_core_loaded()) {

			require_once wu_path('inc/functions/documentation.php');

			/**
			 * Loads the necessary apis.
			 */
			WP_Ultimo()->load_public_apis();

			$this->highlight_menu_slug = false;

			$this->type = 'menu';

			$this->position = 10101010;

			$this->menu_icon = 'dashicons-wu-wp-ultimo';

			add_action('admin_enqueue_scripts', array($this, 'register_scripts'));

		} // end if;

		parent::__construct();

		add_action('admin_action_download_migration_logs', array($this, 'download_migration_logs'));

		/*
		 * Serve the installers
		 */
		add_action('wp_ajax_wu_setup_install', array($this, 'setup_install'));

		/*
		 * Load installers
		 */
		add_action('wu_handle_ajax_installers', array(Core_Installer::get_instance(), 'handle'), 10, 3);
		add_action('wu_handle_ajax_installers', array(Default_Content_Installer::get_instance(), 'handle'), 10, 3);
		add_action('wu_handle_ajax_installers', array(Migrator::get_instance(), 'handle'), 10, 3);

		/*
		 * Redirect on activation
		 */
		add_action('wu_activation', array($this, 'redirect_to_wizard'));

	} // end __construct;

	/**
	 * Download the migration logs.
	 *
	 * @since 2.0.7
	 * @return void
	 */
	public function download_migration_logs() {

		check_admin_referer('download_migration_logs', 'nonce');

		$path = Logger::get_logs_folder();

		$file = $path . Migrator::LOG_FILE_NAME . '.log';

		$file_name = str_replace($path, '', $file);

		header('Content-Type: application/octet-stream');

		header("Content-Disposition: attachment; filename=$file_name");

		header('Pragma: no-cache');

		readfile($file);

		exit;

	} // end download_migration_logs;

	/**
	 * Loads the extra elements we need on the wizard itself.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function page_loaded() {

		parent::page_loaded();

		$this->license = \WP_Ultimo\License::get_instance()->get_license();

		$this->customer = \WP_Ultimo\License::get_instance()->get_customer();

		$this->set_settings();

	} // end page_loaded;

	/**
	 * Checks if this is a migration or not.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_migration() {

		if ($this->is_migration === null) {

			$this->is_migration = Migrator::is_legacy_network();

		} // end if;

		return $this->is_migration;

	} // end is_migration;

	/**
	 * Adds missing setup from settings when WP Ultimo is not fully loaded.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function set_settings() {

		WP_Ultimo()->settings->default_sections();

	} // end set_settings;

	/**
	 * Redirects to the wizard, if we need to.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function redirect_to_wizard() {

		if (!\WP_Ultimo\Requirements::run_setup() && wu_request('page') !== 'wp-ultimo-setup') {

			wp_redirect(wu_network_admin_url('wp-ultimo-setup'));

			exit;

		} // end if;

	} // end redirect_to_wizard;

	/**
	 * Handles the ajax actions for installers and migrators.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function setup_install() {

		global $wpdb;

		if (!current_user_can('manage_network')) {

			wp_send_json_error(new \WP_Error('not-allowed', __('Permission denied.', 'wp-ultimo')));

			exit;

		} // end if;

		/*
		 * Load tables.
		 */
		WP_Ultimo()->tables = \WP_Ultimo\Loaders\Table_Loader::get_instance();

		$installer = wu_request('installer', '');

		/*
		 * Installers should hook into this filter
		 */
		$status = apply_filters('wu_handle_ajax_installers', true, $installer, $this);

		if (is_wp_error($status)) {

			wp_send_json_error($status);

		} // end if;

		wp_send_json_success();

	} // end setup_install;

	/**
	 * Check if the core was loaded.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_core_loaded() {

		return \WP_Ultimo\Requirements::met() && \WP_Ultimo\Requirements::run_setup();

	} // end is_core_loaded;

	/**
	 * Returns the logo for the wizard.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_logo() {

		return wu_get_asset('logo.png', 'img');

	} // end get_logo;

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return sprintf(__('Installation', 'wp-ultimo'));

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return WP_Ultimo()->is_loaded() ? __('WP Ultimo Install', 'wp-ultimo') : __('WP Ultimo', 'wp-ultimo');

	} // end get_menu_title;

	/**
	 * Returns the sections for this Wizard.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_sections() {

		$allowed = \WP_Ultimo\License::get_instance()->allowed();

		$sections = array(
			'welcome'      => array(
				'title'       => __('Welcome', 'wp-ultimo'),
				'description' => implode('<br><br>', array(
					__('...and thanks for choosing WP Ultimo!', 'wp-ultimo'),
					__('This quick setup wizard will make sure your server is correctly setup, help you configure your new network, and migrate data from previous WP Ultimo versions if necessary.', 'wp-ultimo'),
					__('You will also have the option of importing default content. It should take 10 minutes or less!', 'wp-ultimo')
				)),
				'next_label'  => __('Get Started &rarr;', 'wp-ultimo'),
				'back'        => false,
			),
			'checks'       => array(
				'title'       => __('Pre-install Checks', 'wp-ultimo'),
				'description' => __('Now it is time to see if this machine has what it takes to run WP Ultimo well!', 'wp-ultimo'),
				'next_label'  => \WP_Ultimo\Requirements::met() ? __('Go to the Next Step &rarr;', 'wp-ultimo') : __('Check Again', 'wp-ultimo'),
				'handler'     => array($this, 'handle_checks'),
				'back'        => false,
				'fields'      => array(
					'requirements' => array(
						'type' => 'note',
						'desc' => array($this, 'renders_requirements_table'),
					)
				),
			),
			'activation'   => array(
				'title'       => __('License Activation', 'wp-ultimo'),
				'description' => __('Let\'s make sure you are able to keep your copy up-to-date with our latest updates via admin panel notifications and more.', 'wp-ultimo'),
				'handler'     => array($this, 'handle_activation'),
				'next_label'  => __('Agree & Activate &rarr;', 'wp-ultimo'),
				'back'        => false,
				'skip'        => $allowed,
				'fields'      => array(
					'terms'       => array(
						'type' => 'note',
						'desc' => array($this, '_terms_of_support'),
					),
					'license_key' => array(
						'type'            => 'text',
						'title'           => __('License Key', 'wp-ultimo'),
						'placeholder'     => __('E.g. sk_***********', 'wp-ultimo'),
						'tooltip'         => __('Your WP Ultimo License Key', 'wp-ultimo'),
						'desc'            => array($this, '_desc_and_validation_error'),
						'wrapper_classes' => $allowed ? 'wu-hidden' : '',
						'html_attr'       => array(
							$allowed ? 'disabled' : 'data-none' => 'disabled',
						),
					),
					'license'     => array(
						'wrapper_classes' => $allowed ? 'sm:wu-w-auto sm:wu-block' : 'sm:wu-w-auto wu-hidden',
						'classes'         => 'sm:wu--mx-6 sm:wu--mt-4 sm:wu--mb-6',
						'type'            => 'note',
						'desc'            => array($this, '_current_license'),
					),
				),
			),
			'installation' => array(
				'title'       => __('Installation', 'wp-ultimo'),
				'description' => __('Now, let\'s update your database and install the Sunrise.php file, which are necessary for the correct functioning of WP Ultimo.', 'wp-ultimo'),
				'next_label'  => Core_Installer::get_instance()->all_done() ? __('Go to the Next Step &rarr;', 'wp-ultimo') : __('Install', 'wp-ultimo'),
				'fields'      => array(
					'terms' => array(
						'type' => 'note',
						'desc' => function() {
							return $this->render_installation_steps(Core_Installer::get_instance()->get_steps(), false);
						},
					),
				),
			),
		);

		/*
		 * In case of migrations, add different sections.
		 */
		if ($this->is_migration()) {

			$dry_run = wu_request('dry-run', true);

			$next = true;

			$errors = Migrator::get_instance()->get_errors();

			$back_traces = Migrator::get_instance()->get_back_traces();

			$next_label = __('Migrate!', 'wp-ultimo');

			$description = __('No errors found during dry run! Now it is time to actually migrate! <br><br><strong>We strongly recommend creating a backup of your database before moving forward with the migration.</strong>', 'wp-ultimo');

			if ($dry_run) {

				$next_label = __('Run Check', 'wp-ultimo');

				$description = __('It seems that you were running WP Ultimo 1.X on this network. This migrator will convert the data from the old version to the new one.', 'wp-ultimo') . '<br><br>' . __('First, let\'s run a test migration to see if we can spot any potential errors.', 'wp-ultimo');

			} // end if;

			$fields = array(
				'migration' => array(
					'type' => 'note',
					'desc' => function() {

						return $this->render_installation_steps(Migrator::get_instance()->get_steps(), false);

					},
				),
			);

			if ($errors) {

				$subject = 'Errors on migrating my network';

				$user = wp_get_current_user();

				$message_lines = array(
					'Hi there,',
					sprintf('My name is %s.', $user->display_name),
					sprintf('License Key: %s', License::get_instance()->get_license_key(true)),
					'I tried to migrate my network from version 1 to version 2, but was not able to do it successfully...',
					'Here are the error messages I got:',
					sprintf('```%s%s%s```', PHP_EOL, implode(PHP_EOL, $errors), PHP_EOL),
					sprintf('```%s%s%s```', PHP_EOL, $back_traces ? implode(PHP_EOL, $back_traces) : 'No backtraces found.', PHP_EOL),
					'Kind regards.'
				);

				$message = implode(PHP_EOL . PHP_EOL, $message_lines);

				License::get_instance()->maybe_add_support_window($subject, $message);

				$description = __('The dry run test detected issues during the test migration. Please, <a class="wu-trigger-support" href="#">contact our support team</a> to get help migrating from 1.X to version 2.', 'wp-ultimo');

				$next = true;

				$next_label = __('Try Again!', 'wp-ultimo');

				$error_list = '<strong>' . __('List of errors detected:', 'wp-ultimo') . '</strong><br><br>';

				$errors[] = sprintf('<br><a href="%2$s" class="wu-no-underline wu-text-red-500 wu-font-bold"><span class="dashicons-wu-download wu-mr-2"></span>%1$s</a>', __('Download migration error log', 'wp-ultimo'), add_query_arg(array(
					'action' => 'download_migration_logs',
					'nonce'  => wp_create_nonce('download_migration_logs'),
				), network_admin_url('admin.php')));

				$errors[] = sprintf('<br><a href="%2$s" class="wu-no-underline wu-text-red-500 wu-font-bold"><span class="dashicons-wu-back-in-time wu-mr-2"></span>%1$s</a>', __('Rollback to version 1.10.13', 'wp-ultimo'), add_query_arg(array(
					'page'    => 'wp-ultimo-rollback',
					'version' => '1.10.13',
					'type'    => 'select-version',
				), network_admin_url('admin.php')));

				$error_list .= implode('<br>', $errors);

				$fields = array_merge(array(
					'errors' => array(
						'type'    => 'note',
						'classes' => 'wu-flex-grow',
						'desc'    => function() use ($error_list) {

							/** Reset errors */
							Migrator::get_instance()->session->set('errors', array());

							return sprintf('<div class="wu-mt-0 wu-p-4 wu-bg-red-100 wu-border wu-border-solid wu-border-red-200 wu-rounded-sm wu-text-red-500">%s</div>', $error_list);

						},
					),
				), $fields);

			} // end if;

			$sections['migration'] = array(
				'title'       => __('Migration', 'wp-ultimo'),
				'description' => $description,
				'next_label'  => $next_label,
				'skip'        => false,
				'next'        => $next,
				'handler'     => array($this, 'handle_migration'),
				'fields'      => $fields,
			);

		} else {

			$sections['your-company'] = array(
				'title'       => __('Your Company', 'wp-ultimo'),
				'description' => __('Before we move on, let\'s configure the basic settings of your network, shall we?', 'wp-ultimo'),
				'handler'     => array($this, 'handle_save_settings'),
				'fields'      => array($this, 'get_general_settings'),
			);

			$sections['defaults'] = array(
				'title'       => __('Default Content', 'wp-ultimo'),
				'description' => __('Starting from scratch can be scarry, specially when first starting out. In this step, you can create default content to have a starting point for your network. Everything can be customized later.', 'wp-ultimo'),
				'next_label'  => Default_Content_Installer::get_instance()->all_done() ? __('Go to the Next Step &rarr;', 'wp-ultimo') : __('Install', 'wp-ultimo'),
				'fields'      => array(
					'terms' => array(
						'type' => 'note',
						'desc' => function() {
							return $this->render_installation_steps(Default_Content_Installer::get_instance()->get_steps());
						},
					),
				),
			);

		} // end if;

		$sections['done'] = array(
			'title' => __('Ready!', 'wp-ultimo'),
			'view'  => array($this, 'section_ready'),
		);

		/**
		 * Allow developers to add additional setup wizard steps.
		 *
		 * @since 2.0.0
		 *
		 * @param array  $sections Current sections.
		 * @param bool   $is_migration If this is a migration or not.
		 * @param object $this The current instance.
		 * @return array
		 */
		return apply_filters('wu_setup_wizard', $sections, $this->is_migration(), $this);

	} // end get_sections;

	/**
	 * Returns the general settings to add to the wizard.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_general_settings() {
		/*
		 * Get the general fields for company and currency.
		 */
		$general_fields = \WP_Ultimo\Settings::get_instance()->get_section('general')['fields'];

		/*
		 * Unset a couple of undesired settings
		 */
		$fields_to_unset = array(
			'error_reporting_header',
			'enable_error_reporting',
			'uninstall_header',
			'uninstall_wipe_tables',
		);

		foreach ($fields_to_unset as $field_to_unset) {

			unset($general_fields[$field_to_unset]);

		} // end foreach;

		// Adds a fake first field to bypass some styling issues with the top-border
		$fake_field = array(
			array(
				'type' => 'hidden',
			),
		);

		$fields = array_merge($fake_field, $general_fields);

		return apply_filters('wu_setup_get_general_settings', $fields);

	} // end get_general_settings;

	/**
	 * Returns the payment settings to add to the setup wizard.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_payment_settings() {
		/*
		 * Get the general fields for company and currency.
		 */
		$payment_fields = \WP_Ultimo\Settings::get_instance()->get_section('payment-gateways')['fields'];

		$fields_to_unset = array(
			'main_header',
		);

		foreach ($fields_to_unset as $field_to_unset) {

			unset($payment_fields[$field_to_unset]);

		} // end foreach;

		$fields = array_merge($payment_fields);

		return apply_filters('wu_setup_get_payment_settings', $fields);

	} // end get_payment_settings;

	/**
	 * Shows the description and possible error.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function _desc_and_validation_error() {

		ob_start();

		echo __('Your license key starts with "sk_".', 'wp-ultimo');

		$error = wu_request('error', false);

		if ($error) :

		// phpcs:disable ?>

			<span class="wu-text-red-500 wu-ml-1">

				&mdash; <?php echo is_string($error) ? $error : __('Invalid License Key.', 'wp-ultimo'); ?>

			</span>

			<?php

		endif;

		return ob_get_clean();

	} // end _desc_and_validation_error;

	/**
	 * Displays the block about the current license.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function _current_license() {

		ob_start();

		if (\WP_Ultimo\License::get_instance()->allowed()) : // phpcs:ignore ?>

			<span class="wu-py-4 wu-px-6 wu-bg-green-100 wu-block wu-text-green-500">
			<?php printf(__('Your license key was already validated, %1$s. To change your license, go to the <a href="%2$s" class="wu-no-underline">Account Page</a>.', 'wp-ultimo'), $this->customer->first, wu_network_admin_url('wp-ultimo-account')); ?>
			</span>

			<?php

		// phpcs:enable

		endif;

		return ob_get_clean();

	} // end _current_license;

	/**
	 * Render the installation steps table.
	 *
	 * @since 2.0.0
	 *
	 * @param array   $steps The list of steps.
	 * @param boolean $checks If we should add the checkbox for selection or not.
	 * @return string
	 */
	public function render_installation_steps($steps, $checks = true) {

		wp_localize_script('wu-setup-wizard', 'wu_setup', $steps);

		wp_localize_script('wu-setup-wizard', 'wu_setup_settings', array(
			'dry_run'               => wu_request('dry-run', true),
			'generic_error_message' => __('A server error happened while processing this item.', 'wp-ultimo'),
		));

		wp_enqueue_script('wu-setup-wizard');

		return wu_get_template_contents('wizards/setup/installation_steps', array(
			'page'   => $this,
			'steps'  => $steps,
			'checks' => $checks,
		));

	} // end render_installation_steps;

	/**
	 * Renders the terms of support.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function _terms_of_support() {

		return wu_get_template_contents('wizards/setup/support_terms');

	} // end _terms_of_support;

	/**
	 * Renders the requirements tables.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function renders_requirements_table() {

		global $wp_version;

		$requirements = array(
			'php'       => array(
				'name'                => __('PHP', 'wp-ultimo'),
				'help'                => wu_get_documentation_url('wp-ultimo-requirements'),
				'required_version'    => \WP_Ultimo\Requirements::$php_version,
				'recommended_version' => \WP_Ultimo\Requirements::$php_recommended_version,
				'installed_version'   => phpversion(),
				'pass_requirements'   => version_compare(phpversion(), \WP_Ultimo\Requirements::$php_version, '>='),
				'pass_recommendation' => version_compare(phpversion(), \WP_Ultimo\Requirements::$php_recommended_version, '>=')
			),
			'wordpress' => array(
				'name'                => __('WordPress', 'wp-ultimo'),
				'help'                => wu_get_documentation_url('wp-ultimo-requirements'),
				'required_version'    => \WP_Ultimo\Requirements::$wp_version,
				'recommended_version' => \WP_Ultimo\Requirements::$wp_recommended_version,
				'installed_version'   => $wp_version,
				'pass_requirements'   => version_compare(phpversion(), \WP_Ultimo\Requirements::$wp_version, '>='),
				'pass_recommendation' => version_compare(phpversion(), \WP_Ultimo\Requirements::$wp_recommended_version, '>=')
			),
		);

		$plugin_requirements = array(
			'multisite' => array(
				'name'              => __('WordPress Multisite', 'wp-ultimo'),
				'help'              => wu_get_documentation_url('wp-ultimo-requirements'),
				'condition'         => __('Installed & Activated', 'wp-ultimo'),
				'pass_requirements' => is_multisite(),
			),
			'wp-ultimo' => array(
				'name'              => __('WP Ultimo', 'wp-ultimo'),
				'help'              => wu_get_documentation_url('wp-ultimo-requirements'),
				'condition'         => apply_filters('wp_ultimo_skip_network_active_check', false) ? __('Bypassed via filter', 'wp-ultimo') : __('Network Activated', 'wp-ultimo'),
				'pass_requirements' => \WP_Ultimo\Requirements::is_network_active(),
			),
			'wp-cron'   => array(
				'name'              => __('WordPress Cron', 'wp-ultimo'),
				'help'              => wu_get_documentation_url('wp-ultimo-requirements'),
				'condition'         => __('Activated', 'wp-ultimo'),
				'pass_requirements' => \WP_Ultimo\Requirements::check_wp_cron(),
			),
		);

		return wu_get_template_contents('wizards/setup/requirements_table', array(
			'requirements'        => $requirements,
			'plugin_requirements' => $plugin_requirements,
		));

	} // end renders_requirements_table;

	/**
	 * Displays the content of the final section.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function section_ready() {

		update_network_option(null, 'wu_setup_finished', true);

		/**
		 * Mark the migration as done, if this was a migration.
		 *
		 * @since 2.0.7
		 */
		if (Migrator::is_legacy_network()) {

			update_network_option(null, 'wu_is_migration_done', true);

		} // end if;

		wu_enqueue_async_action('wu_async_take_screenshot', array(
			'site_id' => wu_get_main_site_id(),
		), 'site');

		wu_get_template('wizards/setup/ready', array(
			'screen' => get_current_screen(),
			'page'   => $this,
		));

	} // end section_ready;

	/**
	 * Handles the requirements check.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_checks() {

		if (\WP_Ultimo\Requirements::met() === false) {

			wp_redirect(add_query_arg());

			exit;

		} // end if;

		wp_redirect($this->get_next_section_link());

		exit;

	} // end handle_checks;

	/**
	 * Handles the saving of setting steps.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_save_settings() {

		$this->set_settings();

		$step = wu_request('step');

		if ($step === 'your-company') {

			$fields_to_save = $this->get_general_settings();

		} elseif ($step === 'payment-gateways') {

			$fields_to_save = $this->get_payment_settings();

		} else {

			return;

		} // end if;

		$settings_to_save = array_intersect_key($_POST, $fields_to_save);

		\WP_Ultimo\Settings::get_instance()->save_settings($settings_to_save);

		wp_redirect($this->get_next_section_link());

		exit;

	} // end handle_save_settings;

	/**
	 * Handles the migration step and checks for a test run.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_migration() {

		$dry_run = wu_request('dry-run', true);

		$errors = Migrator::get_instance()->get_errors();

		if ($dry_run) {

			$url = add_query_arg('dry-run', empty($errors) ? 0 : 1);

		} else {

			if (empty($errors)) {

				$url = remove_query_arg('dry-run', $this->get_next_section_link());

			} else {

				$url = add_query_arg('dry-run', 0);

			} // end if;

		} // end if;

		wp_redirect($url);

		exit;

	} // end handle_migration;

	/**
	 * Handles the activation of a given integration.
	 *
	 * @since 2.0.0
	 * @return void|WP_Error
	 */
	public function handle_activation() {

		$license = License::get_instance();

		/*
		 * Already activated.
		 */
		if ($license->allowed()) {

			wp_redirect($this->get_next_section_link());

			exit;

		} // end if;

		$activation_results = $license->activate(wu_request('license_key'));

		if (isset($activation_results->error)) {
			/*
			 * Kinda hacky, but well...
			 */
			$_REQUEST['error'] = $activation_results->error;

		} elseif (is_wp_error($activation_results)) {

			$_REQUEST['error'] = $activation_results->get_error_message();

		} else {

			wp_redirect($this->get_next_section_link());

			exit;

		} // end if;

	} // end handle_activation;

	/**
	 * Handles the configuration of a given integration.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_configuration() {

		if ($_POST['submit'] === '1') {

			$this->integration->setup_constants($_POST);

			$redirect_url = $this->get_next_section_link();

			wp_redirect($redirect_url);

			exit;

		} // end if;

	} // end handle_configuration;

	/**
	 * Handles the testing of a given configuration.
	 *
	 * @todo Move Vue to a scripts management class.
	 * @since 2.0.0
	 * @return void
	 */
	public function section_test() {

		wp_enqueue_script('wu-vue');

		wu_get_template('wizards/host-integrations/test', array(
			'screen'      => get_current_screen(),
			'page'        => $this,
			'integration' => $this->integration,
		));

	} // end section_test;

	/**
	 * Adds the necessary missing scripts if WP Ultimo was not loaded.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts() {

		if (WP_Ultimo()->is_loaded() === false) {

			wp_enqueue_style('wu-styling', wu_get_asset('framework.css', 'css'), false, wu_get_version());

			wp_enqueue_style('wu-admin', wu_get_asset('admin.css', 'css'), array('wu-styling'), wu_get_version());

			/*
			* Adds tipTip
			*/
			wp_enqueue_script('wu-tiptip', wu_get_asset('lib/tiptip.js', 'js'));

			/*
			* Adds jQueryBlockUI
			*/
			wp_enqueue_script('wu-block-ui', wu_get_asset('lib/jquery.blockUI.js', 'js'), array('jquery'));

			wp_register_script('wu-fields', wu_get_asset('fields.js', 'js'), array('jquery'));

			/*
			* Localize components
			*/
			wp_localize_script('wu-fields', 'wu_fields', array(
				'l10n' => array(
					'image_picker_title'       => __('Select an Image.', 'wp-ultimo'),
					'image_picker_button_text' => __('Use this image', 'wp-ultimo'),
				),
			));

			wp_register_script('wu-functions', wu_get_asset('functions.js', 'js'), array('jquery'));

			wp_register_script('wubox', wu_get_asset('wubox.js', 'js/lib'), array('jquery', 'wu-functions'));

			wp_localize_script('wubox', 'wuboxL10n', array(
				'next'             => __('Next &gt;'),
				'prev'             => __('&lt; Prev'),
				'image'            => __('Image'),
				'of'               => __('of'),
				'close'            => __('Close'),
				'noiframes'        => __('This feature requires inline frames. You have iframes disabled or your browser does not support them.'),
				'loadingAnimation' => includes_url('js/thickbox/loadingAnimation.gif'),
			));

			wp_add_inline_script('wu-setup-wizard-polyfill', 'jQuery(document).ready(() => 
    wu_initialize_imagepicker());', 'after');

		} // end if;

		wp_enqueue_script('wu-setup-wizard-polyfill', wu_get_asset('setup-wizard-polyfill.js', 'js'), array('jquery', 'wu-fields', 'wu-functions', 'wubox'), wu_get_version());

		wp_enqueue_media();

		wp_register_script('wu-setup-wizard', wu_get_asset('setup-wizard.js', 'js'), array('jquery'), wu_get_version());

		wp_add_inline_style('wu-admin', sprintf('
		body.wu-page-wp-ultimo-setup #wpwrap {
			background: url("%s") right bottom no-repeat;
			background-size: 90%%;
		}', wu_get_asset('bg-setup.png', 'img')));

	} // end register_scripts;

} // end class Setup_Wizard_Admin_Page;
