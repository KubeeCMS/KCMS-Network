<?php

namespace WP_Ultimo\Dependencies\Stripe\Exception;

/**
 * PermissionException is thrown in cases where access was attempted on a
 * resource that wasn't allowed.
 */
class PermissionException extends \WP_Ultimo\Dependencies\Stripe\Exception\ApiErrorException
{
}
