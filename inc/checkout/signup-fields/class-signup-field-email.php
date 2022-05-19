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
class Signup_Field_Email extends Base_Signup_Field {

	/**
	 * Returns the type of the field.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_type() {

		return 'email';

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
	 * Is this a user-related field?
	 *
	 * If this is set to true, this field will be hidden
	 * when the user is already logged in.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_user_field() {

		return false;

	} // end is_user_field;

	/**
	 * Requires the title of the field/element type.
	 *
	 * This is used on the Field/Element selection screen.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_title() {

		return __('Email', 'wp-ultimo');

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

		return __('Adds a email address field. This email address will be used to create the WordPress user.', 'wp-ultimo');

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

		return __('Adds a email address field. This email address will be used to create the WordPress user.', 'wp-ultimo');

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

		return 'dashicons-wu-at-sign';

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

		return array(
			'display_notices' => true,
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
			'id'       => 'email_address',
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

		return array(
			'display_notices' => array(
				'type'      => 'toggle',
				'title'     => __('Display Notices', 'wp-ultimo'),
				'desc'      => __('When the customer is already logged in, a box with the customer\'s username and a link to logout is displayed instead of the email field. Disable this option if you do not want that box to show up.', 'wp-ultimo'),
				'tooltip'   => '',
				'value'     => 1,
				'html_attr' => array(
					'v-model' => 'display_notices',
				),
			),
		);

	} // end get_fields;

	/**
	 * Returns the field/element actual field array to be used on the checkout form.
	 *
	 * @since 2.0.0
	 *
	 * @param array $attributes Attributes saved on the editor form.
	 * @return array An array of fields, not the field itself.
	 */
	public function to_fields_array($attributes) {

		$checkout_fields = array();

		if (is_user_logged_in()) {

			if ($attributes['display_notices']) {

				$checkout_fields['login_note'] = array(
					'type'              => 'note',
					'title'             => __('Not you?', 'wp-ultimo'),
					'desc'              => array($this, 'render_not_you_customer_message'),
					'wrapper_classes'   => wu_get_isset($attributes, 'wrapper_element_classes', ''),
					'wrapper_html_attr' => array(
						'style' => $this->calculate_style_attr(),
					),
				);

			} // end if;

		} else {

			if ($attributes['display_notices']) {

				$checkout_fields['login_note'] = array(
					'type'              => 'note',
					'title'             => __('Existing customer?', 'wp-ultimo'),
					'desc'              => array($this, 'render_existing_customer_message'),
					'wrapper_classes'   => wu_get_isset($attributes, 'wrapper_element_classes', ''),
					'wrapper_html_attr' => array(
						'style' => $this->calculate_style_attr(),
					),
				);

			} // end if;

			$checkout_fields['email_address'] = array(
				'type'              => 'text',
				'id'                => 'email_address',
				'name'              => $attributes['name'],
				'placeholder'       => $attributes['placeholder'],
				'tooltip'           => $attributes['tooltip'],
				'value'             => $this->get_value(),
				'required'          => true,
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
	 * Renders the login message for users that are not logged in.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function render_existing_customer_message() {

		$login_url = wp_login_url(add_query_arg('logged', '1'));

		ob_start(); ?>

		<div class="wu-p-4 wu-bg-yellow-200">

			<?php // phpcs:disable

			// translators: %s is the login URL.
			printf(__('<a href="%s">Log in</a> to renew or change an existing membership.', 'wp-ultimo'), $login_url);

			?>

		</div>

		<?php // phpcs:enable

		return ob_get_clean();

	} // end render_existing_customer_message;

	/**
	 * Renders the login message for users that are not logged in.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function render_not_you_customer_message() {

		$login_url = wp_login_url(add_query_arg('logged', '1'), true);

		ob_start();

		?>

		<p class="wu-p-4 wu-bg-yellow-200">
		<?php

		// translators: 1$s is the display name of the user currently logged in.
		printf(__('Not %1$s? <a href="%2$s">Log in</a> using your account.', 'wp-ultimo'), wp_get_current_user()->display_name, $login_url);

		?>
		</p>

		<?php

		return ob_get_clean();

	} // end render_not_you_customer_message;

} // end class Signup_Field_Email;
