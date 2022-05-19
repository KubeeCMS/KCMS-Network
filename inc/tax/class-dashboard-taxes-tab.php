<?php
/**
 * WP Ultimo Dashboard Tax Admin Panel
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Tax;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo Dashboard Tax Admin Panel
 */
class Dashboard_Taxes_Tab {

	/**
	 * Reference to the main admin page object so we can
	 * access its methods on the render functions.
	 *
	 * @since 2.0.11
	 * @var \WP_Ultimo\Admin_Pages\Base_Admin_Page
	 */
	protected $dashboard_page;

	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		add_filter('wu_dashboard_filter_bar', array($this, 'add_tab'));

		add_action('wu_dashboard_taxes_widgets', array($this, 'register_widgets'), 10, 3);

	} // end __construct;

	/**
	 * Checks if tax support is enabled or not.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	protected function is_enabled() {

		return wu_get_setting('enable_taxes');

	} // end is_enabled;

	/**
	 * Add_tab to dashboard
	 *
	 * @since 2.0.0
	 *
	 * @param array $dashboard_filters Dashboard tabs.
	 */
	public function add_tab($dashboard_filters) {

		$dashboard_filters['taxes'] = array(
			'field' => 'type',
			'label' => __('Taxes', 'wp-ultimo'),
			'url'   => add_query_arg('tab', 'taxes'),
			'count' => 0,
		);

		return $dashboard_filters;

	} // end add_tab;

	/**
	 * Renders the disabled message, if taxes are not enabled.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function disabled_message() {

		echo wu_render_empty_state(array(
			'message'     => __('You do not have tax support enabled yet...'),
			'sub_message' => __('If you need to collect taxes, you\'ll be glad to hear that WP Ultimo offers tax support!'),
			'link_label'  => __('Enable Tax Support', 'wp-ultimo'),
			'link_url'    => wu_network_admin_url('wp-ultimo-settings', array(
				'tab' => 'taxes',
			)),
		));

	} // end disabled_message;

	/**
	 * Adds a back link to the taxes without tax.
	 *
	 * @since 2.0.0
	 *
	 * @param array $links The already existent links.
	 * @return array
	 */
	public function add_back_link($links) {

		$back_link = array(
			array(
				'url'   => wu_network_admin_url('wp-ultimo'),
				'label' => __('Go Back', 'wp-ultimo'),
				'icon'  => 'wu-reply',
			),
		);

		return array_merge($back_link, $links);

	} // end add_back_link;

	/**
	 * Register_widgets
	 *
	 * @since 2.0.0
	 *
	 * @param string                                      $tab Name of selected tab.
	 * @param \WP_Screen                                  $screen The current screen object.
	 * @param \WP_Ultimo\Admin_Pages\Dashboard_Admin_Page $dashboard_page Name of selected tab.
	 * @return void
	 */
	public function register_widgets($tab, $screen, $dashboard_page) {

		/**
		 * Set the dashboard page as a property
		 * to make the helper methods available on the render
		 * functions for the metaboxes.
		 */
		$this->dashboard_page = $dashboard_page;

		/*
		 * Displays an empty page with the option to activate tax support.
		 */
		if (!$this->is_enabled()) {

			add_filter('wu_dashboard_display_filter', '__return_false');

			add_filter('wu_dashboard_display_widgets', '__return_false');

			add_action('wu_dash_before_metaboxes', array($this, 'disabled_message'));

			add_filter('wu_page_get_title_links', array($this, 'add_back_link'));

			return;

		} // end if;

		$this->dashboard_page = $dashboard_page;

		add_meta_box('wp-ultimo-taxes', __('Taxes', 'wp-ultimo'), array($this, 'output_widget_taxes'), $screen->id, 'full', 'high');

		add_meta_box('wp-ultimo-taxes-by-rate', __('Taxes by Code', 'wp-ultimo'), array($this, 'output_widget_taxes_by_rate'), $screen->id, 'normal', 'high');

		add_meta_box('wp-ultimo-taxes-by-day', __('Taxes by Day', 'wp-ultimo'), array($this, 'output_widget_taxes_by_day'), $screen->id, 'side', 'high');

		$this->register_scripts();

	} // end register_widgets;

	/**
	 * Registers the necessary scripts to handle the tax graph.
	 *
	 * @todo: extract the calculations onto their own function.
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts() {

		$payments_per_month = array(
			'january'   => array(),
			'february'  => array(),
			'march'     => array(),
			'april'     => array(),
			'may'       => array(),
			'june'      => array(),
			'july'      => array(),
			'august'    => array(),
			'september' => array(),
			'october'   => array(),
			'november'  => array(),
			'december'  => array()
		);

		$data = wu_calculate_taxes_by_month();

		$index = 1;

		foreach ($payments_per_month as &$month) {

			$month = $data[$index];

			$index++;

		} // end foreach;

		$month_list = array();

		$start = date_i18n('Y-m-d 00:00:00', strtotime('first day of january this year'));

		for ($i = 0; $i < 12; $i++) {

			$start_date = wu_date($start);

			$month_list[] = date_i18n('M y', $start_date->addMonths($i)->format('U'));

		} // end for;

		wp_register_script('wu-tax-stats', wu_get_asset('tax-statistics.js', 'js'), array('jquery', 'wu-functions', 'wu-ajax-list-table', 'moment', 'wu-block-ui', 'dashboard', 'wu-apex-charts', 'wu-vue-apex-charts'), wu_get_version(), true);

		wp_localize_script('wu-tax-stats', 'wu_tax_statistics_vars', array(
			'data'       => $payments_per_month,
			'start_date' => date_i18n('Y-m-d', strtotime(wu_request('start_date', '-1 month'))),
			'end_date'   => date_i18n('Y-m-d', strtotime(wu_request('end_date', 'tomorrow'))),
			'today'      => date_i18n('Y-m-d', strtotime('tomorrow')),
			'month_list' => $month_list,
			'i18n'       => array(
				'net_profit_label' => __('Net Profit', 'wp-ultimo'),
				'taxes_label'      => __('Taxes Collected', 'wp-ultimo'),
			)
		));

		wp_enqueue_script('wu-tax-stats');

	} // end register_scripts;

	/**
	 * Renders the tax graph.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function output_widget_taxes() {

		wu_get_template('dashboard-statistics/widget-tax-graph');

	} // end output_widget_taxes;

	/**
	 * Renders the taxes by rate widget.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function output_widget_taxes_by_rate() {

		$taxes_by_rate = wu_calculate_taxes_by_rate($this->dashboard_page->start_date, $this->dashboard_page->end_date);

		wu_get_template('dashboard-statistics/widget-tax-by-code', array(
			'taxes_by_rate' => $taxes_by_rate,
			'page'          => $this->dashboard_page,
		));

	} // end output_widget_taxes_by_rate;

	/**
	 * Renders the taxes by date widget.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function output_widget_taxes_by_day() {

		$taxes_by_day = wu_calculate_taxes_by_day($this->dashboard_page->start_date, $this->dashboard_page->end_date);

		wu_get_template('dashboard-statistics/widget-tax-by-day', array(
			'taxes_by_day' => $taxes_by_day,
			'page'         => $this->dashboard_page,
		));

	} // end output_widget_taxes_by_day;

} // end class Dashboard_Taxes_Tab;
