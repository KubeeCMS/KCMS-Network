<?php
/**
 * The SSO Session Exception.
 *
 * @package WP_Ultimo
 * @subpackage SSO\Exception
 * @since 2.0.11
 */

namespace WP_Ultimo\SSO\Exception;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Thrown when our session handler implementation fails somewhere.
 *
 * @since 2.0.11
 */
class SSO_Session_Exception extends \RuntimeException {} // end class SSO_Session_Exception;
