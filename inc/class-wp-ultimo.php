<?php
/**
 * WP Ultimo main class.
 *
 * @package WP_Ultimo
 * @since 2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo main class
 *
 * This class instantiates our dependencies and load the things
 * our plugin needs to run.
 *
 * @package WP_Ultimo
 * @since 2.0.0
 */
final class WP_Ultimo {

	use \WP_Ultimo\Traits\Singleton, \WP_Ultimo\Traits\WP_Ultimo_Deprecated;

	/**
	 * Checks if WP Ultimo was loaded or not.
	 *
	 * This is set to true when all the WP Ultimo requirements are met.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $loaded = false;

	/**
	 * Version of the Plugin
	 *
	 * @var string
	 */
	public $version = '2.0.0-beta.2';

	/**
	 * Holds an instance of the helper functions layer.
	 *
	 * @since 2.0.0
	 * @var WP_Ultimo\Helper
	 */
	public $helper;

	/**
	 * Holds an instance of the settings layer.
	 *
	 * @since 2.0.0
	 * @var WP_Ultimo\Settings
	 */
	public $settings;

	/**
	 * Loads the necessary components into the main class
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {
		/*
		 * Loads the WP_Ultimo\Helper class.
		 */
		$this->helper = WP_Ultimo\Helper::get_instance();

		/*
		 * Helper Functions
		 */
		require_once $this->helper->path('inc/functions/helper.php');

		/*
		 * Scheduler Functions
		 */
		require_once $this->helper->path('inc/functions/scheduler.php');

		/*
		 * Date Functions
		 */
		require_once $this->helper->path('inc/functions/date.php');

		/*
		 * Render Functions
		 */
		require_once $this->helper->path('inc/functions/template.php');

		/*
		 * Loads the currency helper functions.
		 */
		require_once $this->helper->path('inc/functions/currency.php');

		/*
		 * Country Functions
		 */
		require_once $this->helper->path('inc/functions/countries.php');

		/*
		 * Settings Functions
		 */
		require_once $this->helper->path('inc/functions/settings.php');

		/*
		 * Invoice Functions
		 */
		require_once $this->helper->path('inc/functions/invoice.php');

		/*
		 * Admin helper functions
		 */
		if (is_admin()) {

			require_once $this->helper->path('inc/functions/admin.php');

		} // end if;

		/*
		 * Set up the text-domain for translations
		 */
		$this->setup_textdomain();

		/*
		 * Setup Wizard
		 */
		new WP_Ultimo\Admin_Pages\Setup_Wizard_Admin_Page();

		/*
		 * Loads the WP Ultimo settings helper class.
		 */
		$this->settings = WP_Ultimo\Settings::get_instance();

		/*
		 * Check if the WP Ultimo requirements are present.
		 *
		 * Everything we need to run our setup install needs top be loaded before this
		 * and have no dependencies outside of the classes loaded so far.
		 */
		if (WP_Ultimo\Requirements::met() === false || WP_Ultimo\Requirements::run_setup() === false) {

			return;

		} // end if;

		$this->loaded = true;

		/*
		 * Loads the current site.
		 */
		$this->currents = WP_Ultimo\Current::get_instance();

		/*
		 * Loads the WP Ultimo admin notices helper class.
		 */
		$this->notices = WP_Ultimo\Admin_Notices::get_instance();

		/*
		 * Loads the WP Ultimo scripts handler
		 */
		$this->scripts = WP_Ultimo\Scripts::get_instance();

		/*
		 * Checks Sunrise versions
		 */
		WP_Ultimo\Sunrise::manage_sunrise_updates();

		/*
		 * Loads tables
		 */
		$this->setup_tables();

		/*
		 * Loads files containing public functions.
		 */
		$this->load_public_apis();

		/*
		 * Loads extra components
		 */
		$this->load_extra_components();

		/*
		 * Loads managers
		 */
		$this->load_managers();

		/**
		 * Triggers when all the dependencies were loaded
		 *
		 * Allows plugin developers to add new functionality. For example, support to new
		 * Hosting providers, etc.
		 *
		 * @since 2.0.0
		 */
		do_action('wp_ultimo_load');

		/*
		 * Loads admin pages
		 * @todo: move this to a manager in the future?
		 */
		$this->load_admin_pages();

	} // end init;

	/**
	 * Returns true if all the requirements are met.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_loaded() {

		return $this->loaded;

	} // end is_loaded;

	/**
	 * Setup the plugin text domain to be used in translations.
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function setup_textdomain() {
		/*
		 * Loads the translation files.
		 */
		load_plugin_textdomain('wp-ultimo', false, dirname(WP_ULTIMO_PLUGIN_BASENAME) . '/lang');

	} // end setup_textdomain;

	/**
	 * Loads the table objects for our custom tables.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function setup_tables() {

		$this->tables = \WP_Ultimo\Loaders\Table_Loader::get_instance();

	} // end setup_tables;

	/**
	 * Loads public apis that should be on the global scope
	 *
	 * This method is responsible for loading and exposing public apis that
	 * plugin developers will use when creating extensions for WP Ultimo.
	 * Things like render functions, helper methods, etc.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	protected function load_public_apis() {
		/*
		 * Gateway Functions
		 */
		require_once $this->helper->path('inc/functions/checkout.php');
		require_once $this->helper->path('inc/functions/legacy.php');
		require_once $this->helper->path('inc/functions/gateway.php');

		/*
		 * Site Functions
		 */
		require_once $this->helper->path('inc/functions/site.php');

		/*
		 * Domain Functions
		 */
		require_once $this->helper->path('inc/functions/domain.php');

		/*
		 * Customer Functions
		 */
		require_once $this->helper->path('inc/functions/customer.php');

		/*
		 * Support Agent Functions
		 */
		require_once $this->helper->path('inc/functions/support-agent.php');

		/*
		 * Discount Code Functions
		 */
		require_once $this->helper->path('inc/functions/discount-code.php');

		/*
		 * Product Functions
		 */
		require_once $this->helper->path('inc/functions/product.php');

		/*
		 * Checkout Forms Functions
		 */
		require_once $this->helper->path('inc/functions/checkout-form.php');

		/*
		 * Membership Functions
		 */
		require_once $this->helper->path('inc/functions/membership.php');

		/*
		 * Payments Functions
		 */
		require_once $this->helper->path('inc/functions/payment.php');

		/*
		 * Taxes Functions
		 */
		require_once $this->helper->path('inc/functions/tax.php');

		/*
		 * Webhooks Functions
		 */
		require_once $this->helper->path('inc/functions/webhook.php');

		/*
		 * Loads deprecated functions.
		 */
		require_once $this->helper->path('inc/deprecated/deprecated.php');

		/*
		 * Loads the events public apis.
		 */
		require_once $this->helper->path('inc/functions/event.php');

		/*
		 * Loads the form public apis.
		 */
		require_once $this->helper->path('inc/functions/form.php');

		/*
		 * Loads the broadcast public apis.
		 */
		require_once $this->helper->path('inc/functions/broadcast.php');

		/*
		 * Loads the webhook public apis.
		 */
		require_once $this->helper->path('inc/functions/webhook.php');

		/*
		 * Financial Functions
		 */
		require_once $this->helper->path('inc/functions/financial.php');

		/*
		 * Mock Functions
		 */
		require_once $this->helper->path('inc/functions/mock.php');

		/*
		 * Color Functions
		 */
		require_once $this->helper->path('inc/functions/color.php');

	} // end load_public_apis;

	/**
	 * Load extra the WP Ultimo elements
	 *
	 * @since 2.0.0
	 * @return void
	 */
	protected function load_extra_components() {
		/*
		 * Loads the debugger tools
		 */
		WP_Ultimo\Debug\Debug::get_instance();

		/*
		 * Loads the Jumper UI
		 */
		WP_Ultimo\UI\Jumper::get_instance();

		/*
		 * Loads the Template Previewer
		 */
		WP_Ultimo\UI\Template_Previewer::get_instance();

		/*
		 * Loads the Toolbox UI
		 */
		WP_Ultimo\UI\Toolbox::get_instance();

		/*
		 * Loads the Tours
		 */
		WP_Ultimo\UI\Tours::get_instance();

		/*
		 * Loads the Maintenance Mode
		 */
		WP_Ultimo\Maintenance_Mode::get_instance();

		/*
		 * Support for Page Builder
		 * @todo: move to add-on
		 */
		\WP_Ultimo\Builders\Block_Editor\Block_Editor_Widget_Manager::get_instance();
		// \WP_Ultimo\Builders\Elementor\Elementor_Widget_Manager::get_instance();
		// \WP_Ultimo\Builders\Beaver_Builder\Beaver_Builder_Widget_Manager::get_instance();

		/*
		 * Loads the Checkout Block
		 * @todo remove those
		 */
		// WP_Ultimo\UI\Payment_Methods_Element::get_instance();
		// WP_Ultimo\UI\Invoices_Element::get_instance();

		WP_Ultimo\UI\Thank_You_Element::get_instance();
		WP_Ultimo\UI\Checkout_Element::get_instance();
		WP_Ultimo\UI\Login_Form_Element::get_instance();
		WP_Ultimo\UI\Simple_Text_Element::get_instance();

		/*
		 * Customer Blocks
		 */
		\WP_Ultimo\UI\My_Sites_Element::get_instance();
		\WP_Ultimo\UI\Current_Site_Element::get_instance();
		\WP_Ultimo\UI\Current_Membership_Element::get_instance();
		\WP_Ultimo\UI\Billing_Info_Element::get_instance();
		\WP_Ultimo\UI\Invoices_Element::get_instance();
		\WP_Ultimo\UI\Site_Actions_Element::get_instance();

		\WP_Ultimo\UI\Account_Summary_Element::get_instance();
		\WP_Ultimo\UI\Limits_Element::get_instance();
		\WP_Ultimo\UI\Domain_Mapping_Element::get_instance();
		\WP_Ultimo\UI\Site_Maintenance_Element::get_instance();

		/*
		 * Loads our Light Ajax implementation
		 */
		\WP_Ultimo\Light_Ajax::get_instance();

		/*
		 * Loads the Tax functionality
		 */
		\WP_Ultimo\Tax\Tax::get_instance();

		/*
		 * Loads our general Ajax endpoints.
		 */
		\WP_Ultimo\Ajax::get_instance();

		/*
		 * Loads API auth code.
		 */
		\WP_Ultimo\API::get_instance();

		/*
		 * Loads Documentation
		 */
		\WP_Ultimo\Documentation::get_instance();

		/*
		 * Loads our Limitations implementation
		 */
		\WP_Ultimo\Limits\Post_Type_Limits::get_instance();

		/*
		 * Loads the disk space limitations
		 */
		\WP_Ultimo\Limits\Disk_Space_Limits::get_instance();

		/*
		 * Loads Checkout
		 */
		\WP_Ultimo\Checkout\Checkout::get_instance();

		\WP_Ultimo\Checkout\Legacy_Checkout::get_instance();

		/*
		 * Dashboard Statistics
		 */
		\WP_Ultimo\Dashboard_Statistics::get_instance();

		/*
		 * Loads Permission Control
		 */
		\WP_Ultimo\Permission_Control::get_instance();

		/*
		 * Loads User Switching
		 */
		\WP_Ultimo\User_Switching::get_instance();

		/*
		 * Loads Legacy Shortcodes
		 */
		\WP_Ultimo\Compat\Legacy_Shortcodes::get_instance();

		/*
		 * Gutenberg Compatibility
		 */
		\WP_Ultimo\Compat\Gutenberg_Support::get_instance();

		/*
		 * Backwards compatibility with 1.X for products
		 */
		\WP_Ultimo\Compat\Product_Compat::get_instance();

		/*
		 * Backwards compatibility with 1.X for discount codes
		 */
		\WP_Ultimo\Compat\Discount_Code_Compat::get_instance();

		/*
		 * Elementor compatibility Layer
		 */
		\WP_Ultimo\Compat\Elementor_Compat::get_instance();

		/*
		 * Loads Basic White-labeling
		 */
		\WP_Ultimo\Whitelabel::get_instance();

		/*
		 * Adds support to multiple accounts.
		 *
		 * This used to be an add-on on WP Ultimo 1.X
		 * Now it is native, but needs to be activated on WP Ultimo settings.
		 */
		\WP_Ultimo\Compat\Multiple_Accounts_Compat::get_instance();

		/*
		 * Network Admin Widgets
		 */
		\WP_Ultimo\Dashboard_Widgets::get_instance();

		/*
		 * Support code
		 */
		\WP_Ultimo\Support::get_instance();

		/**
		 *  Admin Themes Compatibility for WP Ultimo
		 */
		\WP_Ultimo\Admin_Themes_Compatibility::get_instance();

	} // end load_extra_components;

	/**
	 * Load the WP Ultimo Admin Pages.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	protected function load_admin_pages() {
		/*
		 * Loads the Dashboard admin page.
		 */
		new WP_Ultimo\Admin_Pages\Dashboard_Admin_Page();

		/*
		 * The about admin page.
		 */
		new WP_Ultimo\Admin_Pages\About_Admin_Page();

		/*
		 * Loads the Checkout Form admin page.
		 */
		new WP_Ultimo\Admin_Pages\Checkout_Form_List_Admin_Page();

		new WP_Ultimo\Admin_Pages\Checkout_Form_Edit_Admin_Page();

		/*
		 * Loads the Product Pages
		 */
		new WP_Ultimo\Admin_Pages\Product_List_Admin_Page();

		new WP_Ultimo\Admin_Pages\Product_Edit_Admin_Page();

		/*
		 * Loads the Memberships Pages
		 */
		new WP_Ultimo\Admin_Pages\Membership_List_Admin_Page();

		new WP_Ultimo\Admin_Pages\Membership_Edit_Admin_Page();

		/*
		 * Loads the Payments Pages
		 */
		new WP_Ultimo\Admin_Pages\Payment_List_Admin_Page();

		new WP_Ultimo\Admin_Pages\Payment_Edit_Admin_Page();

		/*
		 * Loads the Customers Pages
		 */
		new WP_Ultimo\Admin_Pages\Customer_List_Admin_Page();

		new WP_Ultimo\Admin_Pages\Customer_Edit_Admin_Page();

		/*
		 * Loads the Support Agents Pages
		 */
		new WP_Ultimo\Admin_Pages\Support_Agent_List_Admin_Page();

		new WP_Ultimo\Admin_Pages\Support_Agent_Edit_Admin_Page();

		/*
		 * Loads the Site Pages
		 */
		new WP_Ultimo\Admin_Pages\Site_List_Admin_Page();

		new WP_Ultimo\Admin_Pages\Site_Edit_Admin_Page();

		/*
		 * Loads the Domain Pages
		 */
		new WP_Ultimo\Admin_Pages\Domain_List_Admin_Page();

		new WP_Ultimo\Admin_Pages\Domain_Edit_Admin_Page();

		/*
		 * Loads the Discount Code Pages
		 */
		new WP_Ultimo\Admin_Pages\Discount_Code_List_Admin_Page();

		new WP_Ultimo\Admin_Pages\Discount_Code_Edit_Admin_Page();

		/*
		 * Loads the Broadcast Pages
		 */
		new WP_Ultimo\Admin_Pages\Broadcast_List_Admin_Page();

		new WP_Ultimo\Admin_Pages\Broadcast_Edit_Admin_Page();

		/*
		 * Loads the Broadcast Pages
		 */
		new WP_Ultimo\Admin_Pages\Email_List_Admin_Page();

		new WP_Ultimo\Admin_Pages\Email_Edit_Admin_Page();

		new WP_Ultimo\Admin_Pages\Email_Template_Customize_Admin_Page();

		/*
		 * Loads the Settings
		 */
		new WP_Ultimo\Admin_Pages\Settings_Admin_Page();

		new WP_Ultimo\Admin_Pages\Addons_Admin_Page();

		new WP_Ultimo\Admin_Pages\Invoice_Template_Customize_Admin_Page();

		new WP_Ultimo\Admin_Pages\Template_Previewer_Customize_Admin_Page();

		/*
		 * Loads the Hosting Integration
		 */
		new WP_Ultimo\Admin_Pages\Hosting_Integration_Wizard_Admin_Page();

		/*
		 * Loads the Events Pages
		 */
		new WP_Ultimo\Admin_Pages\Event_List_Admin_Page();

		new WP_Ultimo\Admin_Pages\Event_View_Admin_Page();

		/*
		 * Loads the Webhooks Pages
		 */
		new WP_Ultimo\Admin_Pages\Webhook_List_Admin_Page();

		new WP_Ultimo\Admin_Pages\Webhook_Edit_Admin_Page();

		/*
		 * Loads the Jobs Pages
		 */
		new WP_Ultimo\Admin_Pages\Jobs_List_Admin_Page();

		/*
		 * Loads the System Info Pages
		 */
		new WP_Ultimo\Admin_Pages\System_Info_Admin_Page();

		/*
		 * Loads the View Logs Pages
		 */
		new WP_Ultimo\Admin_Pages\View_Logs_Admin_Page();

		/*
		 * Loads the View Logs Pages
		 */
		new WP_Ultimo\Admin_Pages\Customer_Panel\Account_Admin_Page();
		new WP_Ultimo\Admin_Pages\Customer_Panel\My_Sites_Admin_Page();

		// TODO: Remove all those
		new WP_Ultimo\Admin_Pages\Customer_Panel\Sites_Admin_Page();
		new WP_Ultimo\Admin_Pages\Customer_Panel\Shop_Admin_Page();
		// new WP_Ultimo\Admin_Pages\Customer_Panel\Checkout_Admin_Page();
		// new WP_Ultimo\Admin_Pages\Customer_Panel\My_Account_Admin_Page();
		new WP_Ultimo\Admin_Pages\Customer_Panel\My_Info_Admin_Page();
		new WP_Ultimo\Admin_Pages\Customer_Panel\Memberships_Admin_Page();
		new WP_Ultimo\Admin_Pages\Customer_Panel\Billing_Admin_Page();

		/*
		 * Loads the Tax Pages
		 */
		new WP_Ultimo\Tax\Dashboard_Taxes_Tab();

		do_action('wp_ultimo_admin_pages');

	} // end load_admin_pages;

	/**
	 * Load extra the WP Ultimo managers.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	protected function load_managers() {
		/*
		 * Loads the Event manager.
		 */
		WP_Ultimo\Managers\Event_Manager::get_instance();

		/*
		 * Loads the Domain Mapping manager.
		 */
		WP_Ultimo\Managers\Domain_Manager::get_instance();

		/*
		 * Loads the Product manager.
		 */
		WP_Ultimo\Managers\Product_Manager::get_instance();

		/*
		 * Loads the Discount Code manager.
		 */
		WP_Ultimo\Managers\Discount_Code_Manager::get_instance();

		/*
		 * Loads the Membership manager.
		 */
		WP_Ultimo\Managers\Membership_Manager::get_instance();

		/*
		 * Loads the Payment manager.
		 */
		WP_Ultimo\Managers\Payment_Manager::get_instance();

		/*
		 * Loads the Gateway manager.
		 */
		WP_Ultimo\Managers\Gateway_Manager::get_instance();

		/*
		 * Loads the Customer manager.
		 */
		WP_Ultimo\Managers\Customer_Manager::get_instance();

		/*
		 * Loads the support Agent manager.
		 */
		WP_Ultimo\Managers\Support_Agent_Manager::get_instance();

		/*
		 * Loads the Site manager.
		 */
		WP_Ultimo\Managers\Site_Manager::get_instance();

		/*
		 * Loads the Checkout Form manager.
		 */
		WP_Ultimo\Managers\Checkout_Form_Manager::get_instance();

		/*
		 * Loads the Webhook manager.
		 */
		WP_Ultimo\Managers\Webhook_Manager::get_instance();

		/*
		 * Loads the Broadcasts manager.
		 */
		WP_Ultimo\Managers\Email_Manager::get_instance();

		/*
		 * Loads the Broadcasts manager.
		 */
		WP_Ultimo\Managers\Broadcast_Manager::get_instance();

		/*
		 * Loads the Limitation manager.
		 */
		WP_Ultimo\Managers\Limitation_Manager::get_instance();

		/*
		 * Loads the Visits Manager.
		 */
		WP_Ultimo\Managers\Visits_Manager::get_instance();

		/*
		 * Loads the Job Queue manager.
		 */
		WP_Ultimo\Managers\Job_Manager::get_instance();

		/*
		 * Loads the Block manager.
		 */
		WP_Ultimo\Managers\Block_Manager::get_instance();

		/*
		 * Loads the Notification manager.
		 */
		WP_Ultimo\Managers\Notification_Manager::get_instance();

		/*
		 * License handler
		 */
		WP_Ultimo\License::get_instance();

	} // end load_managers;

} // end class WP_Ultimo;
