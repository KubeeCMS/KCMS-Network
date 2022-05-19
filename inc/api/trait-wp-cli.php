<?php
/**
 * A trait to be included in entities to enable WP CLI commands.
 *
 * @package WP_Ultimo
 * @subpackage Apis
 * @since 2.0.0
 */

namespace WP_Ultimo\Apis;

/**
 * WP CLI trait.
 */
trait WP_CLI {

	/**
	 * The base used in the command right after the root: `wp <root> <command_base> <sub_command>`.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $wp_cli_command_base = '';

	/**
	 * WP-CLI Sub_command enabled for this entity.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $wp_cli_enabled_sub_commands = array();

	/**
	 * Returns the base used right after the root.
	 * Uses the `wp_cli_command_base` attribute if set, `slug` otherwise.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_wp_cli_command_base() {

		return (!empty($this->wp_cli_command_base)) ? $this->wp_cli_command_base : $this->slug;

	} // end get_wp_cli_command_base;

	/**
	 * Registers the routes. Should be called by the entity
	 * to actually enable the REST API.
	 *
	 * @since 2.0.0
	 */
	public function enable_wp_cli() {

		if (!defined('WP_CLI')) {

			return;

		} // end if;

		$wp_cli_root = 'wu';

		$this->set_wp_cli_enabled_sub_commands();

		foreach ($this->wp_cli_enabled_sub_commands as $sub_command => $sub_command_data) {

			\WP_CLI::add_command(
				"{$wp_cli_root} {$this->get_wp_cli_command_base()} {$sub_command}",
				$sub_command_data['callback'],
				array(
					'synopsis' => $sub_command_data['synopsis'],
				)
			);

		} // end foreach;

	} // end enable_wp_cli;

	/**
	 * Set wP-CLI Sub-command enabled for this entity.
	 */
	public function set_wp_cli_enabled_sub_commands() {

		$sub_commands = array(
			'get'    => array(
				'callback' => array($this, 'wp_cli_get_item'),
			),
			'list'   => array(
				'callback' => array($this, 'wp_cli_get_items'),
			),
			'create' => array(
				'callback' => array($this, 'wp_cli_create_item'),
			),
			'update' => array(
				'callback' => array($this, 'wp_cli_update_item'),
			),
			'delete' => array(
				'callback' => array($this, 'wp_cli_delete_item'),
			),
		);

		$params = array_merge($this->wp_cli_get_fields(), $this->wp_cli_extra_parameters());

		$params = array_unique($params);

		/**
		 * Unset undesired Params.
		 */
		$params_to_remove = apply_filters('wu_cli_params_to_remove', array(
			'id',
			'model',
		));

		$params = array_filter($params, function($param) use ($params_to_remove) {

			return !in_array($param, $params_to_remove, true);

		});

		foreach ($sub_commands as $sub_command => &$sub_command_data) {

			$sub_command_data['synopsis'] = array();

			if (in_array($sub_command, array('get', 'update', 'delete'), true)) {

				$sub_command_data['synopsis'][] = array(
					'name'        => 'id',
					'type'        => 'positional',
					'description' => __('The id for the resource.', 'wp-ultimo'),
					'optional'    => false,
				);

			} // end if;

			if (in_array($sub_command, array('list', 'update', 'create'), true)) {

				$explanation_list = wu_rest_get_endpoint_schema($this->model_class, 'update');

				foreach ($params as $name) {

					$explanation = wu_get_isset($explanation_list, $name, array());

					$type = wu_get_isset($explanation, 'type', 'assoc');

					$field = array(
						'name'        => $name,
						'description' => wu_get_isset($explanation, 'description', __('No description found.', 'wp-ultimo')),
						'optional'    => !wu_get_isset($explanation, 'required'),
						'type'        => 'assoc',
					);

					$options = wu_get_isset($explanation, 'options', array());

					if ($options) {

						$field['options'] = $options;

					} // end if;

					$sub_command_data['synopsis'][] = $field;

				} // end foreach;

			} // end if;

			if (in_array($sub_command, array('create', 'update'), true)) {

				$sub_command_data['synopsis'][] = array(
					'name'        => 'porcelain',
					'type'        => 'flag',
					'description' => __('Output just the id when the operation is successful.', 'wp-ultimo'),
					'optional'    => true,
				);

			} // end if;

			if (in_array($sub_command, array('list', 'get'), true)) {

				$sub_command_data['synopsis'][] = array(
					'name'        => 'format',
					'type'        => 'assoc',
					'description' => __('Render response in a particular format.', 'wp-ultimo'),
					'optional'    => true,
					'default'     => 'table',
					'options'     => array(
						'table',
						'json',
						'csv',
						'ids',
						'yaml',
						'count',
					),
				);

				$sub_command_data['synopsis'][] = array(
					'name'        => 'fields',
					'type'        => 'assoc',
					'description' => __('Limit response to specific fields. Defaults to id, name', 'wp-ultimo'),
					'optional'    => true,
					'options'     => array_merge(array('id'), $params),
				);

			} // end if;

		} // end foreach;

		$this->wp_cli_enabled_sub_commands = $sub_commands;

		/**
		 * Filters which sub_commands are enabled for this entity.
		 *
		 * @since 2.0.0
		 *
		 * @param array        $sub_commands  Default sub_commands.
		 * @param string       $command_base The base used in the command right after the root.
		 * @param Base_Manager $this         The object instance.
		 */
		$this->wp_cli_enabled_sub_commands = apply_filters(
			'wu_wp_cli_enabled_sub_commands',
			$this->wp_cli_enabled_sub_commands,
			$this->get_wp_cli_command_base(),
			$this
		);

	} // end set_wp_cli_enabled_sub_commands;

	/**
	 * Allows the additional of additional parameters.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function wp_cli_extra_parameters() {

		$model = new $this->model_class;

		return array_keys($model->to_array());

	} // end wp_cli_extra_parameters;

	/**
	 * Returns the list of default fields, based on the table schema.
	 *
	 * @since 2.0.0
	 * @return array List of the schema columns.
	 */
	public function wp_cli_get_fields() {

		$schema = $this->model_class::get_schema();

		return array_column($schema, 'name');

	} // end wp_cli_get_fields;

	/**
	 * Returns a specific item.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args        Positional arguments passed. ID expected.
	 * @param array $array_assoc Assoc arguments passed.
	 */
	public function wp_cli_get_item($args, $array_assoc) {

		$item = $this->model_class::get_by_id($args[0]);

		if (empty($item)) {

			\WP_CLI::error('Invalid ID.');

		} // end if;

		$fields = (!empty($array_assoc['fields'])) ? $array_assoc['fields'] : $this->wp_cli_get_fields();

		$formatter = new \WP_CLI\Formatter($array_assoc, $fields);

		$formatter->display_item($item->to_array());

	} // end wp_cli_get_item;

	/**
	 * Returns a list of items.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args        Positional arguments passed. ID expected.
	 * @param array $array_assoc Assoc arguments passed.
	 */
	public function wp_cli_get_items($args, $array_assoc) {

		$fields = (!empty($array_assoc['fields'])) ? $array_assoc['fields'] : $this->wp_cli_get_fields();

		unset($array_assoc['fields']);

		$items = $this->model_class::query($array_assoc);

		$items = array_map(function($item) {

			return $item->to_array();

		}, $items);

		\WP_CLI\Utils\format_items($array_assoc['format'], $items, $fields);

	} // end wp_cli_get_items;

	/**
	 * Creates an item.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args        Positional arguments passed. ID expected.
	 * @param array $array_assoc Assoc arguments passed.
	 */
	public function wp_cli_create_item($args, $array_assoc) {

		$item = new $this->model_class($array_assoc);

		$success = $item->save();

		if ($success === true) {

			$item_id = $item->get_id();

			if (!empty($array_assoc['porcelain'])) {

				\WP_CLI::line($item_id);

			} else {

				$message = sprintf('Item created with ID %d', $item_id);

				\WP_CLI::success($message);

			} // end if;

		} else {

			\WP_CLI::error($success);

		} // end if;

	} // end wp_cli_create_item;

	/**
	 * Updates an item.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args        Positional arguments passed. ID expected.
	 * @param array $array_assoc Assoc arguments passed.
	 */
	public function wp_cli_update_item($args, $array_assoc) {

		$item = $this->model_class::get_by_id($args[0]);

		if (empty($item)) {

			\WP_CLI::error('Invalid ID.');

		} // end if;

		$porcelain = false;

		if (!empty($array_assoc['porcelain'])) {

			$porcelain = true;

			unset($array_assoc['porcelain']);

		} // end if;

		$params = $array_assoc;

		foreach ($params as $param => $value) {

			$set_method = "set_{$param}";

			if ($param === 'meta') {

				$item->update_meta_batch($value);

			} elseif (method_exists($item, $set_method)) {

				call_user_func(array($item, $set_method), $value);

			} else {

				$error_message = sprintf(
					/* translators: 1. Object class name; 2. Set method name */
					__('The %1$s object does not have a %2$s method', 'wp-ultimo'),
					get_class($item),
					$set_method
				);

				\WP_CLI::error($error_message);

			} // end if;

		} // end foreach;

		$success = $item->save();

		if ($success) {

			$item_id = $item->get_id();

			if ($porcelain) {

				\WP_CLI::line($item_id);

			} else {

				$message = sprintf('Item updated with ID %d', $item_id);

				\WP_CLI::success($message);

			} // end if;

		} else {

			\WP_CLI::error('Unexpected error. The item was not updated.');

		} // end if;

	} // end wp_cli_update_item;

	/**
	 * Deletes an item.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Positional arguments passed. ID expected.
	 */
	public function wp_cli_delete_item($args) {

		$item = $this->model_class::get_by_id($args[0]);

		if (empty($item)) {

			\WP_CLI::error('Invalid ID.');

		} // end if;

		$success = $item->delete();

		if (is_wp_error($success) || !$success) {

			\WP_CLI::error('Unexpected error. The item was not deleted.');

		} else {

			\WP_CLI::success('Item deleted.');

		} // end if;

	} // end wp_cli_delete_item;

} // end trait WP_CLI;
