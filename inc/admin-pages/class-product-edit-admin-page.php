<?php
/**
 * WP Ultimo Product Edit/Add New Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Models\Product;
use \WP_Ultimo\Database\Products\Product_Type;

/**
 * WP Ultimo Product Edit/Add New Admin Page.
 */
class Product_Edit_Admin_Page extends Edit_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-edit-product';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $type = 'submenu';

	/**
	 * Object ID being edited.
	 *
	 * @since 1.8.2
	 * @var string
	 */
	public $object_id = 'product';

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
	protected $highlight_menu_slug = 'wp-ultimo-products';

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
		'network_admin_menu' => 'wu_edit_products',
	);

	/**
	 * Register ajax forms.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms() {
		/*
		 * Adds the hooks to handle deletion.
		 */
		add_filter('wu_form_fields_delete_product_modal', array($this, 'product_extra_delete_fields'), 10, 2);

		add_action('wu_after_delete_product_modal', array($this, 'product_after_delete_actions'));

		add_filter("wu_page_{$this->id}_load", array($this, 'add_new_product_warning_message'));

	} // end register_forms;

	/**
	 * Adds the new product warning.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_new_product_warning_message() {

		if (wu_request('wu-new-model')) {

			if (!$this->get_object() || $this->get_object()->get_type() !== Product_Type::PLAN) {

				return;

			} // end if;

			\WP_Ultimo\UI\Tours::get_instance()->create_tour('new_product_warning', array(
				array(
					'id'       => 'new-product-warning',
					'title'    => __('On adding a new product...', 'wp-ultimo'),
					'text'     => array(
						__("You just successfully added a new product to your WP Ultimo network and that's awesome!", 'wp-ultimo'),
						__('Keep in mind that newly created products do not appear automatically in your checkout forms.', 'wp-ultimo'),
						__('To make a product available on registration, you will need to manually add it to the pricing table field of your checkout forms.', 'wp-ultimo'),
					),
					'buttons'  => array(
						array(
							'classes' => 'button wu-text-xs sm:wu-normal-case wu-float-left',
							'text'    => __('Go to Checkout Forms', 'wp-ultimo'),
							'url'     => wu_network_admin_url('wp-ultimo-checkout-forms'),
						)
					),
					'attachTo' => array(
						'element' => '#message.updated',
						'on'      => 'top',
					),
				),
			));

		} // end if;

	} // end add_new_product_warning_message;

	/**
	 * Adds the extra delete fields to the delete form.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $fields The original fields.
	 * @param object $product The product object.
	 * @return array
	 */
	public function product_extra_delete_fields($fields, $product) {

		$custom_fields = array(
			're_assignment_product_id' => array(
				'type'        => 'model',
				'title'       => __('Re-assign Memberships to', 'wp-ultimo'),
				'placeholder' => __('Select Product...', 'wp-ultimo'),
				'tooltip'     => __('The product you select here will be assigned to all the memberships attached to the product you are deleting.', 'wp-ultimo'),
				'html_attr'   => array(
					'data-model'        => 'product',
					'data-value-field'  => 'id',
					'data-label-field'  => 'name',
					'data-search-field' => 'name',
					'data-max-items'    => 1,
					'data-exclude'      => json_encode(array($product->get_id()))
				),
			),
		);

		return array_merge($custom_fields, $fields);

	} // end product_extra_delete_fields;

	/**
	 * Adds the primary domain handling to the product deletion.
	 *
	 * @since 2.0.0
	 *
	 * @param object $product The product object.
	 * @return void
	 */
	public function product_after_delete_actions($product) {

		global $wpdb;

		$new_product_id = wu_request('re_assignment_product_id');

		$re_assignment_product = wu_get_product($new_product_id);

		if ($re_assignment_product) {

			$query = $wpdb->prepare(
				"UPDATE {$wpdb->base_prefix}wu_memberships
				 SET product_id = %d
				 WHERE product_id = %d",
				$re_assignment_product->get_id(),
				$product->get_id()
			);

			$wpdb->query($query); // phpcs:ignore

		} // end if;

	} // end product_after_delete_actions;

	/**
	 * Registers the necessary scripts and styles for this admin page.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts() {

		parent::register_scripts();

		wp_enqueue_media();

	} // end register_scripts;

	/**
	 * Allow child classes to register widgets, if they need them.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_widgets() {

		parent::register_widgets();

		$this->add_fields_widget('description', array(
			'title'    => __('Description', 'wp-ultimo'),
			'position' => 'normal',
			'fields'   => array(
				'description' => array(
					'type'        => 'textarea',
					'title'       => __('Product Description', 'wp-ultimo'),
					'placeholder' => __('Tell your customers what this product is about.', 'wp-ultimo'),
					'tooltip'     => __('This description is made available for layouts and can be shown to end customers.', 'wp-ultimo'),
					'value'       => $this->get_object()->get_description(),
					'html_attr'   => array(
						'rows' => 3,
					),
				),
			),
		));

		$this->add_tabs_widget('product_options', array(
			'title'    => __('Product Options', 'wp-ultimo'),
			'position' => 'normal',
			'sections' => $this->get_product_option_sections(),
		));

		/*
		 * Handle legacy options for back-compat.
		 */
		$this->handle_legacy_options();

		$this->add_list_table_widget('events', array(
			'title'        => __('Events', 'wp-ultimo'),
			'table'        => new \WP_Ultimo\List_Tables\Inside_Events_List_Table(),
			'query_filter' => array($this, 'query_filter'),
		));

		$save_widget_args = apply_filters('wu_product_edit_save_widget', array(
			'html_attr' => array(
				'data-wu-app' => 'product_pricing',
				'data-state'  => json_encode(array(
					'is_recurring'  => $this->get_object()->is_recurring(),
					'pricing_type'  => $this->get_object()->get_pricing_type(),
					'has_trial'     => $this->get_object()->get_trial_duration() > 0,
					'has_setup_fee' => $this->get_object()->has_setup_fee(),
					'setup_fee'     => $this->get_object()->get_setup_fee(),
					'amount'        => $this->get_object()->get_formatted_amount(),
					'duration'      => $this->get_object()->get_duration(),
					'duration_unit' => $this->get_object()->get_duration_unit(),
				)),
			),
			'fields'    => array(
				// Fields for price
				'pricing_type'     => array(
					'type'              => 'select',
					'title'             => __('Pricing Type', 'wp-ultimo'),
					'placeholder'       => __('Select Pricing Type', 'wp-ultimo'),
					'desc'              => __('Products can be free, paid, or require further contact for pricing.', 'wp-ultimo'),
					'value'             => $this->get_object()->get_pricing_type(),
					'tooltip'           => '',
					'options'           => array(
						'paid'       => __('Paid', 'wp-ultimo'),
						'free'       => __('Free', 'wp-ultimo'),
						'contact_us' => __('Contact Us', 'wp-ultimo'),
					),
					'wrapper_html_attr' => array(
						'v-cloak' => '1',
					),
					'html_attr'         => array(
						'v-model' => 'pricing_type',
					),
				),
				'contact_us_label' => array(
					'type'              => 'text',
					'title'             => __('Button Label', 'wp-ultimo'),
					'placeholder'       => __('E.g. Contact us', 'wp-ultimo'),
					'desc'              => __('This will be used on the pricing table CTA button, as the label.', 'wp-ultimo'),
					'value'             => $this->get_object()->get_contact_us_label(),
					'wrapper_html_attr' => array(
						'v-show'  => "pricing_type == 'contact_us'",
						'v-cloak' => '1',
					),
				),
				'contact_us_link'  => array(
					'type'              => 'url',
					'title'             => __('Button Link', 'wp-ultimo'),
					'placeholder'       => __('E.g. https://contactus.page.com', 'wp-ultimo'),
					'desc'              => __('This will be used on the pricing table CTA button.', 'wp-ultimo'),
					'value'             => $this->get_object()->get_contact_us_link(),
					'wrapper_html_attr' => array(
						'v-show'  => "pricing_type == 'contact_us'",
						'v-cloak' => '1',
					),
				),
				'recurring'        => array(
					'type'              => 'toggle',
					'title'             => __('Is Recurring?', 'wp-ultimo'),
					'desc'              => __('Check this if this product has a recurring charge.', 'wp-ultimo'),
					'value'             => $this->get_object()->is_recurring(),
					'wrapper_html_attr' => array(
						'v-show'  => "pricing_type == 'paid'",
						'v-cloak' => '1',
					),
					'html_attr'         => array(
						'v-model' => 'is_recurring',
					),
				),
				'amount'           => array(
					'type'      => 'hidden',
					'html_attr' => array(
						'v-model' => 'amount',
					),
				),
				'_amount'          => array(
					'type'              => 'text',
					'title'             => __('Price', 'wp-ultimo'),
					'placeholder'       => __('Price', 'wp-ultimo'),
					'value'             => $this->get_object()->get_formatted_amount(),
					'tooltip'           => '',
					'money'             => true,
					'wrapper_html_attr' => array(
						'v-show'  => "pricing_type == 'paid' && !is_recurring ",
						'v-cloak' => '1',
					),
					'html_attr'         => array(
						'v-bind:name' => '""',
						'v-model'     => 'amount',
					),
				),
				'amount_group'     => array(
					'type'              => 'group',
					'title'             => __('Price', 'wp-ultimo'),
					// translators: placeholder %1$s is the amount, %2$s is the duration (such as 1, 2, 3), and %3$s is the unit (such as month, year, week)
					'desc'              => sprintf(__('The customer will be charged %1$s every %2$s %3$s(s).', 'wp-ultimo'), '{{ wu_format_money(amount) }}', '{{ duration }}', '{{ duration_unit }}'),
					'tooltip'           => '',
					'wrapper_html_attr' => array(
						'v-show'  => "is_recurring && pricing_type == 'paid'",
						'v-cloak' => '1',
					),
					'fields'            => array(
						'_amount'       => array(
							'type'            => 'text',
							'value'           => $this->get_object()->get_formatted_amount(),
							'placeholder'     => wu_format_currency('99'),
							'wrapper_classes' => '',
							'money'           => true,
							'html_attr'       => array(
								'v-bind:name' => '""',
								'v-model'     => 'amount',
							),
						),
						'duration'      => array(
							'type'            => 'number',
							'value'           => $this->get_object()->get_duration(),
							'placeholder'     => '',
							'wrapper_classes' => 'wu-mx-2 wu-w-1/3',
							'min'             => 0,
							'html_attr'       => array(
								'v-model' => 'duration',
								'steps'   => 1,
							),
						),
						'duration_unit' => array(
							'type'            => 'select',
							'value'           => $this->get_object()->get_duration_unit(),
							'placeholder'     => '',
							'wrapper_classes' => 'wu-w-2/3',
							'html_attr'       => array(
								'v-model' => 'duration_unit',
							),
							'options'         => array(
								'day'   => __('Days', 'wp-ultimo'),
								'week'  => __('Weeks', 'wp-ultimo'),
								'month' => __('Months', 'wp-ultimo'),
								'year'  => __('Years', 'wp-ultimo'),
							),
						),
					),
				),
				'billing_cycles'   => array(
					'type'              => 'number',
					'title'             => __('Billing Cycles', 'wp-ultimo'),
					'placeholder'       => __('E.g. 1', 'wp-ultimo'),
					'desc'              => __('How many times should we bill this customer. Leave 0 to charge until cancelled.', 'wp-ultimo'),
					'value'             => $this->get_object()->get_billing_cycles(),
					'tooltip'           => '',
					'wrapper_html_attr' => array(
						'v-show'  => "is_recurring && pricing_type == 'paid'",
						'v-cloak' => '1',
					),
				),
				'has_trial'        => array(
					'type'              => 'toggle',
					'title'             => __('Offer Trial', 'wp-ultimo'),
					'desc'              => __('Check if you want to add a trial period to this product.', 'wp-ultimo'),
					'value'             => $this->get_object()->has_trial(),
					'wrapper_html_attr' => array(
						'v-show'  => "pricing_type == 'paid'",
						'v-cloak' => '1',
					),
					'html_attr'         => array(
						'v-model' => 'has_trial',
					),
				),
				'trial_group'      => array(
					'type'              => 'group',
					'title'             => __('Trial', 'wp-ultimo'),
					'tooltip'           => '',
					'wrapper_html_attr' => array(
						'v-show'  => "has_trial && pricing_type == 'paid'",
						'v-cloak' => '1',
					),
					'fields'            => array(
						'trial_duration'      => array(
							'type'            => 'number',
							'value'           => $this->get_object()->get_trial_duration(),
							'placeholder'     => '',
							'wrapper_classes' => 'wu-mr-2 wu-w-1/3',
						),
						'trial_duration_unit' => array(
							'type'            => 'select',
							'value'           => $this->get_object()->get_trial_duration_unit(),
							'placeholder'     => '',
							'wrapper_classes' => 'wu-w-2/3',
							'options'         => array(
								'day'   => __('Days', 'wp-ultimo'),
								'week'  => __('Weeks', 'wp-ultimo'),
								'month' => __('Months', 'wp-ultimo'),
								'year'  => __('Years', 'wp-ultimo'),
							),
						),
					),
				),
				'has_setup_fee'    => array(
					'type'              => 'toggle',
					'title'             => __('Add Setup Fee?', 'wp-ultimo'),
					'desc'              => __('Check if you want to add a setup fee.', 'wp-ultimo'),
					'value'             => $this->get_object()->has_setup_fee(),
					'wrapper_html_attr' => array(
						'v-show'  => "pricing_type == 'paid'",
						'v-cloak' => '1',
					),
					'html_attr'         => array(
						'v-model' => 'has_setup_fee',
					),
				),
				'setup_fee'        => array(
					'type'      => 'hidden',
					'html_attr' => array(
						'v-model' => 'setup_fee',
					),
				),
				'_setup_fee'       => array(
					'type'              => 'text',
					'money'             => true,
					'title'             => __('Setup Fee', 'wp-ultimo'),
					'desc'              => __('The setup fee will be added to the first charge, in addition to the regular price of the product.', 'wp-ultimo'),
					'placeholder'       => sprintf(__('E.g. %s', 'wp-ultimo'), wu_format_currency(199)),
					'value'             => $this->get_object()->get_formatted_amount('setup_fee'),
					'wrapper_html_attr' => array(
						'v-show'  => "has_setup_fee && pricing_type == 'paid'",
						'v-cloak' => '1',
					),
					'html_attr'         => array(
						'v-model' => 'setup_fee',
					),
				),
			),
		), $this->get_object());

		$this->add_save_widget('save', $save_widget_args);

		$this->add_fields_widget('active', array(
			'title'  => __('Active', 'wp-ultimo'),
			'fields' => array(
				'active' => array(
					'type'  => 'toggle',
					'title' => __('Active', 'wp-ultimo'),
					'desc'  => __('Use this option to manually enable or disable this product for new sign-ups.', 'wp-ultimo'),
					'value' => $this->get_object()->is_active(),
				),
			),
		));

		$this->add_fields_widget('image', array(
			'title'  => __('Product Image', 'wp-ultimo'),
			'fields' => array(
				'featured_image_id' => array(
					'type'    => 'image',
					'stacked' => true,
					'title'   => __('Product Image', 'wp-ultimo'),
					'desc'    => __('This image is used on product list tables and other places.', 'wp-ultimo'),
					'value'   => $this->get_object()->get_featured_image_id(),
					'img'     => $this->get_object()->get_featured_image(),
				),
			),
		));

	} // end register_widgets;

	/**
	 * Handles legacy advanced options for plans.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_legacy_options() {

		global $wp_filter;

		$tabs = apply_filters_deprecated('wu_plans_advanced_options_tabs', array(
			array(),
		), '2.0.0', 'wu_product_options_sections');

		if (!isset($wp_filter['wu_plans_advanced_options_after_panels'])) {

			return;

		} // end if;

		wp_enqueue_style('wu-legacy-admin-tabs', wu_get_asset('legacy-admin-tabs.css', 'css'), false, wu_get_version());

		$priorities = $wp_filter['wu_plans_advanced_options_after_panels']->callbacks;

		$fields = array(
			'heading' => array(
				'type'  => 'header',
				'title' => __('Legacy Options', 'wp-ultimo'),
				// translators: %s is the name of legacy add-ons.
				'desc'  => sprintf(__('Options for %s, and others.', 'wp-ultimo'), implode(', ', $tabs)),
			),
		);

		foreach ($priorities as $priority => $callbacks) {

			foreach ($callbacks as $id => $callable) {

				$fields[$id] = array(
					'type'    => 'html',
					'classes' => 'wu--mt-2',
					'content' => function() use ($callable) {

						call_user_func($callable['function'], new \WU_Plan($this->get_object()));

					},
				);

			} // end foreach;

		} // end foreach;

		$this->add_fields_widget('legacy-options', array(
			'title'                 => __('Legacy Options', 'wp-ultimo'),
			'position'              => 'normal',
			'fields'                => $fields,
			'classes'               => 'wu-legacy-options-panel',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'style' => 'margin-top: -5px;',
			),
		));

	} // end handle_legacy_options;

	/**
	 * Returns the list of sections and its fields for the product page.
	 *
	 * Can be filtered via 'wu_product_options_sections'.
	 *
	 * @see inc/managers/class-limitation-manager.php
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function get_product_option_sections() {

		$sections = array(
			'general' => array(
				'title'  => __('General', 'wp-ultimo'),
				'desc'   => __('General product options such as product slug, type, etc.', 'wp-ultimo'),
				'icon'   => 'dashicons-wu-globe',
				'state'  => array(
					'slug'         => $this->get_object()->get_slug(),
					'product_type' => $this->get_object()->get_type(),
				),
				'fields' => array(
					'slug'          => array(
						'type'        => 'text',
						'title'       => __('Product Slug', 'wp-ultimo'),
						'placeholder' => __('e.g. premium', 'wp-ultimo'),
						'desc'        => __('This serves as a id to the product in a number of different contexts.', 'wp-ultimo'),
						'value'       => $this->get_object()->get_slug(),
						'tooltip'     => __('Lowercase alpha-numeric characters with dashes or underlines. No spaces allowed.', 'wp-ultimo'),
						'html_attr'   => array(
							'v-on:input'   => 'slug = $event.target.value.toLowerCase().replace(/[^a-z0-9-_]+/g, "")',
							'v-bind:value' => 'slug',
						),
					),
					// Fields for price
					'type'          => array(
						'type'        => 'select',
						'title'       => __('Product Type', 'wp-ultimo'),
						'placeholder' => __('Product Type', 'wp-ultimo'),
						'desc'        => __('Different product types have different options.', 'wp-ultimo'),
						'value'       => $this->get_object()->get_type(),
						'tooltip'     => '',
						'options'     => Product_Type::to_array(),
						'html_attr'   => array(
							'v-model' => 'product_type',
						),
					),
					'modules[customer_user_role][limit]' => array(
						'title'             => __('Customer Role', 'wp-ultimo'),
						'desc'              => __('Select the role WP Ultimo should use when adding the user to their newly created site.', 'wp-ultimo'),
						'type'              => 'select',
						'value'             => $this->get_object()->get_customer_role(),
						'default'           => 'administrator',
						'options'           => function() {
							return wu_get_roles_as_options(true);
						},
						'wrapper_html_attr' => array(
							'v-show'  => 'product_type === "plan"',
							'v-cloak' => 1,
						),
					),
				),
			),
		);

		$sections['ups-and-downs'] = array(
			'title'  => __('Up & Downgrades', 'wp-ultimo'),
			'desc'   => __('Settings related to upgrade and downgrade flows.', 'wp-ultimo'),
			'icon'   => 'dashicons-wu-shop',
			'v-show' => 'product_type === "plan"',
			'state'  => array(),
			'fields' => array(
				'group'            => array(
					'title'       => __('Plan Group', 'wp-ultimo'),
					'desc'        => __('Add related plans to the same group to have them show up as upgrade/downgrade paths.', 'wp-ultimo'),
					'placeholder' => __('Type and press enter to search and/or add.', 'wp-ultimo'),
					'type'        => 'select',
					'value'       => $this->get_object()->get_group(),
					'options'     => array_merge(array('' => __('Select Group', 'wp-ultimo')), wu_get_product_groups()),
					'html_attr'   => array(
						'data-selectize-categories' => 999,
						'data-max-items'            => 1,
					),
				),
				'list_order'       => array(
					'title'       => __('Product Order', 'wp-ultimo'),
					'desc'        => __('Plans are shown in the order determined by this parameter, from the lowest to the highest.', 'wp-ultimo'),
					'placeholder' => __('Type and press enter to search and/or add.', 'wp-ultimo'),
					'type'        => 'number',
					'value'       => $this->get_object()->get_list_order(),
				),
				'available_addons' => array(
					'type'        => 'model',
					'title'       => __('Offer Add-ons', 'wp-ultimo'),
					'placeholder' => __('Search for a package or service', 'wp-ultimo'),
					'desc'        => __('This products will be offered inside upgrade/downgrade forms as order bumps.', 'wp-ultimo'),
					'html_attr'   => array(
						'data-exclude'      => implode(',', array_keys(wu_get_plans_as_options())),
						'data-model'        => 'product',
						'data-value-field'  => 'id',
						'data-label-field'  => 'name',
						'data-search-field' => 'name',
						'data-max-items'    => 99,
						'data-selected'     => json_encode(wu_get_products(array(
							'id__in'     => $this->get_object()->get_available_addons(),
							'id__not_in' => array_keys(wu_get_plans_as_options()),
						))),
					),
				),
			),
		);

		$sections['price-variations'] = array(
			'title'  => __('Price Variations', 'wp-ultimo'),
			'desc'   => __('Discounts for longer membership commitments.', 'wp-ultimo'),
			'icon'   => 'dashicons-wu-price-tag',
			'state'  => array(
				'enable_price_variations' => !empty($this->get_object()->get_price_variations()),
				'price_variations'        => $this->get_object()->get_price_variations(),
			),
			'fields' => array(
				'enable_price_variations' => array(
					'type'      => 'toggle',
					'title'     => __('Enable Price Variations', 'wp-ultimo'),
					'desc'      => __('Price Variations are an easy way to offer discounted prices for longer subscription commitments.', 'wp-ultimo'),
					'value'     => false,
					'html_attr' => array(
						'v-model' => 'enable_price_variations',
					),
				),
				'price_variations'        => array(
					'type'              => 'group',
					// translators: 1 is the price, 2 is the duration and 3 the duration unit
					'desc'              => sprintf(__('A discounted price of %1$s will be used when memberships are created with the recurrency of %2$s %3$s(s) instead of the regular period.', 'wp-ultimo'), '{{ wu_format_money(price_variation.amount) }}', '{{ price_variation.duration }}', '{{ price_variation.duration_unit }}'),
					'tooltip'           => '',
					'wrapper_classes'   => 'wu-relative',
					'wrapper_html_attr' => array(
						'v-for'   => '(price_variation, index) in price_variations',
						'v-show'  => 'enable_price_variations',
						'v-cloak' => '1',
					),
					'fields'            => array(
						'price_variations_remove'        => array(
							'type'            => 'note',
							'desc'            => sprintf('<a title="%s" class="wu-no-underline wu-inline-block wu-text-gray-600 wu-mt-2 wu-mr-2" href="#" @click.prevent="() => price_variations.splice(index, 1)"><span class="dashicons-wu-squared-cross"></span></a>', __('Remove', 'wp-ultimo')),
							'wrapper_classes' => 'wu-absolute wu-top-0 wu-right-0',
						),
						'price_variations_duration'      => array(
							'type'            => 'number',
							'title'           => __('Duration', 'wp-ultimo'),
							'placeholder'     => '',
							'wrapper_classes' => 'wu-w-1/3',
							'min'             => 1,
							'html_attr'       => array(
								'v-model'     => 'price_variation.duration',
								'steps'       => 1,
								'v-bind:name' => '"price_variations[" + index + "][duration]"',
							),
						),
						'price_variations_duration_unit' => array(
							'type'            => 'select',
							'title'           => __('Period', 'wp-ultimo'),
							'placeholder'     => '',
							'wrapper_classes' => 'wu-w-1/3 wu-mx-2',
							'html_attr'       => array(
								'v-model'     => 'price_variation.duration_unit',
								'v-bind:name' => '"price_variations[" + index + "][duration_unit]"',
							),
							'options'         => array(
								'day'   => __('Days', 'wp-ultimo'),
								'week'  => __('Weeks', 'wp-ultimo'),
								'month' => __('Months', 'wp-ultimo'),
								'year'  => __('Years', 'wp-ultimo'),
							),
						),
						'price_variations_amount'        => array(
							'type'            => 'text',
							'title'           => __('New Price', 'wp-ultimo'),
							'placeholder'     => wu_format_currency('99'),
							'wrapper_classes' => 'wu-w-1/3',
							'money'           => true,
							'html_attr'       => array(
								'v-model'     => 'price_variation.amount',
								'v-bind:name' => '"price_variations[" + index + "][amount]"',
							),
						),
					),
				),
				'repeat'                  => array(
					'type'              => 'submit',
					'title'             => __('Add new Price Variation', 'wp-ultimo'),
					'classes'           => 'button wu-self-end',
					'wrapper_classes'   => 'wu-bg-whiten wu-items-end',
					'wrapper_html_attr' => array(
						'v-show'  => 'enable_price_variations',
						'v-cloak' => '1',
					),
					'html_attr'         => array(
						'v-on:click.prevent' => '() => price_variations.push({
							duration: 1,
							duration_unit: "month",
							amount: get_value("wu_product_pricing").amount,
						})',
					),
				),
			),
		);

		$sections['taxes'] = array(
			'title'  => __('Taxes', 'wp-ultimo'),
			'desc'   => __('Tax settings for your products.', 'wp-ultimo'),
			'icon'   => 'dashicons-wu-credit',
			'state'  => array(
				'taxable' => $this->get_object()->is_taxable(),
			),
			'fields' => array(
				'taxable'      => array(
					'type'      => 'toggle',
					'title'     => __('Is Taxable?', 'wp-ultimo'),
					'desc'      => __('Enable this if you plan to collect taxes for this product.', 'wp-ultimo'),
					'value'     => $this->get_object()->is_taxable(),
					'html_attr' => array(
						'v-model' => 'taxable',
					),
				),
				'tax_category' => array(
					'type'              => 'select',
					'title'             => __('Tax Category', 'wp-ultimo'),
					'desc'              => __('Select the product tax category.', 'wp-ultimo'),
					'value'             => $this->get_object()->get_tax_category(),
					'options'           => 'wu_get_tax_categories_as_options',
					'wrapper_html_attr' => array(
						'v-cloak' => '1',
						'v-show'  => 'require("taxable", true)',
					),
				),
			),
		);

		$sections['allowed_templates'] = array(
			'title'  => __('Site Templates', 'wp-ultimo'),
			'desc'   => __('Limit which site templates are available for this particular template.', 'wp-ultimo'),
			'icon'   => 'dashicons-wu-grid1 wu-align-text-bottom',
			'v-show' => "get_state_value('product_type', 'none') !== 'service'",
			'state'  => array(
				'allow_site_templates'         => $this->get_object()->get_limitations()->site_templates->is_enabled(),
				'site_template_selection_mode' => $this->get_object()->get_limitations()->site_templates->get_mode(),
				'pre_selected_template'        => $this->get_object()->get_limitations()->site_templates->get_pre_selected_site_template(),
			),
			'fields' => array(
				'modules[site_templates][enabled]' => array(
					'type'              => 'toggle',
					'title'             => __('Allow Site Templates', 'wp-ultimo'),
					'desc'              => __('Toggle this option on to allow this plan to use Site Templates. If this option is disabled, sign-ups on this plan will get a default WordPress site.', 'wp-ultimo'),
					'wrapper_html_attr' => array(
						'v-cloak' => '1',
					),
					'html_attr'         => array(
						'v-model' => 'allow_site_templates',
					),
				),
				'modules[site_templates][mode]'    => array(
					'type'              => 'select',
					'title'             => __('Site Template Selection Mode', 'wp-ultimo'),
					'placeholder'       => __('Site Template Selection Mode', 'wp-ultimo'),
					'desc'              => __('Select the type of limitation you want to apply.', 'wp-ultimo'),
					'tooltip'           => __('"Default" will follow the settings of the checkout form: if you have a template selection field in there, all the templates selected will show up. If no field is present, then a default WordPress site will be created. <br><br>"Assign Site Template" forces new accounts with this plan to use a particular template site (this option removes the template selection field from the signup, if one exists). <br><br>Finally, "Choose Available Site Templates", overrides the templates selected on the checkout form with the templates selected here, while also giving you the chance of pre-select a template to be used as default.', 'wp-ultimo'),
					'value'             => 'default',
					'options'           => array(
						'default'                    => __('Default', 'wp-ultimo'),
						'assign_template'            => __('Assign Site Template', 'wp-ultimo'),
						'choose_available_templates' => __('Choose Available Site Templates', 'wp-ultimo')
					),
					'html_attr'         => array(
						'v-model' => 'site_template_selection_mode',
					),
					'wrapper_html_attr' => array(
						'v-cloak' => '1',
						'v-show'  => 'allow_site_templates',
					),
				),
				'templates'                        => array(
					'type'              => 'html',
					'title'             => __('Site Templates', 'wp-ultimo'),
					'desc'              => esc_attr(sprintf('{{ site_template_selection_mode === "assign_template" ? "%s" : "%s" }}', __('Select the Site Template to assign.', 'wp-ultimo'), __('Customize the access level of each Site Template below.', 'wp-ultimo'))),
					'wrapper_html_attr' => array(
						'v-cloak' => '1',
						'v-show'  => "allow_site_templates && site_template_selection_mode !== 'default'",
					),
					'content'           => function() {
						return $this->get_site_template_selection_list($this->get_object());
					},
				),
			),
		);

		return apply_filters('wu_product_options_sections', $sections, $this->get_object());

	} // end get_product_option_sections;

	/**
	 * Returns the HTML markup for the plugin selector list.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Product $product The product being edited.
	 * @return string
	 */
	public function get_site_template_selection_list($product) {

		$all_templates = wu_get_site_templates();

		return wu_get_template_contents('limitations/site-template-selector', array(
			'templates' => $all_templates,
			'product'   => $product,
		));

	} // end get_site_template_selection_list;

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return $this->edit ? __('Edit Product', 'wp-ultimo') : __('Add new Product', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Edit Product', 'wp-ultimo');

	} // end get_menu_title;

	/**
	 * Returns the action links for that page.
	 *
	 * @since 1.8.2
	 * @return array
	 */
	public function action_links() {

		$actions = array();

		if ($this->get_object()->get_type() === 'plan' && $this->edit) {

			$shareable_link = $this->get_object()->get_shareable_link();

			$actions[] = array(
				'url'     => '#',
				'label'   => __('Click to copy Shareable Link', 'wp-ultimo'),
				'icon'    => 'wu-attachment',
				'classes' => 'wu-copy',
				'attrs'   => 'data-clipboard-text="' . esc_attr($shareable_link) . '"',
			);

		} // end if;

		return $actions;

	} // end action_links;

	/**
	 * Returns the labels to be used on the admin page.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_labels() {

		return array(
			'edit_label'          => __('Edit Product', 'wp-ultimo'),
			'add_new_label'       => __('Add new Product', 'wp-ultimo'),
			'updated_message'     => __('Product updated with success!', 'wp-ultimo'),
			'title_placeholder'   => __('Enter Product Name', 'wp-ultimo'),
			'title_description'   => __('This name will be used on pricing tables, invoices, and more.', 'wp-ultimo'),
			'save_button_label'   => __('Save Product', 'wp-ultimo'),
			'save_description'    => '',
			'delete_button_label' => __('Delete Product', 'wp-ultimo'),
			'delete_description'  => __('Be careful. This action is irreversible.', 'wp-ultimo'),
		);

	} // end get_labels;

	/**
	 * Filters the list table to return only relevant events.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Query args passed to the list table.
	 * @return array Modified query args.
	 */
	public function query_filter($args) {

		$extra_args = array(
			'object_type' => 'product',
			'object_id'   => absint($this->get_object()->get_id()),
		);

		return array_merge($args, $extra_args);

	} // end query_filter;

	/**
	 * Returns the object being edit at the moment.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Product
	 */
	public function get_object() {

		if ($this->object !== null) {

			return $this->object;

		} // end if;

		if (isset($_GET['id'])) {

			$query = new \WP_Ultimo\Database\Products\Product_Query;

			$item = $query->get_item_by('id', $_GET['id']);

			if (!$item) {

				wp_redirect(wu_network_admin_url('wp-ultimo-products'));

				exit;

			} // end if;

			$this->object = $item;

			return $this->object;

		} // end if;

		$this->object = new Product;

		return $this->object;

	} // end get_object;

	/**
	 * Products have titles.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_title() {

		return true;

	} // end has_title;

	/**
	 * Should implement the processes necessary to save the changes made to the object.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_save() {
		/*
		 * Set the recurring value to zero if the toggle is disabled.
		 */
		if (!wu_request('recurring')) {

			$_POST['recurring'] = false;

		} // end if;

		if (!wu_request('legacy_options')) {

			$_POST['legacy_options'] = false;

		} // end if;

		/*
		 * Set the setup fee value to zero if the toggle is disabled.
		 */
		if (!wu_request('has_setup_fee')) {

			$_POST['setup_fee'] = 0;

		} // end if;

		/*
		 * Disabled Trial
		 */
		if (!wu_request('has_trial')) {

			$_POST['trial_duration'] = 0;

		} // end if;

		/*
		 * Set the setup fee value to zero if the toggle is disabled.
		 */
		if (!wu_request('price_variations')) {

			$_POST['price_variations'] = array();

		} // end if;

		/*
		 * Set the taxable value to zero if the toggle is disabled.
		 */
		if (!wu_request('taxable')) {

			$_POST['taxable'] = 0;

		} // end if;

		parent::handle_save();

	} // end handle_save;

} // end class Product_Edit_Admin_Page;
