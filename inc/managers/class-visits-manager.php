<?php
/**
 * Visits Manager
 *
 * Handles processes related to site visits control.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Visits_Manager
 * @since 2.0.0
 */

namespace WP_Ultimo\Managers;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles processes related to limitations.
 *
 * @since 2.0.0
 */
class Visits_Manager {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		if (wu_get_setting('enable_visits_limiting', true) === false || is_main_site()) {

			return; // Feature not active, bail.

		} // end if;

    /*
     * Due to how caching plugins work, we need to count visits via ajax.
     * This adds the ajax endpoint that performs the counting.
     */
		add_action('wp_ajax_nopriv_wu_count_visits', array($this, 'count_visits'), 10, 2);

		add_action('wp_enqueue_scripts', array($this, 'enqueue_visit_counter_script'));

		add_action('template_redirect', array($this, 'maybe_lock_site'));

	} // end init;

	/**
	 * Check if the limits for visits was set. If that's the case, lock the site.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function maybe_lock_site() {

		$site = wu_get_current_site();

		if (!$site) {

			return;

		} // end if;

		/*
		 * Case unlimited visits
		 */
		if (empty($site->get_limitations()->visits->get_limit())) {

			return;

		} // end if;

		if ($site->has_limitations() && $site->get_visits_count() > $site->get_limitations()->visits->get_limit()) {

			wp_die(__('This site is now available at this time.', 'wp-ultimo'), __('Not available', 'wp-ultimo'), 404);

		} // end if;

	} // end maybe_lock_site;

	/**
	 * Counts visits to network sites.
	 *
	 * This needs to be extremely light-weight.
	 * The flow happens more or less like this:
	 * 1. Gets the site current total;
	 * 2. Adds one and re-save;
	 * 3. Checks limits and see if we need to flush caches and such;
	 * 4. Delegate these heavy tasks to action_scheduler.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function count_visits() {

		if (is_main_site() && is_admin()) {

			return; // bail on main site.

		} // end if;

		$site = wu_get_current_site();

		if ($site->get_type() !== 'customer_owned') {

			return;

		} // end if;

		$visits_manager = new \WP_Ultimo\Objects\Visits($site->get_id());

		/*
		 * Add a new visit.
		 */
		$visits_manager->add_visit();

		/*
		 * Checks against the limitations.
		 */
		if (false) {

			$this->flush_known_caches();

			echo 'flushing caches';

			die('2');

		} // end if;

		die('1');

	} // end count_visits;

	/**
	 * Flush known caching plugins, offers hooks to add more plugins in the future
	 *
	 * @since 1.7.0
	 * @return void
	 */
	public function flush_known_caches() {

		if (function_exists('wp_cache_clear_cache')) {

			global $file_prefix, $supercachedir;

			if (empty($supercachedir) && function_exists('get_supercache_dir')) {

				$supercachedir = get_supercache_dir(); // phpcs:ignore

			} // end if;

			wp_cache_clear_cache($file_prefix); // WP Super Cache Flush

		} // end if;

		if (function_exists('w3tc_pgcache_flush')) {

			w3tc_pgcache_flush(); // W3TC Cache Flushing

		} // end if;

		global $wp_fastest_cache;

		if (method_exists('WpFastestCache', 'deleteCache') && !empty($wp_fastest_cache)) {

			$wp_fastest_cache->deleteCache();

		} // end if;

		$this->flush_wpengine_cache(); // WPEngine Cache Flushing

		/**
		 * Hook to additional cleaning
		 */
		do_action('wu_flush_known_caches');

	} // end flush_known_caches;

	/**
	 * Enqueues the visits count script when necessary.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function enqueue_visit_counter_script() {

		if (is_user_logged_in()) {

			return; // bail if user is logged in.

		} // end if;

		wp_register_script('wu-visits-counter', wu_get_asset('visits-counter.js', 'js'), array('jquery'), wu_get_version());

		wp_localize_script('wu-visits-counter', 'wu_visits_counter', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'code'    => wp_create_nonce('wu-visit-counter'),
		));

		wp_enqueue_script('wu-visits-counter');

	} // end enqueue_visit_counter_script;

} // end class Visits_Manager;
