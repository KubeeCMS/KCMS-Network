<?php
/**
 * Schema for broadcast@update.
 *
 * @package WP_Ultimo\API\Schemas
 * @since 2.0.11
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Schema for broadcast@update.
 *
 * @since 2.0.11
 * @internal last-generated in 2022-05
 * @generated class generated by our build scripts, do not change!
 *
 * @since 2.0.11
 */
return array(
	'migrated_from_id' => array(
		'description' => __('The ID of the original 1.X model that was used to generate this item on migration.', 'wp-ultimo'),
		'type'        => 'integer',
		'required'    => false,
	),
	'notice_type'      => array(
		'description' => __('Can be info, success, warning or danger.', 'wp-ultimo'),
		'type'        => 'string',
		'required'    => false,
		'enum'        => array(
			'info',
			'success',
			'warning',
			'danger',
		),
	),
	'name'             => array(
		'description' => __('This broadcast name, which is used as broadcast title as well.', 'wp-ultimo'),
		'type'        => 'string',
		'required'    => false,
	),
	'type'             => array(
		'description' => __('The type being set.', 'wp-ultimo'),
		'type'        => 'string',
		'required'    => false,
	),
	'status'           => array(
		'description' => __('The status being set.', 'wp-ultimo'),
		'type'        => 'string',
		'required'    => false,
	),
	'author_id'        => array(
		'description' => __('The author ID.', 'wp-ultimo'),
		'type'        => 'integer',
		'required'    => false,
	),
	'title'            => array(
		'description' => __('Post title.', 'wp-ultimo'),
		'type'        => 'string',
		'required'    => false,
	),
	'content'          => array(
		'description' => __('Post content.', 'wp-ultimo'),
		'type'        => 'string',
		'required'    => false,
	),
	'excerpt'          => array(
		'description' => __('Post excerpt.', 'wp-ultimo'),
		'type'        => 'string',
		'required'    => false,
	),
	'date_created'     => array(
		'description' => __('Post creation date.', 'wp-ultimo'),
		'type'        => 'string',
		'required'    => false,
	),
	'date_modified'    => array(
		'description' => __('Post last modification date.', 'wp-ultimo'),
		'type'        => 'string',
		'required'    => false,
	),
	'slug'             => array(
		'description' => __('The slug.', 'wp-ultimo'),
		'type'        => 'mixed',
		'required'    => false,
	),
	'skip_validation'  => array(
		'description' => __('Set true to have field information validation bypassed when saving this event.', 'wp-ultimo'),
		'type'        => 'boolean',
		'required'    => false,
	),
);
