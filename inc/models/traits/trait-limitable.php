<?php
/**
 * A trait to handle limitable models.
 *
 * @package WP_Ultimo
 * @subpackage Models\Traits
 * @since 2.0.0
 */

namespace WP_Ultimo\Models\Traits;

use \WP_Ultimo\Objects\Limitations;

/**
 * Singleton trait.
 */
trait Limitable {

	/**
	 * Merges limitations from site, membership, and product.
	 *
	 * Ok, brace yourself cus this one is kinda of hard to grasp:
	 *
	 * This method allows us to create a cascading waterfall of permissions and limitations.
	 * 1. First, we look on the product to see what are the limitations there.
	 *    This is where we'll find the plugins, themes, and post types allowed most of the time.
	 * 2. Then, we search for a membership and see if the limitations where overridden there.
	 * 3. Lastly, we check the site itself to see if we are overridden the permissions there.
	 *
	 * The result is a new Limitations object that handles these overrides gracefully.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Objects\Limitations $default_limitations The site limitations.
	 * @return \WP_Ultimo\Objects\Limitations
	 */
	private function waterfall_limitations($default_limitations) {

		$model = $this->model;

		if ($model === 'product') {

			return $default_limitations;

		} // end if;

		// Set base
		$membership_limitations = new Limitations;
		$product_limitations    = new Limitations;

		if ($model === 'site') {

			$membership_limitations = Limitations::early_get_limitations('membership', $this->get_membership_id());

			/**
			 * Now we need to get the product limitations.
			 */
			$membership = $this->get_membership();

			if ($membership) {

				$product_limitations = Limitations::early_get_limitations('product', $membership->get_plan_id());

			} // end if;

		} elseif ($model === 'membership') {

			$product_limitations = Limitations::early_get_limitations('product', $this->get_plan_id());

		} // end if;

		return new Limitations(array_replace_recursive(
			array_filter($product_limitations->to_array()),
			array_filter($membership_limitations->to_array()),
			array_filter($default_limitations->to_array())
		));

	} // end waterfall_limitations;

	/**
	 * Returns the limitations of this particular blog.
	 *
	 * @since 2.0.0
	 * @param bool $waterfall If we should construct the limitations object recursively.
	 * @return \WP_Ultimo\Objects\Limitations
	 */
	public function get_limitations($waterfall = true) {

		$cache_key = $waterfall ? '_composite_limitations' : '_limitations';

		$cache_key = $this->get_id() . $cache_key;

		$cached_version = wp_cache_get($cache_key, $this->model . 's');

		if (!empty($cached_version)) {

			return $cached_version;

		} // end if;

		if (!is_array($this->meta)) {

			$this->meta = array();

		} // end if;

		$this->meta['wu_limitations'] = $this->get_meta('wu_limitations');

		if (!is_a($this->meta['wu_limitations'], 'WP_Ultimo\Objects\Limitations')) {

			$this->meta['wu_limitations'] = new Limitations();

		} // end if;

		$limitations = $this->meta['wu_limitations'];

		if ($waterfall) {

			$limitations = $this->waterfall_limitations($this->meta['wu_limitations']);

		} // end if;

		wp_cache_set($cache_key, $limitations, $this->model . 's');

		return $limitations;

	} // end get_limitations;

	/**
	 * Checks if this site has limitations or not.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_limitations() {

		return $this->get_limitations()->has_limitations();

	} // end has_limitations;

	/**
	 * Checks if a particular module is being limited.
	 *
	 * @since 2.0.0
	 *
	 * @param string $module Module to check.
	 * @return boolean
	 */
	public function has_module_limitation($module) {

		return $this->get_limitations()->is_module_enabled($module);

	} // end has_module_limitation;

	/**
	 * Checks if a given plugin is allowed on this site.
	 *
	 * @since 2.0.0
	 *
	 * @param string $plugin_path Plugin slug.
	 * @return boolean
	 */
	public function is_plugin_allowed($plugin_path) {

		return $this->get_limitations()->plugin_has_behavior($plugin_path, array(
			'default',
			'activate',
			'force_activation',
			'make_available',
		));

	} // end is_plugin_allowed;

	/**
	 * Checks if a given theme is allowed on this site.
	 *
	 * @since 2.0.0
	 *
	 * @param string $theme_path Theme theme slug.
	 * @return boolean
	 */
	public function is_theme_allowed($theme_path) {

		return $this->get_limitations()->theme_has_behavior($theme_path, array(
			'default',
			'activate',
			'available',
		));

	} // end is_theme_allowed;

	/**
	 * Checks if we need to display a particular quota or not.
	 *
	 * @since 2.0.0
	 *
	 * @param string $quota The quota to check.
	 * @return boolean
	 */
	public function should_display_quota($quota) {

		return true;

	} // end should_display_quota;

	/**
	 * Returns the site limits for a specific entity.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type The quota to return.
	 * @return mixed
	 */
	public function get_quota($type = 'post') {

		if ($type === 'visits') {

			$quota = $this->get_limitations()->get_allowed_visits();

		} elseif ($type === 'disk_space') {

			$quota = $this->get_limitations()->get_disk_space();

		} else {

			$quota = $this->get_limitations()->get_post_type_quota($type);

		} // end if;

		/**
		 * Allow plugins developers to filter the quota values.
		 *
		 * @since 1.X
		 * @param int $quota Existing quota.
		 * @param string $type The quota type.
		 * @param array $deprecated Deprecated value.
		 * @param WP_Ultimo\Models\Base_Model $this The current model.
		 */
		return apply_filters('wu_plan_get_quota', $quota, $type, array(), $this);

	} // end get_quota;

	/**
	 * Checks if a given post type is supported on this site.
	 *
	 * @since 2.0.0
	 *
	 * @param string $post_type The post type to check against.
	 * @return boolean
	 */
	public function is_post_type_supported($post_type) {

		$allowed_post_types = $this->get_limitations()->get_allowed_post_types();

		return in_array($post_type, $allowed_post_types, true);

	} // end is_post_type_supported;

	/**
	 * Proxy method to retrieve the allowed post types.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_allowed_post_types() {

		return $this->get_limitations()->get_allowed_post_types();

	} // end get_allowed_post_types;

	/**
	 * Returns all post type quotas.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_quotas() {

		return $this->get_limitations()->get_post_type_quotas();

	} // end get_quotas;

	/**
	 * Returns all user role quotas.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_user_role_quotas() {

		return $this->get_limitations()->get_user_role_quotas();

	} // end get_user_role_quotas;

	/**
	 * Proxy method to retrieve the allowed user roles.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_allowed_user_roles() {

		return $this->get_limitations()->get_allowed_user_roles();

	} // end get_allowed_user_roles;

	/**
	 * Makes sure we save limitations when we are supposed to.
	 *
	 * This is called on the handle_save method of the inc/admin-pages/class-edit-admin-page.php
	 * for all models that have the trait Limitable.
	 *
	 * @see inc/admin-pages/class-edit-admin-page.php
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_limitations() {

		$new_limitations = $this->get_limitations(false)->attributes(array(
			'allowed_plugins'    => wu_request('allowed_plugins', array()),
			'allowed_themes'     => wu_request('allowed_themes', array()),
			'allowed_visits'     => wu_request('allowed_visits', array()),
			'allowed_post_types' => wu_request('allowed_post_types', array()),
			'post_type_quotas'   => wu_request('post_type_quotas', array()),
			'allowed_user_roles' => wu_request('allowed_user_roles', array()),
			'user_role_quotas'   => wu_request('user_role_quotas', array()),
			'modules'            => wu_request('modules', array()),
			'disk_space'         => wu_request('disk_space', ''),
		));

		if ($this->model === 'product') {

			return; // Products do not need recursiveness checking.

		} // end if;

		$current_limitations = $this->get_limitations();

		/*
		 * Compare arrays to only save what's different.
		 */
		$diff = Limitations::array_recursive_diff($new_limitations->to_array(), $current_limitations->to_array());

		/*
		 * Set the new permissions, based on the diff.
		 */
		$this->get_limitations(false)->attributes($diff);

	} // end handle_limitations;

} // end trait Limitable;
