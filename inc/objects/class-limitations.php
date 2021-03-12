<?php
/**
 * The Site model.
 *
 * @package WP_Ultimo
 * @subpackage Models
 * @since 2.0.0
 */

namespace WP_Ultimo\Objects;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Site model class. Implements the Base Model.
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
	 * Limitation modules.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $modules = array();

	/**
	 * Holds the list of allowed plugins on this site.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $allowed_plugins = array();

	/**
	 * Holds the list of allowed themes on this site.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $allowed_themes = array();

	/**
	 * Array containing the number of posts allowed for each post type.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $post_type_quotas = array();

	/**
	 * Holds the list of allowed post types.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $allowed_post_types = array();

	/**
	 * Limitations for user roles.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $user_role_quotas = array();

	/**
	 * Allowed user roles.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $allowed_user_roles = array();

	/**
	 * Holds the value of the disk space limitation for this site.
	 *
	 * @since 2.0.0
	 * @var int|bool
	 */
	protected $disk_space = false;

	/**
	 * Holds the limit for unique visits in a month
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $allowed_visits;

	/**
	 * Created the object.
	 *
	 * @since 2.0.0
	 *
	 * @param array $limitations Assoc array containing the limitations to set.
	 */
	public function __construct($limitations = array()) {

		if (!function_exists('get_editable_roles')) {

			require_once(ABSPATH . '/wp-admin/includes/user.php'); // We need to load this to have get_editable_roles.

		} // end if;

		$limitations = wp_parse_args($limitations, array());

		$this->attributes($limitations);

	} // end __construct;

	/**
	 * Sets the attributes of the model using the setters available.
	 *
	 * @since 2.0.0
	 *
	 * @param array $atts Key-value pairs of model attributes.
	 * @return \WP_Ultimo\Models\Base_Model
	 */
	public function attributes($atts) {

		foreach ($atts as $key => $value) {

			$early_setters = array(
				'allowed_plugins',
				'allowed_themes',
			);

			/*
			 * For plugins and themes, we need them right away.
			 */
			if (in_array($key, $early_setters, true)) {

				call_user_func(array($this, "set_$key"), $value);

				continue;

			} // end if;

			if (method_exists($this, "set_$key")) {
				/*
				 * Delegate the setup to a later hook to prevent errors.
				 */
				if (did_action('plugins_loaded')) {

					call_user_func(array($this, "set_$key"), $value);

				} else {

					add_action('plugins_loaded', function() use ($key, $value) {

						call_user_func(array($this, "set_$key"), $value);

					}, 10);

				} // end if;

			} // end if;

		} // end foreach;

		return $this;

	} // end attributes;

	/**
	 * Returns if we have limitations or not.
	 *
	 * This is used to temporarily disable limitations for testing and such.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_limitations() {

		$has_limitations = !empty($this->modules) || !empty($this->allowed_plugins) || !empty($this->allowed_themes);

		/**
		 * Allows plugin developers to modify the limitations.
		 *
		 * @since 2.0.0
		 *
		 * @param bool $has_limitations The current value.
		 * @param Limitations $limitations This object.
		 *
		 * @return bool
		 */
		return apply_filters('wu_has_limitations', $has_limitations, $this);

	} // end has_limitations;

	/**
	 * Set holds the list of allowed plugins on this site.
	 *
	 * Only save behaviors different than default to shave some bits of the database.
	 *
	 * @since 2.0.0
	 * @param array $allowed_plugins Holds the list of allowed plugins on this site.
	 * @return void
	 */
	public function set_allowed_plugins($allowed_plugins) {

		$this->allowed_plugins = array_filter($allowed_plugins, function($behavior) {

			return $behavior !== 'default';

		});

	} // end set_allowed_plugins;

	/**
	 * Returns the list of allowed plugins.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_allowed_plugins() {

		return $this->allowed_plugins;

	} // end get_allowed_plugins;

	/**
	 * Returns the list of allowed post types.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_allowed_post_types() {

		$default = array_map('__return_true', get_post_types());

		return wp_parse_args($this->allowed_post_types, $default);

	} // end get_allowed_post_types;

	/**
	 * Sets the the list of allowed post types.
	 *
	 * @since 2.0.0
	 * @param array $allowed_post_types The list of allowed post types.
	 * @return void
	 */
	public function set_allowed_post_types($allowed_post_types) {

		$default = array_map('__return_false', get_post_types());

		$this->allowed_post_types = wp_parse_args($allowed_post_types, $default);

	} // end set_allowed_post_types;

	/**
	 * Returns the list of allowed user roles.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_allowed_user_roles() {

		$default = array_map('__return_true', get_editable_roles());

		return wp_parse_args($this->allowed_user_roles, $default);

	} // end get_allowed_user_roles;

	/**
	 * Sets the the list of allowed user roles.
	 *
	 * @since 2.0.0
	 * @param array $allowed_user_roles The list of allowed post types.
	 * @return void
	 */
	public function set_allowed_user_roles($allowed_user_roles) {

		$default = array_map('__return_false', get_editable_roles());

		$this->allowed_user_roles = wp_parse_args($allowed_user_roles, $default);

	} // end set_allowed_user_roles;

	/**
	 * Set holds the list of allowed themes on this site.
	 *
	 * Only save behaviors different than default to shave some bits of the database.
	 *
	 * @since 2.0.0
	 * @param array $allowed_themes Holds the list of allowed themes on this site.
	 * @return void
	 */
	public function set_allowed_themes($allowed_themes) {

		$this->allowed_themes = array_filter($allowed_themes, function($behavior) {

			return $behavior !== 'default';

		});

	} // end set_allowed_themes;

	/**
	 * Returns the list of allowed themes.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_allowed_themes() {

		return $this->allowed_themes;

	} // end get_allowed_themes;

	/**
	 * Returns the number of posts allowed for a given post type.
	 *
	 * @since 2.0.0
	 *
	 * @param string $post_type The post type to check against.
	 * @return int
	 */
	public function get_post_type_quota($post_type) {

		return isset($this->post_type_quotas[$post_type]) ? $this->post_type_quotas[$post_type] : false;

	} // end get_post_type_quota;

	/**
	 * Returns the number of users allowed for a given user role.
	 *
	 * @since 2.0.0
	 *
	 * @param string $user_role The user role to check against.
	 * @return int
	 */
	public function get_user_role_quota($user_role) {

		return isset($this->user_role_quotas[$user_role]) ? $this->user_role_quotas[$user_role] : false;

	} // end get_user_role_quota;

	/**
	 * Transform the object into an assoc array.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function to_array() {

		return get_object_vars($this);

	} // end to_array;

	/**
	 * Returns the plugin behavior.
	 *
	 * Can be one of:
	 * - default
	 * - activate
	 * - available
	 * - force_activation
	 * - force_deactivation
	 *
	 * @since 2.0.0
	 *
	 * @param string $plugin_path Plugin path.
	 * @return string
	 */
	public function get_plugin_behavior($plugin_path) {

		$plugins = $this->get_allowed_plugins();

		$behavior = isset($plugins[$plugin_path]) ? $plugins[$plugin_path] : 'default';

		return $behavior;

	} // end get_plugin_behavior;

	/**
	 * Returns the theme behavior.
	 *
	 * Can be one of:
	 * - default
	 * - activate
	 * - available
	 * - hide
	 *
	 * @since 2.0.0
	 *
	 * @param string $theme_path The theme stylesheet name.
	 * @return string
	 */
	public function get_theme_behavior($theme_path) {

		$themes = $this->get_allowed_themes();

		$behavior = isset($themes[$theme_path]) ? $themes[$theme_path] : 'default';

		return $behavior;

	} // end get_theme_behavior;

	/**
	 * Checks if a plugin is saved with a given behavior.
	 *
	 * @since 2.0.0
	 *
	 * @param string       $plugin_path The plugin to test.
	 * @param string|array $behavior Single behavior or list of behaviors to check.
	 * @return bool
	 */
	public function plugin_has_behavior($plugin_path, $behavior) {

		$behaviors = (array) $behavior;

		$plugin_behavior = $this->get_plugin_behavior($plugin_path);

		return in_array($plugin_behavior, $behaviors, true);

	} // end plugin_has_behavior;

	/**
	 * Checks if a theme is saved with a given behavior.
	 *
	 * @since 2.0.0
	 *
	 * @param string       $theme_path The theme to test.
	 * @param string|array $behavior Single behavior or list of behaviors to check.
	 * @return bool
	 */
	public function theme_has_behavior($theme_path, $behavior) {

		$behaviors = (array) $behavior;

		$theme_behavior = $this->get_theme_behavior($theme_path);

		return in_array($theme_behavior, $behaviors, true);

	} // end theme_has_behavior;

	/**
	 * Returns the forced active theme, depending on the permissions.
	 *
	 * @since 2.0.0
	 * @return string|false
	 */
	public function get_forced_active_theme() {

		$themes = $this->get_allowed_themes();

		foreach ($themes as $theme_stylesheet => $behavior) {

			if ($this->theme_has_behavior($theme_stylesheet, 'activate') === true) {

				return $theme_stylesheet;

			} // end if;

		} // end foreach;

		return false;

	} // end get_forced_active_theme;

	/**
	 * Get the limit for unique visits in a month.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_allowed_visits() {

		return (int) $this->allowed_visits;

	} // end get_allowed_visits;

	/**
	 * Set holds the limit for unique visits in a month.
	 *
	 * @since 2.0.0
	 * @param int $visits Holds the limit for unique visits in a month.
	 * @return void
	 */
	public function set_allowed_visits($visits) {

		$this->allowed_visits = (int) $visits;

	} // end set_allowed_visits;

	/**
	 * Set array containing the number of posts allowed for each post type.
	 *
	 * @since 2.0.0
	 * @param array $post_type_quotas Array containing the number of posts allowed for each post type.
	 * @return void
	 */
	public function set_post_type_quotas($post_type_quotas) {

		$this->post_type_quotas = $post_type_quotas;

	} // end set_post_type_quotas;

	/**
	 * Get array containing the number of posts allowed for each post type.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_post_type_quotas() {

		return $this->post_type_quotas;

	} // end get_post_type_quotas;

	/**
	 * Get limitation modules.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_modules() {

		$default_modules = apply_filters('wu_limitations_modules', array(
			'post_types' => false,
			'visits'     => false,
			'users'      => false,
			'sites'      => false,
			'disk_space' => false,
		));

		return wep_parse_args($this->modules, $default_modules);

	} // end get_modules;

	/**
	 * Checks iof a particular limitation is being set on the entity.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key The property key. E.g. modules.
	 * @param string $value The property values.
	 * @return bool
	 */
	public function exists($key, $value = false) {

		if (!property_exists($this, $key)) {

			return false;

		} // end if;

		$property_value = $this->{$key};

		if (is_array($property_value)) {

			return in_array($value, $property_value) || in_array($value, array_keys($property_value)); // phpcs:ignore

		} // end if;

		return $key == $value || !is_null($property_value); // phpcs:ignore

	} // end exists;

	/**
	 * Checks if a limitation module is enabled.
	 *
	 * @since 2.0.0
	 *
	 * @param string $module The name of the module.
	 * @return boolean
	 */
	public function is_module_enabled($module) {

		return wu_get_isset($this->modules, $module, false);

	} // end is_module_enabled;

	/**
	 * Set limitation modules.
	 *
	 * @since 2.0.0
	 * @param array $modules Limitation modules.
	 * @return void
	 */
	public function set_modules($modules) {

		$this->modules = $modules;

	} // end set_modules;

	/**
	 * Get limitations for user roles.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_user_role_quotas() {

		return $this->user_role_quotas;

	} // end get_user_role_quotas;

	/**
	 * Set limitations for user roles..
	 *
	 * @since 2.0.0
	 * @param array $user_role_quotas Limitations for user roles.
	 * @return void
	 */
	public function set_user_role_quotas($user_role_quotas) {

		$this->user_role_quotas = $user_role_quotas;

	} // end set_user_role_quotas;

	/**
	 * Get holds the value of the disk space limitation for this site.
	 *
	 * @since 2.0.0
	 * @return int|bool
	 */
	public function get_disk_space() {

		return ((int) $this->disk_space);

	} // end get_disk_space;

	/**
	 * Set holds the value of the disk space limitation for this site.
	 *
	 * @since 2.0.0
	 * @param int|bool $disk_space Holds the value of the disk space limitation for this site.
	 * @return void
	 */
	public function set_disk_space($disk_space) {

		$this->disk_space = $disk_space;

	} // end set_disk_space;

	/**
	 * Static method to return limitations in very early stages of the WordPress lifecycle.
	 *
	 * @since 2.0.0
	 *
	 * @param string $slug Slug of the model.
	 * @param int    $id ID of the model.
	 * @return \WP_Ultimo\Objects\Limitations
	 */
	public static function early_get_limitations($slug = 'membership', $id) {

		$cache = static::$limitations_cache;

		$key = sprintf('%s-%s', $slug, $id);

		if (isset($cache[$key])) {

			return $cache[$key];

		} // end if;

		global $wpdb;

		$limitations = new Limitations;

		$table_name = "{$wpdb->base_prefix}wu_{$slug}meta";

		$sql = $wpdb->prepare("SELECT meta_value FROM {$table_name} WHERE meta_key = 'wu_limitations' AND  wu_{$slug}_id = %d LIMIT 1", $id); // phpcs:ignore

		$results = $wpdb->get_var($sql); // phpcs:ignore

		if (!empty($results)) {

			$limitations = unserialize($results);

		} // end if;

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
	public static function remove_limitations($slug = 'membership', $id) {

		if ($slug === 'site') {

			wu_get_site($id)->update_meta('wu_limitations', array());

			return;

		} // end if;

		global $wpdb;

		$table_name = "{$wpdb->base_prefix}wu_{$slug}meta";

		$sql = $wpdb->prepare("DELETE FROM {$table_name} WHERE meta_key = 'wu_limitations' AND  wu_{$slug}_id = %d LIMIT 1", $id); // phpcs:ignore

		$wpdb->get_var($sql); // phpcs:ignore

	} // end remove_limitations;

	/**
	 * Compares two arrays and returns the diff, recursively.
	 *
	 * This is frequently used to compare Limitation sets so we can have
	 * a waterfall of limitations coming from the product, to the
	 * membership, down to the site.
	 *
	 * @since 2.0.0
	 *
	 * @param array $array1 Array 1.
	 * @param array $array2 Array 2.
	 * @return array
	 */
	public static function array_recursive_diff($array1, $array2) {

		$arr_return = array();

		$array1 = (array) $array1;
		$array2 = (array) $array2;

		foreach ($array1 as $key => $value) {

			if (array_key_exists($key, $array2)) {

				if (is_array($value)) {

					$array_recursive_diff = self::array_recursive_diff($value, $array2[$key]);

					if (count($array_recursive_diff)) {

						$arr_return[$key] = $array_recursive_diff;

					} // end if;

				} else {

					if ($value != $array2[$key]) { // phpcs:ignore

						$arr_return[$key] = $value;

					} // end if;

				} // end if;

			} else {

				$arr_return[$key] = $value;

			} // end if;

		} // end foreach;

		return $arr_return;

	} // end array_recursive_diff;

} // end class Limitations;
