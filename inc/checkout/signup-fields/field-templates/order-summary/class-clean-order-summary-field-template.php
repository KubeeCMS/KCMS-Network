<?php
/**
 * Order Summary Clean
 *
 * @package WP_Ultimo
 * @subpackage Checkout\Signup_Fields
 * @since 2.0.0
 */

namespace WP_Ultimo\Checkout\Signup_Fields\Field_Templates\Order_Summary;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Checkout\Signup_Fields\Field_Templates\Base_Field_Template;

/**
 * Order Summary Clean
 *
 * @since 2.0.0
 */
class Clean_Order_Summary_Field_Template extends Base_Field_Template {

	/**
	 * Field template id.
	 *
	 * Needs to take the following format: field-type/id.
	 * e.g. pricing-table/clean.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $id = 'order-summary/simple';

	/**
	 * The render type for the template.
	 *
	 * Field templates can have two different render types, ajax and dynamic.
	 * If ajax is selected, when we detect a change in the billing period and other
	 * sensitive info, an ajax request is made to fetch the new pricing table HTML
	 * markup.
	 *
	 * If dynamic is selected, nothing is done as the template can handle
	 * reactive updates natively (using Vue.js)
	 *
	 * In terms of performance, dynamic is preferred, but ajax should
	 * work just fine.
	 *
	 * @since 2.0.0
	 * @return string Either ajax or dynamic
	 */
	public function get_render_type() {

		return 'dynamic';

 	} // end get_render_type;

	/**
	 * The title of the field template.
	 *
	 * This is used on the template selector.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_title() {

		return __('Clean', 'wp-ultimo');

	} // end get_title;

	/**
	 * The description of the field template.
	 *
	 * This is used on the template selector.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return __('A simple layout with minimal styling, just enough to make it usable out-of-the-box.', 'wp-ultimo');

	} // end get_description;

	/**
	 * The preview of the field template.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_preview() {

		return wu_get_asset('checkout-forms/clean-order-summary.png');

	} // end get_preview;

	/**
	 * The content of the template.
	 *
	 * @since 2.0.0
	 *
	 * @param array $attributes The field template attributes.
	 * @return void
	 */
	public function output($attributes) {

		/**
     * Loads the actual order-summary template
     */
		wu_get_template('checkout/templates/order-summary/simple', $attributes);

	} // end output;

} // end class Clean_Order_Summary_Field_Template;
