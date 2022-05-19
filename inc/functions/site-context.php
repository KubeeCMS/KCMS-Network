<?php
/**
 * Site Context Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Tries to switch to a site to run the callback, before returning.
 *
 * @since 2.0.0
 *
 * @param array|string $callback Callable to run.
 * @param int          $site_id Site to switch to. Defaults to main site.
 * @return mixed
 */
function wu_switch_blog_and_run($callback, $site_id = false) {

	if (!$site_id) {

		$site_id = wu_get_main_site_id();

	} // end if;

	is_multisite() && switch_to_blog($site_id);

		$result = call_user_func($callback); // phpcs:ignore

	is_multisite() && restore_current_blog();

	return $result;

} // end wu_switch_blog_and_run;
