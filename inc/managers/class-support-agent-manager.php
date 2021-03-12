<?php
/**
 * Support Agent Manager
 *
 * Handles processes related to Support Agents.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Support_Agent_Manager
 * @since 2.0.0
 */

namespace WP_Ultimo\Managers;

use WP_Ultimo\Managers\Base_Manager;
use WP_Ultimo\Models\Support_Agent;
use WP_Ultimo\Logger;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles processes related to webhooks.
 *
 * @since 2.0.0
 */
class Support_Agent_Manager extends Base_Manager {

	use \WP_Ultimo\Apis\Rest_Api, \WP_Ultimo\Apis\WP_CLI, \WP_Ultimo\Traits\Singleton;

	/**
	 * The manager slug.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $slug = 'customer';

	/**
	 * The model class associated to this manager.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $model_class = '\\WP_Ultimo\\Models\\Support_Agent';

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		add_action('wp_network_dashboard_setup', array($this, 'clean_up_widgets'), 999);

	} // end init;

	/**
	 * Cleans the widgets that are not supported.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function clean_up_widgets() {

		global $wp_meta_boxes;

		$support_agent = wu_get_current_support_agent();

		if ($support_agent) {

			$widgets = $support_agent->get_network_dashboard_widgets();

			foreach ($widgets as $key => $value) {

				if ($value || empty($key)) {

					continue;

				} // end if;

				$place_priority_id = explode(':', $key);

				if (isset($wp_meta_boxes['dashboard-network'][$place_priority_id[0]][$place_priority_id[1]][$place_priority_id[2]])) {

					unset($wp_meta_boxes['dashboard-network'][$place_priority_id[0]][$place_priority_id[1]][$place_priority_id[2]]);

				} // end if;

				if (isset($wp_meta_boxes['dashboard-network']['side'][$place_priority_id[1]][$place_priority_id[2]])) {

					unset($wp_meta_boxes['dashboard-network']['side'][$place_priority_id[1]][$place_priority_id[2]]);

				} // end if;

			} // end foreach;

		} // end if;

	} // end clean_up_widgets;

} // end class Support_Agent_Manager;
