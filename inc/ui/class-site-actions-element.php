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
	public $id = 'site-actions';

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
	 * Registers the shortcode.
	 * Here we use this method to provide a stripe portal redirect
	 * TODO: Remove this function after add another way to handle payment change
	 *
	 * @since 2.0.11
	 * @return void
	 */
	public function register_shortcode() {

		if (wu_request('wu-stripe-portal')) {

			$this->site = WP_Ultimo()->currents->get_site();

			if (!$this->site || !$this->site->is_customer_allowed()) {

				return;

			} // end if;

			$this->membership = $this->site->get_membership();

			if (!$this->membership) {

				return;

			} // end if;

			$gateway_id = $this->membership->get_gateway();

			$gateway = wu_get_gateway($gateway_id);

			$allowed_payment_method_types = apply_filters('wu_stripe_checkout_allowed_payment_method_types', array(
				'card',
			), $gateway);

			$customer_id   = $this->membership->get_customer_id();
			$s_customer_id = $this->membership->get_gateway_customer_id();
			$return_url    = remove_query_arg('wu-stripe-portal', wu_get_current_url());

			// If customer is not set, get from checkout session
			if (empty($s_customer_id)) {

				$subscription_data = array(
					'payment_method_types'       => $allowed_payment_method_types,
					'mode'                       => 'setup',
					'success_url'                => $return_url,
					'cancel_url'                 => wu_get_current_url(),
					'billing_address_collection' => 'required',
					'client_reference_id'        => $customer_id,
					'customer'                   => $s_customer_id
				);

				$session       = \WP_Ultimo\Dependencies\Stripe\Checkout\Session::create($subscription_data);
				$s_customer_id = $session->subscript_ion_data['customer'];

			} // end if;

			$portal_config_id = get_site_option('wu_stripe_portal_config_id');

			if (!$portal_config_id) {

				$portal_config = \WP_Ultimo\Dependencies\Stripe\BillingPortal\Configuration::create(array(
					'features'         => array(
						'invoice_history'       => array(
							'enabled' => true,
						),
						'payment_method_update' => array(
							'enabled' => true,
						),
						'subscription_cancel'   => array(
							'enabled'             => true,
							'mode'                => 'at_period_end',
							'cancellation_reason' => array(
								'enabled' => true,
								'options' => array(
									'too_expensive',
									'missing_features',
									'switched_service',
									'unused',
									'customer_service',
									'too_complex',
									'other',
								),
							),
						),
					),
					'business_profile' => array(
						'headline' => __('Manage your membership payment methods.', 'wp-ultimo'),
					),
				));

				$portal_config_id = $portal_config->id;

				update_site_option('wu_stripe_portal_config_id', $portal_config_id);

			} // end if;

			$subscription_data = array(
				'return_url'    => $return_url,
				'customer'      => $s_customer_id,
				'configuration' => $portal_config_id,
			);

			$session = \WP_Ultimo\Dependencies\Stripe\BillingPortal\Session::create($subscription_data);

			wp_redirect($session->url);
			exit;

		} // end if;

		parent::register_shortcode();

	} // end register_shortcode;

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

		$all_blogs = get_blogs_of_user(get_current_user_id());

		$is_template_switching_enabled = wu_get_setting('allow_template_switching', true);

		if ($is_template_switching_enabled && is_admin()) {

			$actions['template_switching'] = array(
				'label'        => __('Change Site Template', 'wp-ultimo'),
				'icon_classes' => 'dashicons-wu-edit wu-align-middle',
				'href'         => admin_url('admin.php?page=wu-template-switching'),
			);

		} // end if;

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

		// TODO: Remove this part after set a better way to handle payment methods change.
		if ($this->membership && in_array($this->membership->get_gateway(), array('stripe', 'stripe-checkout'), true)) {

			$s_subscription_id = $this->membership->get_gateway_subscription_id();

			if (!empty($s_subscription_id)) {

				$actions['change_payment_method'] = array(
					'label'        => __('Change Payment Method', 'wp-ultimo'),
					'icon_classes' => 'dashicons-wu-edit wu-align-middle',
					'href'         => add_query_arg(array('wu-stripe-portal' => true)),
				);

			} // end if;

		} // end if;

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
				'href'         => wu_get_form_url('delete_site'),
			),
			'delete_account' => array(
				'label'        => __('Delete Account', 'wp-ultimo'),
				'icon_classes' => 'dashicons-wu-edit wu-align-middle',
				'classes'      => 'wubox wu-text-red-500',
				'href'         => wu_get_form_url('delete_account'),
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
