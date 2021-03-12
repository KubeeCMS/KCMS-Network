<?php

// File generated from our OpenAPI spec
namespace WP_Ultimo\Dependencies\Stripe\Service\Issuing;

/**
 * Service factory class for API resources in the Issuing namespace.
 *
 * @property AuthorizationService $authorizations
 * @property CardholderService $cardholders
 * @property CardService $cards
 * @property DisputeService $disputes
 * @property TransactionService $transactions
 */
class IssuingServiceFactory extends \WP_Ultimo\Dependencies\Stripe\Service\AbstractServiceFactory
{
    /**
     * @var array<string, string>
     */
    private static $classMap = ['authorizations' => \WP_Ultimo\Dependencies\Stripe\Service\Issuing\AuthorizationService::class, 'cardholders' => \WP_Ultimo\Dependencies\Stripe\Service\Issuing\CardholderService::class, 'cards' => \WP_Ultimo\Dependencies\Stripe\Service\Issuing\CardService::class, 'disputes' => \WP_Ultimo\Dependencies\Stripe\Service\Issuing\DisputeService::class, 'transactions' => \WP_Ultimo\Dependencies\Stripe\Service\Issuing\TransactionService::class];
    protected function getServiceClass($name)
    {
        return \array_key_exists($name, self::$classMap) ? self::$classMap[$name] : null;
    }
}
