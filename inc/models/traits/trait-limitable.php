<?php
/**
 * A trait to handle limitable models.
 *
 * @package WP_Ultimo
 * @subpackage Models\Traits
 * @since 2.0.0
 */

namespace WP_Ultimo\Models\Traits;

use \WP_Ultimo\Database\Sites\Site_Type;
use \WP_Ultimo\Objects\Limitations;

/**
 * Singleton trait.
 */
trait Limitable {

	/**
	 * Internal limitations cache.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $_limitations = array();

	/**
	 * List of limitations that need to be merged.
	 *
	 * Every model that is limitable (imports this trait)
	 * needs to declare explicitly the limitations that need to be
	 * merged. This allows us to chain the merges, and gives us
	 * a final list of limitations at the end of the process.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	abstract public function limitations_to_merge();

	/**
	 * Returns the limitations of this particular blog.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $waterfall If we should construct the limitations object recursively.
	 * @param bool $skip_self If we should skip the current limitations.
	 * @return \WP_Ultimo\Objects\Limitations
	 */
	public function get_limitations($waterfall = true, $skip_self = false) {

		$cache_key = $waterfall ? '_composite_limitations_' : '_limitations_';

		$cache_key = $skip_self ? $cache_key . '_no_self_' : $cache_key;

		$cache_key = $this->get_id() . $cache_key . $this->model;

		$cached_version = wu_get_isset($this->_limitations, $cache_key);

		if (!empty($cached_version)) {

			return $cached_version;

		} // end if;

		if (!is_array($this->meta)) {

			$this->meta = array();

		} // end if;

		if (did_action('muplugins_loaded') === false) {

			$modules_data = $this->get_meta('wu_limitations', array());

		} else {

			$modules_data = Limitations::early_get_limitations($this->model, $this->get_id());

		} // end if;

		$limitations = new Limitations(array());

		if ($waterfall) {

			/**
			 * If we don't want to take into consideration our own permissions
			 * we set this flag to true.
			 *
			 * This will return only the parents permissions and is super useful for
			 * comparisons.
			 */
			if ($skip_self) {

				$modules_data = array();

			} // end if;

			$limitations = $limitations->merge($modules_data, ...$this->limitations_to_merge());

		} else {

			$limitations = $limitations->merge($modules_data);

		} // end if;

		$this->_limitations[$cache_key] = $limitations;

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
	 * Schedules plugins to be activated or deactivated based on the current limitations;
	 *
	 * @since 2.0.5
	 * @return void
	 */
	public function sync_plugins() {

		$sites = array();

		if ($this->model === 'site') {

			$sites[] = $this;

		} elseif ($this->model === 'membership') {

			$sites = $this->get_sites();

		} // end if;

		foreach ($sites as $site_object) {

			if (!$site_object->get_id()) {

				continue;

			} // end if;

			$site_id     = $site_object->get_id();
			$limitations = $site_object->get_limitations();

			$plugins_to_deactivate = $limitations->plugins->get_by_type('force_inactive');
			$plugins_to_activate   = $limitations->plugins->get_by_type('force_active');

			if ($plugins_to_deactivate) {

				wu_async_deactivate_plugins($site_id, array_keys($plugins_to_deactivate));

			} // end if;

			if ($plugins_to_activate) {

				wu_async_activate_plugins($site_id, array_keys($plugins_to_activate));

			} // end if;

		} // end foreach;

	} // end sync_plugins;

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
		/*
		 * Only handle limitations if there are to handle in the first place.
		 */
		if (!wu_request('modules')) {

			return;

		} // end if;

		$object_limitations = $this->get_limitations(false);

		$saved_limitations = $object_limitations->to_array();

		$modules_to_save = array();

		$limitations = Limitations::repository();

		$current_limitations = $this->get_limitations(true, true);

		foreach ($limitations as $limitation_id => $class_name) {

			$module = wu_get_isset($saved_limitations, $limitation_id, array());

			$module['enabled'] = $object_limitations->{$limitation_id}->handle_enabled();

			$module['limit'] = $object_limitations->{$limitation_id}->handle_limit();

			$module = $object_limitations->{$limitation_id}->handle_others($module);

			if ($module) {

				$modules_to_save[$limitation_id] = $module;

			} // end if;

		} // end foreach;

		if ($this->model !== 'product') {
			/*
			 * Set the new permissions, based on the diff.
			 */
			$limitations = wu_array_recursive_diff($modules_to_save, $current_limitations->to_array());

		} elseif ($this->model === 'product' && $this->get_type() !== 'plan') {

			$limitations = wu_array_recursive_diff($modules_to_save, Limitations::get_empty()->to_array());

		} else {

			$limitations = $modules_to_save;

		} // end if;

		$this->meta['wu_limitations'] = $limitations;

	} // end handle_limitations;

	/**
	 * Returns the list of product slugs associated with this model.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_applicable_product_slugs() {

		if ($this->model === 'product') {

			return array($this->get_slug());

		} // end if;

		$slugs = array();

		if ($this->model === 'membership') {

			$membership = $this;

		} elseif ($this->model === 'site') {

			$membership = $this->get_membership();

		} // end if;

		if (!empty($membership)) {

			$slugs = array_column(array_map('wu_cast_model_to_array', array_column($membership->get_all_products(), 'product')), 'slug'); // WOW

		} // end if;

		return $slugs;

	} // end get_applicable_product_slugs;

} // end trait Limitable;
