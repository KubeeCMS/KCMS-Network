<?php
/**
 * Default Ajax hooks.
 *
 * @package WP_Ultimo
 * @subpackage Ajax
 * @since 2.0.0
 */

namespace WP_Ultimo;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds a lighter ajax option to WP Ultimo.
 *
 * @since 1.9.14
 */
class Ajax {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Sets up the listeners.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		/*
		 * Load search endpoints.
		 */
		add_action('wu_ajax_wu_search', array($this, 'search_models'));

		/*
		 * Adds the Selectize templates to the admin_footer.
		 */
		add_action('in_admin_footer', array($this, 'render_selectize_templates'));

		/*
		 * Load search endpoints.
		 */
		add_action('wp_ajax_wu_list_table_fetch_ajax_results', array($this, 'refresh_list_table'));

	} // end __construct;

	/**
	 * Reverts the name of the table being processed.
	 *
	 * @since 2.0.0
	 *
	 * @param string $table_id The ID of the table in the format "line_item_list_table".
	 * @return string
	 */
	private function get_table_class_name($table_id) {

		return str_replace(' ', '_', (ucwords(str_replace('_', ' ', $table_id))));

	} // end get_table_class_name;

	/**
	 * Serves the pagination and search results of a list table ajax query.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function refresh_list_table() {

		$table_id = wu_request('table_id');

		$class_name = $this->get_table_class_name($table_id);

		$full_class_name = "\\WP_Ultimo\\List_Tables\\{$class_name}";

		if (class_exists($full_class_name)) {

			$table = new $full_class_name;

			$table->ajax_response();

		} // end if;

		do_action('wu_list_table_fetch_ajax_results', $table_id);

	} // end refresh_list_table;

	/**
	 * Search models using our ajax endpoint.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function search_models() {

		/**
		 * Fires before the processing of the search request.
		 *
		 * @since 2.0.0
		 */
		do_action('wu_before_search_models');

		if (wu_request('model') === 'all') {

			$this->search_all_models();

			return;

		} // end if;

		$args = wp_parse_args($_REQUEST, array(
			'model'   => 'membership',
			'query'   => array(),
			'exclude' => array()
		));

		$query = array_merge($args['query'], array(
			'number' => -1,
		));

		if ($args['exclude']) {

			if (is_string($args['exclude'])) {

				$args['exclude'] = explode(',', $args['exclude']);

				$args['exclude'] = array_map('trim', $args['exclude']);

			} // end if;

			$query['id__not_in'] = $args['exclude'];

		} // end if;

		if (wu_get_isset($args, 'include')) {

			if (is_string($args['include'])) {

				$args['include'] = explode(',', $args['include']);

				$args['include'] = array_map('trim', $args['include']);

			} // end if;

			$query['id__in'] = $args['include'];

		} // end if;

		/*
		 * Deal with site
		 */
		if ($args['model'] === 'site') {

			if (wu_get_isset($query, 'id__in')) {

				$query['blog_id__in'] = $query['id__in'];

				unset($query['id__in']);

			} // end if;

			if (wu_get_isset($query, 'id__not_in')) {

				$query['blog_id__not_in'] = $query['id__not_in'];

				unset($query['id__not_in']);

			} // end if;

		} // end if;

		$results = array();

		if ($args['model'] === 'user') {

			$results = $this->search_wordpress_users($query);

		} elseif ($args['model'] === 'page') {

			$results = get_posts(array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			));

		} elseif ($args['model'] === 'setting') {

			$results = $this->search_wp_ultimo_setting($query);

		} else {

			$model_func = 'wu_get_' . strtolower($args['model']) . 's';

			if (function_exists($model_func)) {

				$results = $model_func($query);

			} // end if;

		} // end if;

		// Try search by hash if do not have any result
		if (empty($results)) {

			$model_func = 'wu_get_' . strtolower($args['model']) . '_by_hash';

			if (function_exists($model_func)) {

				$result = $model_func(trim($query['search'], '*'));

				$results = $result ? array($result) : array();

			} // end if;

		} // end if;

		wp_send_json($results);

		exit;

	} // end search_models;

	/**
	 * Search all models for Jumper.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function search_all_models() {

		$query = array_merge(wu_request('query', array()), array(
			'number' => 10000,
		));

		$results_user = array_map(function($item) {

			$item->model = 'user';

			$item->group = 'Users';

			$item->value = network_admin_url("user-edit.php?user_id={$item->ID}");

			return $item;

		}, $this->search_wordpress_users($query));

		$results_settings = array_map(function($item) {

			$item['model'] = 'setting';

			$item['group'] = 'Settings';

			$item['value'] = $item['url'];

			return $item;

		}, $this->search_wp_ultimo_setting($query));

		$data = array_merge($results_user, $results_settings);

		/**
		 * Allow plugin developers to add more search models functions.
		 *
		 * @since 2.0.0
		 */
		$data_sources = apply_filters('wu_search_models_functions', array(
			'wu_get_customers',
			'wu_get_products',
			'wu_get_plans',
			'wu_get_domains',
			'wu_get_sites',
			'wu_get_memberships',
			'wu_get_payments',
			'wu_get_broadcasts',
			'wu_get_checkout_forms',
		));

		foreach ($data_sources as $function) {

			$results = call_user_func($function, $query);

			array_map(function($item) {

				$url = str_replace('_', '-', $item->model);

				$item->value = wu_network_admin_url("wp-ultimo-edit-{$url}", array(
					'id' => $item->get_id(),
				));

				$item->group = ucwords($item->model) . 's';

				return $item;

			}, $results);

			$discount_codes = array_map(function($item) {

				$discount = $item->to_array();

				$discount['value'] = wu_network_admin_url('wp-ultimo-edit-discount-code', array(
					'id' => $discount['id'],
				));

				$discount['group'] = 'Discount Codes';

				return $discount;

			}, wu_get_discount_codes($query));

			$data = array_merge($data, $results, $discount_codes);

		} // end foreach;

		wp_send_json($data);

	}  // end search_all_models;

	/**
	 * Search for WP Ultimo settings to help customers find them.
	 *
	 * @since 2.0.0
	 *
	 * @param array $query Query arguments.
	 * @return array
	 */
	public function search_wp_ultimo_setting($query) {

		$sections = \WP_Ultimo\Settings::get_instance()->get_sections();

		$all_fields = array();

		foreach ($sections as $section_slug => $section) {

			$section['fields'] = array_map(function($item) use ($section, $section_slug) {

				$item['section'] = $section_slug;

				$item['section_title'] = wu_get_isset($section, 'title', '');

				$item['url'] = wu_network_admin_url('wp-ultimo-settings', array(
					'tab' => $section_slug,
				)) . '#' . $item['setting_id'];

				return $item;

			}, $section['fields']);

			$all_fields = array_merge($all_fields, $section['fields']);

		} // end foreach;

		$_settings = \WP_Ultimo\Dependencies\Arrch\Arrch::find($all_fields, array(
			'sort_key' => 'title',
			'where'    => array(
				array('setting_id', '~', trim($query['search'], '*')),
				array('type', '!=', 'header'),
			),
		));

		return array_values($_settings);

	} // end search_wp_ultimo_setting;

	/**
	 * Handles the special case of searching native WP users.
	 *
	 * @since 2.0.0
	 *
	 * @param array $query Query arguments.
	 * @return array
	 */
	public function search_wordpress_users($query) {

		$results = get_users(array(
			'blog_id'        => 0,
			'search'         => '*' . $query['search'] . '*',
			'search_columns' => array(
				'ID', 'user_login', 'user_email', 'user_url', 'user_nicename', 'display_name',
			),
		));

		$results = array_map(function($item) {

			$item->data->user_pass = '';

			$item->data->avatar = get_avatar($item->data->user_email, 40, 'identicon', '', array(
				'force_display' => true,
				'class'         => 'wu-rounded-full wu-mr-3',
			));

			return $item->data;

		}, $results);

		return $results;

	} // end search_wordpress_users;

	/**
	 * Adds the selectize templates to the admin footer.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_selectize_templates() {

		if (current_user_can('manage_network')) {

			wu_get_template('ui/selectize-templates');

		} // end if;

	} // end render_selectize_templates;

} // end class Ajax;
