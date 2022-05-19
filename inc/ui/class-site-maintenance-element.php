<?php
/**
 * Adds the Site Maintenance Element UI to the Admin Panel.
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
class Site_Maintenance_Element extends Base_Element {

	use \WP_Ultimo\Traits\Singleton;

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
	public $id = 'site-maintenance';

	/**
	 * Initializes the singleton.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		if (wu_get_setting('maintenance_mode')) {

			parent::init();

		} // end if;

	} // end init;

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

			return 'eicon-lock-user';

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

		return __('Site Maintenance', 'wp-ultimo');

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

		return __('Adds the toggle control to turn maintenance mode on.', 'wp-ultimo');

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

		$fields['title'] = array(
			'type'        => 'text',
			'title'       => __('Label', 'wp-ultimo'),
			'value'       => __('Toggle Maintenance Mode', 'wp-ultimo'),
			'placeholder' => __('e.g. Toggle Maintenance Mode', 'wp-ultimo'),
			'tooltip'     => '',
		);

		$fields['desc'] = array(
			'type'    => 'textarea',
			'title'   => __('Description', 'wp-ultimo'),
			'value'   => __('Put your site on maintenance mode. When activated, the front-end will only be accessible to logged users.', 'wp-ultimo'),
			'tooltip' => '',
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
	 *  'Billing_Address',
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
			'Login',
			'Reset Password',
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

		return array(
			'title' => __('Toggle Maintenance Mode', 'wp-ultimo'),
			'desc'  => __('Put your site on maintenance mode. When activated, the front-end will only be accessible to logged users.', 'wp-ultimo'),
		);

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

	} // end setup;

	/**
	 * Allows the setup in the context of previews.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function setup_preview() {

		$this->site = wu_mock_site();

	} // end setup_preview;

	/**
	 * Registers scripts and styles necessary to render this.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts() {

		wp_register_script('wu-site-maintenance', wu_get_asset('site-maintenance.js', 'js'), array('jquery', 'wu-functions'), wu_get_version());

		wp_localize_script('wu-site-maintenance', 'wu_site_maintenance', array(
			'nonce'   => wp_create_nonce('wu_toggle_maintenance_mode'),
			'ajaxurl' => wu_ajax_url(),
		));

		wp_enqueue_script('wu-site-maintenance');

	} // end register_scripts;

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

		$fields = array(
			'maintenance_mode' => array(
				'type'  => 'toggle',
				'title' => $atts['title'],
				'desc'  => $atts['desc'],
				'value' => wu_string_to_bool($this->site->get_meta('wu_maintenance_mode')),
			),
			'site_hash' => array(
				'type'  => 'hidden',
				'value' => $this->site->get_hash(),
			),
		);

		/**
		 * Instantiate the form for the order details.
		 *
		 * @since 2.0.0
		 */
		$form = new \WP_Ultimo\UI\Form('maintenance-mode', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-widget-list wu-striped wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0 wu-list-none wu-p-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(),
		));

		$atts['form'] = $form;

		return wu_get_template_contents('dashboard-widgets/site-maintenance', $atts);

	} // end output;

} // end class Site_Maintenance_Element;
