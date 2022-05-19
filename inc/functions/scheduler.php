<?php
/**
 * Scheduler Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Returns how much time it takes until the next queue. In seconds.
 *
 * @since 2.0.0
 * @return int
 */
function wu_get_next_queue_run() {

	if (class_exists('ActionScheduler')) {

		return ActionScheduler::lock()->get_expiration('async-request-runner') - time();

	} // end if;

	return 0;

} // end wu_get_next_queue_run;

/**
 * Enqueue an action to run one time, as soon as possible.
 *
 * @see https://actionscheduler.org/api/#function-reference--as_enqueue_async_action
 *
 * @param string $hook The hook to trigger.
 * @param array  $args Arguments to pass when the hook triggers.
 * @param string $group The group to assign this job to.
 * @return int The action ID.
 */
function wu_enqueue_async_action($hook, $args = array(), $group = '') {

	return wu_switch_blog_and_run(function() use ($hook, $args, $group) {

		return as_enqueue_async_action($hook, $args, $group);

	});

} // end wu_enqueue_async_action;

/**
 * Schedule an action to run one time.
 *
 * @see https://actionscheduler.org/api/#function-reference--as_schedule_single_action
 *
 * @param int    $timestamp When the job will run.
 * @param string $hook The hook to trigger.
 * @param array  $args Arguments to pass when the hook triggers.
 * @param string $group The group to assign this job to.
 *
 * @return int The action ID.
 */
function wu_schedule_single_action($timestamp, $hook, $args = array(), $group = '') {

	return wu_switch_blog_and_run(function() use ($timestamp, $hook, $args, $group) {

		return as_schedule_single_action($timestamp, $hook, $args, $group);

	});

} // end wu_schedule_single_action;

/**
 * Schedule a recurring action.
 *
 * @see https://actionscheduler.org/api/#function-reference--as_schedule_recurring_action
 *
 * @param int    $timestamp When the first instance of the job will run.
 * @param int    $interval_in_seconds How long to wait between runs.
 * @param string $hook The hook to trigger.
 * @param array  $args Arguments to pass when the hook triggers.
 * @param string $group The group to assign this job to.
 *
 * @return int The action ID.
 */
function wu_schedule_recurring_action($timestamp, $interval_in_seconds, $hook, $args = array(), $group = '') {

	return wu_switch_blog_and_run(function() use ($timestamp, $interval_in_seconds, $hook, $args, $group) {

		return as_schedule_recurring_action($timestamp, $interval_in_seconds, $hook, $args, $group);

	});

} // end wu_schedule_recurring_action;

/**
 * Schedule an action that recurs on a cron-like schedule.
 *
 * @see https://actionscheduler.org/api/#function-reference--as_schedule_cron_action
 *
 * @param int    $timestamp The first instance of the action will be scheduled.
 *               to run at a time calculated after this timestamp matching the cron
 *               expression. This can be used to delay the first instance of the action.
 * @param string $schedule A cron-link schedule string.
 * @see http://en.wikipedia.org/wiki/Cron
 *   *    *    *    *    *    *
 *   ┬    ┬    ┬    ┬    ┬    ┬
 *   |    |    |    |    |    |
 *   |    |    |    |    |    + year [optional]
 *   |    |    |    |    +----- day of week (0 - 7) (Sunday=0 or 7)
 *   |    |    |    +---------- month (1 - 12)
 *   |    |    +--------------- day of month (1 - 31)
 *   |    +-------------------- hour (0 - 23)
 *   +------------------------- min (0 - 59)
 * @param string $hook The hook to trigger.
 * @param array  $args Arguments to pass when the hook triggers.
 * @param string $group The group to assign this job to.
 *
 * @return int The action ID.
 */
function wu_schedule_cron_action($timestamp, $schedule, $hook, $args = array(), $group = '') {

	return wu_switch_blog_and_run(function() use ($timestamp, $schedule, $hook, $args, $group) {

		return as_schedule_cron_action($timestamp, $schedule, $hook, $args, $group);

	});

} // end wu_schedule_cron_action;

/**
 * Cancel the next occurrence of a scheduled action.
 *
 * @see https://actionscheduler.org/api/#function-reference--as_unschedule_action
 *
 * @param string $hook The hook that the job will trigger.
 * @param array  $args Args that would have been passed to the job.
 * @param string $group The group the job is assigned to.
 *
 * @return string|null The scheduled action ID if a scheduled action was found, or null if no matching action found.
 */
function wu_unschedule_action($hook, $args = array(), $group = '') {

	return wu_switch_blog_and_run(function() use ($hook, $args, $group) {

		return as_unschedule_action($hook, $args, $group);

	});

} // end wu_unschedule_action;

/**
 * Cancel all occurrences of a scheduled action.
 *
 * @see https://actionscheduler.org/api/#function-reference--as_unschedule_all_actions
 *
 * @param string $hook The hook that the job will trigger.
 * @param array  $args Args that would have been passed to the job.
 * @param string $group The group the job is assigned to.
 */
function wu_unschedule_all_actions($hook, $args = array(), $group = '' ) {

	return wu_switch_blog_and_run(function() use ($hook, $args, $group) {

		return as_unschedule_all_actions($hook, $args, $group);

	});

} // end wu_unschedule_all_actions;

/**
 * Check if there is an existing action in the queue with a given hook, args and group combination.
 *
 * An action in the queue could be pending, in-progress or async. If the is pending for a time in
 * future, its scheduled date will be returned as a timestamp. If it is currently being run, or an
 * async action sitting in the queue waiting to be processed, in which case boolean true will be
 * returned. Or there may be no async, in-progress or pending action for this hook, in which case,
 * boolean false will be the return value.
 *
 * @see https://actionscheduler.org/api/#function-reference--as_next_scheduled_action
 *
 * @param string $hook The hook that the job will trigger.
 * @param array  $args Args that would have been passed to the job.
 * @param string $group The group the job is assigned to.
 *
 * @return int|bool The timestamp for the next occurrence of a pending scheduled action, true for an async or in-progress action or false if there is no matching action.
 */
function wu_next_scheduled_action($hook, $args = null, $group = '') {

	return wu_switch_blog_and_run(function() use ($hook, $args, $group) {

		return as_next_scheduled_action($hook, $args, $group);

	});

} // end wu_next_scheduled_action;

/**
 * Find scheduled actions.
 *
 * @see https://actionscheduler.org/api/#function-reference--as_get_scheduled_actions
 *
 * @param array  $args Possible arguments, with their default values.
 * @param string $return_format OBJECT, ARRAY_A, or ids.
 *
 * @return array
 */
function wu_get_scheduled_actions($args = array(), $return_format = OBJECT) {

	return wu_switch_blog_and_run(function() use ($args, $return_format) {

		return as_get_scheduled_actions($args, $return_format);

	});

} // end wu_get_scheduled_actions;
