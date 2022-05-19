<?php
/**
 * Visit manager for sites.
 *
 * @package WP_Ultimo
 * @subpackage Objects
 * @since 2.0.0
 */

namespace WP_Ultimo\Objects;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Visit manager for sites.
 *
 * @since 2.0.0
 */
class Visits {

	/**
	 * Key to save on the database.
	 */
	const KEY = 'wu_visits';

	/**
	 * The site id.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $site_id;

	/**
	 * Sets the current site to manage.
	 *
	 * @since 2.0.0
	 *
	 * @param int $site_id The current site id.
	 */
	public function __construct($site_id) {

		$this->site_id = $site_id;

	} // end __construct;

	/**
	 * Returns the meta key to save visits.
	 *
	 * @since 2.0.0
	 *
	 * @param boolean $day The day in the Ymd format. E.g. 20210211.
	 * @return string
	 */
	protected function get_meta_key($day) {

		return sprintf('%s_%s', self::KEY, $day);

	} // end get_meta_key;

	/**
	 * Adds visits to a site count.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $count Counts to add.
	 * @param boolean $day The day in the Ymd format. E.g. 20210211.
	 * @return bool
	 */
	public function add_visit($count = 1, $day = false) {

		if (!$day) {

			$day = gmdate('Ymd');

		} // end if;

		$key = $this->get_meta_key($day);

		$current_value = (int) get_site_meta($this->site_id, $key, true);

		$new_value = $current_value + $count;

		return update_site_meta($this->site_id, $key, $new_value);

	} // end add_visit;

	/**
	 * Returns an array of the dates and counts by day.
	 *
	 * @since 2.0.0
	 *
	 * @param bool|string $start_date The start date. Any strtotime-compatible string works.
	 * @param bool|string $end_date The end date. Any strtotime-compatible string works.
	 * @return array
	 */
	public function get_visits($start_date = false, $end_date = false) {

		global $wpdb;

		if (!$start_date) {

			$start_date = wu_get_current_time('mysql', true);

		} // end if;

		if (!$end_date) {

			$end_date = wu_get_current_time('mysql', true);

		} // end if;

		$query = $wpdb->prepare("
      SELECT meta_value as count, str_to_date(meta_key, 'wu_visits_%%Y%%m%%d') as day, blog_id as site_id
      FROM {$wpdb->base_prefix}blogmeta 
      WHERE blog_id = %d
    ", $this->site_id);

		$query .= $wpdb->prepare(" AND str_to_date(meta_key, 'wu_visits_%%Y%%m%%d') BETWEEN %s AND %s", gmdate('Y-m-d', strtotime($start_date)), gmdate('Y-m-d', strtotime($end_date)));

		return $wpdb->get_results($query); // phpcs:ignore

	} // end get_visits;

	/**
	 * The total visits for the current site.
	 *
	 * @since 2.0.0
	 *
	 * @param bool|string $start_date The start date. Any strtotime-compatible string works.
	 * @param bool|string $end_date The end date. Any strtotime-compatible string works.
	 * @return int
	 */
	public function get_visit_total($start_date = false, $end_date = false) {

		global $wpdb;

		if (!$start_date) {

			$start_date = wu_get_current_time('mysql', true);

		} // end if;

		if (!$end_date) {

			$end_date = wu_get_current_time('mysql', true);

		} // end if;

		$query = $wpdb->prepare("
      SELECT SUM(meta_value) as count
      FROM {$wpdb->base_prefix}blogmeta 
      WHERE blog_id = %d
    ", $this->site_id);

		$query .= $wpdb->prepare(" AND str_to_date(meta_key, 'wu_visits_%%Y%%m%%d') BETWEEN %s AND %s", gmdate('Y-m-d', strtotime($start_date)), gmdate('Y-m-d', strtotime($end_date)));

		return (int) $wpdb->get_var($query); // phpcs:ignore

	} // end get_visit_total;

	/**
	 * Get sites by visit count.
	 *
	 * @since 2.0.0
	 *
	 * @param bool|string $start_date The start date. Any strtotime-compatible string works.
	 * @param bool|string $end_date The end date. Any strtotime-compatible string works.
	 * @param integer     $limit The number of sites to return.
	 * @return array
	 */
	public static function get_sites_by_visit_count($start_date = false, $end_date = false, $limit = 5) {

		global $wpdb;

		if (!$start_date) {

			$start_date = wu_get_current_time('mysql', true);

		} // end if;

		if (!$end_date) {

			$end_date = wu_get_current_time('mysql', true);

		} // end if;

		$sub_query = "
      SELECT SUM(meta_value) as count, blog_id
      FROM {$wpdb->base_prefix}blogmeta as m
    ";

		$sub_query .= $wpdb->prepare(" WHERE str_to_date(meta_key, 'wu_visits_%%Y%%m%%d') BETWEEN %s AND %s", gmdate('Y-m-d', strtotime($start_date)), gmdate('Y-m-d', strtotime($end_date)));

		$sub_query .= ' GROUP BY blog_id';

    // phpcs:disable
		$query = $wpdb->prepare("
      SELECT b.blog_id as site_id, s.count
      FROM {$wpdb->base_prefix}blogs as b
      JOIN ({$sub_query}) as s
      ON s.blog_id = b.blog_id
      ORDER BY count DESC
      LIMIT %d
    ", $limit);
    // phpcs:enable

		return $wpdb->get_results($query); // phpcs:ignore

	} // end get_sites_by_visit_count;

} // end class Visits;
