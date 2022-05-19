<?php
/**
 * WP Ultimo My_Account Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages\Customer_Panel;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Admin_Pages\Base_Customer_Facing_Admin_Page;

/**
 * WP Ultimo My_Account Admin Page.
 */
class Account_Admin_Page extends Base_Customer_Facing_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'account';

	/**
	 * Menu position. This is only used for top-level menus
	 *
	 * @since 1.8.2
	 * @var integer
	 */
	protected $position = 101010101;

	/**
	 * Dashicon to be used on the menu item. This is only used on top-level menus
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $menu_icon = 'dashicons-wu-email';

	/**
	 * If this number is greater than 0, a badge with the number will be displayed alongside the menu title
	 *
	 * @since 1.8.2
	 * @var integer
	 */
	protected $badge_count = 0;

	/**
	 * Should we hide admin notices on this page?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $hide_admin_notices = true;

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
		'admin_menu'      => 'exist',
		'user_admin_menu' => 'exist',
	);

	/**
	 * The current site instance.
	 *
	 * @since 2.0.0
	 * @var \WP_Ultimo\Models\Site
	 */
	protected $current_site;

	/**
	 * The current membership instance.
	 *
	 * @since 2.0.0
	 * @var \WP_Ultimo\Models\Membership
	 */
	protected $current_membership;

	/**
	 * Checks if we need to add this page.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$this->current_site = wu_get_current_site();

		$this->current_membership = $this->current_site->get_membership();

		$this->register_page_settings();

		if ($this->current_site->get_type() === 'customer_owned') {

			parent::__construct();

		} // end if;

	} // end __construct;

	/**
	 * Register forms
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms() {

		wu_register_form('change_password', array(
			'render'     => array($this, 'render_change_password'),
			'handler'    => array($this, 'handle_change_password'),
			'capability' => 'read',
		));

		wu_register_form('delete_site', array(
			'render'     => array($this, 'render_delete_site'),
			'handler'    => array($this, 'handle_delete_site'),
			'capability' => 'read',
		));

		wu_register_form('delete_account', array(
			'render'     => array($this, 'render_delete_account'),
			'handler'    => array($this, 'handle_delete_account'),
			'capability' => 'read',
		));

		wu_register_form('change_default_site', array(
			'render'     => array($this, 'render_change_default_site'),
			'handler'    => array($this, 'handle_change_default_site'),
			'capability' => 'read',
		));

	} // end register_forms;

	/**
	 * Renders the delete site modal.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_delete_site() {

		$fields = array(
			'confirm'               => array(
				'type'      => 'toggle',
				'title'     => __('Confirm Site Deletion', 'wp-ultimo'),
				'desc'      => __('This action can not be undone.', 'wp-ultimo'),
				'html_attr' => array(
					'v-model' => 'confirmed',
				),
			),
			'submit_button'         => array(
				'type'            => 'submit',
				'title'           => __('Delete Site', 'wp-ultimo'),
				'placeholder'     => __('Delete Site', 'wp-ultimo'),
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => array(
					'v-bind:disabled' => '!confirmed',
				),
			),
		);

		$form = new \WP_Ultimo\UI\Form('change_password', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'change_password',
				'data-state'  => wu_convert_to_state(array(
					'confirmed' => false,
				)),
			),
		));

		$form->render();

	} // end render_delete_site;

	/**
	 * Handles the delete site modal.
	 *
	 * @since 2.0.0
	 *
	 * @return void|WP_Error Void or WP_Error.
	 */
	public function handle_delete_site() {

		global $wpdb;

		if (!$this->current_site) {

			return new \WP_Error('error', __('An unexpected error happened.', 'wp-ultimo'));

		} // end if;

		$wpdb->query('START TRANSACTION');

		try {

			$saved = $this->current_site->delete();

			if (is_wp_error($saved)) {

				$wpdb->query('ROLLBACK');

				return $saved;

			} // end if;

		} catch (\Throwable $e) {

			$wpdb->query('ROLLBACK');

			return new \WP_Error('exception', $e->getMessage());

		} // end try;

		$wpdb->query('COMMIT');

		wp_send_json_success(array(
			'redirect_url' => user_admin_url(),
		));

	} // end handle_delete_site;

	/**
	 * Renders the delete account form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_delete_account() {

		$fields = array(
			'confirm'               => array(
				'type'      => 'toggle',
				'title'     => __('Confirm Account Deletion', 'wp-ultimo'),
				'desc'      => __('This action can not be undone.', 'wp-ultimo'),
				'html_attr' => array(
					'v-model' => 'confirmed',
				),
			),
			'submit_button'         => array(
				'type'            => 'submit',
				'title'           => __('Delete Account', 'wp-ultimo'),
				'placeholder'     => __('Delete Account', 'wp-ultimo'),
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => array(
					'v-bind:disabled' => '!confirmed',
				),
			),
		);

		$form = new \WP_Ultimo\UI\Form('change_password', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'change_password',
				'data-state'  => wu_convert_to_state(array(
					'confirmed' => false,
				)),
			),
		));

		$form->render();

	} // end render_delete_account;

	/**
	 * Handles the delete account form.
	 *
	 * @since 2.0.0
	 *
	 * @return void|WP_Error Void or WP_Error.
	 */
	public function handle_delete_account() {

		global $wpdb;

		$membership = $this->current_site->get_membership();

		if (!$membership) {

			return new \WP_Error('error', __('An unexpected error happened.', 'wp-ultimo'));

		} // end if;

		$wpdb->query('START TRANSACTION');

		try {
			/*
			 * Get Sites and delete them.
			 */
			$sites = wu_get_sites(array(
				'meta_query' => array(
					'membership_id' => array(
						'key'   => 'wu_membership_id',
						'value' => $membership->get_id(),
					),
				),
			));

			foreach ($sites as $site) {

				$saved = $site->delete();

				if (is_wp_error($saved)) {

					$wpdb->query('ROLLBACK');

					return $saved;

				} // end if;

			} // end foreach;

			/*
			 * Delete the membership
			 */
			$saved = $membership->delete();

			if (is_wp_error($saved)) {

				$wpdb->query('ROLLBACK');

				return $saved;

			} // end if;

		} catch (\Throwable $e) {

			$wpdb->query('ROLLBACK');

			return new \WP_Error('exception', $e->getMessage());

		} // end try;

		$wpdb->query('COMMIT');

		wp_logout();

		wp_send_json_success(array(
			'redirect_url' => get_home_url(wu_get_main_site_id()),
		));

	} // end handle_delete_account;

	/**
	 * Renders the change password modal.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_change_password() {

		$fields = array(
			'password'          => array(
				'type'        => 'password',
				'title'       => __('Current Password', 'wp-ultimo'),
				'placeholder' => __('******', 'wp-ultimo'),
			),
			'new_password'      => array(
				'type'        => 'password',
				'title'       => __('New Password', 'wp-ultimo'),
				'placeholder' => __('******', 'wp-ultimo'),
				'meter'       => true,
			),
			'new_password_conf' => array(
				'type'        => 'password',
				'placeholder' => __('******', 'wp-ultimo'),
				'title'       => __('Confirm New Password', 'wp-ultimo'),
			),
			'submit_button'     => array(
				'type'            => 'submit',
				'title'           => __('Reset Password', 'wp-ultimo'),
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => array(
					// 'v-bind:disabled' => '!confirmed',
				),
			),
		);

		$form = new \WP_Ultimo\UI\Form('change_password', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'change_password',
				'data-state'  => wu_convert_to_state(),
			),
		));

		$form->render();

	} // end render_change_password;

	/**
	 * Handles the password reset form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_change_password() {

		$user = wp_get_current_user();

		if (!$user) {

			$error = new \WP_Error('user-dont-exist', __('Something went wrong.', 'wp-ultimo'));

			wp_send_json_error($error);

		} // end if;

		$current_password = wu_request('password');

		if (!wp_check_password($current_password, $user->user_pass, $user->ID)) {

			$error = new \WP_Error('wrong-password', __('Your current password is wrong.', 'wp-ultimo'));

			wp_send_json_error($error);

		} // end if;

		$new_password      = wu_request('new_password');
		$new_password_conf = wu_request('new_password_conf');

		if (!$new_password || strlen($new_password) < 6) {

			$error = new \WP_Error('password-min-length', __('The new password must be at least 6 characters long.', 'wp-ultimo'));

			wp_send_json_error($error);

		} // end if;

		if ($new_password !== $new_password_conf) {

			$error = new \WP_Error('passwords-dont-match', __('New passwords do not match.', 'wp-ultimo'));

			wp_send_json_error($error);

		} // end if;

		reset_password($user, $new_password);

		wp_send_json_success(array(
			'redirect_url' => add_query_arg('updated', 1, $_SERVER['HTTP_REFERER']),
		));

	} // end handle_change_password;

	/**
	 * Renders the change current site modal.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_change_default_site() {

		$all_blogs = get_blogs_of_user(get_current_user_id());

		$option_blogs = array();

		foreach ($all_blogs as $key => $blog) {

			$option_blogs[$blog->userblog_id] = get_home_url($blog->userblog_id);

		} // end foreach;

		$primary_blog = get_user_meta(get_current_user_id(), 'primary_blog', true);

		$fields = array(
			'new_primary_site' => array(
				'type'      => 'select',
				'title'     => __('Primary Site', 'wp-ultimo'),
				'desc'      => __('Change the primary site of your network.', 'wp-ultimo'),
				'options'   => $option_blogs,
				'value'     => $primary_blog,
				'html_attr' => array(
					'v-model' => 'new_primary_site',
				),
			),
			'submit_button'    => array(
				'type'            => 'submit',
				'title'           => __('Change Default Site', 'wp-ultimo'),
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => array(
					'v-bind:disabled' => 'new_primary_site === "' . $primary_blog . '"',
				),
			),
		);

		$form = new \WP_Ultimo\UI\Form('change_default_site', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'change_default_site',
				'data-state'  => wu_convert_to_state(array(
					'new_primary_site' => $primary_blog
				)),
			),
		));

		$form->render();

	} // end render_change_default_site;

	/**
	 * Handles the change default site form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_change_default_site() {

		$new_primary_site = wu_request('new_primary_site');

		if ($new_primary_site) {

			update_user_meta(get_current_user_id(), 'primary_blog', $new_primary_site);

			wp_send_json_success(array(
				'redirect_url' => add_query_arg('updated', 1, $_SERVER['HTTP_REFERER']),
			));

		} // end if;

		$error = new \WP_Error('no-site-selected', __('You need to select a new primary site.', 'wp-ultimo'));

		wp_send_json_error($error);

	} // end handle_change_default_site;

	/**
	 * Loads the current site and membership.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function page_loaded() {

		$this->current_site = wu_get_current_site();

		$this->current_membership = $this->current_site->get_membership();

		$this->add_notices();

	} // end page_loaded;

	/**
	 * Adds notices after a membership is changed.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	protected function add_notices() {

		$nonce = wu_request('nonce');

		$update_type = wu_request('updated');

		if (empty($update_type)) {

			return;

		} // end if;

		$update_message = apply_filters('wu_account_update_message', __('Your account was successfully updated.', 'wp-ultimo'), $update_type);

		WP_Ultimo()->notices->add($update_message);

	} // end add_notices;

	/**
	 * Allow child classes to add hooks to be run once the page is loaded.
	 *
	 * @see https://codex.wordpress.org/Plugin_API/Action_Reference/load-(page)
	 * @since 1.8.2
	 * @return void
	 */
	public function hooks() {} // end hooks;

	/**
	 * Allow child classes to add screen options; Useful for pages that have list tables.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function screen_options() {} // end screen_options;

	/**
	 * Allow child classes to register widgets, if they need them.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_widgets() {

		\WP_Ultimo\UI\Current_Membership_Element::get_instance()->as_metabox(get_current_screen()->id);

		\WP_Ultimo\UI\Billing_Info_Element::get_instance()->as_metabox(get_current_screen()->id, 'side');

		\WP_Ultimo\UI\Invoices_Element::get_instance()->as_metabox(get_current_screen()->id, 'side');

		\WP_Ultimo\UI\Site_Actions_Element::get_instance()->as_metabox(get_current_screen()->id, 'side');

		\WP_Ultimo\UI\Account_Summary_Element::get_instance()->as_metabox(get_current_screen()->id);

		\WP_Ultimo\UI\Limits_Element::get_instance()->as_metabox(get_current_screen()->id);

		\WP_Ultimo\UI\Domain_Mapping_Element::get_instance()->as_metabox(get_current_screen()->id, 'side');

		\WP_Ultimo\UI\Login_Form_Element::get_instance()->as_inline_content(get_current_screen()->id, 'wu_dash_before_metaboxes');

		\WP_Ultimo\UI\Simple_Text_Element::get_instance()->as_inline_content(get_current_screen()->id, 'wu_dash_before_metaboxes');

		\WP_Ultimo\UI\Current_Site_Element::get_instance()->as_inline_content(get_current_screen()->id, 'wu_dash_before_metaboxes');

	} // end register_widgets;

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return __('Account', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Account', 'wp-ultimo');

	} // end get_menu_title;

	/**
	 * Allows admins to rename the sub-menu (first item) for a top-level page.
	 *
	 * @since 2.0.0
	 * @return string False to use the title menu or string with sub-menu title.
	 */
	public function get_submenu_title() {

		return __('Account', 'wp-ultimo');

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
			'has_full_position' => false,
		));

	} // end output;

} // end class Account_Admin_Page;
