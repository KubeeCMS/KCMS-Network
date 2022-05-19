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
	 * Keeps a copy of the current mapping.
	 *
	 * @since 2.0.0
	 * @var \WP_Ultimo\Models\Domain
	 */
	public $current_mapping = null;

	/**
	 * Keeps a copy of the original URL.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	public $original_url = null;

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

		$is_enabled = (bool) wu_get_setting_early('enable_domain_mapping');

		if ($is_enabled === false) {

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
		add_action('wp_delete_site', array($this, 'clear_mappings_on_delete'));

		/*
		 * Adds the filters that will change the URLs when a mapped domains is in use
		 */
		add_action('ms_loaded', array($this, 'register_mapped_filters'), 11);

		// add_action('allowed_http_origin', array($this, 'add_mapped_domains_as_allowed_origins'));

		/**
		 * On WP Ultimo 1.X builds we used Mercator. The Mercator actions and filters are now deprecated.
		 */
		if (has_action('mercator_load')) {

			do_action_deprecated('mercator_load', array(), '2.0.0', 'wu_domain_mapping_load');

		} // end if;

		add_action('wu_sso_site_allowed_domains', function($list, $site_id) {

			$domains = wu_get_domains(array(
				'active'        => true,
				'blog_id'       => $site_id,
				'stage__not_in' => \WP_Ultimo\Models\Domain::INACTIVE_STAGES,
				'fields'        => 'domain',
			));

			return array_merge($list, $domains);

		}, 10, 2);

		/**
		 * Fired after our core Domain Mapping has been loaded
		 *
		 * Hook into this to handle any add-on functionality.
		 */
		do_action('wu_domain_mapping_load');

	} // end startup;

	/**
	 * Checks if an origin is a mapped domain.
	 *
	 * If that's the case, we should always allow that origin.
	 *
	 * @since 2.0.0
	 *
	 * @param string $origin The origin passed.
	 * @return string
	 */
	public function add_mapped_domains_as_allowed_origins($origin) {

		if (!function_exists('wu_get_domain_by_domain')) {

			return;

		} // end if;

		if (empty($origin) && wp_doing_ajax()) {

			$origin = wu_get_current_url();

		} // end if;

		$the_domain = wp_parse_url($origin, PHP_URL_HOST);

		$domain = wu_get_domain_by_domain($the_domain);

		if ($domain) {

			return $domain->get_domain();

		} // end if;

		return $origin;

	} // end add_mapped_domains_as_allowed_origins;

	/**
	 * Fixes the SSO target site in cases of domain mapping.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Site $target_site The current target site.
	 * @param string   $domain The domain being searched.
	 * @return \WP_Site
	 */
	public function fix_sso_target_site($target_site, $domain) {

		if (!$target_site || !$target_site->blog_id) {

			$mapping = \WP_Ultimo\Models\Domain::get_by_domain($domain);

			if ($mapping) {

				$target_site = get_site($mapping->get_site_id());

			} // end if;

		} // end if;

		return $target_site;

	} // end fix_sso_target_site;

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

		$_site = $site;

		if (is_a($mapped_site, '\WP_Site')) {

			$this->original_url = $mapped_site->domain . $mapped_site->path;

			$_site = $mapped_site;

		} elseif (is_a($mapped_site, '\WP_Ultimo\Models\Site')) {

			$this->original_url = $mapped_site->get_domain() . $mapped_site->get_path();

			$_site = $mapped_site->to_wp_site();

		} // end if;

		/*
		 * We found a site based on the mapped domain =)
		 */
		return $_site;

	} // end check_domain_mapping;

	/**
	 * Clear mappings for a site when it's deleted
	 *
	 * @param WP_Site $site Site being deleted.
	 */
	public function clear_mappings_on_delete($site) {

		$mappings = Domain::get_by_site($site->blog_id);

		if (empty($mappings)) {

			return;

		} // end if;

		foreach ($mappings as $mapping) {

			$error = $mapping->delete();

			if (is_wp_error($error)) {

				// translators: First placeholder is the mapping ID, second is the site ID.
				$message = sprintf(__('Unable to delete mapping %1$d for site %2$d', 'wp-ultimo'), $mapping->get_id(), $site->blog_id);

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

		if (!$current_site) {

			return;

		} // end if;

		$real_domain = $current_site->domain;
		$domain      = $_SERVER['HTTP_HOST'];

		if ($domain === $real_domain) {

			// Domain hasn't been mapped
			return;

		} // end if;

		$domains = $this->get_www_and_nowww_versions($domain);

		$mapping = Domain::get_by_domain($domains);

		if (empty($mapping) || is_wp_error($mapping)) {

			return;

		} // end if;

		$this->current_mapping = $mapping;

		add_filter('site_url', array($this, 'mangle_url'), -10, 4);
		add_filter('home_url', array($this, 'mangle_url'), -10, 4);

		add_filter('theme_file_uri', array($this, 'mangle_url'));
		add_filter('stylesheet_directory_uri', array($this, 'mangle_url'));
		add_filter('template_directory_uri', array($this, 'mangle_url'));
		add_filter('plugins_url', array($this, 'mangle_url'), -10, 3);

		add_filter('autoptimize_filter_base_replace_cdn', array($this, 'mangle_url'), 8); // @since 1.8.2 - Fix for Autoptimizer

		// Fix srcset
		add_filter('wp_calculate_image_srcset', array($this, 'fix_srcset')); // @since 1.5.5

		// If on network site, also filter network urls
		if (is_main_site()) {

			add_filter('network_site_url', array($this, 'mangle_url'), -10, 3);
			add_filter('network_home_url', array($this, 'mangle_url'), -10, 3);

		} // end if;

		add_filter('jetpack_sync_home_url', array($this, 'mangle_url'));
		add_filter('jetpack_sync_site_url', array($this, 'mangle_url'));

		/**
		 * Some plugins will save URL before the mapping was active
		 * or will build URLs in a different manner that is not included on
		 * the above filters.
		 *
		 * In cases like that, we want to add additional filters.
		 * The second parameter passed is the mangle_url callback.
		 *
		 * We recommend against using this filter directly.
		 * Instead, use the Domain_Mapping::apply_mapping_to_url method.
		 *
		 * @since 2.0.0
		 * @param array The mangle callable.
		 * @param self  This object.
		 * @return void
		 */
		do_action('wu_domain_mapping_register_filters', array($this, 'mangle_url'), $this);

	} // end register_mapped_filters;

	/**
	 * Apply the replace URL to URL filters provided by other plugins.
	 *
	 * @since 2.0.0
	 *
	 * @param string|array $hooks List of hooks to apply the callback to.
	 * @return void
	 */
	public static function apply_mapping_to_url($hooks) {

		add_action('wu_domain_mapping_register_filters', function($callback) use ($hooks) {

			$hooks = (array) $hooks;

			foreach ($hooks as $hook) {

				add_filter($hook, $callback);

			} // end foreach;

		});

	} // end apply_mapping_to_url;

	/**
	 * Replaces the URL.
	 *
	 * @since 2.0.0
	 *
	 * @param string                        $url URL to replace.
	 * @param null|\WP_Ultimo\Models\Domain $current_mapping The current mapping.
	 * @return string
	 */
	public function replace_url($url, $current_mapping = null) {

		if ($current_mapping === null) {

			$current_mapping = $this->current_mapping;

		} // end if;

		// Replace the domain
		$domain_base = parse_url($url, PHP_URL_HOST);
		$domain      = rtrim($domain_base . '/' . $current_mapping->get_site()->get_path(), '/');
		$regex       = '#^(\w+://)' . preg_quote($domain, '#') . '#i';
		$mangled     = preg_replace($regex, '${1}' . $current_mapping->get_domain(), $url);

		/*
		 * Another try if we don't need to deal with subdirectory.
		 */
		if ($mangled === $url && $this->current_mapping !== $current_mapping) {

			$domain  = rtrim($domain_base, '/');
			$regex   = '#^(\w+://)' . preg_quote($domain, '#') . '#i';
			$mangled = preg_replace($regex, '${1}' . $current_mapping->get_domain(), $url);

		} // end if;

		$mangled = wu_replace_scheme($mangled, $current_mapping->is_secure() ? 'https://' : 'http://');

		return $mangled;

	} // end replace_url;

	/**
	 * Mangle the home URL to give our primary domain
	 *
	 * @param string      $url The complete home URL including scheme and path.
	 * @param string      $path Path relative to the home URL. Blank string if no path is specified.
	 * @param string|null $orig_scheme Scheme to give the home URL context. Accepts 'http', 'https', 'relative' or null.
	 * @param int|null    $site_id Blog ID, or null for the current blog.
	 * @return string Mangled URL
	 */
	public function mangle_url($url, $path = '/', $orig_scheme = '', $site_id = 0) {

		if (empty($site_id)) {

			$site_id = get_current_blog_id();

		} // end if;

		$current_mapping = $this->current_mapping;

		if (empty($current_mapping) || $site_id !== $current_mapping->get_site_id()) {

			return $url;

		} // end if;

		return $this->replace_url($url);

	} // end mangle_url;

	/**
	 * Adds a fix to the srcset URLs when we need that domain mapped
	 *
	 * @since 1.5.5
	 * @param array $sources Image source URLs.
	 * @return array
	 */
	public function fix_srcset($sources) {

		foreach ($sources as &$source) {

			$sources[$source['value']]['url'] = $this->replace_url($sources[$source['value']]['url']);

		} // end foreach;

		return $sources;

	} // end fix_srcset;

} // end class Domain_Mapping;
