<?php
/**
 * Base class to UI elements that are rendered on the backend and the frontend.
 *
 * @package WP_Ultimo\UI
 * @subpackage Base_Element
 * @since 2.0.0
 */

namespace WP_Ultimo\UI;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Base class to UI elements that are rendered on the backend and the frontend.
 *
 * @since 2.0.0
 */
abstract class Base_Element {

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
	protected $id;

	/**
	 * Should this element be hidden by default?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $hidden_by_default = false;

	/**
	 * Controls whether or not the widget and element should display.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $display = true;

	/**
	 * Holds the status of the setup functions.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $is_ready = false;

	/**
	 * The icon of the UI element.
	 *
	 * E.g. return fa fa-search.
	 *
	 * @since 2.0.0
	 * @param string $context One of the values: block, elementor or bb.
	 * @return string
	 */
	abstract public function get_icon($context = 'block'); // end get_icon;

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
	abstract public function get_title();

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
	abstract public function get_description();

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
	abstract public function fields(); // end fields;

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
	abstract public function keywords(); // end keywords;

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
	abstract public function defaults(); // end defaults;

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
	abstract public function output($atts, $content = null); // end output;

	// Boilerplate -----------------------------------

	/**
	 * Initializes the singleton.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		add_action('admin_init', array($this, 'register_form'));

		add_action('init', array($this, 'register_shortcode'));

		add_action('wp_enqueue_scripts', array($this, 'enqueue_element_scripts'));

		add_action("wu_{$this->id}_scripts", array($this, 'register_default_scripts'));

		add_action("wu_{$this->id}_scripts", array($this, 'register_scripts'));

		add_action('wp', array($this, 'maybe_setup'));

		do_action('wu_element_loaded', $this);

	} // end init;

	/**
	 * Maybe run setup, when the shortcode or block is found.
	 *
	 * @todo check if this is working only when necessary.
	 * @since 2.0.0
	 * @return void
	 */
	public function maybe_setup() {

		global $post;

		if (!is_admin() && !is_a($post, 'WP_Post')) {

			return;

		} // end if;

		if ($this->is_ready === false) {

			if ($this->is_preview()) {

				$this->setup_preview();

			} else {

				$this->setup();

			} // end if;

		} // end if;

	} // end maybe_setup;

	/**
	 * Runs early on the request lifecycle as soon as we detect the shortcode is present.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function setup() {} // end setup;

	/**
	 * Allows the setup in the context of previews.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function setup_preview() {} // end setup_preview;

	/**
	 * Adds custom CSS to the signup screen.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function enqueue_element_scripts() {

		global $post;

		if (!is_a($post, 'WP_Post')) {

			return;

		} // end if;

		if (has_shortcode($post->post_content, $this->get_shortcode_id())) {

			do_action("wu_{$this->id}_scripts", $post, $this);

		} elseif (has_block($this->get_id(), $post->post_content)) {

			do_action("wu_{$this->id}_scripts", $post, $this);

			add_filter('the_content', 'html_entity_decode', 9999);

		} // end if;

	} // end enqueue_element_scripts;

	/**
	 * Registers the shortcode.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_shortcode() {

		add_shortcode($this->get_shortcode_id(), array($this, 'display'));

	} // end register_shortcode;

	/**
	 * Registers the forms.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_form() {
		/*
		 * Add Generator Forms
		 */
		wu_register_form("shortcode_{$this->id}", array(
			'render'     => array($this, 'render_generator_modal'),
			'handler'    => '__return_empty_string',
			'capability' => 'manage_network',
		));

		/*
		 * Add Customize Forms
		 */
		wu_register_form("customize_{$this->id}", array(
			'render'     => array($this, 'render_customize_modal'),
			'handler'    => array($this, 'handle_customize_modal'),
			'capability' => 'manage_network',
		));

	} // end register_form;

	/**
	 * Adds the modal to copy the shortcode for this particular element.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_generator_modal() {

		$fields = $this->fields();

		$defaults = $this->defaults();

		$state = array();

		foreach ($fields as $field_slug => &$field) {

			if ($field['type'] === 'header') {

				unset($fields[$field_slug]);

				continue;

			} // end if;

			/*
			 * Set v-model
			 */
			$field['html_attr'] = array(
				'v-model.lazy' => "attributes.{$field_slug}",
			);

			$state[$field_slug] = wu_request($field_slug, wu_get_isset($defaults, $field_slug));

		} // end foreach;

		$fields['shortcode_result'] = array(
			'type' => 'note',
			'desc' => '<div class="wu-w-full"><h3 class="wu-my-1 wu-text-2xs wu-uppercase wu-block">' . __('Result', 'wp-ultimo') . '</h3><pre v-html="shortcode" id="wu-shortcode" class="wu-text-center wu-overflow-auto wu-p-4 wu-m-0 wu-mt-2 wu-rounded wu-content-center wu-bg-gray-800 wu-text-white wu-font-mono wu-border wu-border-solid wu-border-gray-300 wu-max-h-screen wu-overflow-y-auto"></pre></div>',
		);

		$fields['submit_copy'] = array(
			'type'            => 'submit',
			'title'           => __('Copy Shortcode', 'wp-ultimo'),
			'value'           => 'edit',
			'classes'         => 'button button-primary wu-w-full wu-copy',
			'wrapper_classes' => 'wu-items-end',
			'html_attr'       => array(
				'data-clipboard-action' => 'copy',
				'data-clipboard-target' => '#wu-shortcode',
			),
		);

		$form = new \WP_Ultimo\UI\Form($this->id, $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => "{$this->id}_generator",
				'data-state'  => wu_convert_to_state(array(
					'id'         => $this->get_shortcode_id(),
					'defaults'   => $defaults,
					'attributes' => $state,
				)),
			),
		));

		echo '<div class="wu-styling">';

		$form->render();

		echo '</div>';

	} // end render_generator_modal;

	/**
	 * Adds the modal customize the widget block
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_customize_modal() {

		$fields = array();

		$fields['hide'] = array(
			'type'            => 'toggle',
			'title'           => __('Hide Element', 'wp-ultimo'),
			'desc'            => __('Be careful. Hiding an element from the account page might remove important functionality from your customers\' reach.', 'wp-ultimo'),
			'value'           => $this->hidden_by_default,
			'classes'         => 'button button-primary wu-w-full',
			'wrapper_classes' => 'wu-items-end',
		);

		$fields = array_merge($fields, $this->fields());

		$saved_settings = $this->get_widget_settings();

		foreach ($fields as $field_slug => &$field) {

			if ($field['type'] === 'header') {

				unset($fields[$field_slug]);

				continue;

			} // end if;

			$value = wu_get_isset($saved_settings, $field_slug, null);

			if ($value !== null) {

				$field['value'] = $value;

			} // end if;

		} // end foreach;

		$fields['submit'] = array(
			'type'            => 'submit',
			'title'           => __('Save Changes', 'wp-ultimo'),
			'value'           => 'edit',
			'classes'         => 'button button-primary wu-w-full',
			'wrapper_classes' => 'wu-items-end wu-pb-1',
		);

		$fields['restore'] = array(
			'type'            => 'submit',
			'title'           => __('Restore Settings', 'wp-ultimo'),
			'value'           => 'edit',
			'classes'         => 'button wu-w-full',
			'wrapper_classes' => 'wu-items-end wu-border-t-0 wu-border-transparent wu-border-0 wu-pt-1',
		);

		$form = new \WP_Ultimo\UI\Form($this->id, $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => "{$this->id}_customize",
				'data-state'  => wu_convert_to_state(),
			),
		));

		echo '<div class="wu-styling">';

		$form->render();

		echo '</div>';

	} // end render_customize_modal;

	/**
	 * Saves the customization settings for a given widget.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_customize_modal() {

		$settings = array();

		if (wu_request('submit') !== 'restore') {

			$fields = $this->fields();

			$fields['hide'] = array(
				'type' => 'toggle',
			);

			foreach ($fields as $field_slug => $field) {

				$setting = wu_request($field_slug, false);

				if ($setting !== false || $field['type'] === 'toggle') {

					$settings[$field_slug] = $setting;

				} // end if;

			} // end foreach;

		} // end if;

		$this->save_widget_settings($settings);

		wp_send_json_success(array(
			'send'         => array(
				'scope'         => 'window',
				'function_name' => 'wu_block_ui',
				'data'          => '#wpcontent',
			),
			'redirect_url' => add_query_arg('updated', 1, $_SERVER['HTTP_REFERER']),
		));

	} // end handle_customize_modal;

	/**
	 * Registers scripts and styles necessary to render this.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_default_scripts() {

		wp_enqueue_style('wu-admin');

	} // end register_default_scripts;

	/**
	 * Registers scripts and styles necessary to render this.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts() {} // end register_scripts;

	/**
	 * Loads dependencies that might not be available at render time.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function dependencies() {} // end dependencies;

	/**
	 * Returns the ID of this UI element.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_id() {

		return sprintf('wp-ultimo/%s', $this->id);

	} // end get_id;

	/**
	 * Returns the ID of this UI element.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_shortcode_id() {

		return str_replace('-', '_', sprintf('wu_%s', $this->id));

	} // end get_shortcode_id;

	/**
	 * Treats the attributes before passing them down to the output method.
	 *
	 * @since 2.0.0
	 *
	 * @param array $atts The element attributes.
	 * @return string
	 */
	public function display($atts) {

		$this->maybe_setup();

		if (!$this->should_display()) {

			return; // bail if the display was set to false.

		} // end if;

		$this->dependencies();

		$atts = wp_parse_args($atts, $this->defaults());

		/*
		 * Account for the 'className' Gutenberg attribute.
		 */
		$atts['className'] = trim('wu-' . $this->id . ' ' . wu_get_isset($atts, 'className', ''));

		/*
		 * Pass down the element so we can use helpers.
		 */
		$atts['element'] = $this;

		return call_user_func(array($this, 'output'), $atts);

	} // end display;

	/**
	 * Retrieves a cleaned up version of the content.
	 *
	 * This method strips out vue reactivity tags and more.
	 *
	 * @since 2.0.0
	 *
	 * @param array $atts The element attributes.
	 * @return string
	 */
	public function display_template($atts) {

		$content = $this->display($atts);

		$content = str_replace(array(
			'v-',
			'data-wu',
			'data-state',
		), 'inactive-', $content);

		$content = str_replace(array(
			'{{',
			'}}',
		), '', $content);

		return $content;

	} // end display_template;

	/**
	 * Checks if we need to display admin management attachments.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function should_display_customize_controls() {

		return apply_filters('wu_element_display_super_admin_notice', current_user_can('manage_network'), $this);

	} // end should_display_customize_controls;

	/**
	 * Adds the element as a inline block, without the admin widget frame.
	 *
	 * @since 2.0.0
	 *
	 * @param string $screen_id The screen id.
	 * @param string $hook The hook to add the content to. Defaults to admin_notices.
	 * @param array  $atts Array containing the shortcode attributes.
	 * @return void
	 */
	public function as_inline_content($screen_id, $hook = 'admin_notices', $atts = array()) {

		if (!function_exists('get_current_screen')) {

			_doing_it_wrong(__METHOD__, __('An element can not be loaded as inline content unless the get_current_screen() function is already available.', 'wp-ultimo'), '2.0.0');

			return;

		} // end if;

		$screen = get_current_screen();

		if (!$screen || $screen_id !== $screen->id) {

			return;

		} // end if;

		if (!$this->should_display()) {

			return; // bail if the display was set to false.

		} // end if;

		if (empty($atts)) {

			$atts = $this->get_widget_settings();

		} // end if;

		$control_classes = '';

		if (wu_get_isset($atts, 'hide', $this->hidden_by_default)) {

			if (!$this->should_display_customize_controls()) {

				return;

			} // end if;

			$control_classes = 'wu-customize-mode wu-opacity-25';

		} // end if;

		add_action($hook, function() use ($atts, $control_classes) {

			echo '<div class="wu-inline-widget">';

				echo '<div class="wu-inline-widget-body ' . $control_classes . '">';

					echo $this->display($atts);

				echo '</div>';

				$this->super_admin_notice();

			echo '</div>';

		});

		do_action("wu_{$this->id}_scripts", null, $this);

	} // end as_inline_content;

	/**
	 * Save the widget options.
	 *
	 * @since 2.0.0
	 *
	 * @param array $settings The settings to save. Key => value array.
	 * @return void
	 */
	public function save_widget_settings($settings) {

		$key = wu_replace_dashes($this->id);

		wu_save_setting("widget_{$key}_settings", $settings);

	} // end save_widget_settings;

	/**
	 * Retrieves the settings for a particular widget.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_widget_settings() {

		$key = wu_replace_dashes($this->id);

		return wu_get_setting("widget_{$key}_settings", array());

	} // end get_widget_settings;

	/**
	 * Adds the element as a metabox.
	 *
	 * @since 2.0.0
	 *
	 * @param string $screen_id The screen id.
	 * @param string $position Position on the screen.
	 * @param array  $atts Array containing the shortcode attributes.
	 * @return void
	 */
	public function as_metabox($screen_id, $position = 'normal', $atts = array()) {

		if (!$this->should_display()) {

			return; // bail if the display was set to false.

		} // end if;

		if (empty($atts)) {

			$atts = $this->get_widget_settings();

		} // end if;

		$control_classes = '';

		if (wu_get_isset($atts, 'hide')) {

			if (!$this->should_display_customize_controls()) {

				return;

			} // end if;

			$control_classes = 'wu-customize-mode wu-opacity-25';

		} // end if;

		add_meta_box(
			"wp-ultimo-{$this->id}-element",
			$this->get_title(),
			function() use ($atts, $control_classes) {

				echo '<div class="wu-metabox-widget ' . $control_classes . '">';

					echo $this->display($atts);

				echo '</div>';

				$this->super_admin_notice();

			},
			$screen_id,
			$position,
			'high'
		);

		do_action("wu_{$this->id}_scripts", null, $this);

	} // end as_metabox;

	/**
	 * Adds note for super admins.
	 *
	 * Adds an admin notice to let the super admin know
	 * how to use the widgets.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function super_admin_notice() {

		$should_display = $this->should_display_customize_controls();

		if ($should_display) {

			// translators: %1$s is the URL to the customize modal. %2$s is the URL of the shortcode generation modal
			$message = __('<a class="wubox wu-no-underline" title="Customize" href="%1$s">Customize this element</a>, or <a class="wubox wu-no-underline" title="Shortcode" href="%2$s">generate a shortcode</a> to use it on the front-end!', 'wp-ultimo');

			$message .= wu_tooltip(__('You are seeing this because you are a super admin', 'wp-ultimo'));

			$link_shortcode = wu_get_form_url("shortcode_{$this->id}");
			$link_customize = wu_get_form_url("customize_{$this->id}");

			$text = sprintf(
				$message,
				$link_customize,
				$link_shortcode
			);

			$html = '
				<div class="wu-styling">
						<div class="wu-widget-inset">
							<div class="wubox wu-no-underline wu-py-4 wu-bg-gray-200 wu-block wu-mt-4 wu-text-center wu-text-sm wu-text-gray-600 wu-m-auto wu-border-solid wu-border-0 wu-border-t wu-border-gray-400">
								' . $text . '
							</div>
					</div>
				</div>
			';

			echo $html;

		} // end if;

	} // end super_admin_notice;

	/**
	 * Checks if we are in a preview context.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_preview() {

		$is_preview = false;

		if (did_action('init')) {

			$is_preview = wu_request('preview') && current_user_can('edit_posts');

		} // end if;

		return apply_filters('wu_element_is_preview', false, $this);

	} // end is_preview;

	/**
	 * Get controls whether or not the widget and element should display..
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function should_display() {

		return $this->display || $this->is_preview();

	} // end should_display;

	/**
	 * Set controls whether or not the widget and element should display..
	 *
	 * @since 2.0.0
	 * @param boolean $display Controls whether or not the widget and element should display.
	 * @return void
	 */
	public function set_display($display) {

		$this->display = $display;

	} // end set_display;

} // end class Base_Element;
