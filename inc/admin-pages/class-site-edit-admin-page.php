<?php
/**
 * WP Ultimo Site Edit New Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Models\Site;
use \WP_Ultimo\Managers\Site_Manager;

/**
 * WP Ultimo Site Edit New Admin Page.
 */
class Site_Edit_Admin_Page extends Edit_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-edit-site';

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
	public $object_id = 'site';

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
	protected $highlight_menu_slug = 'wp-ultimo-sites';

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
		'network_admin_menu' => 'wu_edit_sites',
	);

	/**
	 * Registers the necessary scripts and styles for this admin page.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts() {

		parent::register_scripts();

		wp_enqueue_media();

	} // end register_scripts;

	/**
	 * Register ajax forms that we use for site.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms() {
		/*
		 * Transfer site - Confirmation modal
		 */
		wu_register_form('transfer_site', array(
			'render'     => array($this, 'render_transfer_site_modal'),
			'handler'    => array($this, 'handle_transfer_site_modal'),
			'capability' => 'wu_transfer_site',
		));

		/*
		 * Delete Site - Confirmation modal
		 */

		add_filter('wu_data_json_success_delete_site_modal', function($data_json) {
			return array(
				'redirect_url' => wu_network_admin_url('wp-ultimo-sites', array('deleted' => 1))
			);
		});

	} // end register_forms;

	/**
	 * Renders the transfer confirmation form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	function render_transfer_site_modal() {

		$site = wu_get_site(wu_request('id'));

		if (!$site) {

			return;

		} // end if;

		$fields = array(
			'confirm'              => array(
				'type'      => 'toggle',
				'title'     => __('Confirm Transfer', 'wp-ultimo'),
				'desc'      => __('This will start the transfer of assets from one membership to another.', 'wp-ultimo'),
				'html_attr' => array(
					'v-model' => 'confirmed',
				),
			),
			'submit_button'        => array(
				'type'            => 'submit',
				'title'           => __('Start Transfer', 'wp-ultimo'),
				'placeholder'     => __('Start Transfer', 'wp-ultimo'),
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => array(
					'v-bind:disabled' => '!confirmed',
				),
			),
			'id'                   => array(
				'type'  => 'hidden',
				'value' => $site->get_id(),
			),
			'target_membership_id' => array(
				'type'  => 'hidden',
				'value' => wu_request('target_membership_id'),
			),
		);

		$form = new \WP_Ultimo\UI\Form('total-actions', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'transfer_site',
				'data-state'  => json_encode(array(
					'confirmed' => false,
				)),
			),
		));

		$form->render();

	} // end render_transfer_site_modal;

	/**
	 * Handles the transfer of site.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_transfer_site_modal() {
		global $wpdb;

		$site              = wu_get_site(wu_request('id'));
		$target_membership = wu_get_membership(wu_request('target_membership_id'));

		if (!$site) {

			wp_send_json_error(new \WP_Error('not-found', __('Site not found.', 'wp-ultimo')));

		} // end if;

		if (!$target_membership) {

			wp_send_json_error(new \WP_Error('not-found', __('Membership not found.', 'wp-ultimo')));

		} // end if;

		$site->set_membership_id($target_membership->get_id());

		$site->set_customer_id($target_membership->get_customer_id());

		$saved = $site->save();

		if (is_wp_error($saved)) {

			wp_send_json_error($saved);

		} // end if;

		wp_send_json_success(array(
			'redirect_url' => wu_network_admin_url('wp-ultimo-edit-site', array(
				'id' => $site->get_id(),
			))
		));

	} // end handle_transfer_site_modal;

	/**
	 * Allow child classes to register widgets, if they need them.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_widgets() {

		parent::register_widgets();

		$label = $this->get_object()->get_type_label();

		$class = $this->get_object()->get_type_class();

		$tag = "<span class='wu-bg-gray-200 wu-py-1 wu-px-2 wu-rounded-sm wu-text-xs wu-font-mono $class'>{$label}</span>";

		$this->add_fields_widget('at_a_glance', array(
			'title'                 => __('At a Glance', 'wp-ultimo'),
			'position'              => 'normal',
			'classes'               => 'wu-overflow-hidden wu-m-0 wu--mt-1 wu--mx-3 wu--mb-3',
			'field_wrapper_classes' => 'wu-w-1/4 wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t-0 wu-border-l-0 wu-border-r wu-border-b-0 wu-border-gray-300 wu-border-solid wu-float-left wu-relative',
			'html_attr'             => array(
				'style' => 'margin-top: -6px;',
			),
			'fields'                => array(
				'type'            => array(
					'type'          => 'text-display',
					'title'         => __('Site Type', 'wp-ultimo'),
					'display_value' => $tag,
					'tooltip'       => '',
				),
				'id'              => array(
					'type'          => 'text-display',
					'title'         => __('Site ID', 'wp-ultimo'),
					'display_value' => $this->get_object()->get_id(),
					'tooltip'       => '',
				),
				'total_grossed'   => array(
					'type'          => 'text-display',
					'title'         => __('Membership', 'wp-ultimo'),
					'display_value' => $this->get_object()->get_membership()
						? sprintf('<a href="%s" class="wu-no-underline">' . $this->get_object()->get_membership()->get_hash() . ' &rarr;</a>', wu_network_admin_url(
							'wp-ultimo-edit-membership',
							array(
								'id' => $this->get_object()->get_membership_id()
							)
						))
						: '--',
					'tooltip'       => '',
				),
				'date_expiration' => array(
					'type'          => 'text-display',
					'title'         => __('Customer', 'wp-ultimo'),
					'display_value' => $this->get_object()->get_customer()
						? sprintf('<a href="%s" class="wu-no-underline">' . $this->get_object()->get_customer()->get_display_name() . ' &rarr;</a>', wu_network_admin_url(
							'wp-ultimo-edit-customer',
							array(
								'id' => $this->get_object()->get_customer_id()
							)
						))
						: '--',
				),
			),
		));

		$this->add_fields_widget('description', array(
			'title'    => __('Description', 'wp-ultimo'),
			'position' => 'normal',
			'fields'   => array(
				'description' => array(
					'type'        => 'textarea',
					'title'       => __('Site Description', 'wp-ultimo'),
					'placeholder' => __('Tell your customers what this site is about.', 'wp-ultimo'),
					'value'       => $this->get_object()->get_option_blogdescription(),
					'html_attr'   => array(
						'rows' => 3,
					),
				),
			),
		));

		$this->add_tabs_widget('options', array(
			'title'    => __('Site Options', 'wp-ultimo'),
			'position' => 'normal',
			'sections' => $this->get_site_option_sections(),
		));

		$this->add_list_table_widget('domains', array(
			'title'        => __('Mapped Domains', 'wp-ultimo'),
			'table'        => new \WP_Ultimo\List_Tables\Sites_Domain_List_Table(),
			'query_filter' => array($this, 'domain_query_filter'),
		));

		$this->add_list_table_widget('events', array(
			'title'        => __('Events', 'wp-ultimo'),
			'table'        => new \WP_Ultimo\List_Tables\Inside_Events_List_Table(),
			'query_filter' => array($this, 'query_filter'),
		));

		$membership_selected = $this->get_object()->get_membership() ? $this->get_object()->get_membership()->to_search_results() : '';
		$template_selected   = $this->get_object()->get_template() ? $this->get_object()->get_template()->to_search_results() : '';

		$this->add_fields_widget('save', array(
			'html_attr' => array(
				'data-wu-app' => 'site_type',
				'data-state'  => json_encode(array(
					'type'                   => $this->get_object()->get_type(),
					'original_membership_id' => $this->get_object()->get_membership_id(),
					'membership_id'          => $this->get_object()->get_membership_id(),
				)),
			),
			'fields'    => array(
				// Fields for price
				'type'          => array(
					'type'              => 'select',
					'title'             => __('Site Type', 'wp-ultimo'),
					'placeholder'       => __('Select Site Type', 'wp-ultimo'),
					'value'             => $this->get_object()->get_type(),
					'tooltip'           => '',
					'options'           => array(
						'default'        => __('Regular WordPress', 'wp-ultimo'),
						'site_template'  => __('Site Template', 'wp-ultimo'),
						'customer_owned' => __('Customer-owned', 'wp-ultimo'),
						'main'           => __('Main Site', 'wp-ultimo'),
					),
					'html_attr'         => array(
						'v-model'         => 'type',
						'disabled'        => $this->get_object()->get_type() === 'main',
						'v-bind:disabled' => 'type === "main"',
					),
					'wrapper_html_attr' => array(
						'v-cloak' => '1',
					),
				),
				'categories'    => array(
					'type'              => 'select',
					'title'             => __('Template Categories', 'wp-ultimo'),
					'placeholder'       => __('e.g.: Landing Page, Health...', 'wp-ultimo'),
					'tooltip'           => __('Customers will be able to filter by categories during signup.', 'wp-ultimo'),
					'value'             => $this->get_object()->get_categories(),
					'options'           => Site::get_all_categories(),
					'html_attr'         => array(
						'data-selectize-categories' => 1,
						'multiple'                  => 1,
					),
					'wrapper_html_attr' => array(
						'v-show'  => "type === 'site_template'",
						'v-cloak' => '1',
					),
				),
				'membership_id' => array(
					'type'              => 'model',
					'title'             => __('Associated Membership', 'wp-ultimo'),
					'placeholder'       => __('Membership', 'wp-ultimo'),
					'value'             => $this->get_object()->get_membership_id(),
					'tooltip'           => '',
					'wrapper_html_attr' => array(
						'v-show'  => "type === 'customer_owned'",
						'v-cloak' => 1,
					),
					'html_attr'         => array(
						'data-model'        => 'membership',
						'data-value-field'  => 'id',
						'data-label-field'  => 'reference_code',
						'data-search-field' => 'reference_code',
						'data-max-items'    => 1,
						'data-selected'     => json_encode($membership_selected),
					),
				),
				'transfer_note' => array(
					'type'              => 'note',
					'desc'              => __('Changing the membership will transfer the site and all its assets to the new membership.', 'wp-ultimo'),
					'classes'           => 'wu-p-2 wu-bg-red-100 wu-text-red-600 wu-rounded wu-w-full',
					'wrapper_html_attr' => array(
						'v-show'  => '(original_membership_id != membership_id) && membership_id',
						'v-cloak' => '1',
					),
				),
				'submit_save'   => array(
					'type'              => 'submit',
					'title'             => __('Save Site', 'wp-ultimo'),
					'placeholder'       => __('Save Site', 'wp-ultimo'),
					'value'             => 'save',
					'classes'           => 'button button-primary wu-w-full',
					'wrapper_html_attr' => array(
						'v-show'  => 'original_membership_id == membership_id || !membership_id',
						'v-cloak' => 1,
					),
				),
				'transfer'      => array(
					'type'              => 'link',
					'display_value'     => __('Transfer Site', 'wp-ultimo'),
					'wrapper_classes'   => 'wu-bg-gray-200',
					'classes'           => 'button wubox wu-w-full wu-text-center',
					'wrapper_html_attr' => array(
						'v-show'  => 'original_membership_id != membership_id && membership_id',
						'v-cloak' => '1',
					),
					'html_attr'         => array(
						'v-bind:href' => "'" . wu_get_form_url('transfer_site', array(
							'id'                   => $this->get_object()->get_id(),
							'target_membership_id' => '',
						)) . "=' + membership_id",
						'title'       => __('Transfer Site', 'wp-ultimo'),
					),
				),
			),
		));

		$this->add_fields_widget('public', array(
			'title'  => __('Is Active?', 'wp-ultimo'),
			'fields' => array(
				'public' => array(
					'type'  => 'toggle',
					'title' => __('Is Active?', 'wp-ultimo'),
					'desc'  => __('Deactivate this site.', 'wp-ultimo'),
					'value' => $this->get_object()->get_public(),
				),
			),
		));

		$this->add_fields_widget('image', array(
			'title'  => __('Site Image', 'wp-ultimo'),
			'fields' => array(
				'featured_image_id' => array(
					'type'  => 'image',
					'title' => __('Set Site Image', 'wp-ultimo'),
					'value' => $this->get_object()->get_featured_image_id(),
					'img'   => $this->get_object()->get_featured_image(),
				),
				'scraper_note'      => array(
					'type'            => 'note',
					'desc'            => __('You need to save the site for the change to take effect.', 'wp-ultimo'),
					'wrapper_classes' => 'wu-hidden wu-scraper-note',
				),
				'scraper_error'     => array(
					'type'            => 'note',
					'desc'            => '<span class="wu-scraper-error-message wu-p-2 wu-bg-red-100 wu-text-red-600 wu-rounded wu-block"></span>',
					'wrapper_classes' => 'wu-hidden wu-scraper-error',
				),
				'scraper'           => array(
					'type'    => 'submit',
					'title'   => __('Take Screenshot', 'wp-ultimo'),
					'classes' => 'button wu-w-full',
				),
			),
		));

	} // end register_widgets;

	/**
	 * Returns the list of sections and its fields for the site page.
	 *
	 * Can be filtered via 'wu_site_options_sections'.
	 *
	 * @see inc/managers/class-limitation-manager.php
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function get_site_option_sections() {

		$reset_url = wu_get_form_url('confirm_limitations_reset', array(
			'id'    => $this->get_object()->get_id(),
			'model' => 'site',
		));

		$sections = array(
			'general' => array(
				'title'  => __('General', 'wp-ultimo'),
				'desc'   => __('General site options.', 'wp-ultimo'),
				'icon'   => 'dashicons-wu-globe',
				'fields' => array(
					'reset_permissions' => array(
						'type'  => 'note',
						'title' => sprintf("%s<span class='wu-normal-case wu-block wu-text-xs wu-font-normal wu-mt-1'>%s</span>", __('Reset Limitations', 'wp-ultimo'), __('Use this option to remove the custom limitations applied to this object.', 'wp-ultimo')),
						'desc'  => sprintf('<a href="%s" title="%s" class="wubox button-primary">%s</a>', $reset_url, __('Reset Limitations', 'wp-ultimo'), __('Reset Limitations', 'wp-ultimo')),
					)
				),
			),
		);

		$sections = apply_filters('wu_site_options_sections', $sections, $this->get_object());

		$sections['custom_fields'] = array(
			'title'  => __('Extra Information', 'wp-ultimo'),
			'desc'   => __('Add extra information to this site.', 'wp-ultimo'),
			'icon'   => 'dashicons-wu-new-message',
			'fields' => array(
				'extra_information' => array(
					'type'   => 'repeater',
					'fields' => array(
						'field_name'  => array(
							'title'           => __('Name', 'wp-ultimo'),
							'type'            => 'text',
							'value'           => '',
							'wrapper_classes' => 'wu-w-1/2',
						),
						'field_value' => array(
							'title'           => __('Value', 'wp-ultimo'),
							'type'            => 'text',
							'value'           => '',
							'wrapper_classes' => 'wu-w-1/2 wu-ml-4',
						),
					),
					'values' => $this->get_object()->get_extra_information()
				),
			)
		);

		return $sections;

	} // end get_site_option_sections;

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return $this->edit ? __('Edit Site', 'wp-ultimo') : __('Add new Site', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Edit Site', 'wp-ultimo');

	} // end get_menu_title;

	/**
	 * Returns the action links for that page.
	 *
	 * @since 1.8.2
	 * @return array
	 */
	public function action_links() {

		return array(
			array(
				'url'   => network_admin_url('site-settings.php?id=' . $this->get_object()->get_id()),
				'label' => __('Go to the Default Edit Screen', 'wp-ultimo'),
				'icon'  => 'wu-cog',
			),
			array(
				'url'   => get_site_url($this->get_object()->get_id()),
				'label' => __('Visit Site', 'wp-ultimo'),
				'icon'  => 'wu-link',
			),
			array(
				'url'   => get_admin_url($this->get_object()->get_id()),
				'label' => __('Dashboard', 'wp-ultimo'),
				'icon'  => 'dashboard',
			),
		);

	} // end action_links;

	/**
	 * Returns the labels to be used on the admin page.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_labels() {

		return array(
			'edit_label'          => __('Edit Site', 'wp-ultimo'),
			'add_new_label'       => __('Add new Site', 'wp-ultimo'),
			'updated_message'     => __('Site updated with success!', 'wp-ultimo'),
			'title_placeholder'   => __('Enter Site Name', 'wp-ultimo'),
			'title_description'   => __('This name will be used as the site title.', 'wp-ultimo'),
			'save_button_label'   => __('Save Site', 'wp-ultimo'),
			'save_description'    => '',
			'delete_button_label' => __('Delete Site', 'wp-ultimo'),
			'delete_description'  => __('Be careful. This action is irreversible.', 'wp-ultimo'),
		);

	} // end get_labels;

	/**
	 * Filters the list table to return only relevant events.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Query args passed to the list table.
	 * @return array Modified query args.
	 */
	public function domain_query_filter($args) {

		$extra_args = array(
			'blog_id' => abs($this->get_object()->get_id()),
		);

		return array_merge($args, $extra_args);

	} // end domain_query_filter;

	/**
	 * Filters the list table to return only relevant events.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Query args passed to the list table.
	 * @return array Modified query args.
	 */
	public function query_filter($args) {

		$extra_args = array(
			'object_type' => 'site',
			'object_id'   => abs($this->get_object()->get_id()),
		);

		return array_merge($args, $extra_args);

	} // end query_filter;

	/**
	 * Returns the object being edit at the moment.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Site
	 */
	public function get_object() {

		if ($this->object !== null) {

			return $this->object;

		} // end if;

		$item_id = wu_request('id', 0);

		$item = wu_get_site($item_id);

		if (!$item) {

			wp_redirect(wu_network_admin_url('wp-ultimo-sites'));

			exit;

		} // end if;

		$this->object = $item;

		return $this->object;

	} // end get_object;

	/**
	 * Sites have titles.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_title() {

		return true;

	} // end has_title;

} // end class Site_Edit_Admin_Page;
