<?php
/**
 * WP Ultimo Broadcast Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo Broadcast Admin Page.
 */
class Email_List_Admin_Page extends List_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-emails';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $type = 'submenu';

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
	protected $highlight_menu_slug = 'wp-ultimo-broadcasts';

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
		'network_admin_menu' => 'wu_read_emails',
	);

	/**
	 * Initializes the class
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function init() {

		/**
		 * Runs the parent init functions
		 */
		parent::init();

		add_action('wu_page_list_redirect_handlers', array($this, 'handle_page_redirect'), 10);

	} // end init;

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

		return __('System Emails', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('System Emails', 'wp-ultimo');

	} // end get_menu_title;

	/**
	 * Allows admins to rename the sub-menu (first item) for a top-level page.
	 *
	 * @since 2.0.0
	 * @return string False to use the title menu or string with sub-menu title.
	 */
	public function get_submenu_title() {

		return __('System Emails', 'wp-ultimo');

	} // end get_submenu_title;

	/**
	 * Register ajax form that we use for system emails.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms() {
		/*
		 * Send a email test
		 */
		wu_register_form('send_new_test', array(
			'render'     => array($this, 'render_send_new_test_modal'),
			'handler'    => array($this, 'handle_send_new_test_modal'),
			'capability' => 'wu_add_broadcast',
		));

		/*
		 * Reset or Import modal.
		 */
		wu_register_form('reset_import', array(
			'render'     => array($this, 'render_reset_import_modal'),
			'handler'    => array($this, 'handle_reset_import_modal'),
			'capability' => 'wu_add_broadcasts',
		));

		/*
		 * Reset Confirmation modal.
		 */
		wu_register_form('reset_confirmation', array(
			'render'     => array($this, 'render_reset_confirmation_modal'),
			'handler'    => array($this, 'handle_reset_confirmation_modal'),
			'capability' => 'wu_add_broadcasts',
		));

	} // end register_forms;

	/**
	 * Renders the modal to send tests with system emails.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_send_new_test_modal() {

		$fields = array(
			'send_to'       => array(
				'type'        => 'email',
				'title'       => __('Send To', 'wp-ultimo'),
				'placeholder' => __('E.g. network@email.com', 'wp-ultimo'),
				'desc'        => __('The test email will be sent to the above email address.', 'wp-ultimo'),
				'value'       => get_network_option(null, 'admin_email'),
				'html_attr'   => array(
					'required' => 'required',
				)
			),
			'email_id'      => array(
				'type'  => 'hidden',
				'value' => wu_request('id'),
			),
			'page'      => array(
				'type'  => 'hidden',
				'value' => wu_request('page'),
			),
			'submit_button' => array(
				'type'            => 'submit',
				'title'           => __('Send Test Email', 'wp-ultimo'),
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end wu-text-right',
			),
		);

		$form = new \WP_Ultimo\UI\Form('send_new_test', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'send_new_test',
			),
		));

		$form->render();

	} // end render_send_new_test_modal;

	/**
	 * Handles the modal to send tests with system emails.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function handle_send_new_test_modal() {

		$email_id = wu_request('email_id');

		$send_to = wu_request('send_to');

		if (!$email_id || !$send_to) {

			$error = new \WP_Error('error', __('Something wrong happened.', 'wp-ultimo'));

			return wp_send_json_error($error);

		} // end if;

		$from = array(
			'name'  => wu_get_setting('from_name'),
			'email' => wu_get_setting('from_email'),
		);

		$to = array(
			array(
				'name'  => wu_get_setting('from_name'),
				'email' => $send_to,
			)
		);

		$email = wu_get_email($email_id);

		$event_slug = $email->get_event();

		$event_type = wu_get_event_type($event_slug);

		$payload = array();

		if ($event_type) {

			$payload = wu_maybe_lazy_load_payload($event_type['payload']);

		} // end if;

		$args = array(
			'style'   => $email->get_style(),
			'content' => $email->get_content(),
			'subject' => get_network_option(null, 'site_name') . ' - ' . $email->get_title(),
			'payload' => $payload,
		);

		$send_mail = wu_send_mail($from, $to, $args);

		if (!$send_mail) {

			$error = new \WP_Error('error', __('Something wrong happened with your test.', 'wp-ultimo'));

			return wp_send_json_error($error);

		} // end if;

		$page = wu_request('page', 'list');

		if ($page === 'edit') {

			wp_send_json_success(array(
				'redirect_url' => wu_network_admin_url('wp-ultimo-edit-email', array(
					'id'          => $email_id,
					'test_notice' => __('Test sent successfully', 'wp-ultimo')
				))
			));

			die();

		} // end if;

		wp_send_json_success(array(
			'redirect_url' => wu_network_admin_url('wp-ultimo-emails', array(
				'notice' => __('Test sent successfully', 'wp-ultimo'),
			))
		));

	} // end handle_send_new_test_modal;

	/**
	 * Renders the modal to reset or import system emails.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function render_reset_import_modal() {

		$default_system_emails = wu_get_default_system_emails();

		$created_emails = wu_get_all_system_emails();

		$fields = array(
			'reset_emails'         => array(
				'type'      => 'toggle',
				'title'     => __('Reset System Emails ', 'wp-ultimo'),
				'desc'      => __('Restore the system emails to their original content.'),
				'tooltip'   => '',
				'value'     => 0,
				'html_attr' => array(
					'v-model' => 'reset_emails',
				),
			)
		);

		$fields['reset_note'] = array(
			'type'              => 'note',
			'title'             => '',
			'desc'              => __('No emails to reset.', 'wp-ultimo'),
			'tooltip'           => '',
			'value'             => 0,
			'wrapper_html_attr' => array(
				'v-show'  => 'reset_emails',
				'v-cloak' => 1,
			),
		);

		foreach ($created_emails as $system_email_key => $system_email_value) {

			$system_email_slug = $system_email_value->get_slug();

			if (isset($default_system_emails[$system_email_slug])) {

				$field_name = 'reset_' . $system_email_value->get_slug();

				$system_email_target = $system_email_value->get_target();

				$field_title = '<div><strong class="wu-inline-block wu-pr-1">' . $system_email_value->get_title() . '</strong></div>';

				$fields[$field_name] = array(
					'type'              => 'toggle',
					'title'             => $field_title,
					'desc'              => $system_email_value->get_event() . ' <span class="wu-bg-gray-200 wu-text-gray-700 wu-py-1 wu-px-2 wu-rounded-sm wu-text-xs wu-font-mono">' . $system_email_target . '</span>',
					'tooltip'           => '',
					'value'             => 0,
					'wrapper_classes'   => 'wu-bg-gray-100',
					'wrapper_html_attr' => array(
						'v-show'  => 'reset_emails',
						'v-cloak' => 1,
					),
				);

				if (isset($fields['reset_note'])) {

					unset($fields['reset_note']);

				} // end if;

			} // end if;

		} // end foreach;

		$fields['import_emails'] = array(
			'type'    => 'toggle',
			'title'   => __('Import System Emails', 'wp-ultimo'),
			'desc'    => __('Add new system emails based on WP Ultimo presets.'),
			'tooltip' => '',
			'value'   => 0,
			'html_attr' => array(
				'v-model' => 'import_emails',
			),
		);

		$fields['import_note'] = array(
			'type'              => 'note',
			'title'             => '',
			'desc'              => __('All emails are already present.', 'wp-ultimo'),
			'tooltip'           => '',
			'value'             => 0,
			'wrapper_html_attr' => array(
				'v-show'  => 'import_emails',
				'v-cloak' => 1,
			),
		);

		foreach ($default_system_emails as $default_email_key => $default_email_value) {

			$maybe_is_created = wu_get_email_by('slug', $default_email_key);

			if (!$maybe_is_created) {

				$field_name = 'import_' . $default_email_key;

				$field_title = '<div><strong class="wu-inline-block wu-pr-1">' . $default_email_value['title'] . '</strong> </div>';

				$fields[$field_name] = array(
					'type'              => 'toggle',
					'title'             => $field_title,
					'desc'              => $default_email_value['event'] . ' <span class="wu-bg-gray-200 wu-text-gray-700 wu-py-1 wu-px-2 wu-rounded-sm wu-text-xs wu-font-mono">' . $default_email_value['target'] . '</span>',
					'tooltip'           => '',
					'value'             => 0,
					'wrapper_classes'   => 'wu-bg-gray-100',
					'wrapper_html_attr' => array(
						'v-show'  => 'import_emails',
						'v-cloak' => 1,
					),
				);

				if (isset($fields['import_note'])) {

					unset($fields['import_note']);

				} // end if;

			} // end if;

 		} // end foreach;

		$fields['submit_button'] = array(
			'type'            => 'submit',
			'title'           => __('Reset and/or Import', 'wp-ultimo'),
			'value'           => 'save',
			'classes'         => 'button button-primary wu-w-full',
			'wrapper_classes' => 'wu-items-end wu-text-right',
		);

		$form = new \WP_Ultimo\UI\Form('reset_import', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'reset_import',
				'data-state'  => json_encode(array(
					'reset_emails'  => false,
					'import_emails' => false
				)),
			),
		));

		$form->render();

	} // end render_reset_import_modal;

	/**
	 * Handles the modal to reset or import system emails.
	 *
	 * @since 2.0.0
	 *
	 * @return mixed
	 */
	public function handle_reset_import_modal() {

		$reset = wu_request('reset_emails');

		$import = wu_request('import_emails');

		$default_system_emails = wu_get_default_system_emails();

		$created_emails = wu_get_all_system_emails();

		if ($reset) {

			foreach ($created_emails as $created_email) {

				$slug = $created_email->get_slug();

				$maybe_reset = wu_request('reset_' . $slug, '');

				if ($maybe_reset) {

					$created_email->delete();

					wu_create_default_system_email($slug);

				} // end if;

			} // end foreach;

		} // end if;

		if ($import) {

			foreach ($default_system_emails as $default_system_emails_key => $default_system_emails_value) {

				$slug = $default_system_emails_value['slug'];

				$maybe_import = wu_request('import_' . $slug, '');

				if ($maybe_import) {

					wu_create_default_system_email($slug);

				} // end if;

			} // end foreach;

		} // end if;

		wp_send_json_success(array(
			'redirect_url' => wu_network_admin_url('wp-ultimo-emails')
		));

	} // end handle_reset_import_modal;

	/**
	 * Handles the redirect notice from sent new test modal.
	 *
	 * @param WP_Ultimo\Admin_Pages\Base_Admin_Page $page The page object.
	 * @return void
	 */
	public function handle_page_redirect($page) {

		if ($page->get_id() === 'wp-ultimo-emails') {

			if (wu_request('notice')) {

				$notice = wu_request('notice');

				?>

				<div id="message" class="updated notice notice-success is-dismissible below-h2">

					<p><?php echo esc_html($notice); ?></p>

				</div>

				<?php

			} // end if;

		} // end if;

	} // end handle_page_redirect;

	/**
	 * Renders the reset confirmation modal.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_reset_confirmation_modal() {

		$fields = array(
			'single_reset'  => array(
				'type'      => 'toggle',
				'title'     => __('Confirm Reset', 'wp-ultimo'),
				'desc'      => __('This action can not be undone.', 'wp-ultimo'),
				'default'   => 0,
				'html_attr' => array(
					'required' => 'required',
				)
			),
			'email_id'      => array(
				'type'  => 'hidden',
				'value' => wu_request('id'),
			),
			'submit_button' => array(
				'type'            => 'submit',
				'title'           => __('Reset Email', 'wp-ultimo'),
				'value'           => 'reset',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end wu-text-right',
			),
		);

		$form = new \WP_Ultimo\UI\Form('reset_confirmation', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'reset_confirmation',
			),
		));

		$form->render();

	} // end render_reset_confirmation_modal;

	/**
	 * Handles the reset confirmation modal.
	 *
	 * @since 2.0.0
	 *
	 * @return mixed
	 */
	public function handle_reset_confirmation_modal() {

		$single_reset = wu_request('single_reset');

		$email_id = wu_request('email_id');

		if (!$single_reset || !$email_id) {

			$error = new \WP_Error('error', __('Something wrong happened.', 'wp-ultimo'));

			return wp_send_json_error($error);

		} // end if;

		$email = wu_get_email($email_id);

		$slug = $email->get_slug();

		$default_system_emails = wu_get_default_system_emails();

		if (isset($default_system_emails[$slug])) {

			$email->delete();

			wu_create_default_system_email($slug);

			$new_email = wu_get_email_by('slug', $slug);

			if (!$new_email) {

				$error = new \WP_Error('error', __('Something wrong happened.', 'wp-ultimo'));

				return wp_send_json_error($error);

			} // end if;

			wp_send_json_success(array(
				'redirect_url' => wu_network_admin_url('wp-ultimo-edit-email', array(
					'id' => $new_email->get_id(),
				))
			));

		} // end if;

	}  // end handle_reset_confirmation_modal;

	/**
	 * Returns the action links for that page.
	 *
	 * @since 1.8.2
	 * @return array
	 */
	public function action_links() {

		$email_template_default = get_network_option(null, 'wu_default_email_template');

		return array(
			array(
				'url'   => wu_network_admin_url('wp-ultimo-edit-email'),
				'label' => __('Add System Email'),
				'icon'  => 'wu-circle-with-plus',
			),
			array(
				'url'   => wu_network_admin_url('wp-ultimo-customize-email-template&id=' . $email_template_default),
				'label' => __('Email Template'),
				'icon'  => 'wu-mail',
			),
			array(
				'url'     => wu_get_form_url('reset_import'),
				'classes' => 'wubox',
				'label'   => __('Reset or Import'),
				'icon'    => 'wu-cycle',
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

		return new \WP_Ultimo\List_Tables\Email_List_Table();

	} // end table;

} // end class Email_List_Admin_Page;
