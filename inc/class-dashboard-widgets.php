<?php
/**
 * WP Ultimo Dashboard Widgets
 *
 * Log string messages to a file with a timestamp. Useful for debugging.
 *
 * @package WP_Ultimo
 * @subpackage Logger
 * @since 2.0.0
 */

namespace WP_Ultimo;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo Dashboard Widgets
 *
 * @since 2.0.0
 */
class Dashboard_Widgets {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Network Dashboard Screen Id
	 *
	 * @since 2.0.0
	 * @var string
	 */
	public $screen_id = 'dashboard-network';

	/**
	 * Undocumented variable
	 *
	 * @since 2.0.0
	 * @var array
	 */
	public $core_metaboxes = array();

	/**
	 * Runs on singleton instantiation.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));

		add_action('wp_network_dashboard_setup', array($this, 'register_network_widgets'));

		add_action('wp_dashboard_setup', array($this, 'register_widgets'));

		add_action('wp_ajax_wu_fetch_rss', array($this, 'process_ajax_fetch_rss'));

		add_action('wp_ajax_wu_fetch_activity', array($this, 'process_ajax_fetch_events'));

		add_action('wp_ajax_wu_generate_csv', array($this, 'handle_table_csv'));

	} // end init;

	/**
	 * Enqueues the JavaScript code that sends the dismiss call to the ajax endpoint.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function enqueue_scripts() {

		global $pagenow;

		if (!$pagenow || $pagenow !== 'index.php') {

			return;

		} // end if;

		wp_enqueue_script('wu-vue');

		wp_enqueue_script('moment');

	} // end enqueue_scripts;

	/**
	 * Register the widgets
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_network_widgets() {

		add_meta_box('wp-ultimo-setup', __('WP Ultimo - First Steps', 'wp-ultimo'), array($this, 'output_widget_first_steps'), $this->screen_id, 'normal', 'high');

		add_meta_box('wp-ultimo-news', __('WP Ultimo - News & Discussions', 'wp-ultimo'), array($this, 'output_widget_news'), $this->screen_id, 'side', 'low');

		add_meta_box('wp-ultimo-summary', __('WP Ultimo - Summary', 'wp-ultimo'), array($this, 'output_widget_summary'), $this->screen_id, 'normal', 'high');

		add_meta_box('wp-ultimo-activity-stream', __('WP Ultimo - Activity Stream', 'wp-ultimo'), array($this, 'output_widget_activity_stream'), $this->screen_id, 'normal', 'high');

		\WP_Ultimo\UI\Tours::get_instance()->create_tour('dashboard', array(
			array(
				'id'    => 'welcome',
				'title' => __('Welcome!', 'wp-ultimo'),
				'text'  => array(
					__('Welcome to your new network dashboard!', 'wp-ultimo'),
					__('You will notice that <strong>WP Ultimo</strong> adds a couple of useful widgets here so you can keep an eye on how your network is doing.', 'wp-ultimo'),
				),
			),
			array(
				'id'       => 'finish-your-setup',
				'title'    => __('Finish your setup', 'wp-ultimo'),
				'text'     => array(
					__('You still have a couple of things to do configuration-wise. Check the steps on this list and make sure you complete them all.', 'wp-ultimo'),
				),
				'attachTo' => array(
					'element' => '#wp-ultimo-setup',
					'on'      => 'left',
				),
			),
			array(
				'id'       => 'wp-ultimo-menu',
				'title'    => __('Our home', 'wp-ultimo'),
				'text'     => array(
					__('You can always find WP Ultimo settings and other pages under our menu item, here on the Network-level dashboard. 😃', 'wp-ultimo'),
				),
				'attachTo' => array(
					'element' => '.toplevel_page_wp-ultimo',
					'on'      => 'left',
				),
			),
		));

	} // end register_network_widgets;

	/**
	 * Adds the customer's site's dashboard widgets.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_widgets() {

		$screen = get_current_screen();

		if (wu_get_current_site()->get_type() !== 'customer_owned') {

			return;

		} // end if;

		/*
		 * Account Summary
		 */
		\WP_Ultimo\UI\Account_Summary_Element::get_instance()->as_metabox($screen->id, 'normal');

		/*
		 * Limits & Quotas
		 */
		\WP_Ultimo\UI\Limits_Element::get_instance()->as_metabox($screen->id, 'side');

		/*
		 * Maintenance Mode Widget
		 */
		if (wu_get_setting('maintenance_mode')) {

			\WP_Ultimo\UI\Site_Maintenance_Element::get_instance()->as_metabox($screen->id, 'side');

		} // end if;

		/*
		 * Domain Mapping Widget
		 */
		if (wu_get_setting('enable_domain_mapping') && wu_get_setting('custom_domains')) {

			\WP_Ultimo\UI\Domain_Mapping_Element::get_instance()->as_metabox($screen->id, 'side');

		} // end if;

	} // end register_widgets;

	/**
	 * Widget First Steps Output.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function output_widget_first_steps() {

		$initial_setup_done = get_network_option(null, 'wu_setup_finished', false);

		$steps = array(
			'inital-setup'        => array(
				'title'        => __('Initial Setup', 'wp-ultimo'),
				'desc'         => __('Go through the initial Setup Wizard to configure the basic settings of your network.', 'wp-ultimo'),
				'action_label' => __('Finish the Setup Wizard', 'wp-ultimo'),
				'action_link'  => wu_network_admin_url('wp-ultimo-setup'),
				'done'         => wu_string_to_bool($initial_setup_done),
			),
			'payment-method'      => array(
				'title'        => __('Payment Method', 'wp-ultimo'),
				'desc'         => __('You will need to configure at least one payment gateway to be able to receive money from your customers.', 'wp-ultimo'),
				'action_label' => __('Add a Payment Method', 'wp-ultimo'),
				'action_link'  => wu_network_admin_url('wp-ultimo-settings', array(
					'tab' => 'payment-gateways',
				)),
				'done'         => !empty(wu_get_active_gateways()),
			),
			'your-first-customer' => array(
				'done'         => !empty(wu_get_customers()),
				'title'        => __('Your First Customer', 'wp-ultimo'),
				'desc'         => __('Open the link below in an incognito tab and go through your newly created signup form.', 'wp-ultimo'),
				'action_link'  => wp_registration_url(),
				'action_label' => __('Create a test Account', 'wp-ultimo'),
			),
		);

		$done = \WP_Ultimo\Dependencies\Arrch\Arrch::find($steps, array(
			'where' => array(
				array('done', true),
			),
		));

		wu_get_template('dashboard-widgets/first-steps', array(
			'steps'      => $steps,
			'percentage' => round(count($done) / count($steps) * 100),
			'all_done'   => count($done) === count($steps),
		));

	} // end output_widget_first_steps;

	/**
	 * Widget News Output.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function output_widget_news() {

		wu_get_template('dashboard-widgets/news');

	} // end output_widget_news;

	/**
	 * Widget Activity Stream Output.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function output_widget_activity_stream() {

		wu_get_template('dashboard-widgets/activity-stream');

	} // end output_widget_activity_stream;

	/**
	 * Widget Summary Output
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function output_widget_summary() {
		/*
		 * Get today's signups.
		 */
		$signups = wu_get_customers(array(
			'count'      => true,
			'date_query' => array(
				'column'    => 'date_registered',
				'after'     => 'today',
				'inclusive' => true,
			),
		));

		wu_get_template('dashboard-widgets/summary', array(
			'signups'       => $signups,
			'mrr'           => wu_calculate_mrr(),
			'gross_revenue' => wu_calculate_revenue('today'),
		));

	} // end output_widget_summary;

	/**
	 * Process Ajax Filters for rss.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function process_ajax_fetch_rss() {

		$atts = wp_parse_args($_GET, array(
			'url'          => 'https://community.wpultimo.com/topics/feed',
			'title'        => __('Forum Discussions', 'wp-ultimo'),
			'items'        => 3,
			'show_summary' => 1,
			'show_author'  => 0,
			'show_date'    => 1,
		));

		wp_widget_rss_output($atts);

		exit;

	} // end process_ajax_fetch_rss;

	/**
	 * Process Ajax Filters for rss.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function process_ajax_fetch_events() {

		check_ajax_referer('wu_activity_stream');

		$count = wu_get_events(array(
			'count'  => true,
			'number' => -1,
		));

		$data = wu_get_events(array(
			'offset' => (wu_request('page', 1) - 1) * 5,
			'number' => 5,
		));

		wp_send_json_success(array(
			'events' => $data,
			'count'  => $count,
		));

	} // end process_ajax_fetch_events;

	/**
	 * Handle ajax endpoint to generate table CSV.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_table_csv() {

		$date_range = wu_request('date_range');
		$headers    = json_decode(stripslashes(wu_request('headers')));
		$data       = json_decode(stripslashes(wu_request('data')));

		$file_name = sprintf('wp-ultimo-%s_%s_(%s)', wu_request('slug'), $date_range, gmdate('Y-m-d', wu_get_current_time('timestamp')));

		$data = array_merge(array($headers), $data);

		wu_generate_csv($file_name, $data);

		die;

	} // end handle_table_csv;

	/**
	 * Get the registered widgets.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public static function get_registered_dashboard_widgets() {

		global $wp_meta_boxes, $wp_registered_widgets;

		ob_start();

		if (!function_exists('wp_add_dashboard_widget')) {

			require_once ABSPATH . '/wp-admin/includes/dashboard.php';

		} // end if;

		do_action('wp_network_dashboard_setup'); // phpcs:ignore

		ob_clean(); // Prevent eventual echos.

		$dashboard_widgets = wu_get_isset($wp_meta_boxes, 'dashboard-network', array());

		$options = array(
			'normal:core:dashboard_right_now'         => __('At a Glance'),
			'normal:core:network_dashboard_right_now' => __('Right Now'),
			'normal:core:dashboard_activity'          => __('Activity'),
			'normal:core:dashboard_primary'           => __('WordPress Events and News'),
		);

		foreach ($dashboard_widgets as $position => $priorities) {

			foreach ($priorities as $priority => $widgets) {

				foreach ($widgets as $widget_key => $widget) {

					if (empty($widget) || wu_get_isset($widget, 'title') === false) {

						continue;

					} // end if;

					$key = implode(':', array(
						$position,
						$priority,
						$widget_key,
					));

					/**
					 * For some odd reason, in some cases, $options
					 * becomes a bool and the assignment below throws a fatal error.
					 * This checks prevents that error from happening.
					 * I don't know why $options would ever be a boolean here, though.
					 */
					if (!is_array($options)) {

						$options = array();

					} // end if;

					$options[$key] = $widget['title'];

				} // end foreach;

			} // end foreach;

		} // end foreach;

		return $options;

	} // end get_registered_dashboard_widgets;

} // end class Dashboard_Widgets;
