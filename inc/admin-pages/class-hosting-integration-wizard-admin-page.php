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

/**
 * WP Ultimo Dashboard Admin Page.
 */
class Hosting_Integration_Wizard_Admin_Page extends Wizard_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-hosting-integration-wizard';

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
	protected $highlight_menu_slug = 'wp-ultimo-settings';

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
		'network_admin_menu' => 'manage_network',
	);

	/**
	 * Current integration being setup.
	 *
	 * @since 2.0.0
	 * @var WP_Ultimo\Integrations\Host_Providers\Base_Host_Provider
	 */
	protected $integration;

	/**
	 * Allow child classes to add further initializations.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function page_loaded() {

		if (isset($_GET['integration'])) {

			$domain_manager = \WP_Ultimo\Managers\Domain_Manager::get_instance();

			$this->integration = $domain_manager->get_integration_instance($_GET['integration']);

		} // end if;

		if (!$this->integration) {

			wp_redirect(network_admin_url('admin.php?page=wp-ultimo-settings'));

			exit;

		} // end if;

		parent::page_loaded();

	} // end page_loaded;

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return sprintf(__('Integration Setup', 'wp-ultimo'));

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Host Provider Integration', 'wp-ultimo');

	} // end get_menu_title;

	/**
	 * Returns the sections for this Wizard.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_sections() {

		$sections = array(
			'activation'   => array(
				'title'   => __('Activation', 'wp-ultimo'),
				'view'    => array($this, 'section_activation'),
				'handler' => array($this, 'handle_activation'),
			),
			'instructions' => array(
				'title' => __('Instructions', 'wp-ultimo'),
				'view'  => array($this, 'section_instructions'),
			),
			'config'       => array(
				'title'   => __('Configuration', 'wp-ultimo'),
				'view'    => array($this, 'section_configuration'),
				'handler' => array($this, 'handle_configuration'),
			),
			'testing'      => array(
				'title' => __('Testing Integration', 'wp-ultimo'),
				'view'  => array($this, 'section_test'),
			),
			'done'         => array(
				'title' => __('Ready!', 'wp-ultimo'),
				'view'  => array($this, 'section_ready'),
			),
		);

		/*
		 * Some host providers require no instructions.
		 */
		if ($this->integration->supports('no-instructions')) {

			unset($sections['instructions']);

		} // end if;

		/*
		 * Some host providers require no additional setup.
		 */
		if ($this->integration->supports('no-config')) {

			unset($sections['config']);

		} // end if;

		return $sections;

	} // end get_sections;

	/**
	 * Displays the content of the activation section.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function section_activation() {

		$explainer_lines = $this->integration->get_explainer_lines();

		wu_get_template('wizards/host-integrations/activation', array(
			'screen'      => get_current_screen(),
			'page'        => $this,
			'integration' => $this->integration,
			'will'        => $explainer_lines['will'],
			'will_not'    => $explainer_lines['will_not'],
		));

	} // end section_activation;

	/**
	 * Displays the contents of the instructions section.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function section_instructions() {

		call_user_func(array($this->integration, 'get_instructions'));

		$this->render_submit_box();

	} // end section_instructions;

	/**
	 * Displays the content of the configuration section.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function section_configuration() {

		$form = new \WP_Ultimo\UI\Form($this->get_current_section(), $this->integration->get_fields(), array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-widget-list wu-striped wu-m-0 wu--mt-2 wu--mb-3 wu--mx-3',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-px-6 wu-py-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
		));

		if (!empty($_POST)) {

			wu_get_template('wizards/host-integrations/configuration-results', array(
				'screen'      => get_current_screen(),
				'page'        => $this,
				'integration' => $this->integration,
				'form'        => $form,
				'post'        => $_POST,
			));

			return;

		} // end if;

		wu_get_template('wizards/host-integrations/configuration', array(
			'screen'      => get_current_screen(),
			'page'        => $this,
			'integration' => $this->integration,
			'form'        => $form,
		));

	} // end section_configuration;

	/**
	 * Displays the content of the final section.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function section_ready() {

		wu_get_template('wizards/host-integrations/ready', array(
			'screen'      => get_current_screen(),
			'page'        => $this,
			'integration' => $this->integration,
		));

	} // end section_ready;

	/**
	 * Handles the activation of a given integration.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_activation() {

		$is_enabled = $this->integration->is_enabled();

		if ($is_enabled) {

			$this->integration->disable();

			return;

		} // end if;

		$this->integration->enable();

		wp_redirect($this->get_next_section_link());

		exit;

	} // end handle_activation;

	/**
	 * Handles the configuration of a given integration.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_configuration() {

		$this->integration->setup_constants($_POST);

		$redirect_url = $this->get_next_section_link();

		wp_redirect($redirect_url);

		exit;

	} // end handle_configuration;

	/**
	 * Handles the testing of a given configuration.
	 *
	 * @todo Move Vue to a scripts management class.
	 * @since 2.0.0
	 * @return void
	 */
	public function section_test() {

		wp_enqueue_script('wu-vue');

		wu_get_template('wizards/host-integrations/test', array(
			'screen'      => get_current_screen(),
			'page'        => $this,
			'integration' => $this->integration,
		));

	} // end section_test;

} // end class Hosting_Integration_Wizard_Admin_Page;
