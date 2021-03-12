<?php
/**
 * WP Ultimo Dashboard Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Dashboard_Statistics;

/**
 * WP Ultimo Dashboard Admin Page.
 */
class Dashboard_Admin_Page extends Base_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo';

	/**
	 * Menu position. This is only used for top-level menus
	 *
	 * @since 1.8.2
	 * @var integer
	 */
	protected $position = 10101010;

	/**
	 * Dashicon to be used on the menu item. This is only used on top-level menus
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $menu_icon = 'dashicons-wu-wp-ultimo';

	/**
	 * If this number is greater than 0, a badge with the number will be displayed alongside the menu title
	 *
	 * @since 1.8.2
	 * @var integer
	 */
	protected $badge_count = 0;

	/**
	 * Holds the admin panels where this page should be displayed, as well as which capability to require.
	 *
	 * To add a page to the regular admin (wp-admin/), use: 'admin_menu' => 'capability_here'
	 * To add a page to the network admin (wp-admin/network), use: 'network_admin_menu' => 'capability_here'
	 * To add a page to the user (wp-admin/user) admin, use: 'user_admin_menu' => 'capability_here'
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $supported_panels = array(
		'network_admin_menu' => 'wu_read_dashboard',
	);

	/**
	 * Sets up the global parameters.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		parent::init();

		/*
		 * Get the content of the tab.
		 */
		$this->tab        = wu_request('tab', 'general');
		$this->start_date = date_i18n('Y-m-d', strtotime(wu_request('start_date', '-1 month')));
		$this->end_date   = date_i18n('Y-m-d', strtotime(wu_request('end_date', 'tomorrow')));

	} // end init;

	/**
	 * Allow child classes to add hooks to be run once the page is loaded.
	 *
	 * @see https://codex.wordpress.org/Plugin_API/Action_Reference/load-(page)
	 * @since 1.8.2
	 * @return void
	 */
	public function hooks() {

		add_action('wu_dash_after_full_metaboxes', array($this, 'render_filter'));

		add_action('wu_dashboard_general_widgets', array($this, 'register_general_tab_widgets'), 10, 2);

	} // end hooks;

	/**
	 * Renders the filter.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Admin_Pages\Base_Admin_Page $page The page object.
	 * @return void
	 */
	public function render_filter($page) {

		if (apply_filters('wu_dashboard_display_filter', true) === false) {

			return;

		} // end if;

		if ($page->id === 'wp-ultimo') {

			$preset_options = array(
				'last_7_days'  => array(
					'label'      => __('Last 7 days', 'wp-ultimo'),
					'start_date' => date_i18n('Y-m-d', strtotime('-7 days')),
					'end_date'   => date_i18n('Y-m-d'),
				),
				'last_30_days' => array(
					'label'      => __('Last 30 days', 'wp-ultimo'),
					'start_date' => date_i18n('Y-m-d', strtotime('-30 days')),
					'end_date'   => date_i18n('Y-m-d'),
				),
				'year_to_date' => array(
					'label'      => __('Year to date', 'wp-ultimo'),
					'start_date' => date_i18n('Y-m-d', strtotime('first day of january this year')),
					'end_date'   => date_i18n('Y-m-d'),
				),
			);

			$args = array(
				'preset_options'  => $preset_options,
				'filters_el_id'   => 'dashboard-filters',
				'search_label'    => '',
				'has_search'      => false,
				'has_view_switch' => false,
				'table'           => $this,
				'active_tab'      => $this->tab,
				'views'           => $this->get_views(),
			);

			wu_get_template('dashboard-statistics/filter', $args);

		} // end if;

	} // end render_filter;

	/**
	 * Returns the views for the filter menu bar.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_views() {

		$dashboard_filters = array(
			'general' => array(
				'field' => 'type',
				'url'   => add_query_arg('tab', 'general'),
				'label' => __('General', 'wp-ultimo'),
				'count' => 0,
			),
		);

		return apply_filters('wu_dashboard_filter_bar', $dashboard_filters);

	} // end get_views;

	/**
	 * Allow child classes to register widgets, if they need them.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_widgets() {

		$screen = get_current_screen();

		if (!$screen) {

			return;

		} // end if;

		/**
		 * Allow plugin developers to add widgets to Network Dashboard Panel.
		 *
		 * @since 2.0.0
		 *
		 * @param string $tab The current tab.
		 * @param \WP_Screen $screen The screen object.
		 * @param \WP_Ultimo\Admin_Pages\Dashboard_Admin_Page $page WP Ultimo admin page instance.
		 */
		do_action("wu_dashboard_{$this->tab}_widgets", $this->tab, $screen, $this);

		/**
		 * Allow plugin developers to add widgets to Network Dashboard Panel.
		 *
		 * @since 2.0.0
		 *
		 * @param string $tab The current tab.
		 * @param \WP_Screen $screen The screen object.
		 * @param \WP_Ultimo\Admin_Pages\Dashboard_Admin_Page $page WP Ultimo admin page instance.
		 */
		do_action('wu_dashboard_widgets', $this->tab, $screen, $this);

		if (wu_request('tab', 'general') === 'general') {

			\WP_Ultimo\UI\Tours::get_instance()->create_tour('wp-ultimo-dashboard', array(
				array(
					'id'    => 'your-dashboard',
					'title' => __('Our dashboard', 'wp-ultimo'),
					'text'  => array(
						__('This is the <strong>WP Ultimo Dashboard</strong>, where you will find most of the important information you will need regarding your business\' performance.', 'wp-ultimo'),
					),
				),
				array(
					'id'       => 'documentation',
					'title'    => __('Learning more', 'wp-ultimo'),
					'text'     => array(
						__('Most of the WP Ultimo admin pages will contain a link like this one at the top. These will link directly to the relevant knowledge base page on the WP Ultimo site.', 'wp-ultimo'),
					),
					'attachTo' => array(
						'element' => '#wp-ultimo-wrap > h1 > a:last-child',
						'on'      => 'left',
					),
				),
				array(
					'id'       => 'mrr-growth',
					'title'    => __('It\'s all about growth!', 'wp-ultimo'),
					'text'     => array(
						__('This graph allows you to follow how your monthly recurring revenue is growing this year.', 'wp-ultimo'),
					),
					'attachTo' => array(
						'element' => '#wp-ultimo-mrr-growth',
						'on'      => 'bottom',
					),
				),
				array(
					'id'       => 'tailor-made',
					'title'    => __('Date-range support', 'wp-ultimo'),
					'text'     => array(
						__('Checking statistics and comparing data for different periods is key in maintaining a good grasp on your business.', 'wp-ultimo'),
						__('You can use the date-range selectors to have access to just the data you need and nothing more.', 'wp-ultimo'),
					),
					'attachTo' => array(
						'element' => '#dashboard-filters',
						'on'      => 'bottom',
					),
				),
			));

		} // end if;

	} // end register_widgets;

	/**
	 * Register the widgets of the default general tab.
	 *
	 * @since 2.0.0
	 *
	 * @param string     $tab Tab slug.
	 * @param \WP_Screen $screen The screen object.
	 * @return void
	 */
	public function register_general_tab_widgets($tab, $screen) {

		add_meta_box('wp-ultimo-mrr-growth', __('Monthly Recurring Revenue Growth', 'wp-ultimo'), array($this, 'output_widget_mrr_growth'), $screen->id, 'full', 'high');

		add_meta_box('wp-ultimo-revenue', __('Revenue', 'wp-ultimo'), array($this, 'output_widget_revenues'), $screen->id, 'normal', 'high');

		add_meta_box('wp-ultimo-countries', __('Signups by Countries', 'wp-ultimo'), array($this, 'output_widget_countries'), $screen->id, 'side', 'high');

		add_meta_box('wp-ultimo-most-visited-sites', __('Most Visited Sites', 'wp-ultimo'), array($this, 'output_widget_most_visited_sites'), $screen->id, 'side', 'low');

		add_meta_box('wp-ultimo-new-accounts', __('New Memberships', 'wp-ultimo'), array($this, 'output_widget_new_accounts'), $screen->id, 'normal', 'low');

	} // end register_general_tab_widgets;

	/**
	 * Output the statistics filter widget
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function output_widget_mrr_growth() {

		WP_Ultimo()->helper->render('dashboard-statistics/widget-mrr-growth');

	} // end output_widget_mrr_growth;

	/**
	 * Output the statistics filter widget
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function output_widget_countries() {

		WP_Ultimo()->helper->render('dashboard-statistics/widget-countries', array(
			'countries' => wu_get_countries_of_customers(10, $this->start_date, $this->end_date),
		));

	} // end output_widget_countries;

	/**
	 * Output the statistics filter widget
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function output_widget_most_visited_sites() {

		$sites = array();

		$site_results = \WP_Ultimo\Objects\Visits::get_sites_by_visit_count($this->start_date, $this->end_date, 10);

		foreach ($site_results as $site_result) {

			$site = wu_get_site($site_result->site_id);

			if (!$site) {

				continue;

			} // end if;

			$sites[] = (object) array(
				'site'  => $site,
				'count' => $site_result->count,
			);

		} // end foreach;

		WP_Ultimo()->helper->render('dashboard-statistics/widget-most-visited-sites', array(
			'sites' => $sites,
		));

	} // end output_widget_most_visited_sites;

	/**
	 * Outputs the total refunds widget content.
	 *
	 * @since 2.0.0
	 *
	 * @param string $unknown Unknown.
	 * @param array  $metabox With the metabox arguments passed when registered.
	 * @return void.
	 */
	public function output_widget_revenues($unknown = null, $metabox) {

		WP_Ultimo()->helper->render('dashboard-statistics/widget-revenue', array(
			'mrr'           => wu_calculate_mrr(),
			'gross_revenue' => wu_calculate_revenue($this->start_date, $this->end_date),
			'refunds'       => wu_calculate_refunds($this->start_date, $this->end_date),
		));

	} // end output_widget_revenues;

	/**
	 * Outputs the total refunds widget content.
	 *
	 * @since 2.0.0
	 *
	 * @param string $unknown Unknown.
	 * @param array  $metabox With the metabox arguments passed when registered.
	 * @return void.
	 */
	public function output_widget_new_accounts($unknown = null, $metabox) {

		$new_accounts = wu_get_memberships(array(
			'fields'     => array('plan_id'),
			'date_query' => array(
				'column'    => 'date_created',
				'after'     => $this->start_date . ' 00:00:00',
				'before'    => $this->end_date . ' 23:59:59',
				'inclusive' => true,
			),
		));

		$products = wu_get_products(array(
			'type'   => 'plan',
			'fields' => array('id', 'name', 'count'),
		));

		$products_ids = array_column($products, 'id');

		$products = array_combine($products_ids, $products);

		$products = array_map(function($item) {

			$item->count = 0;

			return $item;

		}, $products);

		/**
		 * Add edge case for no plan.
		 */
		$products['none'] = (object) array(
			'name'  => __('No Product', 'wp-ultimo'),
			'count' => 0,
		);

		foreach ($new_accounts as $new_account) {

			if (isset($products[$new_account->plan_id])) {

				$products[$new_account->plan_id]->count += 1;

			} else {

				$products['none']->count += 1;

			} // end if;

		} // end foreach;

		WP_Ultimo()->helper->render('dashboard-statistics/widget-new-accounts', array(
			'new_accounts' => count($new_accounts),
			'products'     => $products,
		));

	} // end output_widget_new_accounts;

	/**
	 * Enqueue the necessary scripts.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts() {

		$month_list = array();

		$start = date_i18n('Y-m-d 00:00:00', strtotime('first day of january this year'));

		for ($i = 0; $i < 12; $i++) {

			$start_date = wu_date($start);

			$month_list[] = date_i18n('M y', $start_date->addMonths($i)->format('U'));

		} // end for;

		$statistics = new Dashboard_Statistics(array(
			'start_date' => $this->start_date,
			'end_date'   => $this->end_date,
			'types'      => array(
				'mrr_growth' => 'mrr_growth',
			),
		));

		$data = $statistics->statistics_data();

		wp_register_script('wu-apex-charts', wu_get_asset('apexcharts.js', 'js/lib'), array(), wu_get_version(), true);

		wp_register_script('wu-vue-apex-charts', wu_get_asset('vue-apexcharts.js', 'js/lib'), array(), wu_get_version(), true);

		wp_register_script('wu-dashboard-stats', wu_get_asset('dashboard-statistics.js', 'js'), array('jquery', 'wu-functions', 'wu-ajax-list-table', 'wu-moment', 'wu-block-ui', 'dashboard', 'wu-apex-charts', 'wu-vue-apex-charts'), wu_get_version(), true);

		wp_localize_script('wu-dashboard-stats', 'wu_dashboard_statistics_vars', array(
			'mrr_array'  => $data['mrr_growth'],
			'start_date' => date_i18n('Y-m-d', strtotime(wu_request('start_date', '-1 month'))),
			'end_date'   => date_i18n('Y-m-d', strtotime(wu_request('end_date', 'tomorrow'))),
			'today'      => date_i18n('Y-m-d', strtotime('tomorrow')),
			'month_list' => $month_list,
			'i18n'       => array(
				'new_mrr'       => __('New MRR', 'wp-ultimo'),
				'cancellations' => __('Cancellations', 'wp-ultimo'),
			),
		));

		wp_enqueue_script('wu-dashboard-stats');

		wp_enqueue_style('wu-apex-charts', wu_get_asset('apexcharts.css', 'css'), array(), wu_get_version());

	} // end register_scripts;

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return __('Dashboard', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('WP Ultimo', 'wp-ultimo');

	} // end get_menu_title;

	/**
	 * Allows admins to rename the sub-menu (first item) for a top-level page.
	 *
	 * @since 2.0.0
	 * @return string False to use the title menu or string with sub-menu title.
	 */
	public function get_submenu_title() {

		return __('Dashboard', 'wp-ultimo');

	} // end get_submenu_title;

	/**
	 * Every child class should implement the output method to display the contents of the page.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function output() {
		/*
		 * Renders the base edit page layout, with the columns and everything else =)
		 */
		wu_get_template('base/dash', array(
			'screen'            => get_current_screen(),
			'page'              => $this,
			'has_full_position' => true,
		));

	} // end output;

} // end class Dashboard_Admin_Page;
