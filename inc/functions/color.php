<?php
/**
 * Color Functions
 *
 * Public APIs to load and deal with color in PHP.
 *
 * Uses the Mexitek\PHPColors\Color class as a basis.
 *
 * @see https://github.com/mexitek/phpColors
 * @see http://mexitek.github.io/phpColors/
 *
 * @author      Arindo Duque
 * @category    Admin
 * @package     WP_Ultimo/Colors
 * @version     2.0.0
 */

use \WP_Ultimo\Dependencies\Mexitek\PHPColors\Color;

// Exit if accessed directly
defined('ABSPATH') || exit;

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

