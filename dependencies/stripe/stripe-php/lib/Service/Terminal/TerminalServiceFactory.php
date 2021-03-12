<?php

// File generated from our OpenAPI spec
namespace WP_Ultimo\Dependencies\Stripe\Service\Terminal;

/**
 * Service factory class for API resources in the Terminal namespace.
 *
 * @property ConnectionTokenService $connectionTokens
 * @property LocationService $locations
 * @property ReaderService $readers
 */
class TerminalServiceFactory extends \WP_Ultimo\Dependencies\Stripe\Service\AbstractServiceFactory
{
    /**
     * @var array<string, string>
     */
    private static $classMap = ['connectionTokens' => \WP_Ultimo\Dependencies\Stripe\Service\Terminal\ConnectionTokenService::class, 'locations' => \WP_Ultimo\Dependencies\Stripe\Service\Terminal\LocationService::class, 'readers' => \WP_Ultimo\Dependencies\Stripe\Service\Terminal\ReaderService::class];
    protected function getServiceClass($name)
    {
        return \array_key_exists($name, self::$classMap) ? self::$classMap[$name] : null;
    }
}
