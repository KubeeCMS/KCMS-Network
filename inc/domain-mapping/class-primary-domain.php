<?php
/**
 * Handles redirects to the primary domain of a site with mappings
 *
 * @package WP_Ultimo
 * @subpackage Domain_Mapping
 * @since 2.0.0
 */

namespace WP_Ultimo\Domain_Mapping;

use WP_Ultimo\Domain_Mapping;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles redirects to the primary domain of a site with mappings
 *
 * @since 2.0.0
 */
class Primary_Domain {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Adds the hooks
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		add_action('wu_domain_mapping_load', array($this, 'add_hooks'), -20);

	} // end init;

	/**
	 * Adds the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_hooks() {

		add_action('template_redirect', array($this, 'redirect_to_primary_domain'));

		add_action('admin_init', array($this, 'maybe_redirect_to_mapped_or_network_domain'));

		add_action('login_init', array($this, 'maybe_redirect_to_mapped_or_network_domain'));

	} // end add_hooks;

	/**
	 * Redirects the site to its primary mapped domain, if any.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function redirect_to_primary_domain() {

		$should_redirect = true;

		if (is_preview()) {

			$should_redirect = false;

		} // end if;

		if (is_customize_preview()) {

			$should_redirect = false;

 		} // end if;

		/**
		 * Allow developers to short-circuit the redirection, preventing it
		 * from happening.
		 *
		 * @since 2.0.0
		 * @param $should_redirect If we should redirect or not.
		 *
		 * @return bool
		 */
		if (apply_filters('wu_should_redirect_to_primary_domain', $should_redirect) === false) {

			return;

		} // end if;

		if (!function_exists('wu_get_domains')) {

			return;

		} // end if;

		$domains = wu_get_domains(array(
			'blog_id'        => get_current_blog_id(),
			'primary_domain' => 1,
			'active'         => 1,
			'domain__not_in' => array($_SERVER['HTTP_HOST']),
		));

		if (empty($domains)) {

			return;

		} // end if;

		$primary_domain = $domains[0];

		if ($_SERVER['HTTP_HOST'] !== $primary_domain->get_domain() && $primary_domain->is_active()) {

			$url = wu_get_current_url();

			$new_url = Domain_Mapping::get_instance()->replace_url($url, $primary_domain);

			wp_redirect(set_url_scheme($new_url));

			exit;

		} // end if;

	} // end redirect_to_primary_domain;

	/**
	 * Handles redirects to mapped ot network domain for the admin panel.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function maybe_redirect_to_mapped_or_network_domain() {

		if ($_SERVER['REQUEST_METHOD'] !== 'GET' || wp_doing_ajax()) {

			return;

		} // end if;

		/*
		 * The visitor is actively trying to logout. Let them do it!
		 */
		if (wu_request('action', 'nothing') === 'logout' || wu_request('loggedout')) {

			return;

		} // end if;

		$site = wu_get_current_site();

		$mapped_domain = $site->get_primary_mapped_domain();

		if (!$mapped_domain) {

			return;

		} // end if;

		$redirect_settings = wu_get_setting('force_admin_redirect', 'both');

		if ($redirect_settings === 'both') {

			return;

		} // end if;

		$current_url = wp_parse_url(wu_get_current_url());

		$mapped_url = wp_parse_url($mapped_domain->get_url());

		$current_url_to_compare = $current_url['host'];

		$mapped_url_to_compare = $mapped_url['host'];

		$query_args = array();

		if (wu_get_isset($current_url, 'query')) {

			wp_parse_str($current_url['query'], $query_args);

		} // end if;

		$redirect_url = false;

		if ($redirect_settings === 'force_map' && $current_url_to_compare !== $mapped_url_to_compare) {

			$redirect_url = Domain_Mapping::get_instance()->replace_url(wu_get_current_url(), $mapped_domain);

			$query_args = array_map(function($value) use ($mapped_domain) {

				return Domain_Mapping::get_instance()->replace_url($value, $mapped_domain);

			}, $query_args);

		} elseif ($redirect_settings === 'force_network' && $current_url_to_compare === $mapped_url_to_compare) {

			$redirect_url = wu_restore_original_url(wu_get_current_url(), $site->get_id());

			$query_args = array_map(function($value) use ($site) {

				return wu_restore_original_url($value, $site->get_id());

			}, $query_args);

		} // end if;

		if ($redirect_url) {

			wp_redirect(add_query_arg($query_args, $redirect_url));

			exit;

		} // end if;

	} // end maybe_redirect_to_mapped_or_network_domain;

} // end class Primary_Domain;
