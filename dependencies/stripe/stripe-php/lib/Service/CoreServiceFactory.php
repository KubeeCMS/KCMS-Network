<?php

// File generated from our OpenAPI spec
namespace WP_Ultimo\Dependencies\Stripe\Service;

/**
 * Service factory class for API resources in the root namespace.
 *
 * @property AccountLinkService $accountLinks
 * @property AccountService $accounts
 * @property ApplePayDomainService $applePayDomains
 * @property ApplicationFeeService $applicationFees
 * @property BalanceService $balance
 * @property BalanceTransactionService $balanceTransactions
 * @property BillingPortal\BillingPortalServiceFactory $billingPortal
 * @property ChargeService $charges
 * @property Checkout\CheckoutServiceFactory $checkout
 * @property CountrySpecService $countrySpecs
 * @property CouponService $coupons
 * @property CreditNoteService $creditNotes
 * @property CustomerService $customers
 * @property DisputeService $disputes
 * @property EphemeralKeyService $ephemeralKeys
 * @property EventService $events
 * @property ExchangeRateService $exchangeRates
 * @property FileLinkService $fileLinks
 * @property FileService $files
 * @property InvoiceItemService $invoiceItems
 * @property InvoiceService $invoices
 * @property Issuing\IssuingServiceFactory $issuing
 * @property MandateService $mandates
 * @property OAuthService $oauth
 * @property OrderReturnService $orderReturns
 * @property OrderService $orders
 * @property PaymentIntentService $paymentIntents
 * @property PaymentMethodService $paymentMethods
 * @property PayoutService $payouts
 * @property PlanService $plans
 * @property PriceService $prices
 * @property ProductService $products
 * @property PromotionCodeService $promotionCodes
 * @property Radar\RadarServiceFactory $radar
 * @property RefundService $refunds
 * @property Reporting\ReportingServiceFactory $reporting
 * @property ReviewService $reviews
 * @property SetupAttemptService $setupAttempts
 * @property SetupIntentService $setupIntents
 * @property Sigma\SigmaServiceFactory $sigma
 * @property SkuService $skus
 * @property SourceService $sources
 * @property SubscriptionItemService $subscriptionItems
 * @property SubscriptionService $subscriptions
 * @property SubscriptionScheduleService $subscriptionSchedules
 * @property TaxRateService $taxRates
 * @property Terminal\TerminalServiceFactory $terminal
 * @property TokenService $tokens
 * @property TopupService $topups
 * @property TransferService $transfers
 * @property WebhookEndpointService $webhookEndpoints
 */
class CoreServiceFactory extends \WP_Ultimo\Dependencies\Stripe\Service\AbstractServiceFactory
{
    /**
     * @var array<string, string>
     */
    private static $classMap = ['accountLinks' => \WP_Ultimo\Dependencies\Stripe\Service\AccountLinkService::class, 'accounts' => \WP_Ultimo\Dependencies\Stripe\Service\AccountService::class, 'applePayDomains' => \WP_Ultimo\Dependencies\Stripe\Service\ApplePayDomainService::class, 'applicationFees' => \WP_Ultimo\Dependencies\Stripe\Service\ApplicationFeeService::class, 'balance' => \WP_Ultimo\Dependencies\Stripe\Service\BalanceService::class, 'balanceTransactions' => \WP_Ultimo\Dependencies\Stripe\Service\BalanceTransactionService::class, 'billingPortal' => \WP_Ultimo\Dependencies\Stripe\Service\BillingPortal\BillingPortalServiceFactory::class, 'charges' => \WP_Ultimo\Dependencies\Stripe\Service\ChargeService::class, 'checkout' => \WP_Ultimo\Dependencies\Stripe\Service\Checkout\CheckoutServiceFactory::class, 'countrySpecs' => \WP_Ultimo\Dependencies\Stripe\Service\CountrySpecService::class, 'coupons' => \WP_Ultimo\Dependencies\Stripe\Service\CouponService::class, 'creditNotes' => \WP_Ultimo\Dependencies\Stripe\Service\CreditNoteService::class, 'customers' => \WP_Ultimo\Dependencies\Stripe\Service\CustomerService::class, 'disputes' => \WP_Ultimo\Dependencies\Stripe\Service\DisputeService::class, 'ephemeralKeys' => \WP_Ultimo\Dependencies\Stripe\Service\EphemeralKeyService::class, 'events' => \WP_Ultimo\Dependencies\Stripe\Service\EventService::class, 'exchangeRates' => \WP_Ultimo\Dependencies\Stripe\Service\ExchangeRateService::class, 'fileLinks' => \WP_Ultimo\Dependencies\Stripe\Service\FileLinkService::class, 'files' => \WP_Ultimo\Dependencies\Stripe\Service\FileService::class, 'invoiceItems' => \WP_Ultimo\Dependencies\Stripe\Service\InvoiceItemService::class, 'invoices' => \WP_Ultimo\Dependencies\Stripe\Service\InvoiceService::class, 'issuing' => \WP_Ultimo\Dependencies\Stripe\Service\Issuing\IssuingServiceFactory::class, 'mandates' => \WP_Ultimo\Dependencies\Stripe\Service\MandateService::class, 'oauth' => \WP_Ultimo\Dependencies\Stripe\Service\OAuthService::class, 'orderReturns' => \WP_Ultimo\Dependencies\Stripe\Service\OrderReturnService::class, 'orders' => \WP_Ultimo\Dependencies\Stripe\Service\OrderService::class, 'paymentIntents' => \WP_Ultimo\Dependencies\Stripe\Service\PaymentIntentService::class, 'paymentMethods' => \WP_Ultimo\Dependencies\Stripe\Service\PaymentMethodService::class, 'payouts' => \WP_Ultimo\Dependencies\Stripe\Service\PayoutService::class, 'plans' => \WP_Ultimo\Dependencies\Stripe\Service\PlanService::class, 'prices' => \WP_Ultimo\Dependencies\Stripe\Service\PriceService::class, 'products' => \WP_Ultimo\Dependencies\Stripe\Service\ProductService::class, 'promotionCodes' => \WP_Ultimo\Dependencies\Stripe\Service\PromotionCodeService::class, 'radar' => \WP_Ultimo\Dependencies\Stripe\Service\Radar\RadarServiceFactory::class, 'refunds' => \WP_Ultimo\Dependencies\Stripe\Service\RefundService::class, 'reporting' => \WP_Ultimo\Dependencies\Stripe\Service\Reporting\ReportingServiceFactory::class, 'reviews' => \WP_Ultimo\Dependencies\Stripe\Service\ReviewService::class, 'setupAttempts' => \WP_Ultimo\Dependencies\Stripe\Service\SetupAttemptService::class, 'setupIntents' => \WP_Ultimo\Dependencies\Stripe\Service\SetupIntentService::class, 'sigma' => \WP_Ultimo\Dependencies\Stripe\Service\Sigma\SigmaServiceFactory::class, 'skus' => \WP_Ultimo\Dependencies\Stripe\Service\SkuService::class, 'sources' => \WP_Ultimo\Dependencies\Stripe\Service\SourceService::class, 'subscriptionItems' => \WP_Ultimo\Dependencies\Stripe\Service\SubscriptionItemService::class, 'subscriptions' => \WP_Ultimo\Dependencies\Stripe\Service\SubscriptionService::class, 'subscriptionSchedules' => \WP_Ultimo\Dependencies\Stripe\Service\SubscriptionScheduleService::class, 'taxRates' => \WP_Ultimo\Dependencies\Stripe\Service\TaxRateService::class, 'terminal' => \WP_Ultimo\Dependencies\Stripe\Service\Terminal\TerminalServiceFactory::class, 'tokens' => \WP_Ultimo\Dependencies\Stripe\Service\TokenService::class, 'topups' => \WP_Ultimo\Dependencies\Stripe\Service\TopupService::class, 'transfers' => \WP_Ultimo\Dependencies\Stripe\Service\TransferService::class, 'webhookEndpoints' => \WP_Ultimo\Dependencies\Stripe\Service\WebhookEndpointService::class];
    protected function getServiceClass($name)
    {
        return \array_key_exists($name, self::$classMap) ? self::$classMap[$name] : null;
    }
}
