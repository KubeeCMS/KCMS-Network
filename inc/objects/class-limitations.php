<?php
/**
 * Limitation manager.
 *
 * This class centralizes the limitation modules.
 *
 * @package WP_Ultimo
 * @subpackage Limitations
 * @since 2.0.0
 */

namespace WP_Ultimo\Objects;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Limitation manager.
 *
 * This class centralizes the limitation modules.
 *
 * @since 2.0.0
 */
class Limitations {

	/**
	 * Caches early limitation queries to prevent
	 * to many database hits.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	static $limitations_cache = array();

	/**
	 * Version of the limitation schema.
	 *
	 * @since 2.0.0
	 * @var integer
	 */
	protected $version = 2;

	/**
	 * Limitation modules.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $modules = array();

	/**
	 * Constructs the limitation class with module data.
	 *
	 * @since 2.0.0
	 *
	 * @param array $modules_data Array of modules data.
	 */
	public function __construct($modules_data = array()) {

		$this->build_modules($modules_data);

	} // end __construct;

	/**
	 * Returns the module via magic getter.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name The module name.
	 * @return \WP_Ultimo\Limitations\Limit
	 */
	public function __get($name) {

		$module = wu_get_isset($this->modules, $name, false);

		if ($module === false) {

			$repo = self::repository();

			$class_name = wu_get_isset($repo, $name, false);

			if (class_exists($class_name)) {

				$module = new $class_name(array());

				$this->modules[$name] = $module;

				return $module;

			} // end if;

		} // end if;

		return $module;

	} // end __get;

	/**
	 * Prepare to serialization.
	 *
	 * @see requires php 7.3
	 * @since 2.0.0
	 * @return array
	 */
	public function __serialize() { // phpcs:ignore

		return $this->to_array();

	} // end __serialize;

	/**
	 * Handles un-serialization.
	 *
	 * @since 2.0.0
	 *
	 * @see requires php 7.3
	 * @param array $modules_data Array of modules data.
	 * @return void
	 */
	public function __unserialize($modules_data) { // phpcs:ignore

		$this->build_modules($modules_data);

	} // end __unserialize;

	/**
	 * Builds the module list based on the module data.
	 *
	 * @since 2.0.0
	 *
	 * @param array $modules_data Array of modules data.
	 * @return self
	 */
	public function build_modules($modules_data) {

		foreach ($modules_data as $type => $data) {

			$module = self::build($data, $type);

			if ($module) {

				$this->modules[$type] = $module;

			} // end if;

		} // end foreach;

		return $this;

	} // end build_modules;

	/**
	 * Build a module, based on the data.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $data The module data.
	 * @param string $module_name The module_name.
	 * @return false|\WP_Ultimo\Limitations\Limit
	 */
	static public function build($data, $module_name) {

		$class = wu_get_isset(self::repository(), $module_name);

		if (class_exists($class)) {

			return new $class($data);

		} // end if;

		return false;

	} // end build;

	/**
	 * Checks if a limitation model exists in this limitations.
	 *
	 * @since 2.0.0
	 *
	 * @param string $module The module name.
	 * @return bool
	 */
	public function exists($module) {

		return wu_get_isset($this->modules, $module, false);

	} // end exists;

	/**
	 * Checks if we have any limitation modules setup at all.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_limitations() {

		$has_limitations = false;

		foreach ($this->modules as $module) {

			if ($module->is_enabled()) {

				return true;

			} // end if;

		} // end foreach;

		return $has_limitations;

	} // end has_limitations;

	/**
	 * Checks if a particular module is enabled.
	 *
	 * @since 2.0.0
	 *
	 * @param string $module_name Module name.
	 * @return boolean
	 */
	public function is_module_enabled($module_name) {

		$module = $this->{$module_name};

		return $module ? $module->is_enabled() : false;

	}  // end is_module_enabled;

	/**
	 * Merges limitations from other entities.
	 *
	 * This is what we use to combine different limitations from
	 * different sources. For example: we override the limitations
	 * of site with restrictions coming from the membership,
	 * products, etc.
	 *
	 * @since 2.0.0
	 *
	 * @param array ...$limitations Limitation arrays.
	 * @return self
	 */
	public function merge(...$limitations) {

		$results = $this->to_array();

		foreach ($limitations as $limitation) {

			if (is_a($limitation, '\\WP_Ultimo\\Objects\\Limitations')) {

				$limitation = $limitation->to_array();

			} // end if;

			if (!is_array($limitation)) {

				continue;

			} // end if;

			$results = wu_array_merge_recursive_distinct($limitation, $results);

		} // end foreach;

		return new self($results);

	} // end merge;

	/**
	 * Converts the limitations list to an array.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function to_array() {

		return json_decode(json_encode($this->modules), true);

	} // end to_array;

	/**
	 * Static method to return limitations in very early stages of the WordPress lifecycle.
	 *
	 * @since 2.0.0
	 *
	 * @param string $slug Slug of the model.
	 * @param int    $id ID of the model.
	 * @return \WP_Ultimo\Objects\Limitations
	 */
	public static function early_get_limitations($slug, $id) {

		$wu_prefix = 'wu_';

		/*
		 * Reset the slug and prefixes
		 * for the native tables of blogs.
		 */
		if ($slug === 'site') {

			$slug = 'blog';

			$wu_prefix = '';

		} // end if;

		$cache = static::$limitations_cache;

		$key = sprintf('%s-%s', $slug, $id);

		if (isset($cache[$key])) {

			return $cache[$key];

		} // end if;

		global $wpdb;

		$limitations = array();

		$table_name = "{$wpdb->base_prefix}{$wu_prefix}{$slug}meta";

		$sql = $wpdb->prepare("SELECT meta_value FROM {$table_name} WHERE meta_key = 'wu_limitations' AND  {$wu_prefix}{$slug}_id = %d LIMIT 1", $id); // phpcs:ignore

		$results = $wpdb->get_var($sql); // phpcs:ignore

		if (!empty($results)) {

			$limitations = unserialize($results);

		} // end if;

		/*
		 * Caches the results.
		 */
		static::$limitations_cache[$key] = $limitations;

		return $limitations;

	} // end early_get_limitations;

	/**
	 * Delete limitations.
	 *
	 * @since 2.0.0
	 *
	 * @param string $slug The slug of the model.
	 * @param int    $id The id of the meta id.
	 * @return void
	 */
	public static function remove_limitations($slug, $id) {

		global $wpdb;

		$wu_prefix = 'wu_';

		/*
		 * Site apis are already available,
		 * so no need to use low-level sql calls.
		 */
		if ($slug === 'site') {

			$wu_prefix = '';

			$slug = 'blog';

		} // end if;

		$table_name = "{$wpdb->base_prefix}{$wu_prefix}{$slug}meta";

		$sql = $wpdb->prepare("DELETE FROM {$table_name} WHERE meta_key = 'wu_limitations' AND  {$wu_prefix}{$slug}_id = %d LIMIT 1", $id); // phpcs:ignore

		$wpdb->get_var($sql); // phpcs:ignore

	} // end remove_limitations;

	/**
	 * Returns an empty permission set, with modules.
	 *
	 * @since 2.0.0
	 * @return self
	 */
	static public function get_empty() {

		$limitations = new self();

		foreach (array_keys(self::repository()) as $module_name) {

			$limitations->{$module_name};

		} // end foreach;

		return $limitations;

	} // end get_empty;

	/**
	 * Repository of the limitation modules.
	 *
	 * @see wu_register_limit_module()
	 *
	 * @since 2.0.0
	 * @return array
	 */
	static public function repository() {

		$classes = array(
			'post_types'         => \WP_Ultimo\Limitations\Limit_Post_Types::class,
			'plugins'            => \WP_Ultimo\Limitations\Limit_Plugins::class,
			'sites'              => \WP_Ultimo\Limitations\Limit_Sites::class,
			'themes'             => \WP_Ultimo\Limitations\Limit_Themes::class,
			'visits'             => \WP_Ultimo\Limitations\Limit_Visits::class,
			'disk_space'         => \WP_Ultimo\Limitations\Limit_Disk_Space::class,
			'users'              => \WP_Ultimo\Limitations\Limit_Users::class,
			'site_templates'     => \WP_Ultimo\Limitations\Limit_Site_Templates::class,
			'domain_mapping'     => \WP_Ultimo\Limitations\Limit_Domain_Mapping::class,
			'customer_user_role' => \WP_Ultimo\Limitations\Limit_Customer_User_Role::class,
		);

		return apply_filters('wu_limit_classes', $classes);

	} // end repository;

} // end class Limitations;
