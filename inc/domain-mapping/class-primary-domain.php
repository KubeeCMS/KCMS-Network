<?php
/**
 * Handles redirects to the primary domain of a site with mappings
 *
 * @package WP_Ultimo
 * @subpackage Domain_Mapping
 * @since 2.0.0
 */

namespace WP_Ultimo\Domain_Mapping;

use WP_Ultimo\Models\Domain;
use WP_Ultimo\Domain_Mapping;
use WP_Ultimo\Domain_Mapping\SSO;

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
		/*
		 * Checks for a primary mapping and redirects
		 */
		add_action('plugins_loaded', array($this, 'redirect_to_primary_domain'), -20);

	} // end init;

	/**
	 * Redirects the site to its primary mapped domain, if any.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function redirect_to_primary_domain() {

		if (headers_sent()) {

			return;

		} // end if;

		global $wu_original_url;

		$mappings = Domain::get_by_site(get_current_blog_id());

		if (!$mappings) {

			return;

		} // end if;

		$force_setting_admin = wu_get_setting('force_admin_redirect', 'both');

		foreach ($mappings as $mapping) {

			if ($mapping->is_primary_domain() && $mapping->is_active()) {

				$url = (is_ssl() ? 'https' : 'http') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

				// Replace the domain
				$domain = parse_url($url, PHP_URL_HOST);

				$regex = '#^(\w+://)' . preg_quote($domain, '#') . '#i';

				$mangled = preg_replace($regex, '${1}' . $mapping->get_domain(), $url);

				$mangled = Domain_Mapping::get_instance()->remove_subdirectory($mangled);

				/*
				 * Fix the schema
				 */
				$mangled = preg_replace('(^https?://)', '', $mangled);

				$mangled = ($mapping->is_secure() ? 'https' : 'http') . '://' . $mangled;

				/*
				 * On the original domain.
				 * Here we need to decide if we keep it on the original domain or redirect it.
				 * 1. Keep if:
				 * - Is Admin
				 * - Force Redirect set to both or force_network
				 */
				if ($mangled !== $url) {

					if (is_admin() && in_array($force_setting_admin, array('both', 'force_network'), true)) {

						return;

					} // end if;

					$redirect_url = apply_filters('wu_redirect_to_primary_domain', $mangled, $mapping);

					wp_redirect($redirect_url, 302);

					exit;

				/*
				 * Here the user is on the mapped domain.
				 * Need to redirect to the original domain if:
				 * - Is Admin
				 * - Force Redirect set to both or force_network
				 */
				} else {

					if (is_admin() && in_array($force_setting_admin, array('force_network'), true)) {

						$domain = parse_url($mangled, PHP_URL_HOST);

						$regex = '#^(\w+://)' . preg_quote($domain, '#') . '#i';

						$original_url = preg_replace($regex, '${1}' . trim($wu_original_url, '/'), $url);

						wp_redirect($original_url, 302);

						exit;

					} // end if;

				} // end if;

			} // end if;

		} // end foreach;

	} // end redirect_to_primary_domain;

} // end class Primary_Domain;
