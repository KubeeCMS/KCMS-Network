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

		return __('Adds a Site URL field. This is used to set the URL of the site being created.', 'wp-ultimo');

	} // end get_description;

	/**
	 * Returns the tooltip of the field/element.
	 *
	 * This is used as the tooltip attribute of the selector.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_tooltip() {

		return __('Adds a Site URL field. This is used to set the URL of the site being created.', 'wp-ultimo');

	} // end get_tooltip;

	/**
	 * Returns the icon to be used on the selector.
	 *
	 * Can be either a dashicon class or a wu-dashicon class.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_icon() {

		return 'dashicons-wu-globe1';

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
			'auto_generate_site_url'    => false,
			'display_url_preview'       => true,
			'enable_domain_selection'   => false,
			'display_field_attachments' => true,
			'available_domains'         => $current_site->domain . PHP_EOL,
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
			'auto_generate_site_url'    => array(
				'order'     => 12,
				'type'      => 'toggle',
				'title'     => __('Auto-generate', 'wp-ultimo'),
				'desc'      => __('Check this option to auto-generate this field based on the username of the customer.', 'wp-ultimo'),
				'tooltip'   => '',
				'value'     => 0,
				'html_attr' => array(
					'v-model' => 'auto_generate_site_url',
				),
			),
			'display_field_attachments' => array(
				'order'             => 18,
				'type'              => 'toggle',
				'title'             => __('Display URL field attachments', 'wp-ultimo'),
				'desc'              => __('Adds the prefix and suffix blocks to the URL field.', 'wp-ultimo'),
				'tooltip'           => '',
				'value'             => 1,
				'tab'               => 'content',
				'wrapper_html_attr' => array(
					'v-show' => '!auto_generate_site_url',
				),
				'html_attr'         => array(
					'v-model' => 'display_field_attachments',
				),
			),
			'display_url_preview'       => array(
				'order'             => 19,
				'type'              => 'toggle',
				'title'             => __('Display URL preview block', 'wp-ultimo'),
				'desc'              => __('Adds a preview block that shows the final URL.', 'wp-ultimo'),
				'tooltip'           => '',
				'value'             => 1,
				'tab'               => 'content',
				'wrapper_html_attr' => array(
					'v-show' => '!auto_generate_site_url',
				),
				'html_attr'         => array(
					'v-model' => 'display_url_preview',
				),
			),
			'enable_domain_selection'   => array(
				'order'             => 20,
				'type'              => 'toggle',
				'title'             => __('Enable Domain Selection', 'wp-ultimo'),
				'desc'              => __('Offer different domain options to your customers to choose from.', 'wp-ultimo'),
				'tooltip'           => '',
				'value'             => 0,
				'tab'               => 'content',
				'wrapper_html_attr' => array(
					'v-show' => '!auto_generate_site_url',
				),
				'html_attr'         => array(
					'v-model' => 'enable_domain_selection',
					'rows'    => 5,
				),
			),
			'available_domains'         => array(
				'order'             => 30,
				'type'              => 'textarea',
				'title'             => __('Available Domains', 'wp-ultimo'),
				'desc'              => '',
				'desc'              => __('Enter one domain option per line.', 'wp-ultimo'),
				'value'             => $current_site->domain . PHP_EOL,
				'tab'               => 'content',
				'wrapper_html_attr' => array(
					'v-show' => '!auto_generate_site_url && enable_domain_selection',
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
			'legacy/signup/steps/step-domain-url-preview' => __('New URL Preview', 'wp-ultimo'),
			// 'legacy/signup/steps/step-domain-url-preview' => __('Legacy Template', 'wp-ultimo'),
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
		if ($attributes['auto_generate_site_url']) {

			return array(
				'auto_generate_site_url' => array(
					'type'  => 'hidden',
					'id'    => 'auto_generate_site_url',
					'value' => 'username',
				),
				'site_url'               => array(
					'type'  => 'hidden',
					'id'    => 'site_url',
					'value' => uniqid(),
				),
			);

		} // end if;

		$checkout_fields = array();

		$checkout_fields['site_url'] = array(
			'type'            => 'text',
			'id'              => 'site_url',
			'wrapper_classes' => 'wu-flex-grow wu-my-0',
			'classes'         => 'disabled sm:wu-my-0',
			'name'            => $attributes['name'],
			'placeholder'     => $attributes['placeholder'],
			'tooltip'         => $attributes['tooltip'],
			'required'        => true,
			'wrapper_classes' => wu_get_isset($attributes, 'wrapper_element_classes', 'wu-my-1'),
			'classes'         => wu_get_isset($attributes, 'element_classes', ''),
			'html_attr'       => array(
				'autocomplete' => 'off',
				'v-on:input'   => 'site_url = $event.target.value.toLowerCase().replace(/[^a-z0-9-]+/g, "")',
				'v-bind:value' => 'site_url',
			),
		);

		if ($attributes['display_field_attachments']) {

			$checkout_fields['site_url']['classes'] .= ' xs:wu-rounded-none';

			$checkout_fields['site_url']['prefix'] = ' ';

			$checkout_fields['site_url']['prefix_html_attr'] = array(
				'class'   => 'wu-flex wu-items-center wu-px-3 wu-mt-1 sm:wu-mb-1 wu-border-box wu-font-mono wu-justify-center sm:wu-border-r-0',
				'style'   => 'background-color: rgba(0, 0, 0, 0.008); border: 1px solid #eee; margin-right: -1px; font-size: 90%;',
				'v-html'  => 'is_subdomain ? "https://" : "https://" + site_domain + "/"',
				'v-cloak' => 1,
			);

			$checkout_fields['site_url']['suffix'] = ' ';

			$checkout_fields['site_url']['suffix_html_attr'] = array(
				'class'   => 'wu-flex wu-items-center wu-px-3 sm:wu-mt-1 wu-mb-1 wu-border-box wu-font-mono wu-justify-center sm:wu-border-l-0',
				'style'   => 'background-color: rgba(0, 0, 0, 0.008); border: 1px solid #eee; margin-left: -1px; font-size: 90%;',
				'v-html'  => '"." + site_domain',
				'v-cloak' => 1,
				'v-show'  => 'is_subdomain',
			);

		} // end if;

		if ($attributes['available_domains'] && $attributes['enable_domain_selection']) {

			$options = $this->get_domain_options($attributes['available_domains']);

			$checkout_fields['site_domain'] = array(
				'name'              => __('Domain', 'wp-ultimo'),
				'options'           => $options,
				'wrapper_classes'   => wu_get_isset($attributes, 'wrapper_element_classes', ''),
				'classes'           => wu_get_isset($attributes, 'element_classes', ''),
				'order'             => 25,
				'required'          => true,
				'id'                => 'site_domain',
				'type'              => 'select',
				'classes'           => 'input',
				'html_attr'         => array(
					'v-model' => 'site_domain',
				),
				'wrapper_html_attr' => array(
					'style' => $this->calculate_style_attr(),
				),
			);

		} // end if;

		if ($attributes['display_url_preview']) {

			$content = wu_get_template_contents('legacy/signup/steps/step-domain-url-preview');

			$checkout_fields['site_url_preview'] = array(
				'type'              => 'note',
				'desc'              => $content,
				'wrapper_classes'   => wu_get_isset($attributes, 'wrapper_element_classes', ''),
				'classes'           => wu_get_isset($attributes, 'element_classes', ''),
				'wrapper_html_attr' => array(
					'style' => $this->calculate_style_attr(),
				),
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
