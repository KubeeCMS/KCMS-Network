<?php
/**
 * A trait to be included in entities to WU_Site Class depecrated methods.
 *
 * @package WP_Ultimo
 * @subpackage Deprecated
 * @since 2.0.0
 */

namespace WP_Ultimo\Traits;

/**
 * WP_Ultimo_Site_Deprecated trait.
 */
trait WP_Ultimo_Site_Deprecated {

	/**
	 * Deprecated: get_subscription.
	 *
	 * @deprecated 2.0.0
	 *
	 * @return \WP_Ultimo\Models\Membership
	 */
	public function get_subscription() {

		_deprecated_function(__CLASS__, '2.0.0', '\WP_Ultimo\Models\Site::get_membership()');

		return $this->get_membership();

	} // end get_subscription;

} // end trait WP_Ultimo_Site_Deprecated;
