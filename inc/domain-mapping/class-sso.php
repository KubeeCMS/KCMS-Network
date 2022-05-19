<?php
/**
 * Deprecated Single Sign-On.
 *
 * This file is here to prevent fatal errors from
 * being thrown when updating from versions before 2.0.11
 * to versions after 2.0.11.
 *
 * @deprecated
 * @see \WP_Ultimo\SSO
 * @package WP_Ultimo\Domain_Mapping\SSO
 * @since 2.0.0
 */

namespace WP_Ultimo\Domain_Mapping\SSO;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles Sign-sign on.
 *
 * @deprecated
 * @since 2.0.0
 */
class SSO {

	use \WP_Ultimo\Traits\Singleton;

} // end class SSO;
