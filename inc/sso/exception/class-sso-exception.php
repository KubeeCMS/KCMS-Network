<?php
/**
 * The SSO Exception.
 *
 * @package WP_Ultimo
 * @subpackage SSO\Exception
 * @since 2.0.11
 */

namespace WP_Ultimo\SSO\Exception;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Thrown when our implementation fails somewhere.
 *
 * @since 2.0.11
 */
class SSO_Exception extends \RuntimeException {} // end class SSO_Exception;
