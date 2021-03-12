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
class Checkout_Admin_Page extends Wizard_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wu-checkout';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $type = 'menu';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $parent = 'none';

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
		'user_admin_menu' => 'read',
		'admin_menu'      => 'read',
	);

	/**
	 * Should we hide admin notices on this page?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $hide_admin_notices = true;

	/**
	 * Should we force the admin menu into a folded state?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $fold_menu = true;

	/**
	 * Defined the id to be used on the main form element.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $form_id = 'wu_form';

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return sprintf(__('Checkout', 'wp-ultimo'));

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Checkout', 'wp-ultimo');

	} // end get_menu_title;

	/**
	 * Registers the necessary scripts.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts() {

		do_action('wu_checkout_scripts', null, null);

	} // end register_scripts;

	/**
	 * Returns the sections for this Wizard.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_sections() {

		$sections = array(
			'plan' => array(
				'title'   => __('Pick a Plan', 'wp-ultimo'),
				'view'    => array($this, 'section_activation'),
				'handler' => array($this, 'handle_activation'),
			),
			'pay'  => array(
				'title' => __('Pay', 'wp-ultimo'),
				'view'  => array($this, 'section_instructions'),
			),
		);

		return $sections;

	} // end get_sections;

	/**
	 * Displays the content of the activation section.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function section_activation() {

		echo do_shortcode('[wu_checkout]');

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
	 * Displayes the content of the configuration section.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function section_configuration() {

		if (!empty($_POST)) {

			wu_get_template('wizards/host-integrations/configuration-results', array(
				'screen'      => get_current_screen(),
				'page'        => $this,
				'integration' => $this->integration,
				'fields'      => $this->integration->get_fields(),
				'post'        => $_POST,
			));

			return;

		} // end if;

		wu_get_template('wizards/host-integrations/configuration', array(
			'screen'      => get_current_screen(),
			'page'        => $this,
			'integration' => $this->integration,
			'fields'      => $this->integration->get_fields(),
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

		if ($_POST['submit'] === '1') {

			$this->integration->setup_constants($_POST);

			$redirect_url = $this->get_next_section_link();

			wp_redirect($redirect_url);

			exit;

		} // end if;

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

}  // end class Checkout_Admin_Page;
