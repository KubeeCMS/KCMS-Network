<?php

// File generated from our OpenAPI spec
namespace WP_Ultimo\Dependencies\Stripe\Service\Radar;

/**
 * Service factory class for API resources in the Radar namespace.
 *
 * @property EarlyFraudWarningService $earlyFraudWarnings
 * @property ValueListItemService $valueListItems
 * @property ValueListService $valueLists
 */
class RadarServiceFactory extends \WP_Ultimo\Dependencies\Stripe\Service\AbstractServiceFactory
{
    /**
     * @var array<string, string>
     */
    private static $classMap = ['earlyFraudWarnings' => \WP_Ultimo\Dependencies\Stripe\Service\Radar\EarlyFraudWarningService::class, 'valueListItems' => \WP_Ultimo\Dependencies\Stripe\Service\Radar\ValueListItemService::class, 'valueLists' => \WP_Ultimo\Dependencies\Stripe\Service\Radar\ValueListService::class];
    protected function getServiceClass($name)
    {
        return \array_key_exists($name, self::$classMap) ? self::$classMap[$name] : null;
    }
}
