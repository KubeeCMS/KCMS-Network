<?php
/**
 * WP Ultimo Support Agent Edit Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Models\Support_Agent;

/**
 * WP Ultimo Support Agent Edit/Add New Admin Page.
 */
class Support_Agent_Edit_Admin_Page extends Edit_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-edit-support-agent';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $type = 'submenu';

	/**
	 * Object ID being edited.
	 *
	 * @since 1.8.2
	 * @var string
	 */
	public $object_id = 'customer';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $parent = 'none';

	/**
	 * This page has no parent, so we need to highlight another sub-menu.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $highlight_menu_slug = 'wp-ultimo-support-agents';

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
		'network_admin_menu' => 'wu_edit_support_agents',
	);

	/**
	 * Get the toggle section slug to data-sate
	 *
	 * @since 2.0.0
	 *
	 * @param array  $data Registred Capabilities.
	 * @param string $slug Tab Slug.
	 * @return array
	 */
	public function get_sections_wrapper_attrs($data, $slug) {

		$states = array();

		foreach ($data as $key => $value) {

			if (is_array($value) && array_key_exists('capabilities', $value)) {

				$states["toggle_all_$slug"]["toggle_section_$key"] = array();

				foreach ($value['capabilities'] as $cap_slug => $cap_value) {

					$states["toggle_all_$slug"]["toggle_section_$key"]["toggle_field_$cap_slug"] = $this->get_object()->can($cap_slug);

				} // end foreach;

			} else {

				$key_underline = str_replace(array(':', '-'), '_', $key);

				$states["toggle_all_$slug"]['toggle_section_dashboard_widgets']["toggle_field_$key_underline"] = $this->get_object()->has_widget($key);

			} // end if;

		} // end foreach;

		return $states;

	} // end get_sections_wrapper_attrs;

	/**
	 * Get the permission fields for the edit screen.
	 *
	 * @since 2.0.0
	 *
	 * @param array $capabilities Registred Capabilities.
	 * @return array
	 */
	public function get_permission_fields($capabilities) {

		$fields = array();

		$slug_all = 'toggle_all_capabilities';

		$fields[$slug_all] = array(
			'type'            => 'toggle',
			'title'           => __('Toggle All', 'wp-ultimo'),
			'desc'            => __('Check to allow this agent to perform all actions on the platform.', 'wp-ultimo'),
			'wrapper_classes' => 'wu-w-full',
			'html_attr'       => array(
				'v-on:click' => "(e) => Object.keys($slug_all).forEach(function (key) {
					Object.keys(" . $slug_all . '[key]).forEach(function (j) {
						' . $slug_all . '[key][j] = e.target.checked;
					}, e);
				}, e)',
			),
			'value'           => false,
		);

		foreach ($capabilities as $permission_type_slug => $permission_type) {

			$fields["{$permission_type_slug}_header"] = array(
				'title'           => $permission_type['title'],
				'desc'            => $permission_type['desc'],
				'type'            => 'toggle',
				'wrapper_classes' => 'wu-w-full wu-bg-gray-100',
				'html_attr'       => array(
					'v-on:click' => "(e) => Object.keys($slug_all.toggle_section_$permission_type_slug).forEach(function (key) {
						$slug_all" . '.toggle_section_' . $permission_type_slug . '[key] = e.target.checked;
					}, e)',
					'v-if'   => "() => $slug_all.toggle_section_.$permission_type_slug.every(v => v === true)"
				),
			);

			$fields['capabilities[]'] = array(
				'type' => 'hidden',
			);

			foreach ($permission_type['capabilities'] as $permission_slug => $permission) {

				$fields["capabilities[{$permission_slug}]"] = array(
					'type'            => 'toggle',
					'title'           => $permission['title'],
					'desc'            => $permission['desc'],
					'tooltip'         => '',
					'wrapper_classes' => 'wu-w-1/2 wu-float-left',
					'html_attr'       => array(
						'v-model' => "$slug_all.toggle_section_$permission_type_slug.toggle_field_$permission_slug",
					),
				);

			} // end foreach;

		} // end foreach;

		return $fields;

	} // end get_permission_fields;

	/**
	 * Get the default WordPress permission fields for the edit screen.
	 *
	 * @since 2.0.0
	 *
	 * @param array $capabilities Registred Capabilities.
	 * @return array
	 */
	public function get_default_wordpress_permission_fields($capabilities) {

		$fields = array();

		$slug_all = 'toggle_all_capabilities_wordpress';

		$fields[$slug_all] = array(
			'type'            => 'toggle',
			'title'           => __('Toggle All', 'wp-ultimo'),
			'desc'            => __('Check to allow this agent to perform all actions on the platform.', 'wp-ultimo'),
			'wrapper_classes' => 'wu-w-full',
			'html_attr'       => array(
				'v-on:click' => "(e) => Object.keys($slug_all).forEach(function (key) {
					Object.keys(" . $slug_all . '[key]).forEach(function (j) {
						' . $slug_all . '[key][j] = e.target.checked;
					}, e);
				}, e)',
			),
			'value'           => false,
		);

		foreach ($capabilities as $permission_type_slug => $permission_type) {

			$fields["{$permission_type_slug}_header"] = array(
				'title'           => $permission_type['title'],
				'desc'            => $permission_type['desc'],
				'type'            => 'toggle',
				'wrapper_classes' => 'wu-w-full wu-bg-gray-100',
				'html_attr'       => array(
					'v-on:click' => "(e) => Object.keys($slug_all.toggle_section_$permission_type_slug).forEach(function (key) {
						$slug_all" . '.toggle_section_' . $permission_type_slug . '[key] = e.target.checked;
					}, e)',
				),
			);

			$fields['capabilities_wordpress[]'] = array(
				'type' => 'hidden',
			);

			foreach ($permission_type['capabilities'] as $permission_slug => $permission) {

				$fields["capabilities_wordpress[{$permission_slug}]"] = array(
					'type'            => 'toggle',
					'title'           => $permission['title'],
					'desc'            => $permission['desc'],
					'tooltip'         => '',
					'wrapper_classes' => 'wu-w-1/2 wu-float-left',
					'html_attr'       => array(
						'v-model' => "$slug_all.toggle_section_$permission_type_slug.toggle_field_$permission_slug",
					),
				);

			} // end foreach;

		} // end foreach;

		return $fields;

	} // end get_default_wordpress_permission_fields;

	/**
	 * Get the Dashboard Network Widgets fields for the edit screen
	 *
	 * @since 2.0.0
	 *
	 * @param array $widgets Registred Widgets.
	 * @return array Toogle Fields;
	 */
	public function get_dashboard_network_widgets($widgets) {

		$fields = array();

		$slug_all = 'toggle_all_network_dashboard_widgets';

		$fields[$slug_all] = array(
			'type'            => 'toggle',
			'title'           => __('Toggle All', 'wp-ultimo'),
			'desc'            => __('Check to allow this agent to perform all actions on the platform.', 'wp-ultimo'),
			'wrapper_classes' => 'wu-w-full',
			'html_attr'       => array(
				'v-on:click' => "(e) => Object.keys($slug_all).forEach(function (key) {
					Object.keys(" . $slug_all . '[key]).forEach(function (j) {
						' . $slug_all . '[key][j] = e.target.checked;
					}, e);
				}, e)',
			),
			'value'           => false,
		);

		$fields['network_dashboard_widgets_header'] = array(
			'title'           => __('Dashboard Network Widgets', 'wp-ultimo'),
			'desc'            => __('Enable and disable dashboard widgets for this agent.', 'wp-ultimo'),
			'type'            => 'toggle',
			'wrapper_classes' => 'wu-w-full wu-bg-gray-100',
			'html_attr'       => array(
				'v-on:click' => "(e) => Object.keys($slug_all.toggle_section_dashboard_widgets).forEach(function (key) {
					$slug_all" . '.toggle_section_dashboard_widgets' . '[key] = e.target.checked;
				}, e)',
			),
		);

		$fields['network_dashboard_widgets[]'] = array(
			'type' => 'hidden',
		);

		foreach ($widgets as $key => $title) {

			$key_underline = str_replace(array(':', '-'), '_', $key);

			$fields["network_dashboard_widgets[{$key}]"] = array(
				'type'            => 'toggle',
				'title'           => $title,
				'desc'            => __('Toggle to disable.', 'wp-ultimo'),
				'tooltip'         => '',
				'wrapper_classes' => 'wu-w-1/2 wu-float-left',
				'html_attr'       => array(
					'v-model' => "$slug_all.toggle_section_dashboard_widgets.toggle_field_$key_underline",
				),
			);

		} // end foreach;

		return $fields;

	} // end get_dashboard_network_widgets;

	/**
	 * Allow child classes to register widgets, if they need them.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_widgets() {

		parent::register_widgets();

		$this->add_fields_widget('at_a_glance', array(
			'title'                 => __('At a Glance', 'wp-ultimo'),
			'position'              => 'normal',
			'classes'               => 'wu-overflow-hidden wu-m-0 wu--mt-1 wu--mx-3 wu--mb-3',
			'field_wrapper_classes' => 'wu-w-1/4 wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t-0 wu-border-l-0 wu-border-r wu-border-b-0 wu-border-gray-300 wu-border-solid wu-float-left wu-relative',
			'html_attr'             => array(
				'style' => 'margin-top: -6px;',
			),
			'fields'                => array(
				'id'         => array(
					'type'          => 'text-display',
					'copy'          => true,
					'title'         => __('Support Agent ID', 'wp-ultimo'),
					'display_value' => $this->get_object()->get_id(),
					'tooltip'       => '',
				),
				'last_login' => array(
					'edit'          => false,
					'title'         => __('Last Login', 'wp-ultimo'),
					'type'          => 'text-edit',
					'display_value' => __('Never', 'wp-ultimo'),
				),
			),
		));

		$capabilities              = \WP_Ultimo\Permission_Control::get_instance()->registered_capabilities();
		$capabilities_wordpress    = \WP_Ultimo\Permission_Control::get_instance()->get_capabilities_default_wordpress();
		$network_dashboard_widgets = \WP_Ultimo\Dashboard_Widgets::get_registered_dashboard_widgets();

		$this->add_tabs_widget('options', array(
			'title'    => __('Support Agent Options', 'wp-ultimo'),
			'position' => 'normal',
			'sections' => array(
				'capabilities'              => array(
					'title'                 => __('WP Ultimo Permissions', 'wp-ultimo'),
					'field_wrapper_classes' => 'wu-w-1/2 wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
					'desc'                  => __('Select what level of access this support access will have.', 'wp-ultimo'),
					'icon'                  => 'dashicons-wu-lock',
					'fields'                => $this->get_permission_fields($capabilities),
					'state'                 => array_merge($this->get_sections_wrapper_attrs($capabilities, 'capabilities')),
				),
				'capabilities_wordpress'    => array(
					'title'                 => __('WordPress Permissions', 'wp-ultimo'),
					'field_wrapper_classes' => 'wu-w-1/2 wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
					'desc'                  => __('Select what level of access this support access will have.', 'wp-ultimo'),
					'icon'                  => 'dashicons-wordpress',
					'fields'                => $this->get_default_wordpress_permission_fields($capabilities_wordpress),
					'state'                 => array_merge($this->get_sections_wrapper_attrs($capabilities_wordpress, 'capabilities_wordpress')),
				),
				'network_dashboard_widgets' => array(
					'title'                 => __('Dashboard Widgets', 'wp-ultimo'),
					'field_wrapper_classes' => 'wu-w-1/2 wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
					'desc'                  => __('Select what level of access this support access will have.', 'wp-ultimo'),
					'icon'                  => 'dashicons-welcome-widgets-menus',
					'fields'                => $this->get_dashboard_network_widgets($network_dashboard_widgets),
					'state'                 => array_merge($this->get_sections_wrapper_attrs($network_dashboard_widgets, 'network_dashboard_widgets')),
				),
			),
		));

		$this->add_save_widget('save', array(
			'before' => wu_get_template_contents('customers/widget-avatar', array(
				'customer' => $this->get_object(),
				'user'     => $this->get_object()->get_user(),
			)),
			'fields' => array(
				'user_id' => array(
					'type'              => 'model',
					'title'             => __('User', 'wp-ultimo'),
					'placeholder'       => __('Search WordPress user...', 'wp-ultimo'),
					'value'             => $this->get_object()->get_user_id(),
					'tooltip'           => '',
					'min'               => 1,
					'wrapper_html_attr' => array(
						'v-cloak' => '1',
					),
					'html_attr'         => array(
						'data-model'        => 'user',
						'data-value-field'  => 'ID',
						'data-label-field'  => 'display_name',
						'data-search-field' => 'display_name',
						'data-max-items'    => 1,
						'data-selected'     => json_encode($this->get_object()->get_user()->data),
					),
				),
			),
		));

	} // end register_widgets;

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return $this->edit ? __('Edit Support Agent', 'wp-ultimo') : __('Add new Support Agent', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Edit Support Agent', 'wp-ultimo');

	} // end get_menu_title;

	/**
	 * Returns the action links for that page.
	 *
	 * @since 1.8.2
	 * @return array
	 */
	public function action_links() {

		return array();

	} // end action_links;

	/**
	 * Returns the labels to be used on the admin page.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_labels() {

		return array(
			'edit_label'          => __('Edit Support Agent', 'wp-ultimo'),
			'add_new_label'       => __('Add new Support Agent', 'wp-ultimo'),
			'updated_message'     => __('Support Agent updated with success!', 'wp-ultimo'),
			'title_placeholder'   => __('Enter Support Agent', 'wp-ultimo'),
			'title_description'   => '',
			'save_button_label'   => __('Save Support Agent', 'wp-ultimo'),
			'save_description'    => '',
			'delete_button_label' => __('Delete Support Agent', 'wp-ultimo'),
			'delete_description'  => __('Be careful. This action is irreversible.', 'wp-ultimo'),
		);

	} // end get_labels;

	/**
	 * Returns the object being edit at the moment.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Support_Agent
	 */
	public function get_object() {

		if ($this->object !== null) {

			return $this->object;

		} // end if;

		$item_id = wu_request('id', 0);

		$item = wu_get_support_agent($item_id);

		// var_dump($item->get_capabilities_wordpress());
		// die;

		if (!$item || $item->get_type() !== 'support-agent') {

			wp_redirect(wu_network_admin_url('wp-ultimo-support-agents'));

			exit;

		} // end if;

		$this->object = $item;

		return $this->object;

	} // end get_object;

	/**
	 * Customers have titles.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_title() {

		return false;

	} // end has_title;

} // end class Support_Agent_Edit_Admin_Page;
