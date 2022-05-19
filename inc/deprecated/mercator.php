<?php
/**
 * Mercator
 *
 * @package WP_Ultimo
 * @subpackage Deprecated
 * @since 2.0.0
 */

namespace Mercator; // phpcs:ignore

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Deprecated: Class Mapping.
 *
 * @since 1.1.3
 */
class Mapping {

	/**
	 * Deprecated: Get mapping by domain(s).
	 *
	 * @since 1.1.3
	 *
	 * @param string $domains Domain(s) to match against.
	 * @deprecated
	 * @return mixed
	 */
	public static function get_by_domain($domains) {

		_deprecated_function(__METHOD__, '2.0.0', 'wu_get_domain_by_domain($domain)');

		if (!function_exists('wu_get_domain_by_domain')) {

			return false;

		} // end if;

		$domain = false;

		foreach ((array) $domains as $url_to_search) {

			$found_domain = wu_get_domain_by_domain($url_to_search);

			if ($found_domain) {

				$domain = $found_domain;

				break;

			} // end if;

		} // end foreach;

		return $domain;

	} // end get_by_domain;

} // end class Mapping;
