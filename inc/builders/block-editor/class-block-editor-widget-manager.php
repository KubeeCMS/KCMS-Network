<?php
/**
 * Handles Block Editor Widget Support.
 *
 * @package WP_Ultimo\Builders
 * @subpackage Block_Editor
 * @since 2.0.0
 */

namespace WP_Ultimo\Builders\Block_Editor;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Database\Sites\Site_Type;

/**
 * Handles Block Editor Widget Support.
 *
 * @since 2.0.0
 */
class Block_Editor_Widget_Manager {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Runs when Block_Editor element support is first loaded.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		if (\WP_Ultimo\Compat\Gutenberg_Support::get_instance()->should_load()) {

			add_action('wu_element_loaded', array($this, 'handle_element'));

			add_action('init', array($this, 'register_scripts'));

			add_action('wu_element_is_preview', array($this, 'is_block_preview'));

		} // end if;

	} // end init;

	/**
	 * Adds the required scripts.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts() {

		\WP_Ultimo\Scripts::get_instance()->register_script('wu-blocks', wu_get_asset('blocks.js', 'js', 'inc/builders/block-editor/assets'), array('underscore', 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor', 'wu-functions', 'wp-i18n', 'wp-polyfill'));

		$blocks = apply_filters('wu_blocks', array());

		wp_localize_script('wu-blocks', 'wu_blocks', $blocks);

	} // end register_scripts;

	/**
	 * Checks if we are inside a block preview render.
	 *
	 * @since 2.0.0
	 * @param boolean $is_preview The previous preview status from the filter.
	 * @return boolean
	 */
	public function is_block_preview($is_preview) {

		if (defined('REST_REQUEST') && true === REST_REQUEST && 'edit' === filter_input(INPUT_GET, 'context', FILTER_SANITIZE_STRING)) {

			$is_preview = true;

		} // end if;

		return $is_preview;

	} // end is_block_preview;

	/**
	 * Gets called when a new element is registered
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\UI\Base_Element $element The element being registered.
	 * @return void
	 */
	public function handle_element($element) {

		if (wu_get_current_site()->get_type() === Site_Type::CUSTOMER_OWNED) {

			return;

		} // end if;

		$this->register_block($element);

		add_filter('wu_blocks', function($blocks) use ($element) {

			return $this->load_block_settings($blocks, $element);

		});

	} // end handle_element;

	/**
	 * Registers block with WordPress.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\UI\Base_Element $element The element being registered.
	 * @return void
	 */
	public function register_block($element) {

		if (\WP_Block_Type_Registry::get_instance()->is_registered($element->get_id())) {

			return;

		} // end if;

		$attributes = $this->get_attributes_from_fields($element);

		register_block_type($element->get_id(), array(
			'attributes'      => $attributes,
			'editor_script'   => 'wu-blocks',
			'render_callback' => array($element, 'display'),
		));

	} // end register_block;

	/**
	 * Consolidate field attributes that are callables for blocks.
	 *
	 * @since 2.0.9
	 *
	 * @param array $fields The list of fields.
	 * @return array
	 */
	protected function consolidate_callables($fields) {

		$callable_keys = array(
			'options',
			'value',
		);

		$fields_to_ignore = array(
			'note',
		);

		foreach ($fields as $field_slug => &$field) {
			/*
			 * Discard fields that are notes and start with _
			 */
			if (in_array($field['type'], $fields_to_ignore, true) && strpos($field_slug, '_') === 0) {

				unset($fields[$field_slug]);

			} // end if;

			/*
			 * Deal with the group type.
			 * On those, we need to loop the sub-fields.
			 */
			if ($field['type'] === 'group') {

				foreach ($field['fields'] as &$sub_field) {

					foreach ($sub_field as $sub_item => &$sub_value) {

						if (in_array($sub_item, $callable_keys, true) && is_callable($sub_value)) {

							$sub_value = call_user_func($sub_value);

						} // end if;

					} // end foreach;

				} // end foreach;

			} // end if;

			/*
			 * Deal with the regular field types and its
			 * callables.
			 */
			foreach ($field as $item => &$value) {

				if (in_array($item, $callable_keys, true) && is_callable($value)) {

					$value = call_user_func($value);

				} // end if;

			} // end foreach;

		} // end foreach;

		return $fields;

	} // end consolidate_callables;

	/**
	 * Registers the block so WP Ultimo can add it on the JS side.
	 *
	 * @since 2.0.0
	 *
	 * @param array                      $blocks List of blocks registered.
	 * @param \WP_Ultimo\UI\Base_Element $element The element being registered.
	 * @return array
	 */
	public function load_block_settings($blocks, $element) {

		$fields = $this->consolidate_callables($element->fields());

		$blocks[] = array(
			'id'          => $element->get_id(),
			'title'       => $element->get_title(),
			'description' => $element->get_description(),
			'fields'      => $fields,
			'keywords'    => $element->keywords(),
		);

		return $blocks;

	} // end load_block_settings;

	/**
	 * Generates the list of attributes supported based on the fields.
	 *
	 * @since 2.0.0
	 * @param \WP_Ultimo\UI\Base_Element $element The element being registered.
	 * @return array
	 */
	public function get_attributes_from_fields($element) {

		$fields = $element->fields();

		$defaults = $element->defaults();

		$_fields = array();

		foreach ($fields as $field_id => $field) {

			$type = 'string';

			if ($field['type'] === 'toggle') {

				$type = 'boolean';

			} // end if;

			if ($field['type'] === 'number') {

				$type = 'integer';

			} // end if;

			$default_value = wu_get_isset($defaults, $field_id, '');

			$_fields[$field_id] = array(
				'default' => wu_get_isset($field, 'value', $default_value),
				'type'    => $type,
			);

		} // end foreach;

		return $_fields;

	} // end get_attributes_from_fields;

} // end class Block_Editor_Widget_Manager;
