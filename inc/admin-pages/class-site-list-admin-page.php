<?php
/**
 * WP Ultimo Sites Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo Sites Admin Page.
 */
class Site_List_Admin_Page extends List_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-sites';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $type = 'submenu';

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
		'network_admin_menu' => 'wu_read_sites',
	);

	/**
	 * Register ajax forms that we use for sites.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms() {
		/*
		 * Edit/Add New Site
		 */
		wu_register_form('add_new_site', array(
			'render'     => array($this, 'render_add_new_site_modal'),
			'handler'    => array($this, 'handle_add_new_site_modal'),
			'capability' => 'wu_add_sites',
		));

		/*
		 * Publish pending site.
		 */
		wu_register_form('publish_pending_site', array(
			'render'     => array($this, 'render_publish_pending_site_modal'),
			'handler'    => array($this, 'handle_publish_pending_site_modal'),
			'capability' => 'wu_publish_sites',
		));

	} // end register_forms;

	/**
	 * Renders the deletion confirmation form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	function render_publish_pending_site_modal() {

		$membership = wu_get_membership(wu_request('membership_id'));

		if (!$membership) {

			return;

		} // end if;

		$fields = array(
			'confirm'       => array(
				'type'      => 'toggle',
				'title'     => __('Confirm Publication', 'wp-ultimo'),
				'desc'      => __('This action can not be undone.', 'wp-ultimo'),
				'html_attr' => array(
					'v-model' => 'confirmed',
				),
			),
			'submit_button' => array(
				'type'            => 'submit',
				'title'           => __('Publish', 'wp-ultimo'),
				'placeholder'     => __('Publish', 'wp-ultimo'),
				'value'           => 'publish',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => array(
					'v-bind:disabled' => '!confirmed',
				),
			),
			'membership_id' => array(
				'type'  => 'hidden',
				'value' => $membership->get_id(),
			),
		);

		$form = new \WP_Ultimo\UI\Form('total-actions', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'true',
				'data-state'  => json_encode(array(
					'confirmed' => false,
				)),
			),
		));

		$form->render();

	} // end render_publish_pending_site_modal;

	/**
	 * Handles the deletion of line items.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_publish_pending_site_modal() {

		$membership = wu_get_membership(wu_request('membership_id'));

		if (!$membership) {

			wp_send_json_error(new \WP_Error('not-found', __('Pending site not found.', 'wp-ultimo')));

		} // end if;

		$pending_site = $membership->get_pending_site();

		if (!is_a($pending_site, '\\WP_Ultimo\\Models\\Site')) {

			wp_send_json_error(new \WP_Error('not-found', __('Pending site not found.', 'wp-ultimo')));

		} // end if;

		$pending_site->set_type('customer_owned');

		$saved = $pending_site->save();

		if (is_wp_error($saved)) {

			wp_send_json_error($saved);

		} // end if;

		$membership->delete_pending_site();

		wp_send_json_success(array(
			'redirect_url' => wu_network_admin_url('wp-ultimo-edit-site', array(
				'id' => $pending_site->get_id(),
			))
		));

	} // end handle_publish_pending_site_modal;

	/**
	 * Handles the add/edit of line items.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function handle_add_new_site_modal() {

		global $current_site;

		$domain_type = wu_request('tab', is_subdomain_install() ? 'sub-domain' : 'sub-directory');

		if ($domain_type === 'domain') {

			$domain = wu_request('domain', '');
			$path   = '';

		} else {

			$d      = wu_get_site_domain_and_path(wu_request('domain', ''));
			$domain = $d->domain;
			$path   = $d->path;

		} // end if;

		$atts = array(
			'domain'                => $domain,
			'path'                  => $path,
			'title'                 => wu_request('title'),
			'type'                  => wu_request('type'),
			'template_id'           => wu_request('template_site', 0),
			'membership_id'         => wu_request('membership_id', false),
			'duplication_arguments' => array(
				'copy_media' => wu_request('copy_media'),
			)
		);

		$site = wu_create_site($atts);

		if (is_wp_error($site)) {

			return wp_send_json_error($site);

		} // end if;

		if ($site->get_blog_id() === false) {

			$error = new \WP_Error('error', __('Something wrong happened.', 'wp-ultimo'));

			return wp_send_json_error($error);

		} // end if;

		wp_send_json_success(array(
			'redirect_url' => wu_network_admin_url('wp-ultimo-edit-site', array(
				'id' => $site->get_id(),
			))
		));

	} // end handle_add_new_site_modal;

	/**
	 * Renders the add/edit line items form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_add_new_site_modal() {

		global $current_site;

		$duplicate_id = wu_request('id');

		$site = wu_get_site($duplicate_id);

		$type          = 'site_template';
		$title         = '';
		$path          = 'mysite';
		$template_id   = '';
		$membership_id = '';

		/*
		 * Checks if this is a duplication process.
		 */
		if ($duplicate_id && $site) {

			$title         = sprintf(__('Copy of %s', 'wp-ultimo'), $site->get_title());
			$path          = sprintf('%s%s', trim($site->get_path(), '/'), 'copy');
			$type          = $site->get_type();
			$template_id   = $duplicate_id;
			$membership_id = $site->get_membership_id();

		} // end if;

		$save_label = $duplicate_id ? __('Duplicate Site', 'wp-ultimo') : __('Add new Site', 'wp-ultimo');

		$fields = array(
			'tab'           => array(
				'type'              => 'tab-select',
				'wrapper_html_attr' => array(
					'v-cloak' => 1,
				),
				'html_attr'         => array(
					'v-model' => 'tab',
				),
				'options'           => array(
					'sub-domain'    => __('Subdomain', 'wp-ultimo'),
					'sub-directory' => __('Subdirectory', 'wp-ultimo'),
					'domain'        => __('Domain', 'wp-ultimo'),
				),
			),
			'title'         => array(
				'type'        => 'text',
				'title'       => __('Site Title', 'wp-ultimo'),
				'placeholder' => __('New Network Site', 'wp-ultimo'),
				'value'       => $title,
			),
			'domain_group'  => array(
				'type'   => 'group',
				'desc'   => sprintf(__('The site URL will be: %s', 'wp-ultimo'), '<span class="wu-font-mono">{{ tab === "domain" ? domain : ( tab === "sub-directory" ? scheme + base_url + domain : scheme + domain + "." + base_url ) }}</span>'),
				'fields' => array(
					'domain' => array(
						'type'            => 'text',
						'title'           => __('Site Domain/Path', 'wp-ultimo'),
						'tooltip'         => __('Enter the complete domain for the site', 'wp-ultimo'),
						'wrapper_classes' => 'wu-w-full',
						'html_attr'       => array(
							'v-bind:placeholder' => 'tab === "domain" ? "mysite.com" : "mysite"',
							'v-on:input'   => 'domain = $event.target.value.toLowerCase().replace(/[^a-z0-9-_\.]+/g, "")',
							'v-bind:value' => 'domain',
						),
					),
				),
			),
			'type'          => array(
				'type'        => 'select',
				'title'       => __('Site Type', 'wp-ultimo'),
				'value'       => $type,
				'placeholder' => '',
				'options'     => array(
					'default'        => __('Regular WP Site', 'wp-ultimo'),
					'site_template'  => __('Site Template', 'wp-ultimo'),
					'customer_owned' => __('Customer-Owned', 'wp-ultimo'),
				),
				'html_attr'   => array(
					'v-model' => 'type',
				),
			),
			'membership_id' => array(
				'type'              => 'model',
				'title'             => __('Associated Membership', 'wp-ultimo'),
				'placeholder'       => __('Membership', 'wp-ultimo'),
				'value'             => '',
				'tooltip'           => '',
				'wrapper_html_attr' => array(
					'v-show' => "type === 'customer_owned'",
				),
				'html_attr'         => array(
					'data-model'        => 'membership',
					'data-value-field'  => 'id',
					'data-label-field'  => 'reference_code',
					'data-search-field' => 'reference_code',
					'data-max-items'    => 1,
				),
			),
			'copy'          => array(
				'type'      => 'toggle',
				'title'     => __('Copy Site?', 'wp-ultimo'),
				'desc'      => __('Select a existing site to use as a starting point.', 'wp-ultimo'),
				'html_attr' => array(
					'v-model' => 'copy',
				),
			),
			'template_site' => array(
				'type'              => 'model',
				'title'             => __('Template Site', 'wp-ultimo'),
				'placeholder'       => __('Search sites', 'wp-ultimo'),
				'tooltip'           => __('The site selected will be used as a started point.', 'wp-ultimo'),
				'value'             => $template_id,
				'html_attr'         => array(
					'data-model'        => 'site',
					'data-selected'     => $site ? json_encode($site->to_search_results()) : '',
					'data-value-field'  => 'blog_id',
					'data-label-field'  => 'title',
					'data-search-field' => 'title',
					'data-max-items'    => 1,
				),
				'wrapper_html_attr' => array(
					'v-show' => 'copy',
				),
			),
			'copy_media'    => array(
				'type'              => 'toggle',
				'title'             => __('Copy Media on Duplication?', 'wp-ultimo'),
				'desc'              => __('Copy media files from the template site on duplication.', 'wp-ultimo'),
				'value'             => true,
				'wrapper_html_attr' => array(
					'v-show' => 'copy',
				),
			),
			'submit_button' => array(
				'type'            => 'submit',
				'title'           => $save_label,
				'placeholder'     => $save_label,
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end wu-text-right',
				'html_attr'       => array(
					'v-bind:disabled' => '!enable_sub_domain && tab === "sub-domain"',
				),
			),
		);

		$form = new \WP_Ultimo\UI\Form('add_new_site', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'add_new_site',
				'data-state'  => wu_convert_to_state(array(
					'tab'               => is_subdomain_install() ? 'sub-domain' : 'sub-directory',
					'enable_sub_domain' => is_subdomain_install(),
					'membership'        => $membership_id,
					'type'              => $type,
					'copy'              => (int) $site,
					'base_url'          => $current_site->domain . '/',
					'scheme'            => is_ssl() ? 'https://' : 'http://',
					'domain'            => $path,
				)),
			),
		));

		$form->render();

	} // end render_add_new_site_modal;

	/**
	 * Allow child classes to register widgets, if they need them.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_widgets() {} // end register_widgets;

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return __('Sites', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Sites', 'wp-ultimo');

	} // end get_menu_title;

	/**
	 * Allows admins to rename the sub-menu (first item) for a top-level page.
	 *
	 * @since 2.0.0
	 * @return string False to use the title menu or string with sub-menu title.
	 */
	public function get_submenu_title() {

		return __('Sites', 'wp-ultimo');

	} // end get_submenu_title;

	/**
	 * Returns the action links for that page.
	 *
	 * @since 1.8.2
	 * @return array
	 */
	public function action_links() {

		return array(
			array(
				'label'   => __('Add Site'),
				'icon'    => 'wu-circle-with-plus',
				'classes' => 'wubox',
				'url'     => wu_get_form_url('add_new_site'),
			),
		);

	} // end action_links;

	/**
	 * Loads the list table for this particular page.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\List_Tables\Base_List_Table
	 */
	public function table() {

		return new \WP_Ultimo\List_Tables\Site_List_Table();

	} // end table;

} // end class Site_List_Admin_Page;
