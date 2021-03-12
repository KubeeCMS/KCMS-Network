<?php
/**
 * Handles Domain Mapping in WP Ultimo.
 *
 * @package WP_Ultimo
 * @subpackage Domain_Mapping
 * @since 2.0.0
 */

namespace WP_Ultimo;

use WP_Ultimo\Models\Domain;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles Domain Mapping in WP Ultimo.
 *
 * @since 2.0.0
 */
class Domain_Mapping {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Runs on singleton instantiation.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		if ($this->should_skip_checks()) {

			$this->startup();

		} else {

			$this->maybe_startup();

		} // end if;

	} // end init;

	/**
	 * Check if we should skip checks before running mapping functions.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public static function should_skip_checks() {

		return defined('WP_ULTIMO_DOMAIN_MAPPING_SKIP_CHECKS') && WP_ULTIMO_DOMAIN_MAPPING_SKIP_CHECKS;

	} // end should_skip_checks;

	/**
	 * Run the checks to make sure the requirements for Domain mapping are in place and execute it.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function maybe_startup() {
		/*
		 * Don't run during installation...
		 */
		if (defined('WP_INSTALLING')) {

			return;

		} // end if;

		/*
		 * Make sure we got loaded in the sunrise stage.
		 */
		if (did_action('muplugins_loaded')) {

			return;

		} // end if;

		/*
		 * Start the engines!
		 */
		$this->startup();

	} // end maybe_startup;

	/**
	 * Actual handles domain mapping functionality.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function startup() {
		/*
		 * Adds the necessary tables to the $wpdb global.
		 */
		if (empty($GLOBALS['wpdb']->wu_dmtable)) {

			$GLOBALS['wpdb']->wu_dmtable = $GLOBALS['wpdb']->base_prefix . 'wu_domain_mappings';

			$GLOBALS['wpdb']->ms_global_tables[] = 'wu_domain_mappings';

		} // end if;

		// Ensure cache is shared
		wp_cache_add_global_groups(array('domain_mappings', 'network_mappings'));

		/*
		 * Check if the URL being accessed right now is a mapped domain
		 */
		add_filter('pre_get_site_by_path', array($this, 'check_domain_mapping'), 10, 2);

		/*
		 * When a site gets delete, clean up the mapped domains
		 */
		add_action('delete_blog', array($this, 'clear_mappings_on_delete') );

		/*
		 * Adds the filters that will change the URLs when a mapped domains is in use
		 */
		add_action('muplugins_loaded', array($this, 'register_mapped_filters'), -10);

		/**
		 * On WP Ultimo 1.X builds we used Mercator. The Mercator actions and filters are now deprecated.
		 */
		if (has_action('mercator_load')) {

			do_action_deprecated('mercator_load', array(), '2.0.0', 'wu_domain_mapping_load');

		} // end if;

		/**
		 * Fired after our core Domain Mapping has been loaded
		 *
		 * Hook into this to handle any add-on functionality.
		 */
		do_action('wu_domain_mapping_load');

	} // end startup;

	/**
	 * Returns both the naked and www. version of the given domain
	 *
	 * @since 2.0.0
	 *
	 * @param string $domain Domain to get the naked and www. versions to.
	 * @return array
	 */
	public function get_www_and_nowww_versions($domain) {

		if (strpos($domain, 'www.') === 0) {

			$www   = $domain;
			$nowww = substr($domain, 4);

		} else {

			$nowww = $domain;
			$www   = 'www.' . $domain;

		} // end if;

		return array($nowww, $www);

	} // end get_www_and_nowww_versions;

	/**
	 * Checks if we have a site associated with the domain being accessed
	 *
	 * This method tries to find a site on the network that has a mapping related to the current
	 * domain being accessed. This uses the default WordPress mapping functionality, added on 4.5.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Site|false $site Site object being searched by path.
	 * @param string        $domain Domain to search for.
	 * @return WP_Site|false
	 */
	public function check_domain_mapping($site, $domain) {

		// Have we already matched? (Allows other plugins to match first)
		if (!empty($site)) {

			return $site;

		} // end if;

		$domains = $this->get_www_and_nowww_versions($domain);

		$mapping = Domain::get_by_domain($domains);

		if (empty($mapping) || is_wp_error($mapping)) {

			return $site;

		} // end if;

		if (has_filter('mercator.use_mapping')) {

			$deprecated_args = array(
				$mapping->is_active(),
				$mapping,
				$domain
			);

			$is_active = apply_filters_deprecated('mercator.use_mapping', $deprecated_args, '2.0.0', 'wu_use_domain_mapping');

		} // end if;

		/**
		 * Determine whether a mapping should be used
		 *
		 * Typically, you'll want to only allow active mappings to be used. However,
		 * if you want to use more advanced logic, or allow non-active domains to
		 * be mapped too, simply filter here.
		 *
		 * @param boolean $is_active Should the mapping be treated as active?
		 * @param WP_Ultimo\Models\Domain $mapping Mapping that we're inspecting
		 * @param string $domain
		 */
		$is_active = apply_filters('wu_use_domain_mapping', $mapping->is_active(), $mapping, $domain);

		// Ignore non-active mappings
		if (!$is_active) {

			return $site;

		} // end if;

		// Fetch the actual data for the site
		$mapped_site = $mapping->get_site();

		if (empty($mapped_site)) {

			return $site;

		} // end if;

		/*
		 * Note: This is only for backwards compatibility with WPMU Domain Mapping,
		 * do not rely on this constant in new code.
		 */
		defined('DOMAIN_MAPPING') or define('DOMAIN_MAPPING', 1); // phpcs:ignore

		/*
		 * Decide if we use SSL
		 */
		if ($mapping->is_secure()) {

			force_ssl_admin(true);

		} // end if;

		$GLOBALS['wu_original_url'] = $mapped_site->domain . $mapped_site->path;

		/*
		 * We found a site based on the mapped domain =)
		 */
		return $mapped_site;

	} // end check_domain_mapping;

	/**
	 * Clear mappings for a site when it's deleted
	 *
	 * @param int $site_id Site being deleted.
	 */
	public function clear_mappings_on_delete($site_id) {

		$mappings = Domain::get_by_site($site_id);

		if (empty($mappings)) {

			return;

		} // end if;

		foreach ($mappings as $mapping) {

			$error = $mapping->delete();

			if (is_wp_error($error)) {

				// translators: First placeholder is the mapping ID, second is the site ID.
				$message = sprintf(__('Unable to delete mapping %1$d for site %2$d', 'wp_ultimo'), $mapping->get_id(), $site_id);

				trigger_error($message, E_USER_WARNING);

			} // end if;

		} // end foreach;

	} // end clear_mappings_on_delete;

	/**
	 * Register filters for URLs, if we've mapped
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_mapped_filters() {

		$current_site = $GLOBALS['current_blog'];
		$real_domain  = $current_site->domain;
		$domain       = $_SERVER['HTTP_HOST'];

		if ($domain === $real_domain) {

			// Domain hasn't been mapped
			return;

		} // end if;

		// Grab both WWW and no-WWW
		$domains = $this->get_www_and_nowww_versions($domain);

		$mapping = Domain::get_by_domain($domains);

		/*
		 * Bail if no mapping.
		 */
		if (empty($mapping) || is_wp_error($mapping)) {

			return;

		} // end if;

		/*
		 * Important: set the current mapping as global for future reference.
		 */
		$GLOBALS['wu_current_mapping'] = $mapping;

		/*
		 * Adds the URL filters, replacing the original URL with the mapped domain instead.
		 */

		add_filter('site_url', array($this, 'replace_url'), -10, 4 );

		add_filter('home_url', array($this, 'replace_url'), -10, 4 );

		add_filter('theme_file_uri', array($this, 'replace_url'), 99);

		add_filter('stylesheet_directory_uri', array($this, 'replace_url'));

		add_filter('template_directory_uri', array($this, 'replace_url'));

		add_filter('plugins_url', array($this, 'replace_url'));

		add_filter('wp_ultimo_url', array($this, 'replace_url'));

		add_filter('wp_get_attachment_url', array($this, 'replace_url'), 1);

		add_filter('script_loader_src', array($this, 'replace_url'));

		add_filter('style_loader_src', array($this, 'replace_url'));

		add_filter('theme_mod_header_image', array($this, 'replace_url')); // @since 1.5.5

		add_filter('wu_get_logo', array($this, 'replace_url')); // @since 1.9.0

		add_filter('autoptimize_filter_base_replace_cdn', array($this, 'replace_url'), 8); // @since 1.8.2 - Fix for Autoptimiza

		// If on network site, also filter network urls
		if (is_main_site()) {

			add_filter('network_site_url', array($this, 'replace_url'), -10, 3);

			add_filter('network_home_url', array($this, 'replace_url'), -10, 3);

		} // end if;

	} // end register_mapped_filters;

	/**
	 * On subdirectory install, remove the subdirectory from the mapped version
	 *
	 * @since 2.0.0
	 *
	 * @param string $url URL containing the path for the site.
	 * @return string
	 */
	public function remove_subdirectory($url) {

		if (!is_subdomain_install()) {

			$site = $GLOBALS['current_blog'];

			$to_remove = rtrim($site->path, '/');

			if ($to_remove && $to_remove !== '/') {

				return esc_url_raw(str_replace($to_remove, '', $url));

			} // end if;

		} // end if;

		return $url;

	} // end remove_subdirectory;

	/**
	 * Mangle the home URL to give our primary domain
	 *
	 * @param string      $url The complete home URL including scheme and path.
	 * @param string      $path Path relative to the home URL. Blank string if no path is specified.
	 * @param string|null $orig_scheme Scheme to give the home URL context. Accepts 'http', 'https', 'relative' or null.
	 * @param int|null    $site_id Blog ID, or null for the current blog.
	 * @return string Mangled URL
	 */
	public function replace_url($url, $path = '/', $orig_scheme = 'http', $site_id = 0) {

		if (empty($site_id)) {

			$site_id = get_current_blog_id();

		} // end if;

		$current_mapping = $GLOBALS['wu_current_mapping'];

		if (empty($current_mapping) || $site_id !== $current_mapping->get_site_id()) {

			return $url;

		} // end if;

		// Replace the domain
		$domain = parse_url($url, PHP_URL_HOST);

		$regex = '#^(\w+://)' . preg_quote($domain, '#') . '#i';

		$mangled = preg_replace($regex, '${1}' . $current_mapping->get_domain(), $url);

		$mangled = $this->remove_subdirectory($mangled);

		return $mangled;

	} // end replace_url;

} // end class Domain_Mapping;
