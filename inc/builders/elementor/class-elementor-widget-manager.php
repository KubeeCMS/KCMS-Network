<?php
/**
 * Handles Elementor Widget Support.
 *
 * @package WP_Ultimo\Builders
 * @subpackage Elementor
 * @since 2.0.0
 */

namespace WP_Ultimo\Builders\Elementor;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles Elementor Widget Support.
 *
 * @since 2.0.0
 */
class Elementor_Widget_Manager {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Runs when Elementor element support is first loaded.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		add_action('wu_element_loaded', array($this, 'handle_element'));

		add_filter('elementor/init', array($this, 'add_wp_ultimo_category'));

		add_action('elementor/editor/before_enqueue_styles', array($this, 'add_elementor_styles'));

		add_action('wu_element_is_preview', array($this, 'is_elementor_preview'));

	} // end init;

	/**
	 * Gets called when a new element is registered
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\UI\Base_Element $element The element being registered.
	 * @return void
	 */
	public function handle_element($element) {

		add_action('elementor/widgets/widgets_registered', function() use ($element) {

			$this->register_elementor_widget($element);

		});

	} // end handle_element;

	/**
	 * Registers the Elementor Block.
	 *
	 * @since 2.0.0
	 * @param \WP_Ultimo\UI\Base_Element $element The element being registered.
	 * @return void
	 */
	public function register_elementor_widget($element) {

		$widget = new Elementor_Base_Widget();

		$widget->build(array(
			'name'             => $element->id,
			'icon'             => 'wu-elementor-icon ' . $element->get_icon('elementor'),
			'title'            => $element->get_title(),
			'keywords'         => $element->keywords(),
			'fields'           => $element->fields(),
			'categories'       => array('wp-ultimo'),
			'render'           => array($element, 'display'),
			'content_template' => array($element, 'display_template'),
		));

		$widgets_manager = \Elementor\Plugin::instance()->widgets_manager;

		$widgets_manager->register_widget_type($widget);

	} // end register_elementor_widget;

	/**
	 * Checks if we are in an Elementor preview screen.
	 *
	 * @since 2.0.0
	 * @param boolean $is_preview The previous preview status from the filter.
	 * @return boolean
	 */
	public function is_elementor_preview($is_preview) {

		if (wu_request('elementor-preview')) {

			$is_preview = true;

		} // end if;

		return $is_preview;

	} // end is_elementor_preview;

	/**
	 * Register the custom scripts we'll use.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_elementor_styles() {

		wp_enqueue_style('wu-elementor', wu_get_asset('elementor.css', 'css', 'inc/builders/elementor/assets'), wu_get_version());

	} // end add_elementor_styles;

	/**
	 * Adds WP Ultimo as an Elementor widget category.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_wp_ultimo_category() {

		$category_manager = \Elementor\Plugin::$instance->elements_manager;

		$category_manager->add_category('wp-ultimo', array(
			'title' => __( 'WP Ultimo', 'wp-ultimo' ),
			'icon'  => 'fa fa-plug', // default icon
		), 2);

	} // end add_wp_ultimo_category;

} // end class Elementor_Widget_Manager;
