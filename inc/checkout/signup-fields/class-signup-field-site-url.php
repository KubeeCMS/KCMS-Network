<?php
/**
 * Creates a cart with the parameters of the purchase being placed.
 *
 * @package WP_Ultimo
 * @subpackage Order
 * @since 2.0.0
 */

namespace WP_Ultimo\Checkout\Signup_Fields;

use \WP_Ultimo\Checkout\Signup_Fields\Base_Signup_Field;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Creates an cart with the parameters of the purchase being placed.
 *
 * @package WP_Ultimo
 * @subpackage Checkout
 * @since 2.0.0
 */
class Signup_Field_Site_Url extends Base_Signup_Field {

	/**
	 * Returns the type of the field.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_type() {

		return 'site_url';

	} // end get_type;

	/**
	 * Returns if this field should be present on the checkout flow or not.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_required() {

		return true;

	} // end is_required;

	/**
	 * Defines if this field/element is related to site creation or not.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_site_field() {

		return true;

	} // end is_site_field;

	/**
	 * Requires the title of the field/element type.
	 *
	 * This is used on the Field/Element selection screen.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_title() {

		return __('Site URL', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the description of the field/element.
	 *
	 * This is used as the title attribute of the selector.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return __('Site URL Description', 'wp-ultimo');

	} // end get_description;

	/**
	 * Returns the icon to be used on the selector.
	 *
	 * Can be either a dashicon class or a wu-dashicon class.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_icon() {

		return 'dashicons-wu-globe';

	} // end get_icon;

	/**
	 * Returns the default values for the field-elements.
	 *
	 * This is passed through a wp_parse_args before we send the values
	 * to the method that returns the actual fields for the checkout form.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function defaults() {

		global $current_site;

		return array(
			'display_url_preview'     => true,
			'enable_domain_selection' => false,
			'available_domains'       => $current_site->domain . PHP_EOL,
		);

	} // end defaults;

	/**
	 * List of keys of the default fields we want to display on the builder.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function default_fields() {

		return array(
			'name',
			'placeholder',
			'tooltip',
		);

	} // end default_fields;

	/**
	 * If you want to force a particular attribute to a value, declare it here.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function force_attributes() {

		return array(
			'id'       => 'site_url',
			'required' => true,
		);

	}  // end force_attributes;

	/**
	 * Returns the list of additional fields specific to this type.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_fields() {

		global $current_site;

		return array(
			'auto_generate'           => array(
				'type'      => 'toggle',
				'title'     => __('Auto-generate?', 'wp-ultimo'),
				'desc'      => __('Check this option to auto-generate this field based on the username of the customer.', 'wp-ultimo'),
				'tooltip'   => '',
				'value'     => 0,
				'html_attr' => array(
					'v-model' => 'auto_generate',
				),
			),
			'display_url_preview'     => array(
				'type'      => 'toggle',
				'title'     => __('Display URL preview block?', 'wp-ultimo'),
				'desc'      => __('Set as the primary domain.', 'wp-ultimo'),
				'tooltip'   => __('Setting this as the primary domain will remove any other domain mapping marked as the primary domain for this site.', 'wp-ultimo'),
				'value'     => 1,
				'html_attr' => array(
					'v-model' => 'display_url_preview',
				),
			),
			'url_preview_template'    => array(
				'type'              => 'select',
				'title'             => __('Pricing Table Template', 'wp-ultimo'),
				'placeholder'       => __('Select your Template', 'wp-ultimo'),
				'options'           => array($this, 'get_url_preview_templates'),
				'wrapper_html_attr' => array(
					'v-show' => 'display_url_preview',
				),
			),
			'enable_domain_selection' => array(
				'type'      => 'toggle',
				'title'     => __('Enable Domain Selection?', 'wp-ultimo'),
				'desc'      => __('Set as the primary domain.', 'wp-ultimo'),
				'tooltip'   => __('Setting this as the primary domain will remove any other domain mapping marked as the primary domain for this site.', 'wp-ultimo'),
				'value'     => 0,
				'html_attr' => array(
					'v-model' => 'enable_domain_selection',
					'rows'    => 5,
				),
			),
			'available_domains'       => array(
				'type'              => 'textarea',
				'title'             => __('Enable Domain Selection?', 'wp-ultimo'),
				'desc'              => __('Set as the primary domain.', 'wp-ultimo'),
				'tooltip'           => __('Setting this as the primary domain will remove any other domain mapping marked as the primary domain for this site.', 'wp-ultimo'),
				'value'             => $current_site->domain . PHP_EOL,
				'wrapper_html_attr' => array(
					'v-show' => 'enable_domain_selection',
				),
				'html_attr'         => array(
					'rows' => 4,
				),
			),
		);

	} // end get_fields;

	/**
	 * Returns the list of available pricing table templates.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_url_preview_templates() {

		$templates = array(
			'checkout/partials/pricing-table-list'        => __('URL Preview', 'wp-ultimo'),
			'legacy/signup/steps/step-domain-url-preview' => __('Legacy Template', 'wp-ultimo'),
		);

		return apply_filters('wu_get_pricing_table_templates', $templates);

	} // end get_url_preview_templates;

	/**
	 * Returns the field/element actual field array to be used on the checkout form.
	 *
	 * @since 2.0.0
	 *
	 * @param array $attributes Attributes saved on the editor form.
	 * @return array An array of fields, not the field itself.
	 */
	public function to_fields_array($attributes) {
		/*
		 * If we should auto-generate, add as hidden.
		 */
		if ($attributes['auto_generate']) {

			return array(
				'site_url' => array(
					'type' => 'hidden',
					'id'   => 'site_url',
				),
			);

		} // end if;

		$checkout_fields = array();

		$checkout_fields['site_url'] = array(
			'type'        => 'text',
			'id'          => 'site_url',
			'name'        => $attributes['name'],
			'placeholder' => $attributes['placeholder'],
			'tooltip'     => $attributes['tooltip'],
			'required'    => true,
			'html_attr'   => array(
				'autocomplete' => 'off',
			),
		);

		if ($attributes['available_domains'] && $attributes['enable_domain_selection']) {

			$options = $this->get_domain_options($attributes['available_domains']);

			$checkout_fields['site_domain'] = array(
				'name'     => __('Domain', 'wp-ultimo'),
				'options'  => $options,
				'order'    => 25,
				'id'       => 'site_domain',
				'type'     => 'select',
				'required' => true,
				'classes'  => 'input',
			);

		} // end if;

		if ($attributes['display_url_preview']) {

			wp_enqueue_script('wu-url-preview', wu_get_asset('url-preview.js', 'js'), false, wu_get_version());

			$content = wu_get_template_contents($attributes['url_preview_template']);

			$checkout_fields['site_url_preview'] = array(
				'type' => 'note',
				'desc' => $content,
			);

		} // end if;

		return $checkout_fields;

	} // end to_fields_array;

	/**
	 * Get the domain options.
	 *
	 * @since 2.0.0
	 *
	 * @param string $domain_options The list of domains, in string format.
	 * @return array
	 */
	protected function get_domain_options($domain_options) {

		$domains = array_filter(explode(PHP_EOL, $domain_options));

		$domains = array_map(function($item) {

			return trim($item);

		}, $domains);

		return array_combine($domains, $domains);

	} // end get_domain_options;

} // end class Signup_Field_Site_Url;
