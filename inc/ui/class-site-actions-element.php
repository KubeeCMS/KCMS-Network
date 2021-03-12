<?php
/**
 * Adds the Site_Actions_Element UI to the Admin Panel.
 *
 * @package WP_Ultimo
 * @subpackage UI
 * @since 2.0.0
 */

namespace WP_Ultimo\UI;

use WP_Ultimo\UI\Base_Element;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds the Checkout Element UI to the Admin Panel.
 *
 * @since 2.0.0
 */
class Site_Actions_Element extends Base_Element {

	/**
	 * The id of the element.
	 *
	 * Something simple, without prefixes, like 'checkout', or 'pricing-tables'.
	 *
	 * This is used to construct shortcodes by prefixing the id with 'wu_'
	 * e.g. an id checkout becomes the shortcode 'wu_checkout' and
	 * to generate the Gutenberg block by prefixing it with 'wp-ultimo/'
	 * e.g. checkout would become the block 'wp-ultimo/checkout'.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	public $id = 'site-actions';

	/**
	 * The icon of the UI element.
	 * e.g. return fa fa-search
	 *
	 * @since 2.0.0
	 * @param string $context One of the values: block, elementor or bb.
	 * @return string
	 */
	public function get_icon($context = 'block') {

		if ($context === 'elementor') {

			return 'eicon-info-circle-o';

		} // end if;

		return 'fa fa-search';

	} // end get_icon;

	/**
	 * The title of the UI element.
	 *
	 * This is used on the Blocks list of Gutenberg.
	 * You should return a string with the localized title.
	 * e.g. return __('My Element', 'wp-ultimo').
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_title() {

		return __('Actions', 'wp-ultimo');

	} // end get_title;

	/**
	 * The description of the UI element.
	 *
	 * This is also used on the Gutenberg block list
	 * to explain what this block is about.
	 * You should return a string with the localized title.
	 * e.g. return __('Adds a checkout form to the page', 'wp-ultimo').
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return __('Adds a checkout form block to the page.', 'wp-ultimo');

	} // end get_description;

	/**
	 * The list of fields to be added to Gutenberg.
	 *
	 * If you plan to add Gutenberg controls to this block,
	 * you'll need to return an array of fields, following
	 * our fields interface (@see inc/ui/class-field.php).
	 *
	 * You can create new Gutenberg panels by adding fields
	 * with the type 'header'. See the Checkout Elements for reference.
	 *
	 * @see inc/ui/class-checkout-element.php
	 *
	 * Return an empty array if you don't have controls to add.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function fields() {

		$fields = array();

		$fields['header'] = array(
			'title' => __('General', 'wp-ultimo'),
			'desc'  => __('General', 'wp-ultimo'),
			'type'  => 'header',
		);

		$fields['password_strength'] = array(
			'type'    => 'toggle',
			'title'   => __('Password Strength Meter', 'wp-ultimo'),
			'desc'    => __('Set this customer as a VIP.', 'wp-ultimo'),
			'tooltip' => '',
			'value'   => 1,
		);

		$fields['apply_styles'] = array(
			'type'    => 'toggle',
			'title'   => __('Apply Styles', 'wp-ultimo'),
			'desc'    => __('Set this customer as a VIP.', 'wp-ultimo'),
			'tooltip' => '',
			'value'   => 1,
		);

		return $fields;

	} // end fields;

	/**
	 * The list of keywords for this element.
	 *
	 * Return an array of strings with keywords describing this
	 * element. Gutenberg uses this to help customers find blocks.
	 *
	 * e.g.:
	 * return array(
	 *  'WP Ultimo',
	 *  'Actions',
	 *  'Form',
	 *  'Cart',
	 * );
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function keywords() {

		return array(
			'WP Ultimo',
			'Actions',
			'Form',
			'Cart',
		);

	} // end keywords;

	/**
	 * List of default parameters for the element.
	 *
	 * If you are planning to add controls using the fields,
	 * it might be a good idea to use this method to set defaults
	 * for the parameters you are expecting.
	 *
	 * These defaults will be used inside a 'wp_parse_args' call
	 * before passing the parameters down to the block render
	 * function and the shortcode render function.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function defaults() {

		return array();

	} // end defaults;

	/**
	 * Runs early on the request lifecycle as soon as we detect the shortcode is present.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function setup() {

		$this->site = WP_Ultimo()->currents->get_site();

		if (!$this->site || !$this->site->is_customer_allowed()) {

			$this->set_display(false);

			return;

		} // end if;

		$this->membership = $this->site->get_membership();

	} // end setup;

	/**
	 * Allows the setup in the context of previews.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function setup_preview() {

		$this->site = wu_mock_site();

		$this->membership = wu_mock_membership();

	} // end setup_preview;

	/**
	 * Returns the actions for the element. These can be filtered.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_actions() {

		$actions = array();

		$all_blogs    = get_blogs_of_user(get_current_user_id());

		if (count($all_blogs) > 1) {

			$actions['default_site'] = array(
				'label'        => __('Change Default Site', 'wp-ultimo'),
				'icon_classes' => 'dashicons-wu-edit wu-align-middle',
				'classes'      => 'wubox',
				'href'         => wu_get_form_url('change_default_site'),
			);

		} // end if;

		$actions['change_password'] = array(
			'label'        => __('Change Password', 'wp-ultimo'),
			'icon_classes' => 'dashicons-wu-edit wu-align-middle',
			'classes'      => 'wubox',
			'href'         => wu_get_form_url('change_password'),
		);

		return apply_filters('wu_element_get_site_actions', $actions);

	} // end get_actions;

	/**
	 * Returns the danger actions actions for the element. These can be filtered.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_danger_zone_actions() {

		$actions = array(
			'delete_site'    => array(
				'label'        => __('Delete Site', 'wp-ultimo'),
				'icon_classes' => 'dashicons-wu-edit wu-align-middle',
				'classes'      => 'wubox wu-text-red-500',
				'href'         => wu_get_form_url('change_password'),
			),
			'delete_account' => array(
				'label'        => __('Delete Account', 'wp-ultimo'),
				'icon_classes' => 'dashicons-wu-edit wu-align-middle',
				'classes'      => 'wubox wu-text-red-500',
				'href'         => wu_get_form_url('change_password'),
			),
		);

		return apply_filters('wu_element_get_site_actions', $actions);

	} // end get_danger_zone_actions;

	/**
	 * The content to be output on the screen.
	 *
	 * Should return HTML markup to be used to display the block.
	 * This method is shared between the block render method and
	 * the shortcode implementation.
	 *
	 * @since 2.0.0
	 *
	 * @param array       $atts Parameters of the block/shortcode.
	 * @param string|null $content The content inside the shortcode.
	 * @return string
	 */
	public function output($atts, $content = null) {

		$atts['actions'] = $this->get_actions();

		$atts['danger_zone_actions'] = $this->get_danger_zone_actions();

		return wu_get_template_contents('dashboard-widgets/site-actions', $atts);

	} // end output;

} // end class Site_Actions_Element;
