<?php
/**
 * Number Helper Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.11
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Extracts a int from a string of text.
 *
 * @since 2.0.0
 *
 * @param string $str The string to process.
 * @return int
 */
function wu_extract_number($str) {

	$matches = array();

	preg_match_all('/\d+/', $str, $matches);

	return isset($matches[0][0]) ? (int) $matches[0][0] : 0;

} // end wu_extract_number;

/**
 * Converts formatted values back into floats.
 *
 * @since 2.0.0
 *
 * @param string $num Formatted number string.
 * @param bool   $decimal_separator The decimal separator.
 * @return float
 */
function wu_to_float($num, $decimal_separator = false) {

	if (is_float($num) || is_numeric($num)) {

		return (float) $num;

	} // end if;

	if (empty($decimal_separator)) {

		$decimal_separator = wu_get_setting('decimal_separator', '.');

	} // end if;

	if ($decimal_separator) {

		$pattern = '/[^0-9\\' . $decimal_separator . '-]+/';

	} else {

		$pattern = '/[^0-9-]+/';

	} // end if;

	$val = preg_replace($pattern, '', $num);

	return floatval($val);

} // end wu_to_float;
