<?php
/**
 * Post Types Limit Module.
 *
 * @package WP_Ultimo
 * @subpackage Limitations
 * @since 2.0.0
 */

namespace WP_Ultimo\Limitations;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Post Types Limit Module.
 *
 * @since 2.0.0
 */
class Limit_Post_Types extends Limit_Subtype {

	/**
	 * The module id.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $id = 'post_types';

	/**
	 * Check if we are already above the post quota.
	 *
	 * @since 2.0.0
	 *
	 * @param string $post_type The post type to check against.
	 * @return boolean
	 */
	public function is_post_above_limit($post_type) {
		/*
		 * Calculate post count based on all different status
		 */
		$post_count = $this->get_post_count($post_type);

		// Get the allowed quota
		$quota = $this->{$post_type}->number;

		/**
		 * Checks if a given post type is allowed on this plan
		 * Allow plugin developers to filter the return value
		 *
		 * @since 1.7.0
		 * @param bool If the post type is disabled or not
		 * @param WU_Plan Plan of the current user
		 * @param int User id
		 */
		return apply_filters('wu_limits_is_post_above_limit', $quota > 0 && $post_count >= $quota);

	} // end is_post_above_limit;

	/**
	 * Get the post count for this site.
	 *
	 * @since 2.0.0
	 *
	 * @param string $post_type The post type to check against.
	 * @return int
	 */
	public static function get_post_count($post_type) {

		$count = 0;

		$post_count = wp_count_posts($post_type);

		$statuses = $post_type === 'attachment' ? array('inherit') : array('publish', 'private');

		/**
		 * Allow plugin developers to change which post status should be counted
		 * By default, published and private posts are counted
		 *
		 * @since 1.9.1
		 * @param array $post_status The list of post statuses
		 * @param string $post_type  The post type slug
		 * @return array New array of post status
		 */
		$post_statuses = apply_filters('wu_post_count_statuses', $statuses, $post_type);

		foreach ($post_statuses as $post_status) {

			if (isset($post_count->{$post_status})) {

				$count += (int) $post_count->{$post_status};

			} // end if;

		} // end foreach;

		/**
		 * Allow plugin developers to change the count total
		 *
		 * @since 1.9.1
		 * @param int $count The total post count
		 * @param object $post_counts WordPress object return by the wp_count_posts fn
		 * @param string $post_type  The post type slug
		 * @return int New total
		 */
		return apply_filters('wu_post_count', $count, $post_count, $post_type);

	} // end get_post_count;

} // end class Limit_Post_Types;
