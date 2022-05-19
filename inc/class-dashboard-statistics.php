<?php
/**
 * WP Ultimo Dashboard Statistics.
 *
 * Log string messages to a file with a timestamp. Useful for debugging.
 *
 * @package WP_Ultimo
 * @subpackage Logger
 * @since 2.0.0
 */

namespace WP_Ultimo;

use \WP_Ultimo\Models\Membership;
use \WP_Ultimo\Models\Payment;
use \WP_Ultimo\Database\Payments\Payment_Status;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo Dashboard Statistics
 *
 * @since 2.0.0
 */
class Dashboard_Statistics {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * The initial date of the statistics.
	 *
	 * @var string
	 */
	protected $start_date;

	/**
	 * The final date of the statistics.
	 *
	 * @var string
	 */
	protected $end_date;

	/**
	 * What kind of information you need.
	 *
	 * @var array
	 */
	protected $types = array();

	/**
	 * Loads the hooks we need for dismissing notices
	 *
	 * @since 2.0.0
	 *
	 * @param array $args With the start_date, end_date and the data type functions.
	 * @return void.
	 */
	public function __construct($args = array()) {

		if ($args) {

			$this->start_date = $args['start_date'];

			$this->end_date = $args['end_date'];

			$this->types = $args['types'];

		} // end if;

	} // end __construct;

	/**
	 * Runs on singleton instantiation.
	 *
	 * @since 2.0.0
	 * @return void.
	 */
	public function init() {} // end init;

	/**
	 * Main function to call the get data functions based on the array of types.
	 *
	 * @since 2.0.0
	 * @return array With all the data requested.
	 */
	public function statistics_data() {

		$data = array();

		foreach ($this->types as $key => $type) {

			$data_function = 'get_data_' . $type;

			$data[$key] = $this->$data_function();

		} // end foreach;

		return $data;

	} // end statistics_data;

	/**
	 * Get data of all completed and refunded payments to show in the main graph.
	 *
	 * @since 2.0.0
	 * @return array With total gross data.
	 */
	public function get_data_mrr_growth() {

		$payments_per_month = array(
			'january'   => array(
				'total'     => 0,
				'cancelled' => 0,
			),
			'february'  => array(
				'total'     => 0,
				'cancelled' => 0,
			),
			'march'     => array(
				'total'     => 0,
				'cancelled' => 0,
			),
			'april'     => array(
				'total'     => 0,
				'cancelled' => 0,
			),
			'may'       => array(
				'total'     => 0,
				'cancelled' => 0,
			),
			'june'      => array(
				'total'     => 0,
				'cancelled' => 0,
			),
			'july'      => array(
				'total'     => 0,
				'cancelled' => 0,
			),
			'august'    => array(
				'total'     => 0,
				'cancelled' => 0,
			),
			'september' => array(
				'total'     => 0,
				'cancelled' => 0,
			),
			'october'   => array(
				'total'     => 0,
				'cancelled' => 0,
			),
			'november'  => array(
				'total'     => 0,
				'cancelled' => 0,
			),
			'december'  => array(
				'total'     => 0,
				'cancelled' => 0,
			),
		);

		$memberships = wu_get_memberships(array(
			'date_query' => array(
				'column'   => 'date_created',
				'compare'  => 'BETWEEN',
				'relation' => '',
				array(
					'year' => current_time('Y', true),
				),
			)
		));

		$mrr_status = array(
			'active',
			'cancelled',
			'expired',
		);

		$churn_status = array(
			'cancelled',
			'expired',
		);

		foreach ($memberships as $membership) {

			if (!$membership->is_recurring()) {

				continue;

			} // end if;

			$status = $membership->get_status();

			if (in_array($status, $mrr_status, true)) {

				$data = getdate(strtotime($membership->get_date_created()));

				$month = strtolower($data['month']);

				$payments_per_month[$month]['total'] += floatval($membership->get_normalized_amount());

			} // end if;

			if (in_array($status, $churn_status, true)) {

				$data = getdate(strtotime($membership->get_date_cancellation()));

				$month = strtolower($data['month']);

				$payments_per_month[$month]['cancelled'] += floatval($membership->get_normalized_amount());

			} // end if;

		} // end foreach;

		return $payments_per_month;

	} // end get_data_mrr_growth;

} // end class Dashboard_Statistics;
