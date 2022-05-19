<?php
/**
 * Financial Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Database\Payments\Payment_Status;
use \WP_Ultimo\Database\Memberships\Membership_Status;

/**
 * Calculates the Monthly Recurring Revenue of the network.
 *
 * @since 2.0.0
 * @return float
 */
function wu_calculate_mrr() {

	$total_mrr = 0;

	$memberships = wu_get_memberships(array(
		'recurring'  => true,
		'status__in' => array(
			Membership_Status::ACTIVE,
		),
	));

	foreach ($memberships as $membership) {

		$recurring_amount = $membership->get_amount();

		if (!$membership->is_recurring()) {

			continue;

		} // end if;

		$duration = $membership->get_duration() ? $membership->get_duration() : 1;

		$duration_unit = $membership->get_duration_unit();

		$normalized_duration_unit = wu_convert_duration_unit_to_month($duration_unit);

		$mrr = $recurring_amount / $duration * $normalized_duration_unit;

		$total_mrr += $mrr;

	} // end foreach;

	return $total_mrr;

} // end wu_calculate_mrr;

/**
 * Converts the duration unit strings such as 'day', 'year' and such into
 * a integer/float representing the amount of monhts.
 *
 * @since 2.0.0
 *
 * @param string $duration_unit The duration unit.
 * @return float
 */
function wu_convert_duration_unit_to_month($duration_unit) {

	$months = 1;

	switch ($duration_unit) {

		case 'day':
			$months = 1 / 30;
            break;

		case 'week':
			$months = 1 / 4;
            break;

		case 'month':
			$months = 1;
            break;

		case 'year':
			$months = 12;
            break;

	} // end switch;

	return $months;

} // end wu_convert_duration_unit_to_month;

/**
 * Calculates the Annual Recurring Revenue.
 *
 * It is basically MRR * 12.
 *
 * @since 2.0.0
 * @return float
 */
function wu_calculate_arr() {

	return wu_calculate_mrr() * 12;

} // end wu_calculate_arr;

/**
 * Calculates the total revenue.
 *
 * @since 2.0.0
 *
 * @param string  $start_date The start date for the stat.
 * @param string  $end_date The end date for the stat.
 * @param boolean $inclusive If true, will include payments on the start and end date.
 * @return float
 */
function wu_calculate_revenue($start_date = false, $end_date = false, $inclusive = true) {

	$total_revenue = 0;

	$query_args = array(
		'fields'     => array('total'),
		'date_query' => array(),
		'status__in' => array(
			Payment_Status::COMPLETED,
			Payment_Status::PARTIAL,
		),
	);

	if ($start_date) {

		$query_args['date_query']['column']    = 'date_created';
		$query_args['date_query']['after']     = $start_date;
		$query_args['date_query']['inclusive'] = $inclusive;

	} // end if;

	if ($end_date) {

		$query_args['date_query']['column']    = 'date_created';
		$query_args['date_query']['before']    = $end_date;
		$query_args['date_query']['inclusive'] = $inclusive;

	} // end if;

	$payments = wu_get_payments($query_args);

	foreach ($payments as $payment) {

		$total_revenue += (float) $payment->total;

	} // end foreach;

	return $total_revenue;

} // end wu_calculate_revenue;

/**
 * Calculates the total refunds.
 *
 * @since 2.0.0
 *
 * @param string  $start_date The start date for the stat.
 * @param string  $end_date The end date for the stat.
 * @param boolean $inclusive If true, will include payments on the start and end date.
 * @return float
 */
function wu_calculate_refunds($start_date = false, $end_date = false, $inclusive = true) {

	$total_revenue = 0;

	$query_args = array(
		'fields'     => array('refund_total'),
		'date_query' => array(),
		'status__in' => array(
			Payment_Status::REFUND,
			Payment_Status::PARTIAL_REFUND,
		),
	);

	if ($start_date) {

		$query_args['date_query']['column']    = 'date_created';
		$query_args['date_query']['after']     = $start_date;
		$query_args['date_query']['inclusive'] = $inclusive;

	} // end if;

	if ($end_date) {

		$query_args['date_query']['column']    = 'date_created';
		$query_args['date_query']['before']    = $end_date;
		$query_args['date_query']['inclusive'] = $inclusive;

	} // end if;

	$payments = wu_get_payments($query_args);

	foreach ($payments as $payment) {

		$total_revenue += -(float) $payment->refund_total;

	} // end foreach;

	return $total_revenue;

} // end wu_calculate_refunds;

/**
 * Calculates the taxes collected grouped by the rate.
 *
 * @since 2.0.0
 *
 * @param string  $start_date The start date to compile data.
 * @param string  $end_date The end date to compile data.
 * @param boolean $inclusive To include or not the start and end date.
 * @return array
 */
function wu_calculate_taxes_by_rate($start_date = false, $end_date = false, $inclusive = true) {

	$query_args = array(
		'date_query' => array(),
	);

	if ($start_date) {

		$query_args['date_query']['after']     = $start_date;
		$query_args['date_query']['inclusive'] = $inclusive;

	} // end if;

	if ($end_date) {

		$query_args['date_query']['before']    = $end_date;
		$query_args['date_query']['inclusive'] = $inclusive;

	} // end if;

	$order = 0;

	$taxes_paid_list = array();

	$line_items_groups = \WP_Ultimo\Checkout\Line_Item::get_line_items($query_args);

	foreach ($line_items_groups as $line_items_group) {

		$order++;

		foreach ($line_items_group as $line_item) {

			$tax_name = $line_item->get_tax_label();

			if ($line_item->get_tax_rate() <= 0) {

				continue;

			} // end if;

			if (!wu_get_isset($taxes_paid_list, $tax_name)) {

				$taxes_paid_list[$tax_name] = array(
					'title'       => $tax_name,
					'country'     => '',
					'state'       => '',
					'order_count' => $order,
					'tax_rate'    => $line_item->get_tax_rate(),
					'tax_total'   => $line_item->get_tax_total(),
				);

			} else {

				$taxes_paid_list[$tax_name]['tax_total']   += $line_item->get_tax_total();
				$taxes_paid_list[$tax_name]['order_count'] += $order;

			} // end if;

		} // end foreach;

	} // end foreach;

	return $taxes_paid_list;

} // end wu_calculate_taxes_by_rate;

/**
 * Aggregate financial data on a per product basis.
 *
 * @since 2.0.0
 *
 * @param string  $start_date The start date to compile data.
 * @param string  $end_date The end date to compile data.
 * @param boolean $inclusive To include or not the start and end date.
 * @return array
 */
function wu_calculate_financial_data_by_product($start_date = false, $end_date = false, $inclusive = true) {

	$query_args = array(
		'date_query' => array(),
	);

	if ($start_date) {

		$query_args['date_query']['after']     = $start_date;
		$query_args['date_query']['inclusive'] = $inclusive;

	} // end if;

	if ($end_date) {

		$query_args['date_query']['before']    = $end_date;
		$query_args['date_query']['inclusive'] = $inclusive;

	} // end if;

	$line_items_groups = \WP_Ultimo\Checkout\Line_Item::get_line_items($query_args);

	$data = array();

	$products = wu_get_products();

	foreach ($products as $product) {

		$data[$product->get_id()] = array(
			'label'   => $product->get_name(),
			'revenue' => 0,
		);

	} // end foreach;

	foreach ($line_items_groups as $line_items_group) {

		foreach ($line_items_group as $line_item) {

			$product_id = $line_item->get_product_id();

			if (empty($product_id)) {

				continue;

			} // end if;

			if (!wu_get_isset($data, $product_id)) {

				continue;

			} // end if;

			$data[$product_id]['revenue'] += $line_item->get_total();

		} // end foreach;

	} // end foreach;

	uasort($data, function($a, $b) {

		return wu_sort_by_column($b, $a, 'revenue');

	});

	return $data;

} // end wu_calculate_financial_data_by_product;

/**
 * Calculates the taxes collected grouped by date.
 *
 * @since 2.0.0
 *
 * @param string  $start_date The start date to compile data.
 * @param string  $end_date The end date to compile data.
 * @param boolean $inclusive To include or not the start and end date.
 * @return array
 */
function wu_calculate_taxes_by_day($start_date = false, $end_date = false, $inclusive = true) {

	$query_args = array(
		'date_query' => array(),
	);

	if ($start_date) {

		$query_args['date_query']['after']     = $start_date;
		$query_args['date_query']['inclusive'] = $inclusive;

	} // end if;

	if ($end_date) {

		$query_args['date_query']['before']    = $end_date;
		$query_args['date_query']['inclusive'] = $inclusive;

	} // end if;

	$line_items_groups = \WP_Ultimo\Checkout\Line_Item::get_line_items($query_args);

	$data = array();

	$period = new \DatePeriod(
		new \DateTime($start_date),
		new \DateInterval('P1D'),
		new \DateTime($end_date . ' 23:59:59')
	);

	$days = array_reverse(iterator_to_array($period));

	foreach ($days as $day_datetime) {

		$data[$day_datetime->format('Y-m-d')] = array(
			'order_count' => 0,
			'total'       => 0,
			'tax_total'   => 0,
			'net_profit'  => 0,
		);

	} // end foreach;

	foreach ($line_items_groups as $line_items_group) {

		foreach ($line_items_group as $line_item) {

			$date = gmdate('Y-m-d', strtotime($line_item->date_created));

			if (!wu_get_isset($data, $date)) {

				$data[$date] = array(
					'order_count' => 0,
					'total'       => $line_item->get_total(),
					'tax_total'   => $line_item->get_tax_total(),
					'net_profit'  => $line_item->get_total() - $line_item->get_tax_total(),
				);

			} else {

				$data[$date]['order_count'] += 1;
				$data[$date]['total']       += $line_item->get_total();
				$data[$date]['tax_total']   += $line_item->get_tax_total();
				$data[$date]['net_profit']  += $line_item->get_total() - $line_item->get_tax_total();

			} // end if;

		} // end foreach;

	} // end foreach;

	return $data;

} // end wu_calculate_taxes_by_day;

/**
 * Calculates the taxes collected this year, segregated by month.
 *
 * @since 2.0.0
 * @return array
 */
function wu_calculate_taxes_by_month() {

	$cache = get_site_transient('wu_tax_monthly_stats');

	if (is_array($cache)) {

		return $cache;

	} // end if;

	$query_args = array(
		'date_query' => array(
			'after'     => 'first day of January this year',
			'before'    => 'last day of December this year',
			'inclusive' => true,
		),
	);

	$line_items_groups = \WP_Ultimo\Checkout\Line_Item::get_line_items($query_args);

	$data = array();

	$period = new \DatePeriod(
		new \DateTime($query_args['date_query']['after']),
		new \DateInterval('P1M'),
		new \DateTime($query_args['date_query']['before'])
	);

	$months = iterator_to_array($period);

	foreach ($months as $month_datetime) {

		$data[$month_datetime->format('n')] = array(
			'order_count' => 0,
			'total'       => 0,
			'tax_total'   => 0,
			'net_profit'  => 0,
		);

	} // end foreach;

	foreach ($line_items_groups as $line_items_group) {

		foreach ($line_items_group as $line_item) {

			$date = gmdate('n', strtotime($line_item->date_created));

			if (!wu_get_isset($data, $date)) {

				$data[$date] = array(
					'order_count' => 0,
					'total'       => $line_item->get_total(),
					'tax_total'   => $line_item->get_tax_total(),
					'net_profit'  => $line_item->get_total() - $line_item->get_tax_total(),
				);

			} else {

				$data[$date]['order_count'] += 1;
				$data[$date]['total']       += $line_item->get_total();
				$data[$date]['tax_total']   += $line_item->get_tax_total();
				$data[$date]['net_profit']  += $line_item->get_total() - $line_item->get_tax_total();

			} // end if;

		} // end foreach;

	} // end foreach;

	set_site_transient('wu_tax_monthly_stats', $data);

	return $data;

} // end wu_calculate_taxes_by_month;

/**
 * Returns the number of sign-ups by form slug.
 *
 * @since 2.0.0
 *
 * @param string  $start_date The start date to compile data.
 * @param string  $end_date The end date to compile data.
 * @param boolean $inclusive To include or not the start and end date.
 * @return array
 */
function wu_calculate_signups_by_form($start_date = false, $end_date = false, $inclusive = true) {

	global $wpdb;

	$query = array(
		'date_query' => array(),
	);

	if ($start_date) {

		$query['date_query']['after']     = $start_date;
		$query['date_query']['inclusive'] = $inclusive;

	} // end if;

	if ($end_date) {

		$query['date_query']['before']    = $end_date;
		$query['date_query']['inclusive'] = $inclusive;

	} // end if;

	$date_query = new \WP_Date_Query($query['date_query']);

	$date_query_sql = $date_query->get_sql();

	$date_query_sql = str_replace($wpdb->base_prefix . 'posts.post_date', 'date_registered', $date_query_sql);

	// phpcs:disable;
	$query_sql = "
		SELECT signup_form, COUNT(id) as count
		FROM {$wpdb->base_prefix}wu_customers as d
		WHERE 1 = 1
		AND signup_form IS NOT NULL
		{$date_query_sql}
		GROUP BY signup_form
		ORDER BY count DESC
	";

	$results = $wpdb->get_results($query_sql); // phpcs:ignore

	return $results;

} // end wu_calculate_signups_by_form;
