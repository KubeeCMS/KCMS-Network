<?php
/**
 * Schema for discount@code-create.
 *
 * @package WP_Ultimo\API\Schemas
 * @since 2.0.11
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Schema for discount@code-create.
 *
 * @since 2.0.11
 * @internal last-generated in 2022-05
 * @generated class generated by our build scripts, do not change!
 *
 * @since 2.0.11
 */
return array(
	'name'              => array(
		'description' => __('Your discount code name, which is used as discount code title as well.', 'wp-ultimo'),
		'type'        => 'string',
		'required'    => true,
	),
	'code'              => array(
		'description' => __('A unique identification to redeem the discount code. E.g. PROMO10.', 'wp-ultimo'),
		'type'        => 'string',
		'required'    => true,
	),
	'description'       => array(
		'description' => __('A description for the discount code, usually a short text.', 'wp-ultimo'),
		'type'        => 'string',
		'required'    => false,
	),
	'uses'              => array(
		'description' => __('Number of times this discount was applied.', 'wp-ultimo'),
		'type'        => 'integer',
		'required'    => false,
	),
	'max_uses'          => array(
		'description' => __('The number of times this discount can be used before becoming inactive.', 'wp-ultimo'),
		'type'        => 'integer',
		'required'    => false,
	),
	'apply_to_renewals' => array(
		'description' => __('Wether or not we should apply the discount to membership renewals.', 'wp-ultimo'),
		'type'        => 'boolean',
		'required'    => false,
	),
	'type'              => array(
		'description' => __("The type of the discount code. Can be 'percentage' (e.g. 10%% OFF), 'absolute' (e.g. $10 OFF).", 'wp-ultimo'),
		'type'        => 'string',
		'required'    => false,
		'enum'        => array(
			'percentage',
			'absolute',
		),
	),
	'value'             => array(
		'description' => __('Amount discounted in cents.', 'wp-ultimo'),
		'type'        => 'integer',
		'required'    => true,
	),
	'setup_fee_type'    => array(
		'description' => __('Type of the discount for the setup fee value. Can be a percentage or absolute.', 'wp-ultimo'),
		'type'        => 'string',
		'required'    => false,
		'enum'        => array(
			'percentage',
			'absolute',
		),
	),
	'setup_fee_value'   => array(
		'description' => __('Amount discounted for setup fees in cents.', 'wp-ultimo'),
		'type'        => 'integer',
		'required'    => false,
	),
	'active'            => array(
		'description' => __('Set this discount code as active (true), which means available to be used, or inactive (false).', 'wp-ultimo'),
		'type'        => 'boolean',
		'required'    => false,
	),
	'date_start'        => array(
		'description' => __('Start date for the coupon code to be considered valid.', 'wp-ultimo'),
		'type'        => 'string',
		'required'    => false,
	),
	'date_expiration'   => array(
		'description' => __('Expiration date for the coupon code.', 'wp-ultimo'),
		'type'        => 'string',
		'required'    => false,
	),
	'date_created'      => array(
		'description' => __('Date when this discount code was created.', 'wp-ultimo'),
		'type'        => 'string',
		'required'    => false,
	),
	'allowed_products'  => array(
		'description' => __('The list of products that allows this discount code to be used. If empty, all products will accept this code.', 'wp-ultimo'),
		'type'        => 'array',
		'required'    => false,
	),
	'limit_products'    => array(
		'description' => __('This discount code will be limited to be used in certain products? If set to true, you must define a list of allowed products.', 'wp-ultimo'),
		'type'        => 'boolean',
		'required'    => false,
	),
	'date_modified'     => array(
		'description' => __('Model last modification date.', 'wp-ultimo'),
		'type'        => 'string',
		'required'    => false,
	),
	'migrated_from_id'  => array(
		'description' => __('The ID of the original 1.X model that was used to generate this item on migration.', 'wp-ultimo'),
		'type'        => 'integer',
		'required'    => false,
	),
	'skip_validation'   => array(
		'description' => __('Set true to have field information validation bypassed when saving this event.', 'wp-ultimo'),
		'type'        => 'boolean',
		'required'    => false,
	),
);
