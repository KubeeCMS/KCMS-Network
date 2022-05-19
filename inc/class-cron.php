<?php
/**
 * Cron Checks
 *
 * Adds the recurring events we use to
 * check if memberships should be manually
 * renewed, marked as expired, etc.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Membership_Manager
 * @since 2.0.0
 */

namespace WP_Ultimo;

use \WP_Ultimo\Database\Memberships\Membership_Status;
use \WP_Ultimo\Database\Payments\Payment_Status;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds the recurring events we use to
 * check if memberships should be manually
 * renewed, marked as expired, etc.
 *
 * @since 2.0.0
 */
class Cron {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {
		/*
		 * Creates general schedules for general uses.
		 */
		add_action('init', array($this, 'create_schedules'));

		/*
		 * Deals with renewals for non-auto-renewing
		 * memberships.
     *
     * First hook registers the check schedule.
     * The second hook adds the handler to be called on that schedule.
     * The third one deals with each membership that needs to be manually renewed.
		 */
		add_action('init', array($this, 'schedule_membership_check'));

		add_action('wu_membership_check', array($this, 'membership_renewal_check'), 10);

		add_action('wu_membership_check', array($this, 'membership_trial_check'), 10);

		add_action('wu_async_create_renewal_payment', array($this, 'async_create_renewal_payment'), 10, 2);

    /*
    * On that same check, we'll
    * search for expired memberships
    * and mark them as such.
    */
		add_action('wu_membership_check', array($this, 'membership_expired_check'), 20);

		add_action('wu_async_mark_membership_as_expired', array($this, 'async_mark_membership_as_expired'), 10);

	} // end init;

	/**
	 * Creates the recurring schedules for WP Ultimo.
	 *
	 * By default, we create a hourly, daily, and monthly schedules.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function create_schedules() {
		/*
		 * Hourly check
		 */
		if (wu_next_scheduled_action('wu_hourly') === false) {

			$next_hour = strtotime(gmdate('Y-m-d H:00:00', strtotime('+1 hour')));

			wu_schedule_recurring_action($next_hour, HOUR_IN_SECONDS, 'wu_hourly', array(), 'wu_cron');

		} // end if;

		/*
		 * Daily check
		 */
		if (wu_next_scheduled_action('wu_daily') === false) {

			wu_schedule_recurring_action(strtotime('tomorrow'), DAY_IN_SECONDS, 'wu_daily', array(), 'wu_cron');

		} // end if;

		/*
		 * Monthly check
		 */
		if (wu_next_scheduled_action('wu_monthly') === false) {

			$next_month = strtotime(gmdate('Y-m-01 00:00:00', strtotime('+1 month')));

			wu_schedule_recurring_action($next_month, MONTH_IN_SECONDS, 'wu_monthly', array(), 'wu_cron');

		} // end if;

	} // end create_schedules;

	/**
	 * Creates the default membership checking schedule.
	 *
	 * By default, checks every hour.
	 *
	 * @see wu_schedule_membership_check_interval
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function schedule_membership_check() {

		$interval = apply_filters('wu_schedule_membership_check_interval', 1 * HOUR_IN_SECONDS);

		if (wu_next_scheduled_action('wu_membership_check') === false) {

			wu_schedule_recurring_action(time(), $interval, 'wu_membership_check', array(), 'wu_cron');

		} // end if;

	} // end schedule_membership_check;

	/**
	 * Checks if non-auto-renewable memberships need work.
	 *
	 * This creates pending payments, emails the link to pay
	 * and marks the membership as on-hold.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function membership_renewal_check() {
		/*
     * Define how many days before we need to
		 * create pending payments.
		 */
		$days_before_expiring = apply_filters('wu_membership_renewal_days_before_expiring', 3);

		$query_params = apply_filters('wu_membership_renewal_check_query_params', array(
			'auto_renew' => false,
			'status__in' => array(
				Membership_Status::ACTIVE,
			),
			'date_query' => array(
				'column'    => 'date_expiration',
				'before'    => "+{$days_before_expiring} days",
				'after'     => 'yesterday',
				'inclusive' => true,
			),
		), $days_before_expiring);

		$memberships = wu_get_memberships($query_params);

		/*
		 * Loop our memberships, triggering
		 * a new async call for each one.
		 */
		foreach ($memberships as $membership) {

			wu_enqueue_async_action('wu_async_create_renewal_payment', array(
				'membership_id' => $membership->get_id(),
			), 'wu_cron_check');

		} // end foreach;

	} // end membership_renewal_check;

	/**
	 * Checks if trialing memberships need work.
	 *
	 * This creates pending payments, emails the link to pay
	 * and marks the membership as on-hold.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function membership_trial_check() {

		$query_params = apply_filters('wu_membership_trial_check_query_params', array(
			'auto_renew' => false,
			'status__in' => array(
				Membership_Status::TRIALING,
			),
			'date_query' => array(
				'column'    => 'date_trial_end',
				'before'    => '-3 hours',
				'inclusive' => true,
			),
		));

		$memberships = wu_get_memberships($query_params);

		/*
		 * Loop our memberships, triggering
		 * a new async call for each one.
		 */
		foreach ($memberships as $membership) {

			wu_enqueue_async_action('wu_async_create_renewal_payment', array(
				'membership_id' => $membership->get_id(),
				'trial'         => true,
			), 'wu_cron_check');

		} // end foreach;

	} // end membership_trial_check;

	/**
	 * Creates the pending payment for a renewing membership.
	 *
	 * @since 2.0.0
	 *
	 * @param int  $membership_id The membership id.
	 * @param bool $trial If the membership was in a trial state before.
	 * @return \WP_Error|true
	 */
	public function async_create_renewal_payment($membership_id, $trial = false) {

		$membership = wu_get_membership($membership_id);

		if (empty($membership)) {

			return;

		} // end if;

    /*
     * List of things to do:
     *
     * 1. Check for an existing pending payment.
     * - If it exists, bail.
     * 2. Create a new pending payment, based on the original one.
     * 3. Change the status to on-hold.
     * 4. Add note to membership about this.
     */
		$pending_payment = $membership->get_last_pending_payment();

		if (empty($pending_payment)) {

			$previous_payments = wu_get_payments(array(
				'number'        => 1,
				'membership_id' => $membership->get_id(),
				'status'        => 'completed',
				'orderby'       => 'id',
				'order'         => 'DESC',
			));

			if (empty($previous_payments)) {

				return;

			} // end if;

			$previous_payment = $previous_payments[0];

			/*
			 * This is kinda hack-y,
			 * but this needs to be here to make sure
			 * line items get loaded from the meta
			 * and get copied over.
			 *
			 * Do not remove =)
			 */
			$previous_payment->get_line_items();

			/*
			 * Duplicate previous payment
			 * remove fees and other non-recurring
			 * items and save.
			 */
			$new_payment = $previous_payment->duplicate();

			if ($trial === false) {
				/*
				 * If this is not a trial,
				 * we need to remove non-recurring items.
				 */
				$new_payment->remove_non_recurring_items();

			} // end if;

			$new_payment->set_status(Payment_Status::PENDING);
			$new_payment->set_gateway_payment_id('');
			$new_payment->recalculate_totals();
			$new_payment->save();

			/*
			 * Update the membership status.
			 */
			$membership->set_status(Membership_Status::ON_HOLD);

			return $membership->save();

		} // end if;

		return true;

	} // end async_create_renewal_payment;

	/**
	 * Checks if any memberships need to be marked as expired.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function membership_expired_check() {
    /*
     * Define how many grace period
     * days we allow for our customers.
		 */
		$grace_period_days = apply_filters('wu_membership_grace_period_days', 3);

		$query_params = apply_filters('wu_membership_expired_check_query_params', array(
			'auto_renew'              => false,
			'status__in'              => array(
				Membership_Status::ACTIVE,
				Membership_Status::ON_HOLD,
			),
			'date_expiration__not_in' => array(null, '0000-00-00 00:00:00'),
			'date_query'              => array(
				'column'    => 'date_expiration',
				'before'    => "-{$grace_period_days} days",
				'inclusive' => true,
			),
		), $grace_period_days);

		$memberships = wu_get_memberships($query_params);

		/*
		 * Loop our memberships, triggering
		 * a new async call for each one.
		 */
		foreach ($memberships as $membership) {

			wu_enqueue_async_action('wu_async_mark_membership_as_expired', array(
				'membership_id' => $membership->get_id(),
			), 'wu_cron_check');

		} // end foreach;

	} // end membership_expired_check;

	/**
	 * Marks expired memberships as such.
	 *
	 * @since 2.0.0
	 *
	 * @param int $membership_id The membership ID.
	 * @return \WP_Error|true
	 */
	public function async_mark_membership_as_expired($membership_id) {

		$membership = wu_get_membership($membership_id);

		if (empty($membership)) {

			return;

		} // end if;

    /*
     * Update the membership status.
     */
		$membership->set_status(Membership_Status::EXPIRED);

    /*
     * Old memberships can be linked to plans
     * that no longer exist and other such things,
     * so we need to bypass validation.
     */
		$membership->set_skip_validation(true);

		return $membership->save();

	} // end async_mark_membership_as_expired;

} // end class Cron;
