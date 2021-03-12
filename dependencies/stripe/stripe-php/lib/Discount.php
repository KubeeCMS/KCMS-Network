<?php

namespace WP_Ultimo\Dependencies\Stripe;

/**
 * Class Discount.
 *
 * @property string $object
 * @property Coupon $coupon
 * @property string $customer
 * @property int $end
 * @property int $start
 * @property string $subscription
 */
class Discount extends \WP_Ultimo\Dependencies\Stripe\StripeObject
{
    const OBJECT_NAME = 'discount';
}
