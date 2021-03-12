<?php
/**
 * Helper Functions
 *
 * We will create some helper functions just to make the whole rendering syntax more similar to
 * existing WordPress Plugins, like WooCommerce and etc.
 *
 * @author      Arindo Duque
 * @category    Admin
 * @package     WP_Ultimo/Helper
 * @version     1.4.0
 */

/**
 * Alias function to be used on the templates
 *
 * @param  string       $view Template to be get.
 * @param  array        $args Arguments to be parsed and made available inside the template file.
 * @param string|false $default_view View to be used if the view passed is not found. Used as fallback.
 * @return void
 */
function wu_get_template($view, $args = array(), $default_view = false) {

	WP_Ultimo()->helper->render($view, $args, $default_view);

} // end wu_get_template;

/**
 * Alias function to be used on the templates;
 * Rather than directly including the template, it returns the contents inside a variable
 *
 * @param  string       $view Template to be get.
 * @param  array        $args Arguments to be parsed and made available inside the template file.
 * @param string|false $default_view View to be used if the view passed is not found. Used as fallback.
 * @return string
 */
function wu_get_template_contents($view, $args = array(), $default_view = false) {

	ob_start();

	WP_Ultimo()->helper->render($view, $args, $default_view);

	return ob_get_clean();

} // end wu_get_template_contents;
