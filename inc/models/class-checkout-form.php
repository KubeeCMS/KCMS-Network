<?php
/**
 * The Checkout Form model for the Checkout_Form Mappings.
 *
 * @package WP_Ultimo
 * @subpackage Models
 * @since 2.0.0
 */

namespace WP_Ultimo\Models;

use \WP_Ultimo\Models\Base_Model;
use \WP_Ultimo\Dependencies\Arrch\Arrch as Array_Search;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Checkout Form model class. Implements the Base Model.
 *
 * @since 2.0.0
 */
class Checkout_Form extends Base_Model {

	/**
	 * The name of the checkout form.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $name;

	/**
	 * Slug of the checkout form.
	 *
	 * @since 2.0.0
	 * @var mixed
	 */
	protected $slug;

	/**
	 * Is this checkout form active?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $active = true;

	/**
	 * Payload of the event.
	 *
	 * @since 2.0.0
	 * @var mixed
	 */
	protected $settings;

	/**
	 * Custom CSS code.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $custom_css;

	/**
	 * Countries allowed on this checkout.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $allowed_countries;

	/**
	 * Thank you page id, if set.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $thank_you_page_id;

	/**
	 * Custom Snippets code.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $conversion_snippets;

	/**
	 * Set a template to use.
	 *
	 * @since 2.0.0
	 * @var string can be either 'blank', 'single-step' or 'multi-step'
	 */
	protected $template;

	/**
	 * Query Class to the static query methods.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = '\\WP_Ultimo\\Database\\Checkout_Forms\\Checkout_Form_Query';

	/**
	 * Set the validation rules for this particular model.
	 *
	 * To see how to setup rules, check the documentation of the
	 * validation library we are using: https://github.com/rakit/validation
	 *
	 * @since 2.0.0
	 * @link https://github.com/rakit/validation
	 * @return array
	 */
	public function validation_rules() {

		$id = $this->get_id();

		return array(
			'name'                => 'required',
			'slug'                => "required|unique:\WP_Ultimo\Models\Checkout_Form,slug,{$id}|min:3",
			'active'              => 'required|default:1',
			'custom_css'          => 'default:',
			'settings'            => 'checkout_steps',
			'allowed_countries'   => 'default:',
			'thank_you_page_id'   => 'integer',
			'conversion_snippets' => 'default:',
			'template'            => 'in:blank,single-step,multi-step',
		);

	} // end validation_rules;

	/**
	 * Get the object type associated with this event.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_slug() {

		return $this->slug;

	} // end get_slug;

	/**
	 * Set the checkout form slug
	 *
	 * @since 2.0.0
	 *
	 * @param string $slug The checkout form slug. It needs to be unique and preferably make it clear what it is about. E.g. my_checkout_form.
	 * @return void
	 */
	public function set_slug($slug) {

		$this->slug = $slug;

	} // end set_slug;

	/**
	 * Get the name of the checkout form.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_name() {

		return $this->name;

	} // end get_name;

	/**
	 * Set the name of the checkout form.
	 *
	 * @since 2.0.0
	 * @param string $name Your checkout form name, which is used as checkout form title as well.
	 * @return void
	 */
	public function set_name($name) {

		$this->name = $name;

	} // end set_name;

	/**
	 * Get is this checkout form active?
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_active() {

		return (bool) $this->active;

	} // end is_active;

	/**
	 * Set is this checkout form active?
	 *
	 * @since 2.0.0
	 * @param boolean $active Set this checkout form as active (true), which means available to be used, or inactive (false).
	 * @return void
	 */
	public function set_active($active) {

		$this->active = (bool) $active;

	} // end set_active;

	/**
	 * Get custom CSS code.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_custom_css() {

		return $this->custom_css;

	} // end get_custom_css;

	/**
	 * Set custom CSS code.
	 *
	 * @since 2.0.0
	 * @param string $custom_css Custom CSS code for the checkout form.
	 * @return void
	 */
	public function set_custom_css($custom_css) {

		$this->custom_css = $custom_css;

	} // end set_custom_css;

	/**
	 * Get settings of the event.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_settings() {

		if (empty($this->settings)) {

			return array();

		} // end if;

		if (is_string($this->settings)) {

			$this->settings = maybe_unserialize($this->settings);

		} // end if;

		return $this->settings;

	} // end get_settings;

	/**
	 * Set settings of the checkout form.
	 *
	 * @since 2.0.0
	 * @param object $settings The checkout form settings and configurations.
	 * @return void
	 */
	public function set_settings($settings) {

		if (is_string($settings)) {

			$settings = maybe_unserialize($settings);

		} // end if;

		$this->settings = $settings;

	} // end set_settings;

	/**
	 * Returns a specific step by the step name.
	 *
	 * @since 2.0.0
	 *
	 * @param string $step_name Name of the step. E.g. 'account'.
	 * @return array|false
	 */
	public function get_step($step_name) {

		$settings = $this->get_settings();

		$step_key = array_search($step_name, array_column($settings, 'id'), true);

		$step = $step_key !== false ? $settings[$step_key] : false;

		if ($step) {

			$step = wp_parse_args($step, array(
				'logged' => 'always',
				'fields' => array(),
			));

		} // end if;

		return $step;

	} // end get_step;

	/**
	 * Returns a specific field by the step name and field name.
	 *
	 * @since 2.0.0
	 *
	 * @param string $step_name Name of the step. E.g. 'account'.
	 * @param string $field_name Name of the field. E.g. 'username'.
	 * @return array|false
	 */
	public function get_field($step_name, $field_name) {

		$step = $this->get_step($step_name);

		if (!is_array($step)) {

			return false;

		} // end if;

		$field_key = array_search($field_name, array_column($step['fields'], 'id'), true);

		return $field_key !== false ? $step['fields'][$field_key] : false;

	} // end get_field;

	/**
	 * Returns all the fields from all steps.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_all_fields() {

		$settings = $this->get_settings();

		if (!is_array($settings)) {

			return array();

		} // end if;

		$fields = array_column($settings, 'fields');

		if (empty($fields)) {

			return array();

		} // end if;

		return call_user_func_array('array_merge', $fields);

	} // end get_all_fields;

	/**
	 * Get all fields of a given type.
	 *
	 * @since 2.0.11
	 *
	 * @param string|array $type The field type or types to search for.
	 * @return array
	 */
	public function get_all_fields_by_type($type) {

		$all_fields = $this->get_all_fields();

		$types = (array) $type;

		return Array_Search::find($all_fields, array(
			'where' => array(
				array('type', $types),
			),
		));

	} // end get_all_fields_by_type;

	/**
	 * Get fields that are meta-related.
	 *
	 * @since 2.0.0
	 *
	 * @param string $meta_type The meta type.
	 * @return array
	 */
	public function get_all_meta_fields($meta_type = 'customer_meta') {

		$all_fields = $this->get_all_fields();

		$types = apply_filters('wu_checkout_form_meta_fields_list', array('text', 'select', 'color', 'color_picker', 'textarea', 'checkbox'), $this);

		return Array_Search::find($all_fields, array(
			'where' => array(
				array('type', $types),
				array('save_as', $meta_type),
			),
		));

	} // end get_all_meta_fields;

	/**
	 * Returns the number of steps in this form.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_step_count() {

		$steps = $this->get_settings();

		return is_array($steps) ? count($steps) : 0;

	} // end get_step_count;

	/**
	 * Returns the number of fields on this form.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_field_count() {

		$fields = $this->get_all_fields();

		return is_array($fields) ? count($fields) : 0;

	} // end get_field_count;

	/**
	 * Returns the shortcode that needs to be placed to embed this form.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_shortcode() {

		return sprintf('[wu_checkout slug="%s"]', $this->get_slug());

	} // end get_shortcode;

	/**
	 * Sets an template for blank.
	 *
	 * @since 2.0.0
	 *
	 * @param string $template The type of the template.
	 * @return void
	 */
	public function use_template($template = 'single-step') {

		$fields = array();

		if ($template === 'multi-step') {

			$fields = $this->get_multi_step_template();

			$this->set_settings($fields);

		} elseif ($template === 'single-step') {

			$fields = $this->get_single_step_template();

		} // end if;

		$this->set_settings($fields);

	} // end use_template;

	/**
	 * Get the contents of the single step template.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	private function get_single_step_template() {

		$steps = array (
			array (
				'id'     => 'checkout',
				'name'   => __('Checkout', 'wp-ultimo'),
				'desc'   => '',
				'fields' => array (
					array (
						'step'                   => 'checkout',
						'name'                   => __('Plans', 'wp-ultimo'),
						'type'                   => 'pricing_table',
						'id'                     => 'pricing_table',
						'required'               => true,
						'pricing_table_products' => implode(',', wu_get_plans(array('fields' => 'ids'))),
						'pricing_table_template' => 'list',
					),
					array (
						'step'        => 'checkout',
						'name'        => __('Email', 'wp-ultimo'),
						'type'        => 'email',
						'id'          => 'email_address',
						'required'    => true,
						'placeholder' => '',
						'tooltip'     => '',
					),
					array (
						'step'          => 'checkout',
						'name'          => __('Username', 'wp-ultimo'),
						'type'          => 'username',
						'id'            => 'username',
						'required'      => true,
						'placeholder'   => '',
						'tooltip'       => '',
						'auto_generate' => false,
					),
					array (
						'step'                    => 'checkout',
						'name'                    => __('Password', 'wp-ultimo'),
						'type'                    => 'password',
						'id'                      => 'password',
						'required'                => true,
						'placeholder'             => '',
						'tooltip'                 => '',
						'password_strength_meter' => '1',
						'password_confirm_field'  => '1',
					),
					array (
						'step'          => 'checkout',
						'name'          => __('Site Title', 'wp-ultimo'),
						'type'          => 'site_title',
						'id'            => 'site_title',
						'required'      => true,
						'placeholder'   => '',
						'tooltip'       => '',
						'auto_generate' => false,
					),
					array (
						'step'                => 'checkout',
						'name'                => __('Site URL', 'wp-ultimo'),
						'type'                => 'site_url',
						'id'                  => 'site_url',
						'placeholder'         => '',
						'tooltip'             => '',
						'required'            => true,
						'auto_generate'       => false,
						'display_url_preview' => true,
					),
					array (
						'step'                   => 'checkout',
						'name'                   => __('Your Order', 'wp-ultimo'),
						'type'                   => 'order_summary',
						'id'                     => 'order_summary',
						'order_summary_template' => 'clean',
						'table_columns'          => 'simple',
					),
					array (
						'step' => 'checkout',
						'name' => __('Payment Method', 'wp-ultimo'),
						'type' => 'payment',
						'id'   => 'payment',
					),
					array (
						'step'            => 'checkout',
						'name'            => __('Billing Address', 'wp-ultimo'),
						'type'            => 'billing_address',
						'id'              => 'billing_address',
						'required'        => true,
						'zip_and_country' => '1',
					),
					array (
						'step' => 'checkout',
						'name' => __('Checkout', 'wp-ultimo'),
						'type' => 'submit_button',
						'id'   => 'checkout',
					),
				),
			),
		);

		return apply_filters('wu_checkout_form_single_step_template', $steps);

	} // end get_single_step_template;

	/**
	 * Get the contents of the multi step template.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	private function get_multi_step_template() {

		$steps = array (
			array(
				'id'     => 'checkout',
				'name'   => __('Checkout', 'wp-ultimo'),
				'desc'   => '',
				'fields' => array(
					array (
						'step'                   => 'checkout',
						'name'                   => 'Plans',
						'type'                   => 'pricing_table',
						'id'                     => 'pricing_table',
						'required'               => true,
						'pricing_table_products' => implode(',', wu_get_plans(array('fields' => 'ids'))),
						'pricing_table_template' => 'list',
					),
					array (
						'step' => 'checkout',
						'name' => __('Next Step', 'wp-ultimo'),
						'type' => 'submit_button',
						'id'   => 'next_step',
					),
				),
			),
			array (
				'id'     => 'site',
				'name'   => __('Site Info', 'wp-ultimo'),
				'desc'   => '',
				'fields' => array (
					array (
						'step'          => 'checkout',
						'name'          => __('Site Title', 'wp-ultimo'),
						'type'          => 'site_title',
						'id'            => 'site_title',
						'required'      => true,
						'placeholder'   => '',
						'tooltip'       => '',
						'auto_generate' => false,
					),
					array (
						'step'                => 'checkout',
						'name'                => __('Site URL', 'wp-ultimo'),
						'type'                => 'site_url',
						'id'                  => 'site_url',
						'required'            => true,
						'placeholder'         => '',
						'tooltip'             => '',
						'auto_generate'       => false,
						'display_url_preview' => true,
					),
					array (
						'step' => 'site',
						'name' => __('Next Step', 'wp-ultimo'),
						'type' => 'submit_button',
						'id'   => 'next_step_site',
					),
				),
			),
			array (
				'id'     => 'user',
				'name'   => __('User Info', 'wp-ultimo'),
				'logged' => 'guests_only',
				'desc'   => '',
				'fields' => array (
					array (
						'step'        => 'checkout',
						'name'        => __('Email', 'wp-ultimo'),
						'type'        => 'email',
						'id'          => 'email_address',
						'required'    => true,
						'placeholder' => '',
						'tooltip'     => '',
					),
					array (
						'step'          => 'checkout',
						'name'          => __('Username', 'wp-ultimo'),
						'type'          => 'username',
						'id'            => 'username',
						'required'      => true,
						'placeholder'   => '',
						'tooltip'       => '',
						'auto_generate' => false,
					),
					array (
						'step'                    => 'checkout',
						'name'                    => __('Password', 'wp-ultimo'),
						'type'                    => 'password',
						'id'                      => 'password',
						'required'                => true,
						'placeholder'             => '',
						'tooltip'                 => '',
						'password_strength_meter' => '1',
						'password_confirm_field'  => '1',
					),
					array (
						'step' => 'user',
						'name' => __('Next Step', 'wp-ultimo'),
						'type' => 'submit_button',
						'id'   => 'next_step_user',
					),
				),
			),
			array (
				'id'     => 'payment',
				'name'   => __('Payment', 'wp-ultimo'),
				'desc'   => '',
				'fields' => array (
					array (
						'step'                   => 'checkout',
						'name'                   => __('Your Order', 'wp-ultimo'),
						'type'                   => 'order_summary',
						'id'                     => 'order_summary',
						'order_summary_template' => 'clean',
						'table_columns'          => 'simple',
					),
					array (
						'step' => 'checkout',
						'name' => __('Payment Method', 'wp-ultimo'),
						'type' => 'payment',
						'id'   => 'payment',
					),
					array (
						'step'            => 'checkout',
						'name'            => __('Billing Address', 'wp-ultimo'),
						'type'            => 'billing_address',
						'id'              => 'billing_address',
						'required'        => true,
						'zip_and_country' => '1',
					),
					array (
						'step' => 'checkout',
						'name' => __('Checkout', 'wp-ultimo'),
						'type' => 'submit_button',
						'id'   => 'checkout',
					),
				),
			),
		);

		return apply_filters('wu_checkout_form_multi_step_template', $steps);

	} // end get_multi_step_template;

	/**
	 * Converts the steps from classic WP Ultimo 1.X to the 2.0 format.
	 *
	 * @since 2.0.0
	 *
	 * @param array $steps The steps to convert.
	 * @param array $old_settings The old settings.
	 * @return array
	 */
	public static function convert_steps_to_v2($steps, $old_settings = array()) {

		$exclude_steps = array(
			'begin-signup',
			'create-account',
		);

		$old_template_list = wu_get_isset($old_settings, 'templates', array());

		if (empty($old_template_list)) {

			$exclude_steps[] = 'template';

		} // end if;

		$new_format = array();

		foreach ($steps as $step_id => $step) {

			if (in_array($step_id, $exclude_steps, true)) {

				continue;

			} // end if;

			/**
			 * Deal with special cases.
			 */
			if ($step_id === 'plan') {

				$products_list = wu_get_plans(array(
					'fields' => 'ids',
				));

				/*
				 * Calculate the period selector
				 */
				$available_periods = array();

				$period_options = array(
					'enable_price_1'  => array(
						'duration'      => '1',
						'duration_unit' => 'month',
						'label'         => __('Monthly', 'wp-ultimo'),
					),
					'enable_price_3'  => array(
						'duration'      => '3',
						'duration_unit' => 'month',
						'label'         => __('Quarterly', 'wp-ultimo'),
					),
					'enable_price_12' => array(
						'duration'      => '1',
						'duration_unit' => 'year',
						'label'         => __('Yearly', 'wp-ultimo'),
					),
				);

				foreach ($period_options as $period_option_key => $period_option) {

					$has_period_option = wu_get_isset($old_settings, $period_option_key, true);

					if ($has_period_option) {

						$available_periods[] = $period_option;

					} // end if;

				} // end foreach;

				$step['fields'] = array();

				if ($available_periods && count($available_periods) > 1) {

					$step['fields']['period_selection'] = array(
						'type'                      => 'period_selection',
						'id'                        => 'period_selection',
						'period_selection_template' => 'legacy',
						'period_options_header'     => '',
						'period_options'            => $available_periods,
					);

				} // end if;

				$step['fields']['pricing_table'] = array(
					'name'                   => __('Pricing Tables', 'wp-ultimo'),
					'id'                     => 'pricing_table',
					'type'                   => 'pricing_table',
					'pricing_table_template' => 'legacy',
					'pricing_table_products' => implode(',', $products_list),
				);

			} // end if;

			/**
			 * Deal with special cases.
			 */
			if ($step_id === 'template' && wu_get_isset($old_settings, 'allow_template', true)) {

				$templates = array();

				foreach (wu_get_site_templates() as $site) {

					$templates[] = $site->get_id();

				} // end foreach;

				$old_template_list = is_array($old_template_list) ? $old_template_list : array();

				$template_list = array_flip($old_template_list);

				$template_list = !empty($template_list) ? $template_list : $templates;

				$step['fields'] = array(
					'template_selection' => array(
						'name'                        => __('Template Selection', 'wp-ultimo'),
						'id'                          => 'template_selection',
						'type'                        => 'template_selection',
						'template_selection_template' => 'legacy',
						'template_selection_sites'    => implode(',', $template_list),
					),
				);

			} // end if;

			/**
			 * Remove unnecessary callbacks
			 */
			unset($step['handler']);
			unset($step['view']);
			unset($step['hidden']);

			$new_fields = array();

			$step['id'] = $step_id;

			$fields_to_skip = array(
				'user_pass_conf',
				'url_preview',
				'site_url' // Despite the name, this is the Honeypot field.
			);

			foreach ($step['fields'] as $field_id => $field) {

				if (in_array($field_id, $fields_to_skip, true)) {

					unset($step['fields'][$field_id]);

					continue;

				} // end if;

				/**
				 * Format specific fields.
				 */
				switch ($field_id) {

					case 'user_name':
						$field['type'] = 'username';
						$field['id']   = 'username';
						break;

					case 'user_pass':
						$field['type']                    = 'password';
						$field['id']                      = 'password';
						$field['password_strength_meter'] = false;
						$field['password_confirm_field']  = true;
						$field['password_confirm_label']  = wu_get_isset($step['fields']['user_pass_conf'], 'name', __('Confirm Password', 'wp-ultimo'));
						break;

					case 'user_email':
						$field['display_notices'] = false;
						$field['id']              = 'email_address';
						break;

					case 'blog_title':
						$field['type']          = 'site_title';
						$field['id']            = 'site_title';
						$field['auto_generate'] = false;
						break;

					case 'blogname':
						$field['type']                      = 'site_url';
						$field['id']                        = 'site_url';
						$field['url_preview_template']      = 'legacy/signup/steps/step-domain-url-preview';
						$field['auto_generate']             = false;
						$field['display_url_preview']       = true;
						$field['required']                  = true;
						$field['display_field_attachments'] = false;
						$field['enable_domain_selection']   = wu_get_isset($old_settings, 'enable_multiple_domains', false);
						$field['available_domains']         = wu_get_isset($old_settings, 'domain_options', array());
						break;

					case 'submit':
						$field['type'] = 'submit_button';
						$field['id']   = 'submit_button';

						if ($step_id === 'account') {

							$field['name'] = __('Continue to the Next Step', 'wp-ultimo');

						} // end if;

						break;

				} // end switch;

				$field['id'] = $field_id;

				$new_fields[] = $field;

			} // end foreach;

			$step['fields'] = $new_fields;

			$new_format[] = $step;

		} // end foreach;

		/**
		 * Add Checkout step
		 */
		$new_format[] = array(
			'id'     => 'payment',
			'name'   => __('Checkout', 'wp-ultimo'),
			'fields' => array(
				array(
					'name'                   => __('Order Summary', 'wp-ultimo'),
					'type'                   => 'order_summary',
					'id'                     => 'order_summary',
					'order_summary_template' => 'clean',
					'table_columns'          => 'simple',
				),
				array(
					'name'            => __('Billing Address', 'wp-ultimo'),
					'type'            => 'billing_address',
					'id'              => 'billing_address',
					'zip_and_country' => true,
				),
				array(
					'type'             => 'discount_code',
					'id'               => 'discount_code',
					'name'             => __('Coupon Code', 'wp-ultimo'),
					'tooltip'          => __('Coupon Code', 'wp-ultimo'),
					'display_checkbox' => true,
				),
				array(
					'name' => __('Payment Methods', 'wp-ultimo'),
					'type' => 'payment',
					'id'   => 'payment',
				),
				array(
					'type' => 'submit_button',
					'id'   => 'submit_button',
					'name' => __('Pay & Create Account', 'wp-ultimo'),
				),
			),
		);

		return $new_format;

	} // end convert_steps_to_v2;

	/**
	 * Checks if this signup has limitations on countries.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_country_lock() {

		return !empty($this->get_allowed_countries());

	} // end has_country_lock;

	/**
	 * Get countries allowed on this checkout.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_allowed_countries() {

		return maybe_unserialize($this->allowed_countries);

	} // end get_allowed_countries;

	/**
	 * Set countries allowed on this checkout.
	 *
	 * @since 2.0.0
	 * @param string $allowed_countries The allowed countries that can access this checkout.
	 * @return void
	 */
	public function set_allowed_countries($allowed_countries) {

		$this->allowed_countries = $allowed_countries;

	} // end set_allowed_countries;

	/**
	 * Checks if this checkout form has a custom thank you page.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_thank_you_page() {

		$page_id = $this->get_thank_you_page_id();

		return $page_id && get_post($page_id);

	} // end has_thank_you_page;

	/**
	 * Get custom thank you page, if set.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_thank_you_page_id() {

		if ($this->thank_you_page_id === null) {

			$this->thank_you_page_id = $this->get_meta('wu_thank_you_page_id', '');

		} // end if;

		return $this->thank_you_page_id;

	} // end get_thank_you_page_id;

	/**
	 * Set custom thank you page, if set.
	 *
	 * @since 2.0.0
	 * @param int $thank_you_page_id The thank you page ID. This page is shown after a successful purchase.
	 * @return void
	 */
	public function set_thank_you_page_id($thank_you_page_id) {

		$this->meta['wu_thank_you_page_id'] = $thank_you_page_id;

		$this->thank_you_page_id = $thank_you_page_id;

	} // end set_thank_you_page_id;

	/**
	 * Get Snippets to run on thank you page.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_conversion_snippets() {

		if ($this->conversion_snippets === null) {

			$this->conversion_snippets = $this->get_meta('wu_conversion_snippets', '');

		} // end if;

		return $this->conversion_snippets;

	} // end get_conversion_snippets;

	/**
	 * Set snippets to run on thank you page.
	 *
	 * @since 2.0.0
	 * @param string $conversion_snippets Snippets to run on thank you page.
	 * @return void
	 */
	public function set_conversion_snippets($conversion_snippets) {

		$this->meta['wu_conversion_snippets'] = $conversion_snippets;

		$this->conversion_snippets = $conversion_snippets;

	} // end set_conversion_snippets;

	/**
	 * Save (create or update) the model on the database.
	 *
	 * Overrides the save method to set the template.
	 * This is used on CLI creation.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function save() {

		$step_types = array(
			'multi-step',
			'single-step',
		);

		if ($this->template && in_array($this->template, $step_types, true)) {

			$this->use_template($this->template);

		} // end if;

		return parent::save();

	} // end save;

	/**
	 * Get can be either 'blank', 'single-step' or 'multi-step'.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_template() {

		return $this->template;

	} // end get_template;

	/**
	 * Set the template mode. THis is mostly used on CLI.
	 *
	 * @since 2.0.0
	 * @param string $template Template mode. Can be either 'blank', 'single-step' or 'multi-step'.
	 * @options blank,single-step,multi-step
	 * @return void
	 */
	public function set_template($template) {

		$this->template = $template;

	} // end set_template;

	/**
	 * Custom fields for back-end upgrade/downgrades and such.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public static function membership_change_form_fields() {

		$membership = wu_get_membership_by_hash(wu_request('membership'));

		if (!$membership && wu_request('membership_id')) {

			$membership = wu_get_membership(wu_request('membership_id'));

		} // end if;

		if (!$membership && current_user_can('manage_options')) {

			$membership = wu_mock_membership();

		} // end if;

		if (!$membership) {

			return array();

		} // end if;

		$fields = array();

		/*
		 * Adds the addons
		 * selected on the plan.
		 */
		$plan = $membership->get_plan();

		if ($plan) {
			/*
			 * Get the group
			 */
			$group = $plan->get_group();

			$search_arguments = array(
				'fields' => 'ids',
			);

			if ($group) {

				$search_arguments['product_group'] = $group;

			} else {
				/*
				 * If there isn't a group available
				 * limit the return to 3.
				 */
				$search_arguments['number'] = 3;

			} // end if;

			$fields[] = array(
				'step'                   => 'checkout',
				'name'                   => __('Plans', 'wp-ultimo'),
				'type'                   => 'pricing_table',
				'id'                     => 'pricing_table',
				'required'               => true,
				'pricing_table_products' => implode(',', wu_get_plans($search_arguments)),
				'pricing_table_template' => 'list',
			);

			$available_addons = (array) $plan->get_available_addons();

			foreach ($available_addons as $addon_id) {

				if (!$addon_id) {

					continue;

				} // end if;

				$addon = wu_get_product($addon_id);

				if (!$addon) {

					continue;

				} // end if;

				$fields[] = array(
					'id'                    => "order_bump_{$addon_id}",
					'type'                  => 'order_bump',
					'name'                  => $addon->get_name(),
					'product'               => $addon_id,
					'display_product_image' => true,
				);

			} // end foreach;

		} // end if;

		$end_fields = array(
			array (
				'step'                   => 'checkout',
				'name'                   => __('Your Order', 'wp-ultimo'),
				'type'                   => 'order_summary',
				'id'                     => 'order_summary',
				'order_summary_template' => 'clean',
				'table_columns'          => 'simple',
			),
			array (
				'step' => 'checkout',
				'name' => __('Payment Method', 'wp-ultimo'),
				'type' => 'payment',
				'id'   => 'payment',
			),
			array (
				'step'  => 'checkout',
				'name'  => __('Complete Checkout', 'wp-ultimo'),
				'type'  => 'submit_button',
				'id'    => 'checkout',
				'order' => 0,
			),
		);

		$fields = array_merge($fields, $end_fields);

		$steps = array (
			array (
				'id'     => 'checkout',
				'name'   => __('Checkout', 'wp-ultimo'),
				'desc'   => '',
				'fields' => $fields,
			),
		);

		return apply_filters('wu_checkout_form_membership_change_form_fields', $steps);

	} // end membership_change_form_fields;

	/**
	 * Custom fields to add to the add new site screen.
	 *
	 * @since 2.0.11
	 * @return array
	 */
	public static function add_new_site_form_fields() {

		$membership = wu_get_current_site()->get_membership();

		if (!$membership) {

			return array();

		} // end if;

		/*
		 * Adds the addons
		 * selected on the plan.
		 */
		$plan = $membership->get_plan();

		$steps = array();

		if ($membership->get_limitations()->site_templates->is_enabled()) {

			$template_selection_fields = array(
				array(
					'step'                        => 'template',
					'name'                        => __('Template Selection', 'wp-ultimo'),
					'type'                        => 'template_selection',
					'id'                          => 'template_selection',
					'cols'                        => 4,
					'template_selection_template' => 'clean',
					'order'                       => 0,
				),
				array(
					'step'        => 'template',
					'type'        => 'hidden',
					'id'          => 'create-new-site',
					'fixed_value' => wp_create_nonce('create-new-site'),
				),
			);

			$steps[] = array(
				'id'     => 'template',
				'name'   => __('Template Selection', 'wp-ultimo'),
				'desc'   => '',
				'fields' => $template_selection_fields,
			);

		} // end if;

		$final_fields = array(
			array(
				'step'     => 'create',
				'type'     => 'products',
				'id'       => 'products',
				'products' => $plan->get_id(),
			),
			array(
				'step'        => 'create',
				'type'        => 'hidden',
				'id'          => 'membership_id',
				'fixed_value' => $membership->get_id(),
			),
			array(
				'step'        => 'create',
				'type'        => 'hidden',
				'id'          => 'create-new-site',
				'fixed_value' => wp_create_nonce('create-new-site'),
			),
		);

		$final_fields[] = array(
			'step'        => 'create',
			'id'          => 'site_title',
			'name'        => __('Site Title', 'wp-ultimo'),
			'tooltip'     => '',
			'placeholder' => '',
			'type'        => 'site_title',
		);

		$domain_options = wu_get_available_domain_options();

		$final_fields[] = array(
			'step'                      => 'create',
			'id'                        => 'site_url',
			'name'                      => __('Site URL', 'wp-ultimo'),
			'tooltip'                   => '',
			'placeholder'               => '',
			'display_field_attachments' => false,
			'type'                      => 'site_url',
			'enable_domain_selection'   => !empty($domain_options),
			'available_domains'         => implode(PHP_EOL, $domain_options),
		);

		$final_fields[] = array(
			'step'  => 'create',
			'name'  => __('Create Site', 'wp-ultimo'),
			'type'  => 'submit_button',
			'id'    => 'checkout',
			'order' => 0,
		);

		$steps[] = array(
			'id'      => 'create',
			'name'    => __('Create Site', 'wp-ultimo'),
			'desc'    => '',
			'classes' => 'wu-max-w-sm',
			'fields'  => $final_fields,
		);

		return apply_filters('wu_checkout_form_add_new_site_form_fields', $steps);

	} // end add_new_site_form_fields;

} // end class Checkout_Form;
