<?php

// File generated from our OpenAPI spec
namespace WP_Ultimo\Dependencies\Stripe;

/**
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property int $created Time at which the object was created. Measured in seconds since the Unix epoch.
 * @property string $url The URL for the login link.
 */
class LoginLink extends \WP_Ultimo\Dependencies\Stripe\ApiResource
{
    const OBJECT_NAME = 'login_link';
}
