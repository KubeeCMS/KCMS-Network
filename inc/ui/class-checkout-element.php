<?php
/**
 * Adds the Checkout_Element UI to the Admin Panel.
 *
 * @package WP_Ultimo
 * @subpackage UI
 * @since 2.0.0
 */

namespace WP_Ultimo\UI;

use \WP_Ultimo\UI\Base_Element;
use \WP_Ultimo\Checkout\Checkout;
use \WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Compiler;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds the Checkout Element UI to the Admin Panel.
 *
 * @since 2.0.0
 */
class Checkout_Element extends Base_Element {

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
	public $id = 'checkout';

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

			return 'eicon-cart-medium';

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

		return __('Checkout', 'wp-ultimo');

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

		$fields['slug'] = array(
			'title' => __('Slug', 'wp-ultimo'),
			'desc'  => __('Slug', 'wp-ultimo'),
			'type'  => 'text',
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
	 *  'Checkout',
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
			'Checkout',
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
			'slug'          => 'main-form',
			'step'          => false,
			'display_title' => false,
		);

	} // end defaults;

	/**
	 * Checks if we are on a thank you page.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_thank_you_page() {

		return is_user_logged_in() && wu_request('payment') && wu_request('status') === 'done';

	} // end is_thank_you_page;

	/**
	 * Triggers the setup event to allow the checkout class to hook in.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function setup() {

		if ($this->is_thank_you_page()) {

			\WP_Ultimo\UI\Thank_You_Element::get_instance()->setup();

			return;

		} // end if;

		do_action('wu_setup_checkout', $this);

	} // end setup;

	/**
	 * Print the Custom CSS added on the checkout.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Checkout_Form $checkout_form The current checkout form.
	 * @return void
	 */
	public function print_custom_css($checkout_form) {

		$scss = new Compiler();

		$slug = $checkout_form->get_slug();

		$custom_css = $checkout_form->get_custom_css();

		$custom_css = $scss->compile(".wu_checkout_form_{$slug} {
			{$custom_css}
		}");

		echo sprintf('<style>%s</style>', $custom_css);

	} // end print_custom_css;

	/**
	 * Outputs thank you page.
	 *
	 * @since 2.0.0
	 *
	 * @param array       $atts Parameters of the block/shortcode.
	 * @param string|null $content The content inside the shortcode.
	 * @return string
	 */
	public function output_thank_you($atts, $content = null) {

		$slug = $atts['slug'];

		$checkout_form = wu_get_checkout_form_by_slug($slug);

		$atts = $checkout_form->get_meta('wu_thank_you_settings');

		/*
		 * Deal with conversion tracking
		 */
		$conversion_snippets = $checkout_form->get_conversion_snippets();

		add_action('wp_print_footer_scripts', function() use ($conversion_snippets) {

			echo $conversion_snippets;

		});

		return \WP_Ultimo\UI\Thank_You_Element::get_instance()->output($atts, $content);

	} // end output_thank_you;

	/**
	 * Outputs the registration form.
	 *
	 * @since 2.0.0
	 *
	 * @param array       $atts Parameters of the block/shortcode.
	 * @param string|null $content The content inside the shortcode.
	 * @return string
	 */
	public function output_form($atts, $content = null) {

		$atts['step'] = wu_request('step', $atts['step']);

		$slug = $atts['slug'];

		$checkout_form = wu_get_checkout_form_by_slug($slug);

		if (!$checkout_form) {

			return sprintf(__('Checkout form %s not found.', 'wp-ultimo'), $slug);

		} // end if;

		if ($checkout_form->has_country_lock()) {

			$geolocation = \WP_Ultimo\Geolocation::geolocate_ip('', true);

			if (!in_array($geolocation['country'], $checkout_form->get_allowed_countries())) {

				return sprintf('<p>%s</p>', __('Registration is closed for your location.', 'wp-ultimo'));

			} // end if;

		} // end if;

		if (!$checkout_form->is_active()) {

			return sprintf('<p>%s</p>', __('Registration is not available at this time.', 'wp-ultimo'));

		} // end if;

		$checkout = \WP_Ultimo\Checkout\Checkout::get_instance();

		$this->steps = $checkout->maybe_hide_steps($checkout_form->get_settings());

		$step = $checkout_form->get_step($atts['step']);

		$this->step = $step ? $step : current($this->steps);

		$this->step_name = $this->step['id'];

		/*
		 * Hack-y way to make signup available on the template.
		 */
		global $signup;

		$signup = new Mocked_Signup($this->step_name, $this->steps); // phpcs:ignore

		add_action('wp_print_footer_scripts', function() use ($checkout_form) {

			$this->print_custom_css($checkout_form);

		});

		return wu_get_template_contents('checkout/form', array(
			'step'               => $this->step,
			'step_name'          => $this->step_name,
			'checkout_form_name' => $atts['slug'],
			'errors'             => $checkout->errors,
			'display_title'      => $atts['display_title'],
		));

	} // end output_form;

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

		if ($this->is_thank_you_page()) {

			return $this->output_thank_you($atts, $content);

		} // end if;

		return $this->output_form($atts, $content);

	} // end output;

} // end class Checkout_Element;

/**
 * Replacement of the old WU_Signup class for templates.
 *
 * @since 2.0.0
 */
class Mocked_Signup {

	/**
	 * Holds the list of settings
	 *
	 * @since 2.0.0
	 * @var array
	 */
	public $steps = array();

	/**
	 * Constructs the class.
	 *
	 * @since 2.0.0
	 *
	 * @param string $step Current step.
	 * @param array  $steps List of all steps.
	 */
	public function __construct($step, $steps) {

		$this->steps = $steps;

		$this->step = $step;

	} // end __construct;

	/**
	 * Get the value of steps.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_steps() {

		return $this->steps;

	} // end get_steps;

	/**
	 * Deprecated: returns the prev step link.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_prev_step_link() {

		return '';

	} // end get_prev_step_link;

} // end class Mocked_Signup;
