<?php
/**
 * Admin Functions
 *
 * Public APIs to load and deal with WP Ultimo admin pages and more.
 *
 * @author      Arindo Duque
 * @category    Admin
 * @package     WP_Ultimo/Functions
 * @version     2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Returns the HTML markup of a empty state page.
 *
 * @since 2.0.0
 *
 * @param array $args List of the page arguments.
 * @return string
 */
function wu_render_empty_state($args = array()) {

	$args = wp_parse_args($args, array(
		'message'      => __('This is not yet available...'),
		'sub_message'  => __('We\'re still working on this part of the product.'),
		'link_label'   => __('&larr; Go Back', 'wp-ultimo'),
		'link_url'     => 'javascript:history.go(-1)',
		'link_classes' => '',
		'link_icon'    => '',
	));

	return wu_get_template_contents('base/empty-state', $args);

} // end wu_render_empty_state;
