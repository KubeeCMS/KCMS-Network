<?php
/**
 * The Support Agent model.
 *
 * @package WP_Ultimo
 * @subpackage Models
 * @since 2.0.0
 */

namespace WP_Ultimo\Models;

use WP_Ultimo\Models\Base_Model;
use WP_Ultimo\Models\Memberships;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Support Agent model class. Extends the Customer Class.
 *
 * Ok, you might be asking yourself: why does the support agent
 * model extends the customer model?
 *
 * The answer: it makes our lives much easier and it simplifies a lot of things.
 * All the APIs are basically the same.
 *
 * @since 2.0.0
 */
class Support_Agent extends Customer {

	/**
	 * Sets the type to support-agent.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $type = 'support-agent';

	/**
	 * Holds the list of WordPress default capabilities for this agent
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $capabilities;

	/**
	 * Holds the list of WordPress default capabilities for this agent
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $capabilities_wordpress;

	/**
	 * Holds the list of Network Dashboard Widgets is enable for this agent
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $network_dashboard_widgets;

	/**
	 * Query Class to the static query methods.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = '\\WP_Ultimo\\Database\\Customers\\Support_Agent_Query';

	/**
	 * Set the validation rules for this particular model.
	 *
	 * To see how to setup rules, check the documentation of the
	 * validation library we are using: https://github.com/rakit/validation
	 *
	 * @since 2.0.0
	 *
	 * @link https://github.com/rakit/validation
	 * @return array
	 */
	public function validation_rules() {

		$id = $this->get_id();

		return array(
			'user_id'            => "required|unique:WP_Ultimo\Models\Support_Agent,user_id,{$id}|integer",
			'email_verification' => 'required|in:none,pending,verified',
			'type'               => 'required|in:support-agent|default:support-agent',
		);

	} // end validation_rules;

	/**
	 * Checks if the current support agent has a given cap.
	 *
	 * @since 2.0.0
	 *
	 * @param string $cap WordPress or WP Ultimo capability.
	 * @return boolean
	 */
	public function can($cap) {

		$capabilities = array_merge($this->get_capabilities(), $this->get_capabilities_wordpress());

		return array_key_exists($cap, $capabilities) && $capabilities[$cap];

	} // end can;

	/**
	 * Checks if the current support agent can see the requested widget.
	 *
	 * @since 2.0.0
	 *
	 * @param string $widget Dashboard Network Widget.
	 * @return boolean
	 */
	public function has_widget($widget) {

		$widgets = $this->get_network_dashboard_widgets();

		if (empty($widgets)) {

			return false;

		} // end if;

		return wu_get_isset($widgets, $widget);

	} // end has_widget;

	/**
	 * Get the list of capabilities for this agent.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_capabilities() {

		if ($this->capabilities === null) {

			$caps = $this->get_meta('wu_capabilities', array());

			$caps['view_query_monitor'] = true;

			$this->capabilities = $caps;

		} // end if;

		return $this->capabilities;

	} // end get_capabilities;

	/**
	 * Get the list of of Network Dashboard Widgets for this agent.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_network_dashboard_widgets() {

		if ($this->network_dashboard_widgets === null) {

			$this->network_dashboard_widgets = $this->get_meta('wu_network_dashboard_widgets', array());

		} // end if;

		return $this->network_dashboard_widgets;

	}  // end get_network_dashboard_widgets;

	/**
	 * Get the list of WordPress capabilities for this agent.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_capabilities_wordpress() {

		if ($this->capabilities_wordpress === null) {

			$caps = $this->get_meta('wu_capabilities_wordpress', array());

			$default_wordpress_caps = array(
				'manage_options' => true,
				'manage_network' => true,
				'setup_network'  => true,
			);

			$caps = array_merge($default_wordpress_caps, $caps);

			$this->capabilities_wordpress = $caps;

		} // end if;

		return $this->capabilities_wordpress;

	} // end get_capabilities_wordpress;

	/**
	 * Set the list of capabilities for this agent.
	 *
	 * @since 2.0.0
	 *
	 * @param array $capabilities Holds the list of capabilities for this agent.
	 * @return void
	 */
	public function set_capabilities($capabilities) {

		$this->meta['wu_capabilities'] = $capabilities;

		$this->capabilities = $capabilities;

	} // end set_capabilities;

	/**
	 * Set the list of WordPress capabilities for this agent.
	 *
	 * @since 2.0.0
	 *
	 * @param array $capabilities_wordpress Holds the list of capabilities for this agent.
	 * @return void
	 */
	public function set_capabilities_wordpress($capabilities_wordpress) {

		$this->meta['wu_capabilities_wordpress'] = $capabilities_wordpress;

		$this->capabilities_wordpress = $capabilities_wordpress;

	} // end set_capabilities_wordpress;

	/**
	 * Set the list of Network Dashboard Widgets for this agent.
	 *
	 * @since 2.0.0
	 *
	 * @param array $network_dashboard_widgets Holds the list of widgets for this agent.
	 * @return void
	 */
	public function set_network_dashboard_widgets($network_dashboard_widgets) {

		$default_widgets = \WP_Ultimo\Dashboard_Widgets::get_registered_dashboard_widgets();

		$default_widgets = array_map('__return_false', $default_widgets);

		$network_dashboard_widgets = wp_parse_args($network_dashboard_widgets, $default_widgets);

		$this->meta['wu_network_dashboard_widgets'] = $network_dashboard_widgets;

		$this->network_dashboard_widgets = $network_dashboard_widgets;

	}  // end set_network_dashboard_widgets;

	/**
	 * Gets a model instance by a column value.
	 *
	 * @since 2.0.0
	 *
	 * @param string $column The name of the column to query for.
	 * @param string $value Value to search for.
	 * @return Base_Model|false
	 */
	public static function get_by($column, $value) {

		$item = parent::get_by($column, $value);

		if ($item && $item->get_type() === 'support-agent') {

			return $item;

		} // end if;

		return false;

	} // end get_by;

	/**
	 * Returns the meta type name.
	 *
	 * @since 2.0.0
	 *
	 * @return mixed Meta type name
	 */
	private function get_meta_type_name() {

		$query_class = new $this->query_class;

		// Maybe apply table prefix
		$table = !empty($query_class->prefix)
		? "{$query_class->prefix}_customer"
		: 'customer';

		// Return table if exists, or false if not
		return $table;

	} // end get_meta_type_name;

} // end class Support_Agent;
