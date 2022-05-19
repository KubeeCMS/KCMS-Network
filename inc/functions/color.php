<?php
/**
 * Color Functions
 *
 * Uses the Mexitek\PHPColors\Color class as a basis.
 *
 * @see https://github.com/mexitek/phpColors
 * @see http://mexitek.github.io/phpColors/
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Dependencies\Mexitek\PHPColors\Color;

/**
 * Returns a Color object.
 *
 * @since 2.0.0
 *
 * @param string $hex Hex code for the color. E.g. #000.
 * @return \WP_Ultimo\Dependencies\Mexitek\PHPColors\Color
 */
function wu_color($hex) {

	try {

		$color = new Color($hex);

	} catch (Exception $e) {

		$color = new Color('#f9f9f9');

	} // end try;

	return $color;

} // end wu_color;

/**
 * Gets a random color for the progress bar.
 *
 * @since 2.0.0
 *
 * @param int $index The index number.
 * @return string
 */
function wu_get_random_color($index) {

	$colors = array(
		'wu-bg-red-500',
		'wu-bg-green-500',
		'wu-bg-blue-500',
		'wu-bg-yellow-500',
		'wu-bg-orange-500',
		'wu-bg-purple-500',
		'wu-bg-pink-500',
	);

	return wu_get_isset($colors, $index, $colors[ rand(0, count($colors) - 1) ]);

} // end wu_get_random_color;
