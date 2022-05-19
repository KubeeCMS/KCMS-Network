<?php
/**
 * Adds the Tours UI to the Admin Panel.
 *
 * @package WP_Ultimo
 * @subpackage UI
 * @since 2.0.0
 */

namespace WP_Ultimo\UI;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds the Tours UI to the Admin Panel.
 *
 * @since 2.0.0
 */
class Tours {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Registered tours.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $tours = array();

	/**
	 * Element construct.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		add_action('wp_ajax_wu_mark_tour_as_finished', array($this, 'mark_as_finished'));

		add_action('admin_enqueue_scripts', array($this, 'register_scripts'));

		add_action('in_admin_footer', array($this, 'enqueue_scripts'));

	} // end __construct;

	/**
	 * Mark the tour as finished for a particular user.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function mark_as_finished() {

		check_ajax_referer('wu_tour_finished', 'nonce');

		$id = wu_request('tour_id');

		if ($id) {

			set_user_setting("wu_tour_$id", true);

			wp_send_json_success();

		} // end if;

		wp_send_json_error();

	} // end mark_as_finished;

	/**
	 * Register the necessary scripts.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts() {

		WP_Ultimo()->scripts->register_script('wu-shepherd', wu_get_asset('lib/shepherd.js', 'js'), array());

		WP_Ultimo()->scripts->register_script('wu-tours', wu_get_asset('tours.js', 'js'), array('wu-shepherd', 'underscore'));

	}  // end register_scripts;

	/**
	 * Enqueues the scripts, if we need to.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function enqueue_scripts() {

		if ($this->has_tours()) {

			wp_localize_script('wu-tours', 'wu_tours', $this->tours);

			wp_localize_script('wu-tours', 'wu_tours_vars', array(
				'ajaxurl' => wu_ajax_url(),
				'nonce'   => wp_create_nonce('wu_tour_finished'),
				'i18n'    => array(
					'next'   => __('Next', 'wp-ultimo'),
					'finish' => __('Close', 'wp-ultimo')
				),
			));

			wp_enqueue_script('wu-tours');

		} // end if;

	}  // end enqueue_scripts;

	/**
	 * Checks if we have registered tours.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_tours() {

		return !empty($this->tours);

	} // end has_tours;

	/**
	 * Register a new tour.
	 *
	 * @see https://shepherdjs.dev/docs/
	 *
	 * @since 2.0.0
	 *
	 * @param string  $id The id of the tour.
	 * @param array   $steps The tour definition. Check shepherd.js docs.
	 * @param boolean $once Whether or not we will show this more than once.
	 * @return void
	 */
	public function create_tour($id, $steps = array(), $once = true) {

		if (did_action('in_admin_header')) {

			return;

		} // end if;

		add_action('in_admin_header', function() use ($id, $steps, $once) {

			$force_hide = wu_get_setting('hide_tours', false);

			if ($force_hide) {

				return;

			} // end if;

			$finished = (bool) get_user_setting("wu_tour_$id", false);

			$finished = apply_filters('wu_tour_finished', $finished, $id, get_current_user_id());

			if (!$finished || !$once) {

				foreach ($steps as &$step) {

					$step['text'] = is_array($step['text']) ? implode('</p><p>', $step['text']) : $step['text'];

					$step['text'] = sprintf('<p>%s</p>', $step['text']);

				} // end foreach;

				$this->tours[$id] = $steps;

			} // end if;

		});

	} // end create_tour;

} // end class Tours;
