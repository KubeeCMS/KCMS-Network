<?php
/**
 * General date functions for WP Ultimo.
 *
 * @author      Arindo Duque
 * @category    Admin
 * @package     WP_Ultimo/Helper
 * @version     2.0.0
 */

/**
 * Checks if a date is valid in the Gregorian calendar.
 *
 * @since 2.0.0
 *
 * @param string $date Date to check.
 * @param string $format Format to check against.
 * @return bool
 */
function wu_validate_date($date, $format = 'Y-m-d H:i:s') {

	$d = \DateTime::createFromFormat($format, $date);

	return $d && $d->format($format) === $date;

} // end wu_validate_date;

/**
 * Returns a Carbon object to deal with dates in a more compeling way.
 *
 * Note: this function uses the wu_validate function to check
 * if the string passed is a valid date string. If the string
 * is not valid, now is used.
 *
 * @since 2.0.0
 * @see https://carbon.nesbot.com/docs/
 *
 * @param boolean $date Parsable date string.
 * @return \WP_Ultimo\Dependencies\Carbon\Carbon
 */
function wu_date($date = false) {

	if (!wu_validate_date($date)) {

		$date = date_i18n('Y-m-d H:i:s');

	} // end if;

	return \WP_Ultimo\Dependencies\Carbon\Carbon::parse($date);

} // end wu_date;

/**
 * Returns how many days ago the first date was in relation to the second date.
 *
 * If second date is empty, now is used.
 *
 * @since 1.7.0
 *
 * @param string       $date_1 First date to compare.
 * @param string|false $date_2 Second date to compare.
 * @return integer Negative if days ago, positive if days in the future.
 */
function wu_get_days_ago($date_1, $date_2 = false) {

	$datetime_1 = wu_date($date_1);

	$datetime_2 = wu_date($date_2);

	return - $datetime_1->diffInDays($datetime_2, false);

} // end wu_get_days_ago;

/**
 * Returns the current time from the network
 *
 * @param string $type Type of the return string to return.
 * @return string
 */
function wu_get_current_time($type = 'mysql') {

	switch_to_blog(get_current_site()->blog_id);

	$time = current_time($type);

	restore_current_blog();

	return $time;

} // end wu_get_current_time;

/**
 * Returns a more user friendly version of the duration unit string.
 *
 * @since 2.0.0
 *
 * @param string $unit The duration unit string.
 * @param int    $length The duration.
 * @return string
 */
function wu_filter_duration_unit($unit, $length) {

	$new_unit = '';

	switch ($unit) :

		case 'day':
			$new_unit = $length > 1 ? __('Days', 'wp-ultimo') : __('Day', 'wp-ultimo');
			break;

		case 'month':
			$new_unit = $length > 1 ? __('Months', 'wp-ultimo') : __('Month', 'wp-ultimo');
			break;

		case 'year':
			$new_unit = $length > 1 ? __('Years', 'wp-ultimo') : __('Year', 'wp-ultimo');
			break;

	endswitch;

	return $new_unit;

} // end wu_filter_duration_unit;
