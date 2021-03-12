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

use \WP_Ultimo\Models\Domain;
use \WP_Ultimo\Database\Domains\Domain_Stage;

/**
 * WP Ultimo Dashboard Admin Page.
 */
class Domain_List_Admin_Page extends List_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-domains';

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
		'network_admin_menu' => 'wu_read_domains',
	);

	/**
	 * Register ajax forms that we use for payments.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms() {
		/*
		 * Add new Domain
		 */
		wu_register_form('add_new_domain', array(
			'render'     => array($this, 'render_add_new_domain_modal'),
			'handler'    => array($this, 'handle_add_new_domain_modal'),
			'capability' => 'wu_edit_domains',
		));

	} // end register_forms;

	/**
	 * Renders the add new customer modal.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	function render_add_new_domain_modal() {

		$fields = array(
			'type'                   => array(
				'type'      => 'tab-select',
				'options'   => array(
					'add'      => __('Add Existing Domain', 'wp-ultimo'),
					'register' => __('Register New', 'wp-ultimo'),
				),
				'html_attr' => array(
					'v-model' => 'type',
				),
			),
			'domain'                 => array(
				'type'        => 'text',
				'title'       => __('Domain', 'wp-ultimo'),
				'placeholder' => __('mydomain.com', 'wp-ultimo'),
			),
			'blog_id'                => array(
				'type'        => 'model',
				'title'       => __('Apply to Site', 'wp-ultimo'),
				'placeholder' => __('Search sites', 'wp-ultimo'),
				'tooltip'     => __('The site selected will be used as a started point.', 'wp-ultimo'),
				'html_attr'   => array(
					'data-model'        => 'site',
					'data-value-field'  => 'blog_id',
					'data-label-field'  => 'title',
					'data-search-field' => 'title',
					'data-max-items'    => 1,
				),
			),
			'stage'                  => array(
				'type'        => 'select',
				'title'       => __('Stage', 'wp-ultimo'),
				'placeholder' => __('Select Stage', 'wp-ultimo'),
				'options'     => Domain_Stage::to_array(),
				'value'       => Domain_Stage::CHECKING_DNS,
			),
			'primary_domain'         => array(
				'type'      => 'toggle',
				'title'     => __('Primary Domain', 'wp-ultimo'),
				'desc'      => __('Check to set this domain as the primary', 'wp-ultimo'),
				'html_attr' => array(
					'v-model' => 'primary_domain',
				),
			),
			'primary_note'           => array(
				'type'              => 'note',
				'desc'              => __('By making this the primary domain, we will convert the previous primary domain for this site, if one exists, into an alias domain.', 'wp-ultimo'),
				'wrapper_html_attr' => array(
					'v-if' => "require('primary_domain', true)",
				),
			),
			'submit_button_register' => array(
				'type'              => 'submit',
				'title'             => __('Register and Add Domain (soon)', 'wp-ultimo'),
				'value'             => 'save',
				'classes'           => 'button button-primary wu-w-full',
				'wrapper_classes'   => 'wu-items-end',
				'wrapper_html_attr' => array(
					'v-if' => "require('type', 'register')",
				),
				'html_attr'         => array(
					'disabled' => 'disabled',
				),
			),
			'submit_button_new'      => array(
				'type'              => 'submit',
				'title'             => __('Add Existing Domain', 'wp-ultimo'),
				'value'             => 'save',
				'classes'           => 'button button-primary wu-w-full',
				'wrapper_classes'   => 'wu-items-end',
				'wrapper_html_attr' => array(
					'v-if' => "require('type', 'add')",
				),
			),
		);

		$form = new \WP_Ultimo\UI\Form('add_new_domain', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'add_new_domain',
				'data-state'  => json_encode(array(
					'type'           => 'add',
					'primary_domain' => false,
				)),
			),
		));

		$form->render();

	} // end render_add_new_domain_modal;

	/**
	 * Handles creation of a new customer.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_add_new_domain_modal() {

		if (wu_request('type', 'existing') === 'register') {
			/*
			 * @todo We need to integrate domain name registrars here.
			 */
			wp_send_json_error(new \WP_Error('not-supported', __('Domain registering is not yet supported', 'wp-ultimo')));

		} // end if;

		/*
		* Tries to create the domain
		*/
		$domain = wu_create_domain(array(
			'domain'         => wu_request('domain'),
			'stage'          => wu_request('stage'),
			'blog_id'        => (int) wu_request('blog_id'),
			'primary_domain' => (bool) wu_request('primary_domain'),
		));

		if (is_wp_error($domain)) {

			wp_send_json_error($domain);

		} // end if;

		if (wu_request('primary_domain')) {

			$old_primary_domains = wu_get_domains(array(
				'primary_domain' => true,
				'blog_id'        => wu_request('blog_id'),
				'id__not_in'     => array($domain->get_id()),
				'fields'         => 'ids',
			));

			/*
			 * Trigger async action to update the old primary domains.
			 */
			wu_enqueue_async_action('wu_async_remove_old_primary_domains', array($old_primary_domains), 'domains');

		} // end if;

		wu_enqueue_async_action('wu_async_process_domain_stage', array('domain_id' => $domain->get_id()), 'domain');

		wp_send_json_success(array(
			'redirect_url' => wu_network_admin_url('wp-ultimo-edit-domain', array(
				'id' => $domain->get_id(),
			))
		));

	} // end handle_add_new_domain_modal;

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return __('Domains', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Domains', 'wp-ultimo');

	} // end get_menu_title;

	/**
	 * Allows admins to rename the sub-menu (first item) for a top-level page.
	 *
	 * @since 2.0.0
	 * @return string False to use the title menu or string with sub-menu title.
	 */
	public function get_submenu_title() {

		return __('Domains', 'wp-ultimo');

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
				'label'   => __('Add Domain'),
				'icon'    => 'wu-circle-with-plus',
				'classes' => 'wubox',
				'url'     => wu_get_form_url('add_new_domain'),
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

		return new \WP_Ultimo\List_Tables\Domain_List_Table();

	} // end table;

} // end class Domain_List_Admin_Page;
