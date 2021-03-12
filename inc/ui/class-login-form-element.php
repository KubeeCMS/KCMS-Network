<?php
/**
 * Adds the Login Form Element UI to the Admin Panel.
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
class Login_Form_Element extends Base_Element {

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
	public $id = 'login-form';

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

		return __('Login Form', 'wp-ultimo');

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

		return __('Adds a login form to the page.', 'wp-ultimo');

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

		$fields['display_title'] = array(
			'type'    => 'toggle',
			'title'   => __('Display Title?', 'wp-ultimo'),
			'desc'    => __('Toggle to show/hide the title element.', 'wp-ultimo'),
			'tooltip' => '',
			'value'   => 1,
		);

		$fields['title'] = array(
			'type'     => 'text',
			'title'    => __('Title', 'wp-ultimo'),
			'value'    => __('Login', 'wp-ultimo'),
			'desc'     => '',
			'tooltip'  => '',
			'required' => array(
				'display_title' => 1,
			),
		);

		$fields['header_username'] = array(
			'title' => __('Username Field', 'wp-ultimo'),
			'desc'  => __('Username Field', 'wp-ultimo'),
			'type'  => 'header',
		);

		$fields['label_username'] = array(
			'type'    => 'text',
			'title'   => __('Username Field Label', 'wp-ultimo'),
			'value'   => __('Username or Email Address', 'wp-ultimo'),
			'desc'    => __('Leave blank to hide.', 'wp-ultimo'),
			'tooltip' => '',
		);

		$fields['placeholder_username'] = array(
			'type'    => 'text',
			'title'   => __('Username Field Placeholder', 'wp-ultimo'),
			'desc'    => __('e.g. Username Here', 'wp-ultimo'),
			'value'   => '',
			'tooltip' => '',
		);

		$fields['header_password'] = array(
			'title' => __('Password Field', 'wp-ultimo'),
			'desc'  => __('Password Field', 'wp-ultimo'),
			'type'  => 'header',
		);

		$fields['label_password'] = array(
			'type'    => 'text',
			'title'   => __('Password Field Label', 'wp-ultimo'),
			'value'   => __('Password', 'wp-ultimo'),
			'desc'    => __('Leave blank to hide.', 'wp-ultimo'),
			'tooltip' => '',
		);

		$fields['placeholder_password'] = array(
			'type'    => 'text',
			'title'   => __('Password Field Placeholder', 'wp-ultimo'),
			'desc'    => __('e.g. Your Password', 'wp-ultimo'),
			'value'   => '',
			'tooltip' => '',
		);

		$fields['header_remember'] = array(
			'title' => __('Remember Me', 'wp-ultimo'),
			'desc'  => __('Remember Me', 'wp-ultimo'),
			'type'  => 'header',
		);

		$fields['remember'] = array(
			'type'    => 'toggle',
			'title'   => __('Display Remember Toggle?', 'wp-ultimo'),
			'desc'    => __('Toggle to show/hide the remember me checkbox.', 'wp-ultimo'),
			'tooltip' => '',
			'value'   => 1,
		);

		$fields['label_remember'] = array(
			'type'     => 'text',
			'title'    => __('Remember Me Label', 'wp-ultimo'),
			'value'    => __('Remember Me'),
			'desc'     => '',
			'tooltip'  => '',
			'required' => array(
				'remember' => 1,
			),
		);

		$fields['desc_remember'] = array(
			'type'     => 'text',
			'title'    => __('Remember Me Description', 'wp-ultimo'),
			'value'    => __('Keep me logged in for two weeks.', 'wp-ultimo'),
			'desc'     => '',
			'tooltip'  => '',
			'required' => array(
				'remember' => 1,
			),
		);

		$fields['header_submit'] = array(
			'title' => __('Submit Button', 'wp-ultimo'),
			'desc'  => __('Submit Button', 'wp-ultimo'),
			'type'  => 'header',
		);

		$fields['label_log_in'] = array(
			'type'    => 'text',
			'title'   => __('Submit Button Label', 'wp-ultimo'),
			'value'   => __('Log In', 'wp-ultimo'),
			'tooltip' => '',
		);

		return $fields;

	} // end fields;

	/**
	 * Registers scripts and styles necessary to render this.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts() {

		wp_enqueue_style('wu-admin');

	} // end register_scripts;

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

		// Default 'redirect' value takes the user back to the request URI.
		$redirect_to = wu_get_current_url();

		return array(
			'display_title'        => 1,
			'title'                => __('Login', 'wp-ultimo'),

			'redirect'             => $redirect_to,
			'form_id'              => 'loginform',

			'label_username'       => __('Username or Email Address'),
			'placeholder_username' => '',

			'label_password'       => __('Password'),
			'placeholder_password' => '',

			'label_remember'       => __('Remember Me'),
			'desc_remember'        => __('Keep me logged in for two weeks.', 'wp-ultimo'),

			'label_log_in'         => __('Log In'),

			'id_username'          => 'user_login',
			'id_password'          => 'user_pass',
			'id_remember'          => 'rememberme',
			'id_submit'            => 'wp-submit',
			'remember'             => true,
			'value_username'       => '',
			'value_remember'       => false, // Set 'value_remember' to true to default the "Remember me" checkbox to checked.
		);

	} // end defaults;

	/**
	 * Runs early on the request lifecycle as soon as we detect the shortcode is present.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function setup() {

		$this->logged = is_user_logged_in();

	} // end setup;

	/**
	 * Allows the setup in the context of previews.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function setup_preview() {

		$this->logged = false;

	} // end setup_preview;

	/**
	 * Returns the logout URL for the "not you bar".
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_logout_url() {

		$redirect_to = wu_get_current_url();

		return wp_logout_url($redirect_to);

	} // end get_logout_url;

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
			'log' => array(
				'type'        => 'text',
				'title'       => $atts['label_username'],
				'placeholder' => $atts['placeholder_username'],
				'tooltip'     => '',
			),
			'pwd' => array(
				'type'        => 'password',
				'title'       => $atts['label_password'],
				'placeholder' => $atts['placeholder_password'],
				'tooltip'     => '',
			),
		);

		if ($atts['remember']) {

			$fields['rememberme'] = array(
				'type'  => 'toggle',
				'title' => $atts['label_remember'],
				'desc'  => $atts['desc_remember'],
			);

		} // end if;

		$fields['redirect_to'] = array(
			'type'  => 'hidden',
			'value' => $atts['redirect'],
		);

		$fields['wp-submit'] = array(
			'type'            => 'submit',
			'title'           => $atts['label_log_in'],
			'value'           => $atts['label_log_in'],
			'classes'         => 'button button-primary wu-w-full',
			'wrapper_classes' => 'wu-items-end wu-bg-none',
		);

		/**
		 * Instantiate the form for the order details.
		 *
		 * @since 2.0.0
		 */
		$form = new \WP_Ultimo\UI\Form($this->get_id(), $fields, array(
			'action'                => esc_url(site_url('wp-login.php', 'login_post')),
			'wrap_in_form_tag'      => true,
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-p-0 wu-m-0',
			'field_wrapper_classes' => 'wu-box-border wu-items-center wu-flex wu-justify-between wu-py-4 wu-m-0',
		));

		$atts['logged']    = $this->logged;
		$atts['login_url'] = $this->get_logout_url();
		$atts['form']      = $form;

		return wu_get_template_contents('dashboard-widgets/login-form', $atts);

	} // end output;

} // end class Login_Form_Element;
