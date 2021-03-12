<?php
/**
 * WP Ultimo Domain Edit/Add New Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Models\Domain;
use \WP_Ultimo\Managers\Domain_Manager;
use \WP_Ultimo\Database\Domains\Domain_Stage;

/**
 * WP Ultimo Domain Edit/Add New Admin Page.
 */
class Domain_Edit_Admin_Page extends Edit_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-edit-domain';

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
	public $object_id = 'domain';

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
	protected $highlight_menu_slug = 'wp-ultimo-domains';

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
		'network_admin_menu' => 'wu_edit_domains',
	);

	/**
	 * Register ajax forms.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms() {
		/*
		 * Adds the hooks to handle deletion.
		 */
		add_filter('wu_form_fields_delete_domain_modal', array($this, 'domain_extra_delete_fields'), 10, 2);

		add_action('wu_after_delete_domain_modal', array($this, 'domain_after_delete_actions'));

	} // end register_forms;

	/**
	 * Adds the extra delete fields to the delete form.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $fields The original fields.
	 * @param object $domain The domain object.
	 * @return array
	 */
	public function domain_extra_delete_fields($fields, $domain) {

		$is_primary_domain = $domain->is_primary_domain();

		$has_other_domains = false;

		if ($is_primary_domain) {

			$other_domains = \WP_Ultimo\Models\Domain::get_by_site($domain->get_blog_id());

			$has_other_domains = is_countable($other_domains) ? count($other_domains) - 1 : false;

		} // end if;

		$custom_fields = array(
			'set_domain_as_primary' => array(
				'type'              => 'model',
				'title'             => __('Set another domain as primary', 'wp-ultimo'),
				'html_attr'         => array(
					'data-model'        => 'domain',
					'data-value-field'  => 'id',
					'data-label-field'  => 'domain',
					'data-search-field' => 'domain',
					'data-max-items'    => 1,
					'data-exclude'      => json_encode(array($domain->get_id())),
					'data-include'      => json_encode(array('blog_id__in' => array($domain->get_blog_id())))
				),
				'wrapper_html_attr' => array(
					'v-if' => $is_primary_domain && $has_other_domains ? 'true' : 'false',
				),
			),
			'confirm'               => array(
				'type'      => 'toggle',
				'title'     => __('Confirm Deletion', 'wp-ultimo'),
				'desc'      => __('This action can not be undone.', 'wp-ultimo'),
				'html_attr' => array(
					'v-model' => 'confirmed',
				),
			),
			'submit_button'         => array(
				'type'            => 'submit',
				'title'           => __('Delete', 'wp-ultimo'),
				'placeholder'     => __('Delete', 'wp-ultimo'),
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => array(
					'v-bind:disabled' => '!confirmed',
				),
			),
			'id'                    => array(
				'type'  => 'hidden',
				'value' => $domain->get_id(),
			),
		);

		return array_merge($custom_fields, $fields);

	} // end domain_extra_delete_fields;

	/**
	 * Adds the primary domain handling to the domain deletion.
	 *
	 * @since 2.0.0
	 *
	 * @param object $domain The domain object.
	 * @return void
	 */
	public function domain_after_delete_actions($domain) {

		$new_primary_domain_name = wu_request('set_domain_as_primary');

		$new_primary_domain = wu_get_domain($new_primary_domain_name);

		if ($new_primary_domain) {

			$new_primary_domain->set_primary_domain(true);

			$new_primary_domain->save();

		} // end if;

	} // end domain_after_delete_actions;

	/**
	 * Allow child classes to register widgets, if they need them.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_widgets() {

		parent::register_widgets();

		$this->add_fields_widget('domain-url', array(
			'title'    => __('Domain URL', 'wp-ultimo'),
			'position' => 'normal',
			'after'    => array($this, 'render_dns_widget'),
			'fields'   => array(
				'domain' => array(
					'type'          => 'text-display',
					'title'         => __('Domain', 'wp-ultimo'),
					'tooltip'       => __('Editing an existing domain is not possible. If you want to make changes to this domain, first delete it, and then re-add the right domain.', 'wp-ultimo'),
					'display_value' => '<span class="wu-text-sm wu-uppercase wu-font-mono">' . $this->get_object()->get_domain() . '</span>',
				),
			),
		));

		$this->add_tabs_widget('options', array(
			'title'    => __('Domain Options', 'wp-ultimo'),
			'position' => 'normal',
			'sections' => array(
				'general' => array(
					'title'  => __('General', 'wp-ultimo'),
					'desc'   => __('General options for the domain.', 'wp-ultimo'),
					'icon'   => 'dashicons-wu-globe',
					'state'  => array(
						'primary_domain' => $this->get_object()->is_primary_domain(),
					),
					'fields' => array(
						'primary_domain' => array(
							'type'      => 'toggle',
							'title'     => __('Is Primary Domain?', 'wp-ultimo'),
							'desc'      => __('Set as the primary domain.', 'wp-ultimo'),
							'tooltip'   => __('Setting this as the primary domain will remove any other domain mapping marked as the primary domain for this site.', 'wp-ultimo'),
							'value'     => $this->get_object()->is_primary_domain(),
							'html_attr' => array(
								'v-model' => 'primary_domain',
							),
						),
						'primary_note'   => array(
							'type'              => 'note',
							'desc'              => __('By making this the primary domain, we will convert the previous primary domain for this site, if one exists, into an alias domain.', 'wp-ultimo'),
							'wrapper_html_attr' => array(
								'v-if' => "require('primary_domain', true)",
							),
						),
						'secure'         => array(
							'type'  => 'toggle',
							'title' => __('Is Secure?', 'wp-ultimo'),
							'desc'  => __('Force the load using HTTPS.', 'wp-ultimo'),
							'value' => $this->get_object()->is_secure(),
						),
					),
				),
			),
		));

		add_meta_box('wp-ultimo-domain-log', __('Domain Test Log', 'wp-ultimo'), array($this, 'render_log_widget'), get_current_screen()->id, 'normal', null);

		$this->add_list_table_widget('events', array(
			'title'        => __('Events', 'wp-ultimo'),
			'table'        => new \WP_Ultimo\List_Tables\Inside_Events_List_Table(),
			'query_filter' => array($this, 'query_filter'),
		));

		$this->add_save_widget('save', array(
			'html_attr' => array(
				'data-wu-app' => 'save',
				'data-state'  => wu_convert_to_state(),
			),
			'fields'    => array(
				'stage'   => array(
					'type'              => 'select',
					'title'             => __('Stage', 'wp-ultimo'),
					'placeholder'       => __('Select Stage', 'wp-ultimo'),
					'options'           => Domain_Stage::to_array(),
					'value'             => $this->get_object()->get_stage(),
					'tooltip'           => __('Name of the service responsible for creating this webhook. If you are manually creating this webhook, use the value "manual".', 'wp-ultimo'),
					'wrapper_html_attr' => array(
						'v-cloak' => '1',
					),
				),
				'blog_id' => array(
					'type'              => 'model',
					'title'             => __('Site', 'wp-ultimo'),
					'placeholder'       => __('Site', 'wp-ultimo'),
					'value'             => $this->get_object()->get_blog_id(),
					'tooltip'           => '',
					'html_attr'         => array(
						'data-model'        => 'site',
						'data-value-field'  => 'blog_id',
						'data-label-field'  => 'title',
						'data-search-field' => 'title',
						'data-max-items'    => 1,
						'data-selected'     => $this->get_object()->get_site() ? json_encode($this->get_object()->get_site()->to_search_results()) : '',
					),
					'wrapper_html_attr' => array(
						'v-cloak' => '1',
					),
				),
			),
		));

		$this->add_fields_widget('basic', array(
			'title'  => __('Active', 'wp-ultimo'),
			'fields' => array(
				'active' => array(
					'type'  => 'toggle',
					'title' => __('Is Active?', 'wp-ultimo'),
					'desc'  => __('Deactivate this domain.', 'wp-ultimo'),
					'value' => $this->get_object()->is_active(),
				),
			),
		));
	} // end register_widgets;

	/**
	 * Renders the DNS widget
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_dns_widget() {

		wu_get_template('domain/dns-table', array(
			'domain' => $this->get_object(),
		));

	} // end render_dns_widget;

	/**
	 * Renders the DNS widget
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_log_widget() {

		wu_get_template('domain/log', array(
			'domain'   => $this->get_object(),
			'log_path' => \WP_Ultimo\Logger::get_logs_folder(),
		));

	} // end render_log_widget;

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return $this->edit ? __('Edit Domain', 'wp-ultimo') : __('Add new Domain', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Edit Domain', 'wp-ultimo');

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
			'edit_label'          => __('Edit Domain', 'wp-ultimo'),
			'add_new_label'       => __('Add new Domain', 'wp-ultimo'),
			'updated_message'     => __('Domain updated with success!', 'wp-ultimo'),
			'title_placeholder'   => __('Enter Domain', 'wp-ultimo'),
			'title_description'   => '',
			'save_button_label'   => __('Save Domain', 'wp-ultimo'),
			'save_description'    => '',
			'delete_button_label' => __('Delete Domain', 'wp-ultimo'),
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
	public function query_filter($args) {

		$extra_args = array(
			'object_type' => 'domain',
			'object_id'   => abs($this->get_object()->get_id()),
		);

		return array_merge($args, $extra_args);

	} // end query_filter;

	/**
	 * Returns the object being edit at the moment.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Domain
	 */
	public function get_object() {

		if ($this->object !== null) {

			return $this->object;

		} // end if;

		$item_id = wu_request('id', 0);

		$item = wu_get_domain($item_id);

		if (!$item) {

			wp_redirect(wu_network_admin_url('wp-ultimo-domains'));

			exit;

		} // end if;

		$this->object = $item;

		return $this->object;

	} // end get_object;

	/**
	 * Domains have titles.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_title() {

		return false;

	} // end has_title;

	/**
	 * Should implement the processes necessary to save the changes made to the object.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_save() {

		wu_enqueue_async_action('wu_async_process_domain_stage', array('domain_id' => $this->get_object()->get_id()), 'domain');

		parent::handle_save();

	} // end handle_save;

} // end class Domain_Edit_Admin_Page;
