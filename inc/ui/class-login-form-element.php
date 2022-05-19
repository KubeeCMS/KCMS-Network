<?php
/**
 * Adds the Login Form Element UI to the Admin Panel.
 *
 * @package WP_Ultimo
 * @subpackage UI
 * @since 2.0.0
 */

namespace WP_Ultimo\UI;

use \WP_Ultimo\UI\Base_Element;
use \WP_Ultimo\Checkout\Checkout_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds the Checkout Element UI to the Admin Panel.
 *
 * @since 2.0.0
 */
class Login_Form_Element extends Base_Element {

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
	public $id = 'login-form';

	/**
	 * Initializes the singleton.
	 *
	 * @since 2.0.11
	 * @return void
	 */
	public function init() {

		// Handle login redirection
		add_filter('login_redirect', array($this, 'handle_redirect'), -1, 3);

		parent::init();

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

		$fields['redirect_type'] = array(
			'type'    => 'select',
			'title'   => __('Redirect Type', 'wp-ultimo'),
			'desc'    => __('The behavior after login', 'wp-ultimo'),
			'tooltip' => '',
			'default' => 'default',
			'options' => array(
				'default'       => __('Wordpress Default', 'wp-ultimo'),
				'customer_site' => __('Send To Customer Site', 'wp-ultimo'),
				'main_site'     => __('Send To Main Site', 'wp-ultimo'),
			),
		);

		$fields['customer_redirect_path'] = array(
			'type'     => 'text',
			'title'    => __('Customer Redirect Path', 'wp-ultimo'),
			'value'    => __('/wp-admin', 'wp-ultimo'),
			'desc'     => __('e.g. /wp-admin', 'wp-ultimo'),
			'tooltip'  => '',
			'required' => array(
				'redirect_type' => 'customer_site',
			),
		);

		$fields['main_redirect_path'] = array(
			'type'     => 'text',
			'title'    => __('Main Site Redirect Path', 'wp-ultimo'),
			'value'    => __('/wp-admin', 'wp-ultimo'),
			'desc'     => __('e.g. /wp-admin', 'wp-ultimo'),
			'tooltip'  => '',
			'required' => array(
				'redirect_type' => 'main_site',
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
			'display_title'          => 1,
			'title'                  => __('Login', 'wp-ultimo'),

			'redirect_type'          => 'default',
			'customer_redirect_path' => '/wp-admin',
			'main_redirect_path'     => '/wp-admin',

			'redirect'               => $redirect_to,
			'form_id'                => 'loginform',

			'label_username'         => __('Username or Email Address'),
			'placeholder_username'   => '',

			'label_password'         => __('Password'),
			'placeholder_password'   => '',

			'label_remember'         => __('Remember Me'),
			'desc_remember'          => __('Keep me logged in for two weeks.', 'wp-ultimo'),

			'label_log_in'           => __('Log In'),

			'id_username'            => 'user_login',
			'id_password'            => 'user_pass',
			'id_remember'            => 'rememberme',
			'id_submit'              => 'wp-submit',
			'remember'               => true,
			'value_username'         => '',
			'value_remember'         => false, // Set 'value_remember' to true to default the "Remember me" checkbox to checked.
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

		if ($this->is_reset_password_page()) {

			$rp_path = '/';

			$rp_cookie = 'wp-resetpass-' . COOKIEHASH;

			if (isset($_GET['key']) && isset($_GET['login'])) {

				$value = sprintf('%s:%s', wp_unslash($_GET['login']), wp_unslash($_GET['key']));

				setcookie($rp_cookie, $value, 0, $rp_path, COOKIE_DOMAIN, is_ssl(), true);

				wp_safe_redirect(remove_query_arg(array('key', 'login')));

				exit;

			} // end if;

		} // end if;

		global $post;

		/*
		 * Handles maintenance mode on Elementor.
		 */
		if ($post && $post->ID === absint(wu_get_setting('default_login_page', 0))) {

			add_filter('elementor/maintenance_mode/is_login_page', '__return_true');

		} // end if;

	} // end setup;

	/**
	 * Checks if we are in a lost password form page.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_lost_password_page() {

		return wu_request('action') === 'lostpassword';

	} // end is_lost_password_page;

	/**
	 * Checks if we are in the email confirm instruction page of a reset password.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_check_email_confirm() {

		return wu_request('checkemail') === 'confirm';

	} // end is_check_email_confirm;

	/**
	 * Checks if we are in a reset password page.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_reset_password_page() {

		return wu_request('action') === 'rp' || wu_request('action') === 'resetpass';

	} // end is_reset_password_page;

	/**
	 * Checks if we are in the the password rest confirmation page.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_reset_confirmation_page() {

		return wu_request('password-reset') === 'success';

	} // end is_reset_confirmation_page;

	/**
	 * Handle custom login redirection
	 *
	 * @since 2.0.11
	 *
	 * @param string             $redirect_to            The redirect destination URL.
	 * @param string             $requested_redirect_to  The requested redirect destination URL.
	 * @param /WP_User|/WP_Error $user                   The URL to redirect user.
	 * @return string
	 */
	public function handle_redirect($redirect_to, $requested_redirect_to, $user) {

		if (is_wp_error($user)) {

			return $redirect_to;

		} // end if;

		$redirect_type = wu_request('wu_login_form_redirect_type', 'default');

		// If some condition match, force user redirection to the URL
		if ($redirect_type === 'customer_site') {

			$user_site = get_active_blog_for_user( $user->ID );

			wp_redirect($user_site->siteurl . $requested_redirect_to);
			exit;

		} elseif ($redirect_type === 'main_site') {

			wp_redirect(network_site_url($requested_redirect_to));
			exit;

		} // end if;

		return $redirect_to;

	} // end handle_redirect;

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

		$view = 'dashboard-widgets/login-additional-forms';

		/*
		 * Checks if we are in the confirmation page.
		 *
		 * If that's the case, we show a successful message and the
		 * login URL so the user can re-login with the new password.
		 */
		if ($this->is_reset_confirmation_page()) {

			$fields = array(
				'email-activation-instructions' => array(
					'type' => 'note',
					'desc' => __('Your password has been reset.') . ' <a href="' . esc_url(wp_login_url()) . '">' . __('Log in') . '</a>',
				),
			);

		/*
		 * Check if are in the email confirmation instructions page.
		 *
		 * If that's the case, we show the instructions.
		 */
		} elseif ($this->is_check_email_confirm()) {

			$fields = array(
				'email-activation-instructions' => array(
					'type' => 'note',
					'desc' => sprintf(
						/* translators: %s: Link to the login page. */
						__('Check your email for the confirmation link, then visit the <a href="%s">login page</a>.'),
						wp_login_url()
					),
				),
			);

		/*
		 * Check if we are in the set new password page.
		 *
		 * If that's the case, we show the new password fields
		 * so the user can set a new password.
		 */
		} elseif ($this->is_reset_password_page()) {

			$rp_cookie = 'wp-resetpass-' . COOKIEHASH;

			if (isset($_COOKIE[$rp_cookie]) && 0 < strpos($_COOKIE[$rp_cookie], ':')) {

				list($rp_login, $rp_key) = explode(':', wp_unslash($_COOKIE[$rp_cookie]), 2);

				$user = check_password_reset_key($rp_key, $rp_login);

				if (isset($_POST['pass1']) && !hash_equals($rp_key, $_POST['rp_key'])) {

					$user = false;

				} // end if;

			} else {

				$user = false;

			} // end if;

			$redirect_to = add_query_arg('password-reset', 'success', remove_query_arg(array('action', 'error')));

			$fields = array(
				'pass1'                      => array(
					'type'        => 'password',
					'title'       => __('New password'),
					'placeholder' => '',
					'value'       => '',
					'html_attr'   => array(
						'size'           => 24,
						'autocapitalize' => 'off',
					),
				),
				'pass2'                      => array(
					'type'        => 'password',
					'title'       => __('Confirm new password'),
					'placeholder' => '',
					'value'       => '',
					'html_attr'   => array(
						'size'           => 24,
						'autocapitalize' => 'off',
					),
				),
				'lost-password-instructions' => array(
					'type'    => 'note',
					'desc'    => wp_get_password_hint(),
					'tooltip' => '',
				),
				'action'                     => array(
					'type'  => 'hidden',
					'value' => 'resetpass',
				),
				'rp_key'                     => array(
					'type'  => 'hidden',
					'value' => $rp_key,
				),
				'user_login'                 => array(
					'type'  => 'hidden',
					'value' => $rp_login,
				),
				'redirect_to'                => array(
					'type'  => 'hidden',
					'value' => $redirect_to,
				),
				'wp-submit'                  => array(
					'type'            => 'submit',
					'title'           => __('Save Password'),
					'value'           => __('Save Password'),
					'classes'         => 'button button-primary wu-w-full',
					'wrapper_classes' => 'wu-items-end wu-bg-none',
				),
			);

		/*
		 * Checks if we are in the first reset password page, where the customer requests a reset.
		 *
		 * If that's the case, we show the username/email field, so the user can
		 * get an email with the reset link.
		 */
		} elseif ($this->is_lost_password_page()) {

			$user_login = wu_request('user_login', '');

			if ($user_login) {

				$user_login = wp_unslash($user_login);

			} // end if;

			$redirect_to = add_query_arg('checkemail', 'confirm', remove_query_arg(array('action', 'error')));

			$fields = array(
				'lost-password-instructions' => array(
					'type'    => 'note',
					'desc'    => __('Please enter your username or email address. You will receive an email message with instructions on how to reset your password.'),
					'tooltip' => '',
				),
				'user_login'                 => array(
					'type'        => 'text',
					'title'       => __('Username or Email Address'),
					'placeholder' => '',
					'value'       => $user_login,
					'html_attr'   => array(
						'size'           => 20,
						'autocapitalize' => 'off',
					),
				),
				'action'                     => array(
					'type'  => 'hidden',
					'value' => 'lostpassword',
				),
				'redirect_to'                => array(
					'type'  => 'hidden',
					'value' => $redirect_to,
				),
				'wp-submit'                  => array(
					'type'            => 'submit',
					'title'           => __('Get New Password'),
					'value'           => __('Get New Password'),
					'classes'         => 'button button-primary wu-w-full',
					'wrapper_classes' => 'wu-items-end wu-bg-none',
				),
			);

		} else {

			$view = 'dashboard-widgets/login-form';

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

			if ($atts['redirect_type'] === 'customer_site') {

				$fields['redirect_to']['value'] = $atts['customer_redirect_path'];

			} elseif ($atts['redirect_type'] === 'main_site') {

				$fields['redirect_to']['value'] = $atts['main_redirect_path'];

			} // end if;

			$fields['wu_login_form_redirect_type'] = array(
				'type'  => 'hidden',
				'value' => $atts['redirect_type'],
			);

			$fields['wp-submit'] = array(
				'type'            => 'submit',
				'title'           => $atts['label_log_in'],
				'value'           => $atts['label_log_in'],
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end wu-bg-none',
			);

			$fields['lost-password'] = array(
				'type'            => 'html',
				'content'         => sprintf('<a class="wu-text-xs wu-block wu-text-center wu--mt-4" href="%s">%s</a>', esc_url(add_query_arg('action', 'lostpassword')), __('Lost your password?')),
				'classes'         => '',
				'wrapper_classes' => 'wu-items-end wu-bg-none',
			);

		} // end if;

		/*
		 * Check for error messages
		 *
		 * If we have some, we add an additional field
		 * at the top of the fields array, to display the errors.
		 */
		if (wu_request('error')) {

			$error_message_field = array(
				'error_message' => array(
					'type' => 'note',
					'desc' => Checkout_Pages::get_instance()->get_error_message(wu_request('error')),
				),
			);

			$fields = array_merge($error_message_field, $fields);

		} // end if;

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
			'html_attr'             => array(
				'class' => 'wu-w-full',
			),
		));

		$atts['logged']    = $this->logged;
		$atts['login_url'] = $this->get_logout_url();
		$atts['form']      = $form;

		return wu_get_template_contents($view, $atts);

	} // end output;

} // end class Login_Form_Element;
