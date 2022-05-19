<?php
/**
 * Reflection Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.11
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Dependencies\phpDocumentor\Reflection\DocBlockFactory;

/**
 * Creates the REST API schema blueprint for an object based on setters.
 *
 * @since 2.0.12
 *
 * @param string $class_name The model/object class name.
 * @return array
 */
function wu_reflection_parse_object_arguments($class_name) {

	$base_schema = wu_reflection_parse_arguments_from_setters($class_name, true);

	if (wu_are_code_comments_available() === false) {
		/*
		 * @todo add a logger.
		 */
		return $base_schema;

	} // end if;

	$reflector = new \ReflectionClass($class_name);

	$doc_block_factory = DocBlockFactory::createInstance();

	$arguments = array();

	/**
	 * Tries to fetch the database schema, if one exists.
	 */
	$db_schema = method_exists($class_name, 'get_schema') ? $class_name::get_schema() : false;

	foreach ($base_schema as $column) {

		try {

			$doc_block = $doc_block_factory->create($reflector->getMethod("set_{$column['name']}")->getDocComment());

		} catch (\Throwable $e) {

			/**
			 * Something went wrong trying to parse the doc-blocks.
			 */
			continue;

		} // end try;

		$param = $doc_block->getTagsByName('param');

		if (isset($param[0]) && is_object($param[0])) {

			$arguments[$column['name']] = array(
				'description' => (string) $param[0]->getDescription(),
				'type'        => (string) $param[0]->getType(),
				'required'    => false, // Actual value set later
			);

			if ($db_schema) {

				$db_column = wu_array_find_first_by($db_schema, 'name', $column['name']);

				if ($db_column && wu_get_isset($db_column, 'type') && preg_match('/^ENUM/', $db_column['type'])) {

					preg_match_all("/'(.*?)'/", $db_column['type'], $matches);

					if (isset($matches[1])) {

						$arguments[$column['name']]['enum'] = array_map('strtolower', $matches[1]);

					} // end if;

				} // end if;

			} // end if;

			$option = $doc_block->getTagsByName('options');

			if (isset($option[0])) {

				$description = (string) $option[0]->getDescription();

				if (strpos($description, '\\WP_Ultimo\\') !== false) {

					$enum_options = new $description;

					$arguments[$column['name']]['enum'] = array_map('strtolower', array_keys(array_flip($enum_options->get_options())));

				} else {

					$arguments[$column['name']]['enum'] = explode(',', strtolower($description));

				} // end if;

			} // end if;

		} // end if;

	} // end foreach;

	return $arguments;

} // end wu_reflection_parse_object_arguments;

/**
 * Use php reflection to generate the documentation for the REST API.
 *
 * @since 2.0.11
 *
 * @param string  $class_name The class name of the endpoint object.
 * @param boolean $return_schema If we should return the schame or just a key => value.
 * @return array
 */
function wu_reflection_parse_arguments_from_setters($class_name, $return_schema = true) {

	$arguments = array();

	foreach (get_class_methods($class_name) as $setter_name) {

		if (preg_match('/^set_/', $setter_name)) {

			$argument = str_replace('set_', '', $setter_name);

			if ($return_schema) {

				$arguments[] = array(
					'name' => $argument,
					'type' => '',
				);

			} else {

				$arguments[] = $argument;

			} // end if;

		} // end if;

	} // end foreach;

	return $arguments;

} // end wu_reflection_parse_arguments_from_setters;
