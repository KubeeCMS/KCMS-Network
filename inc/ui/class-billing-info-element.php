<?php
/**
 * Adds the Billing_Info_Element UI to the Admin Panel.
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
class Billing_Info_Element extends Base_Element {

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
	public $id = 'billing-info';

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
	 * Overload the init to add site-related forms.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		parent::init();

		wu_register_form('update_billing_address', array(
			'render'     => array($this, 'render_update_billing_address'),
			'handler'    => array($this, 'handle_update_billing_address'),
			'capability' => 'exist',
		));

	} // end init;

	/**
	 * Loads the required scripts.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts() {

		add_wubox();

	} // end register_scripts;

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

		return __('Billing Information', 'wp-ultimo');

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

		$fields['title'] = array(
			'type'    => 'text',
			'title'   => __('Title', 'wp-ultimo'),
			'value'   => __('Billing Address', 'wp-ultimo'),
			'desc'    => __('Leave blank to hide the title completely.', 'wp-ultimo'),
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
	 *  'Billing Information',
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
			'Billing Information',
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

		return array(
			'title' => __('Billing Address', 'wp-ultimo'),
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

		$this->membership = $this->site->get_membership();

		if (!$this->membership) {

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

		$this->membership = wu_mock_membership();

	} // end setup_preview;

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

		$atts['membership'] = $this->membership;

		$atts['billing_address'] = $this->membership->get_billing_address();

		$atts['update_billing_address_link'] = wu_get_form_url('update_billing_address', array(
			'membership' => $this->membership->get_hash(),
			'width'      => 500,
		));

		return wu_get_template_contents('dashboard-widgets/billing-info', $atts);

	} // end output;

	/**
	 * Apply the placeholders to the fields.
	 *
	 * @since 2.0.0
	 *
	 * @param array $fields The billing fields.
	 * @return array
	 */
	protected function apply_placeholders($fields) {

		foreach ($fields as &$field) {

			$field['placeholder'] = $field['default_placeholder'];

		} // end foreach;

		return $fields;

	} // end apply_placeholders;

	/**
	 * Renders the update billing address form.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function render_update_billing_address() {

		$membership = wu_get_membership_by_hash(wu_request('membership'));

		if (!$membership) {

			return '';

		} // end if;

		$billing_address = $membership->get_billing_address();

		$fields = array();

		$fields['billing-title'] = array(
			'type'            => 'header',
			'order'           => 1,
			'title'           => __('Your Address', 'wp-ultimo'),
			'desc'            => __('Enter your billing address here. This info will be used on your invoices.', 'wp-ultimo'),
			'wrapper_classes' => 'wu-col-span-2',
		);

		$billing_fields = $this->apply_placeholders($billing_address->get_fields());

		$fields = array_merge($fields, $billing_fields);

		$fields['submit'] = array(
			'type'            => 'submit',
			'title'           => __('Save Changes', 'wp-ultimo'),
			'value'           => 'save',
			'classes'         => 'button button-primary wu-w-full',
			'wrapper_classes' => 'wu-col-span-2',
		);

		$fields['membership'] = array(
			'type'  => 'hidden',
			'value' => wu_request('membership'),
		);

		$form = new \WP_Ultimo\UI\Form('edit_site', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0 wu-grid-cols-2 wu-grid',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid wu-grid-col-span-2',
			'html_attr'             => array(
				'data-wu-app' => 'edit_site',
				'data-state'  => wu_convert_to_state(),
			),
		));

		$form->render();

	} // end render_update_billing_address;

	/**
	 * Handles the password reset form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_update_billing_address() {

		$membership = wu_get_membership_by_hash(wu_request('membership'));

		if (!$membership) {

			$error = new \WP_Error('membership-dont-exist', __('Something went wrong.', 'wp-ultimo'));

			wp_send_json_error($error);

		} // end if;

		$billing_address = $membership->get_billing_address();

		$billing_address->attributes($_POST);

		$valid_address = $billing_address->validate();

		if (is_wp_error($valid_address)) {

			wp_send_json_error($valid_address);

		} // end if;

		$membership->set_billing_address($billing_address);

		$saved = $membership->save();

		if (is_wp_error($saved)) {

			wp_send_json_error($saved);

		} // end if;

		wp_send_json_success(array(
			'redirect_url' => add_query_arg('updated', (int) $saved, $_SERVER['HTTP_REFERER']),
		));

	} // end handle_update_billing_address;

} // end class Billing_Info_Element;
