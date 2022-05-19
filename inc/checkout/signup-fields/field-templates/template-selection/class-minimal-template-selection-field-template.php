<?php
/**
 * Template Selection Clean
 *
 * @package WP_Ultimo
 * @subpackage Checkout\Signup_Fields
 * @since 2.0.0
 */

namespace WP_Ultimo\Checkout\Signup_Fields\Field_Templates\Template_Selection;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Checkout\Signup_Fields\Field_Templates\Base_Field_Template;

/**
 * Template Selection Minimal
 *
 * @since 2.0.0
 */
class Minimal_Template_Selection_Field_Template extends Base_Field_Template {

	/**
	 * Field template id.
	 *
	 * Needs to take the following format: field-type/id.
	 * e.g. pricing-table/clean.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $id = 'template-selection/minimal';

	/**
	 * Get render type for the template.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_render_type() {

		return 'ajax';

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

		return __('Minimal', 'wp-ultimo');

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

		return __('A simple template with clean markup and no styling, ready to be customized with custom CSS.', 'wp-ultimo');

	} // end get_description;

	/**
	 * The preview of the field template.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_preview() {

		return wu_get_asset('checkout-forms/minimal-template-selection.png');

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

		wu_get_template('checkout/templates/template-selection/minimal', $attributes);

	} // end output;

} // end class Minimal_Template_Selection_Field_Template;
