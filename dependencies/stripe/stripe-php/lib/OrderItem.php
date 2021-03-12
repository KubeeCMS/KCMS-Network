<?php

namespace WP_Ultimo\Dependencies\Stripe;

/**
 * Class OrderItem.
 *
 * @property string $object
 * @property int $amount
 * @property string $currency
 * @property string $description
 * @property string $parent
 * @property int $quantity
 * @property string $type
 */
class OrderItem extends \WP_Ultimo\Dependencies\Stripe\StripeObject
{
    const OBJECT_NAME = 'order_item';
}
