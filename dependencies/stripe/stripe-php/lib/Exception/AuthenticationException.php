<?php

namespace WP_Ultimo\Dependencies\Stripe\Exception;

/**
 * AuthenticationException is thrown when invalid credentials are used to
 * connect to Stripe's servers.
 */
class AuthenticationException extends \WP_Ultimo\Dependencies\Stripe\Exception\ApiErrorException
{
}
