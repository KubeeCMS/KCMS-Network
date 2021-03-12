<?php

namespace WP_Ultimo\Dependencies\Stripe\Exception\OAuth;

/**
 * Implements properties and methods common to all (non-SPL) Stripe OAuth
 * exceptions.
 */
abstract class OAuthErrorException extends \WP_Ultimo\Dependencies\Stripe\Exception\ApiErrorException
{
    protected function constructErrorObject()
    {
        if (null === $this->jsonBody) {
            return null;
        }
        return \WP_Ultimo\Dependencies\Stripe\OAuthErrorObject::constructFrom($this->jsonBody);
    }
}
