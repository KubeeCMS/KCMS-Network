<?php
/**
 * WP Ultimo Dashboard Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

use WP_Ultimo\Settings;
use WP_Ultimo\UI\Form;
use WP_Ultimo\UI\Field;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo Dashboard Admin Page.
 */
class Settings_Admin_Page extends Wizard_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-settings';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $type = 'submenu';

	/**
	 * Dashicon to be used on the menu item. This is only used on top-level menus
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $menu_icon = 'dashicons-wu-wp-ultimo';

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
	 * Should we hide admin notices on this page?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $hide_admin_notices = false;

	/**
	 * Should we force the admin menu into a folded state?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $fold_menu = false;

	/**
	 * Holds the section slug for the URLs.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $section_slug = 'tab';

	/**
	 * Defines if the step links on the side are clickable or not.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $clickable_navigation = true;

	/**
	 * Registers widgets to the edit page.
	 *
	 * This implementation register the default save widget.
	 * Child classes that wish to inherit that widget while registering other,
	 * can do such by adding a parent::register_widgets() to their own register_widgets() method.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_widgets() {

		parent::register_widgets();

		wu_register_settings_side_panel('general', array(
			'title'  => __('Add-ons', 'wp-ultimo'),
			'render' => array($this, 'render_addons_side_panel'),
		));

		wu_register_settings_side_panel('login-and-registration', array(
			'title'  => __('Checkout Forms', 'wp-ultimo'),
			'render' => array($this, 'render_checkout_forms_side_panel'),
		));

		wu_register_settings_side_panel('integrations', array(
			'title'  => __('Add-ons', 'wp-ultimo'),
			'render' => array($this, 'render_addons_side_panel'),
		));

		wu_register_settings_side_panel('sites', array(
			'title'  => __('Template Previewer', 'wp-ultimo'),
			'render' => array($this, 'render_site_template_side_panel'),
		));

		wu_register_settings_side_panel('payment-gateways', array(
			'title'  => __('Invoices', 'wp-ultimo'),
			'render' => array($this, 'render_invoice_side_panel'),
		));

		wu_register_settings_side_panel('payment-gateways', array(
			'title'  => __('Additional Gateways', 'wp-ultimo'),
			'render' => array($this, 'render_gateways_addons_side_panel'),
		));

		wu_register_settings_side_panel('taxes', array(
			'title'  => __('Tax Rates', 'wp-ultimo'),
			'render' => array($this, 'render_taxes_side_panel'),
		));

		wu_register_settings_side_panel('emails', array(
			'title'  => __('System Emails', 'wp-ultimo'),
			'render' => array($this, 'render_system_emails_side_panel'),
		));

		wu_register_settings_side_panel('emails', array(
			'title'  => __('Email Template', 'wp-ultimo'),
			'render' => array($this, 'render_email_template_side_panel'),
		));

		wu_register_settings_side_panel('all', array(
			'title'  => __('Your License', 'wp-ultimo'),
			'render' => array($this, 'render_account_side_panel'),
			'show'   => array(\WP_Ultimo\License::get_instance(), 'is_not_whitelabel'),
		));

	} // end register_widgets;

	// phpcs:disable

	/**
	 * Renders the addons side panel
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_addons_side_panel() { ?>

		<div class="wu-widget-inset">

			<div class="wu-p-4">

				<span class="wu-text-gray-700 wu-font-bold wu-uppercase wu-tracking-wide wu-text-xs">
					<?php _e('WP Ultimo Add-ons', 'wp-ultimo'); ?>
				</span>

				<div class="wu-py-2">
					<img class="wu-w-full" alt="<?php esc_attr_e('WP Ultimo Add-ons', 'wp-ultimo'); ?>" src="<?php echo wu_get_asset('sidebar/add-ons.png'); ?>">
				</div>

				<p class="wu-text-gray-600 wu-p-0 wu-m-0">
					<?php _e('You can extend WP Ultimo\'s functionality by installing one our add-ons!', 'wp-ultimo'); ?>
				</p>
				
			</div>

			<div class="wu-p-4 wu-bg-gray-100 wu-border-solid wu-border-0 wu-border-t wu-border-gray-300">
				<a class="button wu-w-full wu-text-center" href="<?php echo wu_network_admin_url('wp-ultimo-addons'); ?>">
					<?php _e('Check our Add-ons &rarr;', 'wp-ultimo'); ?>
				</a>
			</div>

		</div>

		<?php

	} // end render_addons_side_panel;

	/**
	 * Renders the account side panel
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_account_side_panel() { 
		/*
		 * Get Freemius Customer
		 */
		$customer = \WP_Ultimo\License::get_instance()->get_customer();

		/*
		 * Get Freemius License
		 */
		$license = \WP_Ultimo\License::get_instance()->get_license();

		?>

		<div class="wu-widget-inset">

			<div class="wu-p-4">

				<span class="wu-text-gray-700 wu-font-bold wu-uppercase wu-tracking-wide wu-text-xs">
					<?php _e('Registered to', 'wp-ultimo'); ?>
				</span>

				<p class="wu-text-gray-700 wu-p-0 wu-m-0 wu-mt-2">
					<?php printf('%s %s', $customer->first, $customer->last); ?>
					<span class="wu-text-xs wu-text-gray-600 wu-block"><?php echo $customer->email; ?></span>
					<span class="wu-text-xs wu-py-1 wu-px-2 wu-bg-gray-100 wu-rounded wu-mt-3 wu-text-gray-600 wu-block wu-border wu-border-solid wu-border-gray-300"><?php echo substr_replace($license->secret_key, str_repeat('*', 16), 4, 24); ?></span>
				</p>
				
			</div>

			<div class="wu-p-4 wu-bg-gray-100 wu-border-solid wu-border-0 wu-border-t wu-border-gray-300">
				<a class="button wu-w-full wu-text-center" href="<?php echo wu_network_admin_url('wp-ultimo-account'); ?>">
					<?php _e('Manage your Account &rarr;', 'wp-ultimo'); ?>
				</a>
			</div>

		</div>

		<?php

	} // end render_account_side_panel;

	/**
	 * Renders the addons side panel
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_gateways_addons_side_panel() { ?>

		<div class="wu-widget-inset">

			<div class="wu-p-4">

				<span class="wu-text-gray-700 wu-font-bold wu-uppercase wu-tracking-wide wu-text-xs">
					<?php _e('Accept Payments wherever you are', 'wp-ultimo'); ?>
				</span>

				<div class="wu-py-2">
					<img class="wu-w-full" alt="<?php esc_attr_e('Accept payments wherever you are', 'wp-ultimo'); ?>" src="<?php echo wu_get_asset('sidebar/gateway-add-ons.png'); ?>">
				</div>

				<p class="wu-text-gray-600 wu-p-0 wu-m-0">
					<?php _e('We are constantly adding support to new payment gateways that can be installed as add-ons.', 'wp-ultimo'); ?>
				</p>
				
			</div>

			<div class="wu-p-4 wu-bg-gray-100 wu-border-solid wu-border-0 wu-border-t wu-border-gray-300">
				<a class="button wu-w-full wu-text-center" href="<?php echo wu_network_admin_url('wp-ultimo-addons', array('tab' => 'gateways')); ?>">
					<?php _e('Check our supported Gateways &rarr;', 'wp-ultimo'); ?>
				</a>
			</div>

		</div>

		<?php

	} // end render_gateways_addons_side_panel;

	/**
	 * Renders the addons side panel
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_checkout_forms_side_panel() { ?>

		<div class="wu-widget-inset">

			<div class="wu-p-4">

				<span class="wu-text-gray-700 wu-font-bold wu-uppercase wu-tracking-wide wu-text-xs">
					<?php _e('Checkout Forms', 'wp-ultimo'); ?>
				</span>

				<div class="wu-py-2">
					<img class="wu-w-full" alt="<?php esc_attr_e('Checkout Forms', 'wp-ultimo'); ?>" src="<?php echo wu_get_asset('sidebar/checkout-forms.png'); ?>">
				</div>

				<p class="wu-text-gray-600 wu-p-0 wu-m-0">
					<?php _e('You can create multiple Checkout Forms for different occasions (season campaigns, launches, etc)!', 'wp-ultimo'); ?>
				</p>
				
			</div>

			<div class="wu-p-4 wu-bg-gray-100 wu-border-solid wu-border-0 wu-border-t wu-border-gray-300">
				<a class="button wu-w-full wu-text-center" href="<?php echo wu_network_admin_url('wp-ultimo-checkout-forms'); ?>">
					<?php _e('Manage Checkout Forms &rarr;', 'wp-ultimo'); ?>
				</a>
			</div>

		</div>

		<?php

	} // end render_checkout_forms_side_panel;

	/**
	 * Renders the site template side panel
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_site_template_side_panel() { ?>

		<div class="wu-widget-inset">

			<div class="wu-p-4">

				<span class="wu-text-gray-700 wu-font-bold wu-uppercase wu-tracking-wide wu-text-xs">
					<?php _e('Customize the Template Previewer', 'wp-ultimo'); ?>
				</span>

				<div class="wu-py-2">
					<img class="wu-w-full" alt="<?php esc_attr_e('Customize the Template Previewer', 'wp-ultimo'); ?>" src="<?php echo wu_get_asset('sidebar/site-template.png'); ?>">
				</div>

				<p class="wu-text-gray-600 wu-p-0 wu-m-0">
					<?php _e('Did you know that you can customize colors, logos, and more options of the Site Template Previewer top-bar?', 'wp-ultimo'); ?>
				</p>
				
			</div>

			<div class="wu-p-4 wu-bg-gray-100 wu-border-solid wu-border-0 wu-border-t wu-border-gray-300">
				<a class="button wu-w-full wu-text-center" target="_blank" href="<?php echo wu_network_admin_url('wp-ultimo-customize-template-previewer'); ?>">
					<?php _e('Go to Customizer &rarr;', 'wp-ultimo'); ?>
				</a>
			</div>

		</div>

		<?php

	} // end render_site_template_side_panel;

	/**
	 * Renders the invoice side panel
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_invoice_side_panel() { ?>

		<div class="wu-widget-inset">

			<div class="wu-p-4">

				<span class="wu-text-gray-700 wu-font-bold wu-uppercase wu-tracking-wide wu-text-xs">
					<?php _e('Customize the Invoice Template', 'wp-ultimo'); ?>
				</span>

				<div class="wu-py-2">
					<img class="wu-w-full" alt="<?php esc_attr_e('Customize the Invoice Template', 'wp-ultimo'); ?>" src="<?php echo wu_get_asset('sidebar/invoice-template.png'); ?>">
				</div>

				<p class="wu-text-gray-600 wu-p-0 wu-m-0">
					<?php _e('Did you know that you can customize colors, logos, and more options of the Invoice PDF template?', 'wp-ultimo'); ?>
				</p>
				
			</div>

			<div class="wu-p-4 wu-bg-gray-100 wu-border-solid wu-border-0 wu-border-t wu-border-gray-300">
				<a class="button wu-w-full wu-text-center" target="_blank" href="<?php echo wu_network_admin_url('wp-ultimo-customize-invoice-template'); ?>">
					<?php _e('Go to Customizer &rarr;', 'wp-ultimo'); ?>
				</a>
			</div>

		</div>

		<?php

	} // end render_invoice_side_panel;

	/**
	 * Render the tax side panel.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_taxes_side_panel() { ?>

		<div id="wu-taxes-side-panel" class="wu-widget-inset">

			<div class="wu-p-4">

				<span class="wu-text-gray-700 wu-font-bold wu-uppercase wu-tracking-wide wu-text-xs">
					<?php _e('Manage Tax Rates', 'wp-ultimo'); ?>
				</span>

				<div class="wu-py-2">
					<img class="wu-w-full" alt="<?php esc_attr_e('Manage Tax Rates', 'wp-ultimo'); ?>" src="<?php echo wu_get_asset('sidebar/invoices.png'); ?>">
				</div>

				<p class="wu-text-gray-600 wu-p-0 wu-m-0">
					<?php _e('Add different tax rates depending on the country of your customers.', 'wp-ultimo'); ?>
				</p>
				
			</div>

			<div v-cloak v-show="enabled == 0" class="wu-mx-4 wu-p-2 wu-bg-blue-100 wu-text-blue-600 wu-rounded wu-mb-4">
				<?php _e('You need to activate tax support first.', 'wp-ultimo'); ?>
			</div>

			<div class="wu-p-4 wu-bg-gray-100 wu-border-solid wu-border-0 wu-border-t wu-border-gray-300">
				<a disable="disable" :disabled="enabled == 0" class="button wu-w-full wu-text-center" target="_blank" href="<?php echo wu_network_admin_url('wp-ultimo-tax-rates'); ?>">
					<?php _e('Manage Tax Rates &rarr;', 'wp-ultimo'); ?>
				</a>
			</div>

		</div>

		<script>
			(function($) {
				$(document).ready(function() {
					new Vue({
						el: "#wu-taxes-side-panel",
						data: {},
						computed: {
							enabled: function() {
								return <?php echo json_encode(wu_get_setting('enable_taxes')); ?>
							}
						}
					});
				});
			}(jQuery));
		</script>

		<?php

	} // end render_taxes_side_panel;

	/**
	 * Renders system emails side panel.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_system_emails_side_panel() { ?>

		<div class="wu-widget-inset">

			<div class="wu-p-4">

				<span class="wu-text-gray-700 wu-font-bold wu-uppercase wu-tracking-wide wu-text-xs">
					<?php _e('Customize System Emails', 'wp-ultimo'); ?>
				</span>

				<div class="wu-py-2">
					<img class="wu-w-full" alt="<?php esc_attr_e('Customize System Emails', 'wp-ultimo'); ?>" src="<?php echo wu_get_asset('sidebar/system-emails.png'); ?>">
				</div>

				<p class="wu-text-gray-600 wu-p-0 wu-m-0">
					<?php _e('You can completely customize the contents of the emails sent out by WP Ultimo when particular events occur, such as Account Creation, Payment Failures, etc.', 'wp-ultimo'); ?>
				</p>
				
			</div>

			<div class="wu-p-4 wu-bg-gray-100 wu-border-solid wu-border-0 wu-border-t wu-border-gray-300">
				<a class="button wu-w-full wu-text-center" target="_blank" href="<?php echo wu_network_admin_url('wp-ultimo-emails'); ?>">
					<?php _e('Customize System Emails &rarr;', 'wp-ultimo'); ?>
				</a>
			</div>

		</div>

		<?php

	} // end render_system_emails_side_panel;

	/**
	 * Renders the email template side panel.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_email_template_side_panel() { ?>

		<div class="wu-widget-inset">

			<div class="wu-p-4">

				<span class="wu-text-gray-700 wu-font-bold wu-uppercase wu-tracking-wide wu-text-xs">
					<?php _e('Customize Email Template', 'wp-ultimo'); ?>
				</span>

				<div class="wu-py-2">
					<img class="wu-w-full" alt="<?php esc_attr_e('Customize Email Template', 'wp-ultimo'); ?>" src="<?php echo wu_get_asset('sidebar/email-template.png'); ?>">
				</div>

				<p class="wu-text-gray-600 wu-p-0 wu-m-0">
					<?php _e('If your network is using the HTML email option, you can customize the look and feel of the email template.', 'wp-ultimo'); ?>
				</p>
				
			</div>

			<div class="wu-p-4 wu-bg-gray-100 wu-border-solid wu-border-0 wu-border-t wu-border-gray-300">
				<a class="button wu-w-full wu-text-center" target="_blank" href="<?php echo wu_network_admin_url('wp-ultimo-customize-email-template'); ?>">
					<?php _e('Customize Email Template &rarr;', 'wp-ultimo'); ?>
				</a>
			</div>

		</div>

		<?php

	} // end render_email_template_side_panel;

	// phpcs:enable

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return __('Settings', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Settings', 'wp-ultimo');

	} // end get_menu_title;

	/**
	 * Every child class should implement the output method to display the contents of the page.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function output() {
		/*
		 * Enqueue the base Dashboard Scripts
		 */
		wp_enqueue_media();
		wp_enqueue_script('dashboard');
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_script('wp-color-picker');
		wp_enqueue_script('media');
		wp_enqueue_script('wu-vue');
		wp_enqueue_script('wu-selectizer');

		do_action('wu_render_settings');

		wu_get_template('base/settings', array(
			'screen'               => get_current_screen(),
			'page'                 => $this,
			'classes'              => '',
			'sections'             => $this->get_sections(),
			'current_section'      => $this->get_current_section(),
			'clickable_navigation' => $this->clickable_navigation,
		));

	} // end output;

	/**
	 * Returns the list of settings sections.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_sections() {

		return WP_Ultimo()->settings->get_sections();

	} // end get_sections;

	/**
	 * Default handler for step submission. Simply redirects to the next step.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function default_handler() {

		WP_Ultimo()->settings->save_settings($_POST);

		wp_redirect(add_query_arg('updated', 1, wu_get_current_url()));

		exit;

	} // end default_handler;

	/**
	 * Default method for views.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function default_view() {

		$sections = $this->get_sections();

		$section_slug = $this->get_current_section();

		$section = $this->current_section;

		$fields = array_filter($section['fields'], function($item) {

			return current_user_can($item['capability']);

		});

		uasort($fields, 'wu_sort_by_order');

		/*
		 * Get Field to save
		 */
		$fields['save'] = array(
			'type'            => 'submit',
			'title'           => __('Save Settings', 'wp-ultimo'),
			'classes'         => 'button button-primary button-large wu-ml-auto',
			'wrapper_classes' => 'wu-sticky wu-bottom-0 wu-save-button wu-mr-px wu-flex',
			'html_attr'       => array(
				'v-on:click' => 'send("window", "wu_block_ui", "#wpcontent")'
			),
		);

		$form = new Form($section_slug, $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu--mt-5 wu--mx-in wu--mb-in',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-py-5 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'style'        => '',
				'data-on-load' => 'remove_block_ui',
				'data-wu-app'  => str_replace('-', '_', $section_slug),
				'data-state'   => json_encode(wu_array_map_keys('wu_replace_dashes', Settings::get_instance()->get_all(true))),
			),
		));

		$form->render();

	} // end default_view;

} // end class Settings_Admin_Page;
