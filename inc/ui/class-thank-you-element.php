<?php
/**
 * Adds the Thank_You_Element UI to the Admin Panel.
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
 * Adds the Thank You Element UI to the Admin Panel.
 *
 * @since 2.0.0
 */
class Thank_You_Element extends Base_Element {

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
	public $id = 'thank-you';

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

	} // end init;

	/**
	 * Replace the register page title with the Thank you title.
	 *
	 * @since 2.0.0
	 *
	 * @param array $title_parts The title parts.
	 * @return array
	 */
	public function replace_page_title($title_parts) {

		$title_parts['title'] = $this->get_title();

		return $title_parts;

	} // end replace_page_title;

	/**
	 * Maybe clear the title at the content level.
	 *
	 * @since 2.0.0
	 *
	 * @param string $title The page title.
	 * @param int    $id The post/page id.
	 * @return string
	 */
	public function maybe_replace_page_title($title, $id) {

		global $post;

		if ($post && $post->ID === $id) {

			return '';

		} // end if;

		return $title;

	} // end maybe_replace_page_title;

	/**
	 * Register additional scripts for the thank you page.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts() {

		$has_pending_site = $this->membership ? (bool) $this->membership->get_pending_site() : false;
		$is_publishing    = $has_pending_site ? $this->membership->get_pending_site()->is_publishing() : false;

		wp_register_script('wu-thank-you', wu_get_asset('thank-you.js', 'js'), array('jquery'), wu_get_version());

		wp_localize_script('wu-thank-you', 'wu_thank_you', array(
			'creating'                        => $is_publishing,
			'has_pending_site'                => $has_pending_site,
			'next_queue'                      => wu_get_next_queue_run(),
			'ajaxurl'                         => admin_url('admin-ajax.php'),
			'resend_verification_email_nonce' => wp_create_nonce('wu_resend_verification_email_nonce'),
			'membership_hash'                 => $this->membership ? $this->membership->get_hash() : false,
			'i18n'                            => array(
				'resending_verification_email' => __('Resending verification email...', 'wp-ultimo'),
				'email_sent'                   => __('Verification email sent!', 'wp-ultimo'),
			),
		));

		wp_enqueue_script('wu-thank-you');

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

		return __('Thank You', 'wp-ultimo');

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
			'value'   => __('Thank You', 'wp-ultimo'),
			'desc'    => __('Leave blank to hide the title completely.', 'wp-ultimo'),
			'tooltip' => '',
		);

		$fields['thank_you_message'] = array(
			'type'      => 'textarea',
			'title'     => __('Thank You Message', 'wp-ultimo'),
			'desc'      => __('Shortcodes are supported.', 'wp-ultimo'),
			'value'     => __('Thank you for your payment! Your transaction has been completed and a receipt for your purchase has been emailed to you.', 'wp-ultimo'),
			'tooltip'   => '',
			'html_attr' => array(
				'rows' => 4,
			),
		);

		$fields['title_pending'] = array(
			'type'    => 'text',
			'title'   => __('Title (Pending)', 'wp-ultimo'),
			'value'   => __('Thank You', 'wp-ultimo'),
			'desc'    => __('Leave blank to hide the title completely. This title is used when the payment was not yet confirmed.', 'wp-ultimo'),
			'tooltip' => '',
		);

		$fields['thank_you_message_pending'] = array(
			'type'      => 'textarea',
			'title'     => __('Thank You Message (Pending)', 'wp-ultimo'),
			'desc'      => __('This content is used when the payment was not yet confirmed. Shortcodes are supported.', 'wp-ultimo'),
			'value'     => __('Thank you for your order! We are waiting on the payment processor to confirm your payment, which can take up to 5 minutes. We will notify you via email when your site is ready.', 'wp-ultimo'),
			'tooltip'   => '',
			'html_attr' => array(
				'rows' => 4,
			),
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
			'Thank You',
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
			'title'                     => __('Thank You', 'wp-ultimo'),
			'thank_you_message'         => __('Thank you for your payment! Your transaction has been completed and a receipt for your purchase has been emailed to you.', 'wp-ultimo'),
			'title_pending'             => __('Thank You', 'wp-ultimo'),
			'thank_you_message_pending' => __('Thank you for your order! We are waiting on the payment processor to confirm your payment, which can take up to 5 minutes. We will notify you via email when your site is ready.', 'wp-ultimo'),
		);

	} // end defaults;

	/**
	 * Runs early on the request lifecycle as soon as we detect the shortcode is present.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function setup() {

		$this->payment = wu_get_payment_by_hash(wu_request('payment'));

		if (!$this->payment) {

			$this->set_display(false);

			return;

		} // end if;

		$this->membership = $this->payment->get_membership();

		if (!$this->membership || !$this->membership->is_customer_allowed()) {

			$this->set_display(false);

			return;

		} // end if;

		$this->customer = $this->membership->get_customer();

		add_filter('document_title_parts', array($this, 'replace_page_title'));

		add_filter('the_title', array($this, 'maybe_replace_page_title'), 10, 2);

	} // end setup;

	/**
	 * Allows the setup in the context of previews.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function setup_preview() {

		$this->payment = wu_mock_payment();

		$this->membership = wu_mock_membership();

		$this->customer = wu_mock_customer();

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

		$atts['payment'] = $this->payment;

		$atts['membership'] = $this->membership;

		$atts['customer'] = $this->customer;

		$atts = wp_parse_args($atts, $this->defaults());

		/*
		 * Deal with conversion tracking
		 */
		$conversion_snippets = $atts['checkout_form']->get_conversion_snippets();

		if (!empty($conversion_snippets)) {

			$product_ids = array_map( function($line_item) {

				return (string) $line_item->get_id();

			}, $this->payment->get_line_items());

			$conversion_placeholders = apply_filters( 'wu_conversion_placeholders', array(
				'MEMBERSHIP_DURATION' => $this->membership->get_recurring_description(),
				'MEMBERSHIP_PLAN'     => $this->membership->get_plan_id(),
				'ORDER_CURRENCY'      => $this->payment->get_currency(),
				'ORDER_PRODUCTS'      => $product_ids,
				'ORDER_AMOUNT'        => $this->payment->get_total(),
			));

			foreach ($conversion_placeholders as $placeholder => $value) {

				$conversion_snippets = preg_replace('/\%\%\s?' . $placeholder . '\s?\%\%/', json_encode($value), $conversion_snippets);

			} // end foreach;

			add_action('wp_print_footer_scripts', function() use ($conversion_snippets) {

				echo $conversion_snippets;

			});

		} // end if;

		/*
		 * Account for the 'className' Gutenberg attribute.
		 */
		$atts['className'] = trim('wu-' . $this->id . ' ' . wu_get_isset($atts, 'className', ''));

		return wu_get_template_contents('dashboard-widgets/thank-you', $atts);

	} // end output;

} // end class Thank_You_Element;
