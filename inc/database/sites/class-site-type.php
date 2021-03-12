<?php
/**
 * Site Types enum.
 *
 * @package WP_Ultimo
 * @subpackage WP_Ultimo\Database\Sites
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Sites;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Database\Engine\Enum;

/**
 * Site Types.
 *
 * @since 2.0.0
 */
class Site_Type extends Enum {

	/**
	 * Default type.
	 */
	const __default = 'default'; // phpcs:ignore

	const REGULAR        = 'default';
	const SITE_TEMPLATE  = 'site_template';
	const CUSTOMER_OWNED = 'customer_owned';
	const PENDING        = 'pending';
	const EXTERNAL       = 'external';
	const MAIN           = 'main';

	/**
	 * Returns an array with values => CSS Classes.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function classes() {

		return array(
			static::REGULAR        => 'wu-bg-gray-700 wu-text-gray-200',
			static::SITE_TEMPLATE  => 'wu-bg-yellow-200 wu-text-yellow-700',
			static::CUSTOMER_OWNED => 'wu-bg-green-200 wu-text-green-700',
			static::PENDING        => 'wu-bg-purple-200 wu-text-purple-700',
			static::EXTERNAL       => 'wu-bg-blue-200 wu-text-blue-700',
			static::MAIN           => 'wu-bg-pink-200 wu-text-pink-700',
		);

	} // end classes;

	/**
	 * Returns an array with values => labels.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function labels() {

		return array(
			static::REGULAR        => __('Regular Site', 'wp-ultimo'),
			static::SITE_TEMPLATE  => __('Site Template', 'wp-ultimo'),
			static::CUSTOMER_OWNED => __('Customer-Owned', 'wp-ultimo'),
			static::PENDING        => __('Pending', 'wp-ultimo'),
			static::MAIN           => __('Main Site', 'wp-ultimo'),
		);

	} // end labels;

} // end class Site_Type;
