<?php
/**
 * Manages WP Ultimo Core Updates.
 *
 * @package WP_Ultimo
 * @subpackage Core Updates
 * @since 2.0.0
 */

namespace WP_Ultimo;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Manages WP Ultimo Core Updates.
 *
 * @since 2.0.0
 */
class Core_Updates {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Initializes the class.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		/**
		 * Check for a WP Ultimo Core Updates.
		 */
		add_action('upgrader_process_complete', array($this, 'maybe_add_core_update_hooks'), 10, 2);

	} // end init;

	/**
	 * Checks if a WP Ultimo core update is being performed and triggers an action if that's the case.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Upgrader $u The upgrader instance.
	 * @param array        $i Upgrade info.
	 * @return void
	 */
	public function maybe_add_core_update_hooks($u, $i) {

		$is_a_wp_ultimo_update = false;

		if (!empty($u->result) && in_array('wp-ultimo.php', $u->result['source_files'], true)) {

			$is_a_wp_ultimo_update = true;

		} elseif (isset($i['plugins']) && is_array($i['plugins'])) {

			if (in_array('wp-ultimo/wp-ultimo.php', $i['plugins'], true)) {

				$is_a_wp_ultimo_update = true;

			} // end if;

		} // end if;

		if ($is_a_wp_ultimo_update) {

			function_exists('wu_log_add') && wu_log_add('wp-ultimo-core', __('Updating WP Ultimo Core...', 'wp-ultimo'));

			try {

				/**
				 * Triggers an action that be used to perform
				 * tasks on a core update.
				 *
				 * @since 2.0.0
				 */
				do_action('wu_core_update');

			} catch (\Throwable $th) {

				// Nothing to do in here.

			} // end try;

		} // end if;

	} // end maybe_add_core_update_hooks;

} // end class Core_Updates;
