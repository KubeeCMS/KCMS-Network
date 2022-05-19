<?php
/**
 * WP Ultimo Customize/Add New Invoice Template Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

use \WP_Ultimo\Invoices\Invoice;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo Invoice Template Customize/Add New Admin Page.
 */
class Invoice_Template_Customize_Admin_Page extends Customizer_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-customize-invoice-template';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $type = 'submenu';

	/**
	 * Object ID being customizeed.
	 *
	 * @since 1.8.2
	 * @var string
	 */
	public $object_id = 'invoice_template';

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
		'network_admin_menu' => 'wu_customize_invoice_template',
	);

	/**
	 * Overrides the original init to add the required ajax endpoints.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		parent::init();

		add_action('wp_ajax_wu-preview-invoice', array($this, 'generate_invoice_preview'));

	} // end init;

	/**
	 * Ajax endpoint to generate the Ajax Preview.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function generate_invoice_preview() {

		if (!current_user_can('wu_manage_invoice')) {

			return;

		} // end if;

		$order = false;

		$payment = wu_mock_payment();

		$invoice = new Invoice($payment, $_REQUEST);

		$invoice->print_file();

		die;

	} // end generate_invoice_preview;

	/**
	 * Returns the preview URL. This is then added to the iframe.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_preview_url() {

		$url = get_admin_url(wu_get_main_site_id(), 'admin-ajax.php');

		return add_query_arg(array(
			'action'            => 'wu-preview-invoice',
			'customizer'        => 1,
			'invoice-customize' => 1
		), $url);

	} // end get_preview_url;

	/**
	 * Allow child classes to register widgets, if they need them.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_widgets() {

		$settings = Invoice::get_settings();

		$this->add_save_widget('save', array(
			'fields' => array(
				'note' => array(
					'type' => 'note',
					'desc' => __('Changes to this template will be applied to all PDF invoices generated after the change. <br><br>Existing PDF Invoices will not be affected unless explicitly re-generated', 'wp-ultimo'),
				),
			)
		));

		$custom_logo = wu_get_isset($settings, 'custom_logo');

		$custom_logo_args = wp_get_attachment_image_src($custom_logo, 'full');

		$custom_logo_url = $custom_logo_args ? $custom_logo_args[0] : '';

		$fields = array(
			'tab'             => array(
				'type'              => 'tab-select',
				'wrapper_classes'   => '',
				'wrapper_html_attr' => array(
					'v-cloak' => 1,
				),
				'html_attr'         => array(
					'v-model' => 'tab',
				),
				'options'           => array(
					'general' => __('General', 'wp-ultimo'),
					'colors'  => __('Colors', 'wp-ultimo'),
					'images'  => __('Images', 'wp-ultimo'),
				),
			),

			'paid_tag_text'   => array(
				'type'              => 'text',
				'title'             => __('Paid Tag', 'wp-ultimo'),
				'placeholder'       => __('e.g. Paid.', 'wp-ultimo'),
				'value'             => wu_get_isset($settings, 'paid_tag_text', __('Paid', 'wp-ultimo')),
				'wrapper_html_attr' => array(
					'v-show'  => 'require("tab", "general")',
					'v-cloak' => 1,
				),
				'html_attr'         => array(
					'v-model.lazy' => 'paid_tag_text',
				),
			),
			'font'            => array(
				'type'              => 'select',
				'title'             => __('Font-Family', 'wp-ultimo'),
				'value'             => wu_get_isset($settings, 'font', ''),
				'options'           => array(
					'DejaVuSansCondensed' => __('Sans-Serif', 'wp-ultimo'),
					'FreeSerif'           => __('Serif', 'wp-ultimo'),
					'FreeMono'            => __('Mono', 'wp-ultimo'),
				),
				'wrapper_html_attr' => array(
					'v-show'  => 'require("tab", "general")',
					'v-cloak' => 1,
				),
				'html_attr'         => array(
					'v-model.lazy' => 'font',
				),
			),
			'footer_message'  => array(
				'type'              => 'textarea',
				'title'             => __('Footer Content', 'wp-ultimo'),
				'placeholder'       => __('e.g. Extra Info about the Invoice.', 'wp-ultimo'),
				'value'             => wu_get_isset($settings, 'footer_message', ''),
				'wrapper_html_attr' => array(
					'v-show'  => 'require("tab", "general")',
					'v-cloak' => 1,
				),
				'html_attr'         => array(
					'v-model.lazy' => 'footer_message',
				),
			),

			'primary_color'   => array(
				'type'              => 'color-picker',
				'title'             => __('Primary Color', 'wp-ultimo'),
				'value'             => '#00a1ff',
				'wrapper_html_attr' => array(
					'v-show'  => 'require("tab", "colors")',
					'v-cloak' => 1,
				),
				'html_attr'         => array(
					'v-model' => 'primary_color',
				),
			),

			'use_custom_logo' => array(
				'type'              => 'toggle',
				'title'             => __('Use Custom Logo', 'wp-ultimo'),
				'desc'              => __('You can set a different logo to be used on the invoice.', 'wp-ultimo'),
				'wrapper_html_attr' => array(
					'v-show'  => 'require("tab", "images")',
					'v-cloak' => 1,
				),
				'html_attr'         => array(
					'v-model' => 'use_custom_logo',
				),
			),
			'custom_logo'     => array(
				'type'              => 'image',
				'title'             => __('Custom Logo', 'wp-ultimo'),
				'desc'              => __('This will be added to the top of the generated PDF.', 'wp-ultimo'),
				'value'             => '',
				'img'               => $custom_logo_url,
				'stacked'           => true,
				'wrapper_html_attr' => array(
					'v-show'  => 'require("tab", "images") && require("use_custom_logo", true)',
					'v-cloak' => 1,
				),
				'html_attr'         => array(
					'v-model' => 'custom_logo',
				),
			),
		);

		$settings = array(
			'footer_message'  => wu_get_isset($settings, 'footer_message', ''),
			'paid_tag_text'   => wu_get_isset($settings, 'paid_tag_text', __('Paid', 'wp-ultimo')),
			'primary_color'   => wu_get_isset($settings, 'primary_color', '00a1ff'),
			'use_custom_logo' => wu_get_isset($settings, 'use_custom_logo'),
			'custom_logo'     => wu_get_isset($settings, 'custom_logo'),
			'font'            => wu_get_isset($settings, 'font', 'DejaVuSansCondensed'),
		);

		$state = array_merge($settings, array(
			'tab'     => 'general',
			'refresh' => true,
		));

		$this->add_fields_widget('customizer', array(
			'title'     => __('Customizer', 'wp-ultimo'),
			'position'  => 'side',
			'fields'    => $fields,
			'html_attr' => array(
				'style'                    => 'margin-top: -6px;',
				'data-wu-app'              => 'invoice_customizer',
				'data-wu-customizer-panel' => true,
				'data-state'               => json_encode($state),
			),
		));

	} // end register_widgets;

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return __('Customize Invoice Template', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Customize Invoice Template', 'wp-ultimo');

	} // end get_menu_title;

	/**
	 * Returns the action links for that page.
	 *
	 * @since 1.8.2
	 * @return array
	 */
	public function action_links() {

		return array();

	} // end action_links;

	/**
	 * Returns the labels to be used on the admin page.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_labels() {

		return array(
			'customize_label'   => __('Customize Invoice Template', 'wp-ultimo'),
			'add_new_label'     => __('Customize Invoice Template', 'wp-ultimo'),
			'edit_label'        => __('Edit Invoice Template', 'wp-ultimo'),
			'updated_message'   => __('Invoice Template updated with success!', 'wp-ultimo'),
			'title_placeholder' => __('Enter Invoice Template Name', 'wp-ultimo'),
			'title_description' => __('This name is used for internal reference only.', 'wp-ultimo'),
			'save_button_label' => __('Save Invoice Template', 'wp-ultimo'),
			'save_description'  => __('Save Invoice Template', 'wp-ultimo'),
		);

	} // end get_labels;

	/**
	 * Should implement the processes necessary to save the changes made to the object.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_save() {

		Invoice::save_settings($_POST);

		$url = add_query_arg('updated', '1');

		wp_redirect($url);

		exit;

	} // end handle_save;

} // end class Invoice_Template_Customize_Admin_Page;
