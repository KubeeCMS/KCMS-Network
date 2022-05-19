<?php
/**
 * Adds the Domain Mapping Element UI to the Admin Panel.
 *
 * @package WP_Ultimo
 * @subpackage UI
 * @since 2.0.0
 */

namespace WP_Ultimo\UI;

use \WP_Ultimo\UI\Base_Element;
use \WP_Ultimo\Models\Domain;
use \WP_Ultimo\Database\Domains\Domain_Stage;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds the Checkout Element UI to the Admin Panel.
 *
 * @since 2.0.0
 */
class Domain_Mapping_Element extends Base_Element {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * The id of the element.
	 *
	 * Something simple, without prefixes, like 'checkout', or 'pricing-tables'.
	 *
	 * This is used to construct shortcodes by prefixing the id with 'wu_'
	 * e.g. an id checkout becomes the shortcode 'wu_checkout' and
	 * to generate the Gutenberg block by prefixing it with 'wp-ultimo/'
	 * e.g. checkout would become the block 'wp-ultimo/checkout'.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	public $id = 'domain-mapping';

	/**
	 * The icon of the UI element.
	 * e.g. return fa fa-search
	 *
	 * @since 2.0.0
	 * @param string $context One of the values: block, elementor or bb.
	 * @return string
	 */
	public function get_icon($context = 'block') {

		if ($context === 'elementor') {

			return 'eicon-url';

		} // end if;

		return 'fa fa-search';

	} // end get_icon;

	/**
	 * The title of the UI element.
	 *
	 * This is used on the Blocks list of Gutenberg.
	 * You should return a string with the localized title.
	 * e.g. return __('My Element', 'wp-ultimo').
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_title() {

		return __('Domains', 'wp-ultimo');

	} // end get_title;

	/**
	 * The description of the UI element.
	 *
	 * This is also used on the Gutenberg block list
	 * to explain what this block is about.
	 * You should return a string with the localized title.
	 * e.g. return __('Adds a checkout form to the page', 'wp-ultimo').
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return __('Adds the site\'s domains block.', 'wp-ultimo');

	} // end get_description;

	/**
	 * The list of fields to be added to Gutenberg.
	 *
	 * If you plan to add Gutenberg controls to this block,
	 * you'll need to return an array of fields, following
	 * our fields interface (@see inc/ui/class-field.php).
	 *
	 * You can create new Gutenberg panels by adding fields
	 * with the type 'header'. See the Checkout Elements for reference.
	 *
	 * @see inc/ui/class-checkout-element.php
	 *
	 * Return an empty array if you don't have controls to add.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function fields() {

		$fields = array();

		$fields['header'] = array(
			'title' => __('General', 'wp-ultimo'),
			'desc'  => __('General', 'wp-ultimo'),
			'type'  => 'header',
		);

		$fields['title'] = array(
			'type'    => 'text',
			'title'   => __('Title', 'wp-ultimo'),
			'value'   => __('Domains', 'wp-ultimo'),
			'desc'    => __('Leave blank to hide the title completely.', 'wp-ultimo'),
			'tooltip' => '',
		);

		return $fields;

	} // end fields;

	/**
	 * The list of keywords for this element.
	 *
	 * Return an array of strings with keywords describing this
	 * element. Gutenberg uses this to help customers find blocks.
	 *
	 * e.g.:
	 * return array(
	 *  'WP Ultimo',
	 *  'Checkout',
	 *  'Form',
	 *  'Cart',
	 * );
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function keywords() {

		return array(
			'WP Ultimo',
			'Domain',
		);

	} // end keywords;

	/**
	 * List of default parameters for the element.
	 *
	 * If you are planning to add controls using the fields,
	 * it might be a good idea to use this method to set defaults
	 * for the parameters you are expecting.
	 *
	 * These defaults will be used inside a 'wp_parse_args' call
	 * before passing the parameters down to the block render
	 * function and the shortcode render function.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function defaults() {

		return array(
			'title' => __('Domains', 'wp-ultimo'),
		);

	} // end defaults;

	/**
	 * Initializes the singleton.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		parent::init();

		if ($this->is_preview()) {

			$this->site = wu_mock_site();

			return;

		} // end if;

		$this->site = wu_get_current_site();

		$maybe_limit_domain_mapping = true;

		if ($this->site->has_limitations()) {

			$maybe_limit_domain_mapping = $this->site->get_limitations()->domain_mapping->is_enabled();

		} // end if;

		if (!$this->site || !wu_get_setting('enable_domain_mapping') || !wu_get_setting('custom_domains') || !$maybe_limit_domain_mapping) {

			$this->set_display(false);

		} // end if;

		add_action('plugins_loaded', array($this, 'register_forms'));

	} // end init;

	/**
	 * Loads the required scripts.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts() {

		add_wubox();

	} // end register_scripts;

	/**
	 * Register ajax forms used to add a new domain.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms() {
		/*
		 * Add new Domain
		 */
		wu_register_form('user_add_new_domain', array(
			'render'     => array($this, 'render_user_add_new_domain_modal'),
			'handler'    => array($this, 'handle_user_add_new_domain_modal'),
			'capability' => 'exist',
		));

		wu_register_form('user_make_domain_primary', array(
			'render'     => array($this, 'render_user_make_domain_primary_modal'),
			'handler'    => array($this, 'handle_user_make_domain_primary_modal'),
			'capability' => 'exist',
		));

		wu_register_form('user_delete_domain_modal', array(
			'render'     => array($this, 'render_user_delete_domain_modal'),
			'handler'    => array($this, 'handle_user_delete_domain_modal'),
			'capability' => 'exist',
		));

	} // end register_forms;

	/**
	 * Renders the add new customer modal.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_user_add_new_domain_modal() {

		$instructions = \WP_Ultimo\Managers\Domain_Manager::get_instance()->get_domain_mapping_instructions();

		$fields = array(
			'instructions_note' => array(
				'type'              => 'note',
				'desc'              => sprintf('<a href="#" class="wu-no-underline" v-on:click.prevent="ready = false">%s</a>', __('&larr; Back to the Instructions', 'wp-ultimo')),
				'wrapper_html_attr' => array(
					'v-if'    => 'ready',
					'v-cloak' => '1',
				),
			),
			'instructions'      => array(
				'type'              => 'text-display',
				'copy'              => false,
				'title'             => __('Instructions', 'wp-ultimo'),
				'tooltip'           => '',
				'display_value'     => sprintf('<div class="wu--mt-2 wu--mb-2">%s</div>', wpautop($instructions)),
				'wrapper_html_attr' => array(
					'v-show'  => '!ready',
					'v-cloak' => 1
				),
			),
			'ready'             => array(
				'type'              => 'submit',
				'title'             => __('Next Step &rarr;', 'wp-ultimo'),
				'value'             => 'save',
				'classes'           => 'button button-primary wu-w-full',
				'wrapper_classes'   => 'wu-items-end',
				'html_attr'         => array(
					'v-on:click.prevent' => 'ready = true',
				),
				'wrapper_html_attr' => array(
					'v-show'  => '!ready',
					'v-cloak' => 1
				),
			),
			'current_site'      => array(
				'type'  => 'hidden',
				'value' => wu_request('current_site'),
			),
			'domain'            => array(
				'type'              => 'text',
				'title'             => __('Domain', 'wp-ultimo'),
				'placeholder'       => __('mydomain.com', 'wp-ultimo'),
				'wrapper_html_attr' => array(
					'v-show'  => 'ready',
					'v-cloak' => 1
				),
			),
			'primary_domain'    => array(
				'type'              => 'toggle',
				'title'             => __('Primary Domain', 'wp-ultimo'),
				'desc'              => __('Check to set this domain as the primary', 'wp-ultimo'),
				'html_attr'         => array(
					'v-model' => 'primary_domain',
				),
				'wrapper_html_attr' => array(
					'v-show'  => 'ready',
					'v-cloak' => 1,
				),
			),
			'primary_note'      => array(
				'type'              => 'note',
				'desc'              => __('By making this the primary domain, we will convert the previous primary domain for this site, if one exists, into an alias domain.', 'wp-ultimo'),
				'wrapper_html_attr' => array(
					'v-if'    => "require('primary_domain', true) && ready",
					'v-cloak' => 1,
				),
			),
			'submit_button_new' => array(
				'type'              => 'submit',
				'title'             => __('Add Domain', 'wp-ultimo'),
				'value'             => 'save',
				'classes'           => 'button button-primary wu-w-full',
				'wrapper_classes'   => 'wu-items-end',
				'wrapper_html_attr' => array(
					'v-show'  => 'ready',
					'v-cloak' => 1
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
					'ready'          => 0,
					'primary_domain' => false,
				)),
			),
		));

		$form->render();

	} // end render_user_add_new_domain_modal;

	/**
	 * Handles creation of a new customer.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_user_add_new_domain_modal() {

		$current_user_id = get_current_user_id();

		$current_site_id = wu_request('current_site');

		$current_site = wu_get_site($current_site_id);

		if (!$current_site && $current_user_id !== absint($current_site->get_customer()->get_user_id())) {

			wp_send_json_error(
				new \WP_Error('no-permissions', __('You do not have permissions to perform this action.', 'wp-ultimo'))
			);

			exit;

		} // end if;

		/*
		* Tries to create the domain
		*/
		$domain = wu_create_domain(array(
			'domain'         => wu_request('domain'),
			'blog_id'        => absint($current_site_id),
			'primary_domain' => (bool) wu_request('primary_domain'),
		));

		if (is_wp_error($domain)) {

			wp_send_json_error($domain);

		} // end if;

		if (wu_request('primary_domain')) {

			$old_primary_domains = wu_get_domains(array(
				'primary_domain' => true,
				'blog_id'        => $current_site_id,
				'id__not_in'     => array($domain->get_id()),
				'fields'         => 'ids',
			));

			/*
			 * Trigger async action to update the old primary domains.
			 */
			do_action_ref_array('wu_async_remove_old_primary_domains', array($old_primary_domains));

		} // end if;

		wu_enqueue_async_action('wu_async_process_domain_stage', array('domain_id' => $domain->get_id()), 'domain');

		/**
		 * Triggers when a new domain mapping is added.
		 */
		do_action('wu_domain_created', $domain, $domain->get_site(), $domain->get_site()->get_membership());

		wp_send_json_success(array(
			'redirect_url' => wu_get_current_url(),
		));

		exit;

	} // end handle_user_add_new_domain_modal;

	/**
	 * Renders the domain delete action.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_user_delete_domain_modal() {

		$fields = array(
			'confirm'       => array(
				'type'      => 'toggle',
				'title'     => __('Confirm Deletion', 'wp-ultimo'),
				'desc'      => __('This action can not be undone.', 'wp-ultimo'),
				'html_attr' => array(
					'v-model' => 'confirmed',
				),
			),
			'domain_id'     => array(
				'type'  => 'hidden',
				'value' => wu_request('domain_id'),
			),
			'submit_button' => array(
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
		);

		$form = new \WP_Ultimo\UI\Form('user_delete_domain_modal', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'user_delete_domain_modal',
				'data-state'  => json_encode(array(
					'confirmed' => false,
				)),
			),
		));

		$form->render();

	} // end render_user_delete_domain_modal;

	/**
	 * Handles deletion of the selected domain
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_user_delete_domain_modal() {

		if (wu_request('user_id')) {

			$customer = wu_get_customer_by_user_id(wu_request('user_id'));

		} // end if;

		$current_site = wu_request('current_site');

		$get_domain = Domain::get_by_id(wu_request('domain_id'));

		$domain = new Domain($get_domain);

		if ($domain) {

			$domain->delete();

		} // end if;

		wp_send_json_success(array(
			'redirect_url' => wu_get_current_url(),
		));

	} // end handle_user_delete_domain_modal;

	/**
	 * Renders the domain delete action.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_user_make_domain_primary_modal() {

		$fields = array(
			'confirm'       => array(
				'type'      => 'toggle',
				'title'     => __('Confirm Action', 'wp-ultimo'),
				'desc'      => __('This action will also convert the previous primary domain (if any) to an alias to prevent unexpected behavior.', 'wp-ultimo'),
				'html_attr' => array(
					'v-model' => 'confirmed',
				),
			),
			'domain_id'     => array(
				'type'  => 'hidden',
				'value' => wu_request('domain_id'),
			),
			'submit_button' => array(
				'type'            => 'submit',
				'title'           => __('Make it Primary', 'wp-ultimo'),
				'placeholder'     => __('Make it Primary', 'wp-ultimo'),
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => array(
					'v-bind:disabled' => '!confirmed',
				),
			),
		);

		$form = new \WP_Ultimo\UI\Form('user_delete_domain_modal', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'user_delete_domain_modal',
				'data-state'  => json_encode(array(
					'confirmed' => false,
				)),
			),
		));

		$form->render();

	} // end render_user_make_domain_primary_modal;

	/**
	 * Handles conversion to primary domain.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_user_make_domain_primary_modal() {

		$current_site = wu_request('current_site');

		$domain_id = wu_request('domain_id');

		$domain = wu_get_domain($domain_id);

		if ($domain) {

			$domain->set_primary_domain(true);

			$status = $domain->save();

			if (is_wp_error($status)) {

				wp_send_json_error($status);

			} // end if;

			$old_primary_domains = wu_get_domains(array(
				'primary_domain' => true,
				'blog_id'        => $domain->get_blog_id(),
				'id__not_in'     => array($domain->get_id()),
				'fields'         => 'ids',
			));

			/*
			 * Trigger async action to update the old primary domains.
			 */
			do_action_ref_array('wu_async_remove_old_primary_domains', array($old_primary_domains));

			wp_send_json_success(array(
				'redirect_url' => get_admin_url($current_site),
			));

		} // end if;

		wp_send_json_error(new \WP_Error('error', __('Something wrong happenned.', 'wp-ultimo')));

	} // end handle_user_make_domain_primary_modal;

	/**
	 * Runs early on the request lifecycle as soon as we detect the shortcode is present.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function setup() {

		$this->site = WP_Ultimo()->currents->get_site();

		if (!$this->site || !$this->site->is_customer_allowed()) {

			$this->set_display(false);

			return;

		} // end if;

		$this->membership = $this->site->get_membership();

	} // end setup;

	/**
	 * Allows the setup in the context of previews.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function setup_preview() {

		$this->site = wu_mock_site();

		$this->membership = wu_mock_membership();

	} // end setup_preview;

	/**
	 * The content to be output on the screen.
	 *
	 * Should return HTML markup to be used to display the block.
	 * This method is shared between the block render method and
	 * the shortcode implementation.
	 *
	 * @since 2.0.0
	 *
	 * @param array       $atts Parameters of the block/shortcode.
	 * @param string|null $content The content inside the shortcode.
	 * @return string
	 */
	public function output($atts, $content = null) {

		$current_site = $this->site;

		$all_domains = wu_get_domains(array(
			'blog_id' => $current_site->get_id(),
			'orderby' => 'primary_domain',
			'order'   => 'DESC',
		));

		$domains = array();

		foreach ($all_domains as $key => $domain) {

			$stage = new Domain_Stage($domain->get_stage());

			$secure = 'dashicons-wu-lock-open';

			$secure_message = __('Domain not secured with HTTPS', 'wp-ultimo');

			if ($domain->is_secure()) {

				$secure = 'dashicons-wu-lock wu-text-green-500';

				$secure_message = __('Domain secured with HTTPS', 'wp-ultimo');

			} // end if;

			$url_atts = array(
				'current_site' => $current_site->get_id(),
				'domain_id'    => $domain->get_id(),
			);

			$delete_url  = wu_get_form_url('user_delete_domain_modal', $url_atts);
			$primary_url = wu_get_form_url('user_make_domain_primary', $url_atts);

			$domains[$key] = array(
				'id'             => $domain->get_id(),
				'domain_object'  => $domain,
				'domain'         => $domain->get_domain(),
				'stage'          => $stage->get_label(),
				'primary'        => $domain->is_primary_domain(),
				'stage_class'    => $stage->get_classes(),
				'secure_class'   => $secure,
				'secure_message' => $secure_message,
				'delete_link'    => $delete_url,
				'primary_link'   => $primary_url,
 			);

		} // end foreach;

		wp_enqueue_script('wu-forms');

		$url_atts = array(
			'current_site' => wu_get_current_site()->get_ID(),
		);

		$other_atts = array(
			'domains' => $domains,
			'modal'   => array(
				'label'   => __('Add Domain', 'wp-ultimo'),
				'icon'    => 'wu-circle-with-plus',
				'classes' => 'wubox',
				'url'     => wu_get_form_url('user_add_new_domain', $url_atts),
			),
		);

		$atts = array_merge($other_atts, $atts);

		return wu_get_template_contents('dashboard-widgets/domain-mapping', $atts);

	} // end output;

} // end class Domain_Mapping_Element;
