<?php
/**
 * Base class to add new Elementor widgets.
 *
 * @package WP_Ultimo\Builders
 * @subpackage Elementor
 * @since 2.0.0
 */

namespace WP_Ultimo\Builders\Elementor;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Base class to add new Elementor widgets.
 *
 * @since 2.0.0
 */
class Elementor_Base_Widget extends \Elementor\Widget_Base {

	/**
	 * The base parameters to use to create the actual widget.
	 *
	 * Should contain:
	 * - name - string, lowercase, no spaces
	 * - title - string
	 * - icon - string
	 * - keywords - array
	 * - categories - array
	 * - fields - array
	 * - render - callback
	 * - content_template - callback
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $args = array();

	/**
	 * Loads the config args.
	 *
	 * This method acts basically as a constructor.
	 * It holds the configurations elements sent over and uses them
	 * to create the widget on Elementor.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Config array.
	 * @return void
	 */
	public function build($args) {

		$this->args = $args;

	} // end build;

	/**
	 * Get the value of a setup argument.
	 *
	 * @since 2.0.0
	 *
	 * @param string $arg_name Argument key.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public function get_arg($arg_name, $default = null) {

		return isset($this->args[$arg_name]) ? $this->args[$arg_name] : $default;

	} // end get_arg;

	/**
	 * Returns the name of the widget.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_name() {

		return $this->get_arg('name');

	} // end get_name;

	/**
	 * Returns the type of the widget.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_title() {

		return $this->get_arg('title');

	} // end get_title;

	/**
	 * Returns the icon class to be used by the widget.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_icon() {

		return $this->get_arg('icon');

	} // end get_icon;

	/**
	 * Returns the keywords of the widget.
	 *
	 * This is used by Elementor's search to find widgets.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_keywords() {

		return $this->get_arg('keywords', array('WP Ultimo'));

	} // end get_keywords;

	/**
	 * Returns the categories to which Elementor should add our widget.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_categories() {

		return $this->get_arg('categories', array('wp-ultimo'));

	} // end get_categories;

	/**
	 * Register the controls for the elementor widget, based on the fields.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	protected function _register_controls() {

		$fields = $this->get_arg('fields');

		if (!$fields) {

			return;

		} // end if;

		$current_section = null;

		foreach ($fields as $field_slug => $field) {
			/*
			 * Handle header types
			 */
			if ($field['type'] === 'header') {

				if ($current_section) {

					$this->end_controls_section();

				} // end if;

				$this->start_controls_section($field_slug, array(
					'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
					'label' => $field['title'],
				));

				$current_section = true;

			} elseif ($field['type'] === 'toggle') {

				$this->add_control($field_slug, array(
					'type'        => \Elementor\Controls_Manager::SELECT,
					'label'       => $field['title'],
					'description' => $field['desc'],
					'options'     => array(
						1 => __('Yes', 'wp-ultimo'),
						0 => __('No', 'wp-ultimo'),
					),
					'default'     => $field['value'],
				));

			} elseif ($field['type'] === 'text') {

				$this->add_control($field_slug, array(
					'type'        => \Elementor\Controls_Manager::TEXT,
					'label'       => $field['title'],
					'description' => $field['desc'],
					'default'     => $field['value'],
				));

			} elseif ($field['type'] === 'number') {

				if ($field['min'] && $field['max']) {

					$this->add_control($field_slug, array(
						'type'        => \Elementor\Controls_Manager::SLIDER,
						'label'       => $field['title'],
						'description' => $field['desc'],
						'range'       => array(
							'' => array(
								'step' => 1,
								'min'  => $field['min'],
								'max'  => $field['max'],
							),
						),
						'default'     => array(
							'value' => $field['value'],
							'unit'  => '',
						),
					));

				} else {

					$this->add_control($field_slug, array(
						'type'        => \Elementor\Controls_Manager::TEXT,
						'label'       => $field['title'],
						'description' => $field['desc'],
						'default'     => $field['value'],
					));

				} // end if;

			} elseif ($field['type'] === 'select') {

				$this->add_control($field_slug, array(
					'type'        => \Elementor\Controls_Manager::SELECT,
					'label'       => $field['title'],
					'description' => $field['desc'],
					'default'     => $field['value'],
					'options'     => $field['options'],
				));

			} elseif ($field['type'] === 'textarea') {

				$this->add_control($field_slug, array(
					'type'        => \Elementor\Controls_Manager::TEXTAREA,
					'label'       => $field['title'],
					'description' => $field['desc'],
					'default'     => $field['value'],
				));

			} // end if;

		} // end foreach;

		$this->end_controls_section();

	} // end _register_controls;

	/**
	 * Renders the widget content on the front-end.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	protected function render() {

		$atts = $this->get_settings_for_display();

		echo call_user_func($this->get_arg('render'), $atts);

	} // end render;

	/**
	 * Renders the content template inside elementor.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	protected function _content_template() {

		echo call_user_func($this->get_arg('content_template'), $atts);

	} // end _content_template;

	/**
	 * Get the styles that this element depends on.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_style_depends() {

		return array('wu-styling');

	} // end get_style_depends;

} // end class Elementor_Base_Widget;
