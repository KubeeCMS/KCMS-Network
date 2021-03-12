<?php

// File generated from our OpenAPI spec
namespace WP_Ultimo\Dependencies\Stripe\Util;

class ObjectTypes
{
    /**
     * @var array Mapping from object types to resource classes
     */
    const mapping = [\WP_Ultimo\Dependencies\Stripe\Account::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Account::class, \WP_Ultimo\Dependencies\Stripe\AccountLink::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\AccountLink::class, \WP_Ultimo\Dependencies\Stripe\AlipayAccount::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\AlipayAccount::class, \WP_Ultimo\Dependencies\Stripe\ApplePayDomain::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\ApplePayDomain::class, \WP_Ultimo\Dependencies\Stripe\ApplicationFee::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\ApplicationFee::class, \WP_Ultimo\Dependencies\Stripe\ApplicationFeeRefund::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\ApplicationFeeRefund::class, \WP_Ultimo\Dependencies\Stripe\Balance::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Balance::class, \WP_Ultimo\Dependencies\Stripe\BalanceTransaction::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\BalanceTransaction::class, \WP_Ultimo\Dependencies\Stripe\BankAccount::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\BankAccount::class, \WP_Ultimo\Dependencies\Stripe\BillingPortal\Session::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\BillingPortal\Session::class, \WP_Ultimo\Dependencies\Stripe\BitcoinReceiver::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\BitcoinReceiver::class, \WP_Ultimo\Dependencies\Stripe\BitcoinTransaction::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\BitcoinTransaction::class, \WP_Ultimo\Dependencies\Stripe\Capability::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Capability::class, \WP_Ultimo\Dependencies\Stripe\Card::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Card::class, \WP_Ultimo\Dependencies\Stripe\Charge::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Charge::class, \WP_Ultimo\Dependencies\Stripe\Checkout\Session::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Checkout\Session::class, \WP_Ultimo\Dependencies\Stripe\Collection::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Collection::class, \WP_Ultimo\Dependencies\Stripe\CountrySpec::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\CountrySpec::class, \WP_Ultimo\Dependencies\Stripe\Coupon::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Coupon::class, \WP_Ultimo\Dependencies\Stripe\CreditNote::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\CreditNote::class, \WP_Ultimo\Dependencies\Stripe\CreditNoteLineItem::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\CreditNoteLineItem::class, \WP_Ultimo\Dependencies\Stripe\Customer::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Customer::class, \WP_Ultimo\Dependencies\Stripe\CustomerBalanceTransaction::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\CustomerBalanceTransaction::class, \WP_Ultimo\Dependencies\Stripe\Discount::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Discount::class, \WP_Ultimo\Dependencies\Stripe\Dispute::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Dispute::class, \WP_Ultimo\Dependencies\Stripe\EphemeralKey::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\EphemeralKey::class, \WP_Ultimo\Dependencies\Stripe\Event::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Event::class, \WP_Ultimo\Dependencies\Stripe\ExchangeRate::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\ExchangeRate::class, \WP_Ultimo\Dependencies\Stripe\File::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\File::class, \WP_Ultimo\Dependencies\Stripe\File::OBJECT_NAME_ALT => \WP_Ultimo\Dependencies\Stripe\File::class, \WP_Ultimo\Dependencies\Stripe\FileLink::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\FileLink::class, \WP_Ultimo\Dependencies\Stripe\Invoice::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Invoice::class, \WP_Ultimo\Dependencies\Stripe\InvoiceItem::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\InvoiceItem::class, \WP_Ultimo\Dependencies\Stripe\InvoiceLineItem::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\InvoiceLineItem::class, \WP_Ultimo\Dependencies\Stripe\Issuing\Authorization::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Issuing\Authorization::class, \WP_Ultimo\Dependencies\Stripe\Issuing\Card::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Issuing\Card::class, \WP_Ultimo\Dependencies\Stripe\Issuing\CardDetails::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Issuing\CardDetails::class, \WP_Ultimo\Dependencies\Stripe\Issuing\Cardholder::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Issuing\Cardholder::class, \WP_Ultimo\Dependencies\Stripe\Issuing\Dispute::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Issuing\Dispute::class, \WP_Ultimo\Dependencies\Stripe\Issuing\Transaction::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Issuing\Transaction::class, \WP_Ultimo\Dependencies\Stripe\LineItem::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\LineItem::class, \WP_Ultimo\Dependencies\Stripe\LoginLink::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\LoginLink::class, \WP_Ultimo\Dependencies\Stripe\Mandate::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Mandate::class, \WP_Ultimo\Dependencies\Stripe\Order::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Order::class, \WP_Ultimo\Dependencies\Stripe\OrderItem::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\OrderItem::class, \WP_Ultimo\Dependencies\Stripe\OrderReturn::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\OrderReturn::class, \WP_Ultimo\Dependencies\Stripe\PaymentIntent::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\PaymentIntent::class, \WP_Ultimo\Dependencies\Stripe\PaymentMethod::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\PaymentMethod::class, \WP_Ultimo\Dependencies\Stripe\Payout::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Payout::class, \WP_Ultimo\Dependencies\Stripe\Person::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Person::class, \WP_Ultimo\Dependencies\Stripe\Plan::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Plan::class, \WP_Ultimo\Dependencies\Stripe\Price::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Price::class, \WP_Ultimo\Dependencies\Stripe\Product::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Product::class, \WP_Ultimo\Dependencies\Stripe\PromotionCode::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\PromotionCode::class, \WP_Ultimo\Dependencies\Stripe\Radar\EarlyFraudWarning::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Radar\EarlyFraudWarning::class, \WP_Ultimo\Dependencies\Stripe\Radar\ValueList::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Radar\ValueList::class, \WP_Ultimo\Dependencies\Stripe\Radar\ValueListItem::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Radar\ValueListItem::class, \WP_Ultimo\Dependencies\Stripe\Recipient::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Recipient::class, \WP_Ultimo\Dependencies\Stripe\RecipientTransfer::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\RecipientTransfer::class, \WP_Ultimo\Dependencies\Stripe\Refund::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Refund::class, \WP_Ultimo\Dependencies\Stripe\Reporting\ReportRun::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Reporting\ReportRun::class, \WP_Ultimo\Dependencies\Stripe\Reporting\ReportType::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Reporting\ReportType::class, \WP_Ultimo\Dependencies\Stripe\Review::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Review::class, \WP_Ultimo\Dependencies\Stripe\SetupAttempt::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\SetupAttempt::class, \WP_Ultimo\Dependencies\Stripe\SetupIntent::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\SetupIntent::class, \WP_Ultimo\Dependencies\Stripe\Sigma\ScheduledQueryRun::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Sigma\ScheduledQueryRun::class, \WP_Ultimo\Dependencies\Stripe\SKU::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\SKU::class, \WP_Ultimo\Dependencies\Stripe\Source::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Source::class, \WP_Ultimo\Dependencies\Stripe\SourceTransaction::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\SourceTransaction::class, \WP_Ultimo\Dependencies\Stripe\Subscription::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Subscription::class, \WP_Ultimo\Dependencies\Stripe\SubscriptionItem::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\SubscriptionItem::class, \WP_Ultimo\Dependencies\Stripe\SubscriptionSchedule::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\SubscriptionSchedule::class, \WP_Ultimo\Dependencies\Stripe\TaxId::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\TaxId::class, \WP_Ultimo\Dependencies\Stripe\TaxRate::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\TaxRate::class, \WP_Ultimo\Dependencies\Stripe\Terminal\ConnectionToken::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Terminal\ConnectionToken::class, \WP_Ultimo\Dependencies\Stripe\Terminal\Location::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Terminal\Location::class, \WP_Ultimo\Dependencies\Stripe\Terminal\Reader::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Terminal\Reader::class, \WP_Ultimo\Dependencies\Stripe\ThreeDSecure::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\ThreeDSecure::class, \WP_Ultimo\Dependencies\Stripe\Token::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Token::class, \WP_Ultimo\Dependencies\Stripe\Topup::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Topup::class, \WP_Ultimo\Dependencies\Stripe\Transfer::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\Transfer::class, \WP_Ultimo\Dependencies\Stripe\TransferReversal::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\TransferReversal::class, \WP_Ultimo\Dependencies\Stripe\UsageRecord::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\UsageRecord::class, \WP_Ultimo\Dependencies\Stripe\UsageRecordSummary::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\UsageRecordSummary::class, \WP_Ultimo\Dependencies\Stripe\WebhookEndpoint::OBJECT_NAME => \WP_Ultimo\Dependencies\Stripe\WebhookEndpoint::class];
}
