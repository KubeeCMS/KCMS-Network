<?php
/**
 * Site Manager
 *
 * Handles processes related to sites.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Site_Manager
 * @since 2.0.0
 */

namespace WP_Ultimo\Managers;

use \WP_Ultimo\Managers\Base_Manager;
use \WP_Ultimo\Helpers\Screenshot;
use \WP_Ultimo\Database\Sites\Site_Type;
use \WP_Ultimo\Database\Memberships\Membership_Status;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles processes related to sites.
 *
 * @since 2.0.0
 */
class Site_Manager extends Base_Manager {

	use \WP_Ultimo\Apis\Rest_Api, \WP_Ultimo\Apis\WP_CLI, \WP_Ultimo\Traits\Singleton;

	/**
	 * The manager slug.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $slug = 'site';

	/**
	 * The model class associated to this manager.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $model_class = '\\WP_Ultimo\\Models\\Site';

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		$this->enable_rest_api();

		$this->enable_wp_cli();

		add_action('after_setup_theme', array($this, 'additional_thumbnail_sizes'));

		add_action('wp_ajax_wu_get_screenshot', array($this, 'get_site_screenshot'));

		add_action('wu_async_take_screenshot', array($this, 'async_get_site_screenshot'));

		add_action('init', array($this, 'lock_site'));

		add_action('admin_init', array($this, 'add_no_index_warning'));

		add_action('wp_head', array($this, 'prevent_site_template_indexing'), 0);

		add_action('login_enqueue_scripts', array($this, 'custom_login_logo'));

		add_filter('login_headerurl', array($this, 'login_header_url'));

		add_filter('login_headertext', array($this, 'login_header_text'));

		add_action('wu_pending_site_published', array($this, 'handle_site_published'), 10, 2);

		add_action('load-sites.php', array($this, 'add_notices_to_default_site_page'));

		add_action('load-site-new.php', array($this, 'add_notices_to_default_site_page'));

		add_filter('mucd_string_to_replace', array($this, 'search_and_replace_on_duplication'), 10, 3);

		add_filter('wu_site_created', array($this, 'search_and_replace_for_new_site'), 10, 2);

		add_action('wu_handle_bulk_action_form_site_delete-pending', array($this, 'handle_delete_pending_sites'), 100, 3);

		add_action('users_list_table_query_args', array($this, 'hide_super_admin_from_list'), 10, 1);

		add_action('wu_before_handle_order_submission', array($this, 'maybe_validate_add_new_site'), 15);

		add_action('wu_checkout_before_process_checkout', array($this, 'maybe_add_new_site'), 5);

		add_action('pre_get_blogs_of_user', array($this, 'hide_customer_sites_from_super_admin_list'), 999, 3);

	} // end init;

	/**
	 * Handles the request to add a new site, if that's the case.
	 *
	 * @since 2.0.11
	 *
	 * @param \WP_Ultimo\Checkout\Checkout $checkout The current checkout object.
	 * @return void
	 */
	public function maybe_validate_add_new_site($checkout) {

		global $wpdb;

		if (wu_request('create-new-site') && wp_verify_nonce(wu_request('create-new-site'), 'create-new-site')) {

			$errors = new \WP_Error;

			$rules = array(
				'site_title' => 'min:4',
				'site_url'   => 'required|lowercase|unique_site',
			);

			if ($checkout->is_last_step()) {

				$membership = wu_get_current_site()->get_membership();

				$customer = wu_get_current_customer();

				if (!$customer || !$membership || $customer->get_id() !== $membership->get_customer_id()) {

					$errors->add('not-owner', __('You do not have the necessary permissions to create a site to this membership', 'wp-ultimo'));

				} // end if;

				if ($errors->has_errors() === false) {

					$d = wu_get_site_domain_and_path(wu_request('site_url', ''), $checkout->request_or_session('site_domain'));

					$pending_site = $membership->create_pending_site(array(
						'domain'        => $d->domain,
						'path'          => $d->path,
						'template_id'   => $checkout->request_or_session('template_id'),
						'title'         => $checkout->request_or_session('site_title'),
						'customer_id'   => $customer->get_id(),
						'membership_id' => $membership->get_id(),
					));

					if (is_wp_error($pending_site)) {

						wp_send_json_error($pending_site);

						exit;

					} // end if;

					$results = $membership->publish_pending_site();

					if (is_wp_error($results)) {

						wp_send_json_error($errors);

					} // end if;

				} else {

					wp_send_json_error($errors);

				} // end if;

				wp_send_json_success(array());

			} else {

				$validation = $checkout->validate($rules);

				if (is_wp_error($validation)) {

					wp_send_json_error($validation);

				} // end if;

				$wpdb->query('COMMIT');

				wp_send_json_success(array());

			} // end if;

		} // end if;

	} // end maybe_validate_add_new_site;

	/**
	 * Checks if the current request is a add new site request.
	 *
	 * @since 2.0.11
	 * @return void
	 */
	public function maybe_add_new_site() {

		if (wu_request('create-new-site') && wp_verify_nonce(wu_request('create-new-site'), 'create-new-site')) {

			wp_redirect(admin_url('admin.php?page=sites'));

			exit;

		} // end if;

	} // end maybe_add_new_site;

	/**
	 * Triggers the do_event of the site publish successful.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Site       $site The site.
	 * @param \WP_Ultimo\Models\Membership $membership The payment.
	 * @return void
	 */
	public function handle_site_published($site, $membership) {

		$payload = array_merge(
			wu_generate_event_payload('site', $site),
			wu_generate_event_payload('membership', $membership),
			wu_generate_event_payload('customer', $membership->get_customer())
		);

		wu_do_event('site_published', $payload);

	} // end handle_site_published;

	/**
	 * Locks the site front-end if the site is not public.
	 *
	 * @todo Let the admin chose the behavior. Maybe redirect to main site?
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function lock_site() {

		if (is_main_site() || is_admin() || wu_is_login_page()) {

			return;

		} // end if;

		$can_access = true;

		$redirect_url = null;

		$site = wu_get_current_site();

		if (!$site->is_active()) {

			$can_access = false;

		} // end if;

		$membership = $site->get_membership();

		if ($membership && !$membership->is_active() && $membership->get_status() !== Membership_Status::TRIALING && wu_get_setting('block_frontend', false)) {

			$grace_period = (int) wu_get_setting('block_frontend_grace_period', 0);

			$expiration_time = wu_date($membership->get_date_expiration())->timestamp + $grace_period * DAY_IN_SECONDS;

			if ($expiration_time < wu_date()->timestamp) {

				$checkout_pages = \WP_Ultimo\Checkout\Checkout_Pages::get_instance();

				$redirect_url = $checkout_pages->get_page_url('block_frontend');

				$can_access = false;

			} // end if;

		} // end if;

		if ($can_access === false) {

			if ($redirect_url) {

				wp_redirect($redirect_url);

				exit;

			} // end if;

			wp_die(new \WP_Error(
				'not-available',
				// phpcs:ignore
				sprintf( __('This site is not available at the moment.<br><small>If you are the site admin, click <a href="%s">here</a> to login.</small>', 'wp-ultimo'), wp_login_url()),
				array(
					'title' => __('Site not available', 'wp-ultimo'),
				)
			), '', array('code' => 200));

		} // end if;

	} // end lock_site;

	/**
	 * Takes screenshots asynchronously.
	 *
	 * @since 2.0.0
	 *
	 * @param int $site_id The site ID.
	 * @return mixed
	 */
	public function async_get_site_screenshot($site_id) {

		$site = wu_get_site($site_id);

		if (!$site) {

			return false;

		} // end if;

		$domain = $site->get_active_site_url();

		$attachment_id = Screenshot::take_screenshot($domain);

		if (!$attachment_id) {

			return false;

		} // end if;

		$site->set_featured_image_id($attachment_id);

		return $site->save();

	} // end async_get_site_screenshot;

	/**
	 * Listens for the ajax endpoint and generate the screenshot.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function get_site_screenshot() {

		$site_id = wu_request('site_id');

		$site = wu_get_site($site_id);

		if (!$site) {

			wp_send_json_error(
				new \WP_Error('missing-site', __('Site not found.', 'wp-ultimo'))
			);

		} // end if;

		$domain = $site->get_active_site_url();

		$attachment_id = Screenshot::take_screenshot($domain);

		if (!$attachment_id) {

			wp_send_json_error(
				new \WP_Error('error', __('We were not able to fetch the screenshot.', 'wp-ultimo'))
			);

		} // end if;

		$attachment_url = wp_get_attachment_image_src($attachment_id, 'wu-thumb-medium');

		wp_send_json_success(array(
			'attachment_id'  => $attachment_id,
			'attachment_url' => $attachment_url[0],
		));

	} // end get_site_screenshot;

	/**
	 * Add the additional sizes required by WP Ultimo.
	 *
	 * Add for the main site only.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function additional_thumbnail_sizes() {

		if (is_main_site()) {

			add_image_size('wu-thumb-large', 900, 675, array('center', 'top')); // (cropped)

			add_image_size('wu-thumb-medium', 400, 300, array('center', 'top')); // (cropped)

		} // end if;

	} // end additional_thumbnail_sizes;

	/**
	 * Adds a notification if the no-index setting is active.
	 *
	 * @since 1.9.8
	 * @return void
	 */
	public function add_no_index_warning() {

		if (wu_get_setting('stop_template_indexing', false)) {

			add_meta_box('wu-warnings', __('WP Ultimo - Search Engines', 'wp-ultimo'), array($this, 'render_no_index_warning'), 'dashboard-network', 'normal', 'high');

		} // end if;

	} // end add_no_index_warning;

	/**
	 * Renders the no indexing warning.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_no_index_warning() { // phpcs:disable ?>

		<div class="wu-styling">

			<div class="wu-border-l-4 wu-border-yellow-500 wu-border-solid wu-border-0 wu-px-4 wu-py-2 wu--m-3">

				<p><?php _e('Your WP Ultimo settings are configured to <strong>prevent search engines such as Google from indexing your template sites</strong>.', 'wp-ultimo'); ?></p>

				<p><?php printf(__('If you are experiencing negative SEO impacts on other sites in your network, consider disabling this setting <a href="%s">here</a>.', 'wp-ultimo'), wu_network_admin_url('wp-ultimo-settings', array('tab' => 'sites'))); ?></p>

			</div>

		</div>

		<?php // phpcs:enable

	} // end render_no_index_warning;

	/**
	 * Prevents Search Engines from indexing Site Templates.
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function prevent_site_template_indexing() {

		if (!wu_get_setting('stop_template_indexing', false)) {

			return;

		} // end if;

		$site = wu_get_current_site();

		if ($site && $site->get_type() === Site_Type::SITE_TEMPLATE) {

			if (function_exists('wp_robots_no_robots')) {

				add_filter('wp_robots', 'wp_robots_no_robots'); // WordPress 5.7+

			} else {

				wp_no_robots();

			} // end if;

		} // end if;

	} // end prevent_site_template_indexing;

	/**
	 * Check if sub-site has a custom logo and change login logo.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function custom_login_logo() {

		if (!wu_get_setting('subsite_custom_login_logo', false) || !has_custom_logo()) {

			$logo = wu_get_network_logo();

		} else {

			$logo = wp_get_attachment_image_src(get_theme_mod('custom_logo'), 'full');

			$logo = wu_get_isset($logo, 0, false);

		} // end if;

		if (empty($logo)) {

			return;

		} // end if;

		// phpcs:disable

		?>

    <style type="text/css">

			#login h1 a, .login h1 a {
				background-image: url(<?php echo $logo; ?>);
				background-position: center center;
				background-size: contain;
			}

    </style>

		<?php // phpcs:enable

	} // end custom_login_logo;

	/**
	 * Replaces the WordPress url with the site url.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function login_header_url() {

		return get_site_url();

	} // end login_header_url;

	/**
	 * Replaces the WordPress text with the site name.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function login_header_text() {

		return get_bloginfo('name');

	} // end login_header_text;

	/**
	 * Add notices to default site page, recommending the WP Ultimo option.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_notices_to_default_site_page() {

		$notice = __('Hey there! We highly recommend managing your network sites using the WP Ultimo &rarr; Sites page. <br>If you want to avoid confusion, you can also hide this page from the admin panel completely on the WP Ultimo &rarr; Settings &rarr; Whitelabel options.', 'wp-ultimo');

		WP_Ultimo()->notices->add($notice, 'info', 'network-admin', 'wu-sites-use-wp-ultimo', array(
			array(
				'title' => __('Go to the WP Ultimo Sites page &rarr;', 'wp-ultimo'),
				'url'   => wu_network_admin_url('wp-ultimo-sites'),
			),
			array(
				'title' => __('Go to the Whitelabel Settings &rarr;', 'wp-ultimo'),
				'url'   => wu_network_admin_url('wp-ultimo-settings', array(
					'tab' => 'whitelabel',
				)),
			),
		));

	} // end add_notices_to_default_site_page;

	/**
	 * Add search and replace filter to be used on site duplication.
	 *
	 * @since 1.6.2
	 * @param array $search_and_replace List to search and replace.
	 * @param int   $from_site_id original site id.
	 * @param int   $to_site_id New site id.
	 * @return array
	 */
	public function search_and_replace_on_duplication($search_and_replace, $from_site_id, $to_site_id) {

		$search_and_replace_settings = $this->get_search_and_replace_settings();

		$additional_duplication = apply_filters('wu_search_and_replace_on_duplication', $search_and_replace_settings, $from_site_id, $to_site_id);

		$final_list = array_merge($search_and_replace, $additional_duplication);

		return $this->filter_illegal_search_keys($final_list);

	} // end search_and_replace_on_duplication;

	/**
	 * Get search and replace settings
	 *
	 * @since 1.7.0
	 * @return array
	 */
	public function get_search_and_replace_settings() {

		$search_and_replace = wu_get_setting('search_and_replace', array());

		$pairs = array();

		foreach ($search_and_replace as $item) {

			if ((isset($item['search']) && !empty($item['search'])) && isset($item['replace'])) {

				$pairs[$item['search']] = $item['replace'];

			} // end if;

		} // end foreach;

		return $pairs;

	} // end get_search_and_replace_settings;

	/**
	 * Handles search and replace for new blogs from WordPress.
	 *
	 * @since 1.7.0
	 * @param array  $data The date being saved.
	 * @param object $site The site object.
	 * @return void
	 */
	public static function search_and_replace_for_new_site($data, $site) {

		$to_site_id = $site->get_id();

		if (!$to_site_id) {

			return;

		} // end if;

		/**
		 * In order to be backwards compatible here, we'll have to do some crazy stuff,
		 * like overload the form session with the meta data saved on the pending site.
		 */
		$transient = wu_get_site($to_site_id)->get_meta('wu_form_data', array());

		wu_get_session('signup')->set('form', $transient);

		global $wpdb;

		$to_blog_prefix = $wpdb->get_blog_prefix($to_site_id);

		$string_to_replace = apply_filters('mucd_string_to_replace', array(), false, $to_site_id); // phpcs:ignore

		$tables = array();

		$to_blog_prefix_like = $wpdb->esc_like($to_blog_prefix);

		$results = \MUCD_Data::do_sql_query('SHOW TABLES LIKE \'' . $to_blog_prefix_like . '%\'', 'col', false);

		foreach ($results as $k => $v) {

			$tables[str_replace($to_blog_prefix, '', $v)] = array();

		} // end foreach;

		foreach ( $tables as $table => $col) {

			$results = \MUCD_Data::do_sql_query('SHOW COLUMNS FROM `' . $to_blog_prefix . $table . '`', 'col', false);

			$columns = array();

			foreach ($results as $k => $v) {

				$columns[] = $v;

			} // end foreach;

			$tables[$table] = $columns;

		} // end foreach;

		$default_tables = \MUCD_Option::get_fields_to_update();

		foreach ($default_tables as $table => $field) {

			$tables[$table] = $field;

		} // end foreach;

		foreach ($tables as $table => $field) {

			foreach ($string_to_replace as $from_string => $to_string) {

				\MUCD_Data::update($to_blog_prefix . $table, $field, $from_string, $to_string);

			} // end foreach;

		} // end foreach;

	} // end search_and_replace_for_new_site;

	/**
	 * Makes sure the search and replace array have no illegal values, such as null, false, etc
	 *
	 * @since 1.7.3
	 * @param array $search_and_replace The search and replace list.
	 * @return array
	 */
	public function filter_illegal_search_keys($search_and_replace) {

		return array_filter($search_and_replace, function($k) {

			return !is_null($k) && $k !== false && !empty($k);

		}, ARRAY_FILTER_USE_KEY);

	} // end filter_illegal_search_keys;

	/**
	 * Handle the deletion of pending sites.
	 *
	 * @since 2.0.0
	 *
	 * @param string $action The action.
	 * @param string $model The model.
	 * @param array  $ids The ids list.
	 * @return void
	 */
	public function handle_delete_pending_sites($action, $model, $ids) {

		foreach ($ids as $membership_id) {

			$membership = wu_get_membership($membership_id);

			if (empty($membership)) {
				/*
				 * Make sure we are able to delete pending
				 * sites even when memberships no longer exist.
				 */
				delete_metadata('wu_membership', $membership_id, 'pending_site');

				continue;

			} // end if;

			$membership->delete_pending_site();

		} // end foreach;

		wp_send_json_success(array(
			'redirect_url' => add_query_arg('deleted', count($ids), wu_get_current_url()),
		));

	} // end handle_delete_pending_sites;

	/**
	 * Hide the super admin user from the sub-site table list.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args List table user search arguments.
	 * @return array
	 */
	public function hide_super_admin_from_list($args) {

		if (!is_super_admin()) {

			$args['login__not_in'] = get_super_admins();

		} // end if;

		return $args;

	} // end hide_super_admin_from_list;

	/**
	 * Hides customer sites from the super admin user on listing.
	 *
	 * @since 2.0.11
	 *
	 * @param null|object[] $sites   An array of site objects of which the user is a member.
	 * @param int           $user_id User ID.
	 * @param bool          $all     Whether the returned array should contain all sites, including
	 *                               those marked 'deleted', 'archived', or 'spam'. Default false.
	 */
	public function hide_customer_sites_from_super_admin_list($sites, $user_id, $all) {

		global $wpdb;

		if (!is_super_admin()) {

			return $sites;

		} // end if;

		$keys = get_user_meta($user_id);

		if (empty($keys)) {

			return $sites;

		} // end if;

		// List the main site at beginning of array.
		if (isset($keys[$wpdb->base_prefix . 'capabilities']) && defined('MULTISITE')) {

			$site_ids[] = 1;

			unset($keys[$wpdb->base_prefix . 'capabilities']);

		} // end if;

		$keys = array_keys($keys);

		foreach ($keys as $key) {

			if ('capabilities' !== substr($key, -12)) {

				continue;

			} // end if;

			if ($wpdb->base_prefix && 0 !== strpos($key, $wpdb->base_prefix)) {

				continue;

			} // end if;

			$site_id = str_replace(array($wpdb->base_prefix, '_capabilities'), '', $key);

			if (!is_numeric($site_id)) {

				continue;

			} // end if;

			$site_ids[] = (int) $site_id;

		} // end foreach;

		$sites = array();

		if (!empty($site_ids)) {

			/**
			 * Here we change the default WP behavior to filter
			 * sites with wu_type meta value different than
			 * Site_Type::CUSTOMER_OWNED or without this meta
			 */
			$args = array(
				'number'                 => '',
				'site__in'               => $site_ids,
				'update_site_meta_cache' => false,
				'meta_query'             => array(
					'relation' => 'OR',
					array(
						'key'     => 'wu_type',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => 'wu_type',
						'compare' => 'NOT LIKE',
						'value'   => Site_Type::CUSTOMER_OWNED,
					),
				),
			);

			if (!$all) {

				$args['archived'] = 0;
				$args['spam']     = 0;
				$args['deleted']  = 0;

			} // end if;

			$_sites = get_sites($args);

			foreach ($_sites as $site) {

				$sites[$site->id] = (object) array(
					'userblog_id' => $site->id,
					'blogname'    => $site->blogname,
					'domain'      => $site->domain,
					'path'        => $site->path,
					'site_id'     => $site->network_id,
					'siteurl'     => $site->siteurl,
					'archived'    => $site->archived,
					'mature'      => $site->mature,
					'spam'        => $site->spam,
					'deleted'     => $site->deleted,
				);

			} // end foreach;

		} // end if;

		/**
		 * Replicates the original WP Filter here, for good measure.
		 *
		 * Filters the list of sites a user belongs to.
		 *
		 * @since 2.0.11
		 *
		 * @param object[] $sites   An array of site objects belonging to the user.
		 * @param int      $user_id User ID.
		 * @param bool     $all     Whether the returned sites array should contain all sites, including
		 *                          those marked 'deleted', 'archived', or 'spam'. Default false.
		 */
		return apply_filters('get_blogs_of_user', $sites, $user_id, $all); // phpcs:ignore

	} // end hide_customer_sites_from_super_admin_list;

} // end class Site_Manager;
