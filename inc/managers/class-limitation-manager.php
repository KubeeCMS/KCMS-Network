<?php
/**
 * Limitation Manager
 *
 * Handles processes related to limitations.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Limitation_Manager
 * @since 2.0.0
 */

namespace WP_Ultimo\Managers;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Objects\Limitations;

/**
 * Handles processes related to limitations.
 *
 * @since 2.0.0
 */
class Limitation_Manager {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		add_filter('wu_product_options_sections', array($this, 'add_limitation_sections'), 10, 2);

		add_filter('wu_membership_options_sections', array($this, 'add_limitation_sections'), 10, 2);

		add_filter('wu_site_options_sections', array($this, 'add_limitation_sections'), 10, 2);

		add_action('admin_init', array($this, 'register_forms'));

	} // end init;

	/**
	 * Register the modal windows to confirm reseting the limitations.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms() {

		wu_register_form('confirm_limitations_reset', array(
			'render'  => array($this, 'render_confirm_limitations_reset'),
			'handler' => array($this, 'handle_confirm_limitations_reset'),
		));

	} // end register_forms;

	/**
	 * Renders the conformation modal to reset limitations.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_confirm_limitations_reset() {

		$fields = array(
			'confirm'       => array(
				'type'      => 'toggle',
				'title'     => __('Confirm Reset', 'wp-ultimo'),
				'desc'      => __('This action can not be undone.', 'wp-ultimo'),
				'html_attr' => array(
					'v-model' => 'confirmed',
				),
			),
			'submit_button' => array(
				'type'            => 'submit',
				'title'           => __('Reset Limitations', 'wp-ultimo'),
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => array(
					'v-bind:disabled' => '!confirmed',
				),
			),
			'id'            => array(
				'type'  => 'hidden',
				'value' => wu_request('id'),
			),
			'model'         => array(
				'type'  => 'hidden',
				'value' => wu_request('model'),
			),
		);

		$form_attributes = array(
			'title'                 => __('Reset', 'wp-ultimo'),
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'reset_limitations',
				'data-state'  => json_encode(array(
					'confirmed' => false,
				)),
			),
		);

		$form = new \WP_Ultimo\UI\Form('reset_limitations', $fields, $form_attributes);

		$form->render();

	} // end render_confirm_limitations_reset;

	/**
	 * Handles the reset of permissions.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_confirm_limitations_reset() {

		$id = wu_request('id');

		$model = wu_request('model');

		if (!$id || !$model) {

			wp_send_json_error(new \WP_Error(
				'parameters-not-found',
				__('Required parameters are missing.', 'wp-ultimo')
			));

		} // end if;

		/*
		 * Remove limitations object
		 */
		Limitations::remove_limitations($model, $id);

		wp_send_json_success(array(
			'redirect_url' => wu_network_admin_url("wp-ultimo-edit-{$model}", array(
				'id'      => $id,
				'updated' => 1,
			))
		));

	} // end handle_confirm_limitations_reset;

	/**
	 * Returns the type of the object that has limitations.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Base_Model $object Model to test.
	 * @return string
	 */
	public function get_object_type($object) {

		$model = false;

		if (is_a($object, '\\WP_Ultimo\\Models\\Site')) {

			$model = 'site';

		} elseif (is_a($object, '\\WP_Ultimo\Models\Membership')) {

			$model = 'membership';

		} elseif (is_a($object, '\\WP_Ultimo\Models\Product')) {

			$model = 'product';

		} // end if;

		return apply_filters('wu_limitations_get_object_type', $model);

	} // end get_object_type;

	/**
	 * Injects the limitations panels when necessary.
	 *
	 * @since 2.0.0
	 *
	 * @param array                        $sections List of tabbed widget sections.
	 * @param \WP_Ultimo\Models\Base_Model $object The model being edited.
	 * @return array
	 */
	public function add_limitation_sections($sections, $object) {

		if ($this->get_object_type($object) !== 'site') {

			$sections['sites'] = array(
				'title'  => __('Sites', 'wp-ultimo'),
				'desc'   => __('Control limitations imposed to the number of sites allowed for memberships attached to this product.', 'wp-ultimo'),
				'icon'   => 'dashicons-wu-browser',
				'fields' => $this->get_sites_fields($object),
				'v-show' => "get_state_value('product_type', 'none') !== 'service' && false",
				'state'  => array(
					'site_additional_charge' => false,
					'limit_sites'            => $object->has_module_limitation('sites'),
				),
			);

		} // end if;

		/*
		 * Add Visits limitation control
		 */
		if (wu_get_setting('enable_visits_limiting', true)) {

			$sections['visits'] = array(
				'title'  => __('Visits', 'wp-ultimo'),
				'desc'   => __('Control limitations imposed to the number of unique visitors allowed for memberships attached to this product.', 'wp-ultimo'),
				'icon'   => 'dashicons-wu-man',
				'v-show' => "get_state_value('product_type', 'none') !== 'service'",
				'state'  => array(
					'limit_visits' => $object->has_module_limitation('visits'),
				),
				'fields' => array(
					'modules[visits]' => array(
						'type'      => 'toggle',
						'title'     => __('Limit Unique Visits', 'wp-ultimo'),
						'desc'      => __('Toggle this option to enable unique visits limitation.', 'wp-ultimo'),
						'value'     => $object->has_module_limitation('visits'),
						'html_attr' => array(
							'v-model' => 'limit_visits'
						),
					),
				),
			);

			if ($object->model !== 'product') {

				$sections['visits']['fields']['modules_visits_overwrite'] = $this->override_notice($object->get_limitations(false)->exists('modules', 'visits'));

			} // end if;

			$sections['visits']['fields']['allowed_visits'] = array(
				'type'              => 'number',
				'title'             => __('Unique Visits Quota', 'wp-ultimo'),
				'tooltip'           => __('Set a top limit for the number of monthly unique visits.', 'wp-ultimo'),
				'value'             => $object->get_quota('visits'),
				'wrapper_html_attr' => array(
					'v-show'  => 'limit_visits',
					'v-cloak' => '1',
				),
				'html_attr'         => array(
					':min' => 'limit_visits ? 1 : -999',
				),
			);

			if ($object->model !== 'product') {

				$sections['visits']['fields']['allowed_visits_overwrite'] = $this->override_notice($object->get_limitations(false)->exists('allowed_visits'), array('limit_visits'));

			} // end if;

			/*
			 * If this is a site edit screen, show the current values
			 * for visits and the reset date
			 */
			if ($this->get_object_type($object) === 'site') {

				$sections['visits']['fields']['visits_count'] = array(
					'type'              => 'text-display',
					'title'             => __('Current Unique Visits Count this Month', 'wp-ultimo'),
					'tooltip'           => __('Current visits count for this particular site.', 'wp-ultimo'),
					'display_value'     => sprintf('%s visit(s)', $object->get_visits_count()),
					'wrapper_html_attr' => array(
						'v-show'  => 'limit_visits',
						'v-cloak' => '1',
					),
				);

			} // end if;

		} // end if;

		$sections['users'] = array(
			'title'  => __('Users', 'wp-ultimo'),
			'desc'   => __('Control limitations imposed to the number of user allowed for memberships attached to this product.', 'wp-ultimo'),
			'icon'   => 'dashicons-wu-users',
			'v-show' => "get_state_value('product_type', 'none') !== 'service'",
			'state'  => array(
				'limit_users' => $object->has_module_limitation('users'),
			),
			'fields' => array(
				'modules[users]' => array(
					'type'      => 'toggle',
					'title'     => __('Limit User', 'wp-ultimo'),
					'desc'      => __('Enable user limitations for this product.', 'wp-ultimo'),
					'value'     => $object->has_module_limitation('users'),
					'html_attr' => array(
						'v-model' => 'limit_users'
					),
				),
			),
		);

		if ($object->model !== 'product') {

			$sections['users']['fields']['modules_user_overwrite'] = $this->override_notice($object->get_limitations(false)->exists('modules', 'users'));

		} // end if;

		$this->register_user_fields($sections, $object);

		$sections['post_types'] = array(
			'title'  => __('Post Types', 'wp-ultimo'),
			'desc'   => __('Control limitations imposed to the number of posts allowed for memberships attached to this product.', 'wp-ultimo'),
			'icon'   => 'dashicons-wu-book',
			'v-show' => "get_state_value('product_type', 'none') !== 'service'",
			'state'  => array(
				'limit_post_types' => $object->has_module_limitation('post_types'),
			),
			'fields' => array(
				'modules[post_types]' => array(
					'type'      => 'toggle',
					'title'     => __('Limit Post Types', 'wp-ultimo'),
					'desc'      => __('Toggle this option to set limits to each post type.', 'wp-ultimo'),
					'value'     => false,
					'html_attr' => array(
						'v-model' => 'limit_post_types'
					),
				),
			),
		);

		if ($object->model !== 'product') {

			$sections['post_types']['fields']['post_quota_overwrite'] = $this->override_notice($object->get_limitations(false)->exists('modules', 'post_types'));

		} // end if;

		$sections['post_types']['post_quota_note'] = array(
			'type'              => 'note',
			'desc'              => __('<strong>Note:</strong> Using the fields below you can set a post limit for each of the post types activated. <br>Toggle the switch to <strong>deactivate</strong> the post type altogether. Leave 0 or blank for unlimited posts.', 'wp-ultimo'),
			'wrapper_html_attr' => array(
				'v-show'  => 'limit_post_types',
				'v-cloak' => '1',
			),
		);

		$this->register_post_type_fields($sections, $object);

		$sections['limit_disk_space'] = array(
			'title'  => __('Disk Space', 'wp-ultimo'),
			'desc'   => __('Control limitations imposed to the disk space allowed for memberships attached to this entity.', 'wp-ultimo'),
			'icon'   => 'dashicons-wu-drive',
			'v-show' => "get_state_value('product_type', 'none') !== 'service'",
			'state'  => array(
				'site_additional_charge' => false,
				'limit_disk_space'       => $object->has_module_limitation('disk_space'),
			),
			'fields' => array(
				'modules[disk_space]' => array(
					'type'      => 'toggle',
					'title'     => __('Limit Disk Site per Site', 'wp-ultimo'),
					'desc'      => __('Enable disk space limitations for this entity.', 'wp-ultimo'),
					'value'     => $object->has_module_limitation('sites'),
					'html_attr' => array(
						'v-model' => 'limit_disk_space',
					),
				),
			),
		);

		if ($object->model !== 'product') {

			$sections['limit_disk_space']['fields']['disk_space_modules_overwrite'] = $this->override_notice($object->get_limitations(false)->exists('modules', 'disk_space'));

		} // end if;

		$sections['limit_disk_space']['fields']['disk_space'] = array(
			'type'              => 'number',
			'title'             => __('Disk Space Allowance', 'wp-ultimo'),
			'tooltip'           => __('Set a limit in MBs for the disk space for <strong>each</strong> individual site.', 'wp-ultimo'),
			'min'               => 0,
			'placeholder'       => 100,
			'value'             => $object->get_quota('disk_space'),
			'wrapper_html_attr' => array(
				'v-show'  => "get_state_value('product_type', 'none') !== 'service' && limit_disk_space",
				'v-cloak' => '1',
			),
		);

		if ($object->model !== 'product') {

			$sections['limit_disk_space']['fields']['disk_space_override'] = $this->override_notice($object->get_limitations(false)->exists('disk_space'), array('limit_disk_space'));

		} // end if;

		$sections['allowed_themes'] = array(
			'title'  => __('Themes', 'wp-ultimo'),
			'desc'   => __('Limit the number of users on each role, posts, pages, and more.', 'wp-ultimo'),
			'icon'   => 'dashicons-wu-palette',
			'v-show' => "get_state_value('product_type', 'none') !== 'service'",
			'fields' => array(
				'themes' => array(
					'type'    => 'html',
					'title'   => __('Themes', 'wp-ultimo'),
					'desc'    => __('Select how the themes installed on the network should behave.', 'wp-ultimo'),
					'content' => function() use ($object) {
						return $this->get_theme_selection_list($object);
					},
				),
			),
		);

		$sections['allowed_plugins'] = array(
			'title'  => __('Plugins', 'wp-ultimo'),
			'desc'   => __('You can choose the behavior of each plugin installed on the platform.', 'wp-ultimo'),
			'icon'   => 'dashicons-wu-power-plug',
			'v-show' => "get_state_value('product_type', 'none') !== 'service'",
			'fields' => array(
				'plugins' => array(
					'type'    => 'html',
					'title'   => __('Plugins', 'wp-ultimo'),
					'desc'    => __('Select how the plugins installed on the network should behave.', 'wp-ultimo'),
					'content' => function() use ($object) {
						return $this->get_plugin_selection_list($object);
					},
				),
			),
		);

		return $sections;

	} // end add_limitation_sections;

	/**
	 * Generates the override notice.
	 *
	 * @since 2.0.0
	 *
	 * @param boolean $show Wether or not to show the field.
	 * @param array   $additional_checks Array containing javascript conditions that neet to be met.
	 * @return array
	 */
	protected function override_notice($show = false, $additional_checks = array()) {

		$text = sprintf('<p class="wu-m-0 wu-p-2 wu-bg-blue-100 wu-text-blue-600 wu-rounded">%s</p>', __('This value is being applied only to this entity. Changes made to the membership or product permissions will not affect this particular value.', 'wp-ultimo'));

		return array(
			'desc'              => $text,
			'type'              => 'note',
			'wrapper_classes'   => 'wu-pt-0',
			'wrapper_html_attr' => array(
				'v-show'  => ($additional_checks ? (implode(' && ', $additional_checks) . ' && ') : '') . var_export((bool) $show, true),
				'v-cloak' => '1',
				'style'   => 'border-top-width: 0 !important',
			),
		);

	} // end override_notice;

	/**
	 * Register the user roles fields
	 *
	 * @since 2.0.0
	 *
	 * @param array                        $sections Sections and fields.
	 * @param \WP_Ultimo\Models\Base_Model $object The object being edit.
	 * @return void
	 */
	public function register_user_fields(&$sections, $object) {

		$sections['users']['state']['allowed_user_roles'] = $object->get_allowed_user_roles();

		$sections['users']['state']['user_role_quotas'] = $object->get_user_role_quotas();

		$user_roles = get_editable_roles();

		foreach ($user_roles as $user_role_slug => $user_role) {

			$sections['users']['fields']["control_{$user_role_slug}"] = array(
				'type'              => 'group',
				'title'             => sprintf(__('Limit %s Role', 'wp-ultimo'), $user_role['name']),
				'desc'              => sprintf(__('The customer will be able to create %s users(s) of this user role.', 'wp-ultimo'), "{{ allowed_user_roles['{$user_role_slug}'] ? ( parseInt(user_role_quotas['{$user_role_slug}'], 10) ? user_role_quotas['{$user_role_slug}'] : '" . __('unlimited', 'wp-ultimo') . "' ) : '" . __('no', 'wp-ultimo') . "' }}"),
				'tooltip'           => '',
				'wrapper_html_attr' => array(
					'v-bind:class' => "!allowed_user_roles['{$user_role_slug}'] ? 'wu-opacity-75' : ''",
					'v-show'       => 'limit_users',
					'v-cloak'      => '1',
				),
				'fields'            => array(
					"user_role_quotas[{$user_role_slug}]" => array(
						'type'            => 'number',
						'placeholder'     => sprintf(__('%s Role Quota. e.g. 10', 'wp-ultimo'), $user_role['name']),
						'min'             => 0,
						'value'           => $object->get_quota($user_role_slug),
						'wrapper_classes' => 'wu-w-full',
						'html_attr'       => array(
							'v-model'         => "user_role_quotas['{$user_role_slug}']",
							'v-bind:disabled' => "!allowed_user_roles['{$user_role_slug}']",
						),
					),
					"allowed_user_roles[{$user_role_slug}]" => array(
						'type'            => 'toggle',
						'wrapper_classes' => 'wu-mt-2',
						'value'           => $object->get_quota($user_role_slug),
						'html_attr'       => array(
							'v-model' => "allowed_user_roles['{$user_role_slug}']",
						),
					),
				),
			);

			/*
			 * Add override notice.
			 */
			if ($object->model !== 'product') {

				$sections['users']['fields']["override_{$user_role_slug}"] = $this->override_notice($object->get_limitations(false)->exists('user_role_quotas', $user_role_slug), array('limit_users'));

			} // end if;

		} // end foreach;

	} // end register_user_fields;

	/**
	 * Register the post type fields
	 *
	 * @since 2.0.0
	 *
	 * @param array                        $sections Sections and fields.
	 * @param \WP_Ultimo\Models\Base_Model $object The object being edit.
	 * @return void
	 */
	public function register_post_type_fields(&$sections, $object) {

		$sections['post_types']['state']['allowed_post_types'] = $object->get_allowed_post_types();

		$sections['post_types']['state']['post_type_quotas'] = $object->get_quotas();

		$post_types = get_post_types(array(), 'objects');

		foreach ($post_types as $post_type_slug => $post_type) {

			$sections['post_types']['fields']["control_{$post_type_slug}"] = array(
				'type'              => 'group',
				'title'             => sprintf(__('Limit %s', 'wp-ultimo'), $post_type->label),
				'desc'              => sprintf(__('The customer will be able to create %s post(s) of this post type.', 'wp-ultimo'), "{{ allowed_post_types['{$post_type_slug}'] ? ( parseInt(post_type_quotas['{$post_type_slug}'], 10) ? post_type_quotas['{$post_type_slug}'] : '" . __('unlimited', 'wp-ultimo') . "' ) : '" . __('no', 'wp-ultimo') . "' }}"),
				'tooltip'           => '',
				'wrapper_html_attr' => array(
					'v-bind:class' => "!allowed_post_types['{$post_type_slug}'] ? 'wu-opacity-75' : ''",
					'v-show'       => 'limit_post_types',
					'v-cloak'      => '1',
				),
				'fields'            => array(
					"post_type_quotas[{$post_type_slug}]" => array(
						'type'            => 'number',
						'placeholder'     => sprintf(__('%s Quota. e.g. 200', 'wp-ultimo'), $post_type->label),
						'min'             => 0,
						'value'           => $object->get_quota($post_type_slug),
						'wrapper_classes' => 'wu-w-full',
						'html_attr'       => array(
							'v-model'         => "post_type_quotas['{$post_type_slug}']",
							'v-bind:disabled' => "!allowed_post_types['{$post_type_slug}']",
						),
					),
					"allowed_post_types[{$post_type_slug}]" => array(
						'type'            => 'toggle',
						'wrapper_classes' => 'wu-mt-2',
						'value'           => $object->get_quota($post_type_slug),
						'html_attr'       => array(
							'v-model' => "allowed_post_types['{$post_type_slug}']",
						),
					),
				),
			);

			/*
			 * Add override notice.
			 */
			if ($object->model !== 'product') {

				$sections['post_types']['fields']["override_{$post_type_slug}"] = $this->override_notice($object->get_limitations(false)->exists('post_type_quotas', $post_type_slug), array(
					'limit_post_types'
				));

			} // end if;

		} // end foreach;

	} // end register_post_type_fields;

	/**
	 * Returns the list of fields for the site tab.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Base_Model $object The model being edited.
	 * @return array
	 */
	protected function get_sites_fields($object) {

		$fields = array(
			'modules[sites]' => array(
				'type'      => 'toggle',
				'title'     => __('Limit Sites', 'wp-ultimo'),
				'desc'      => __('Enable site limitations for this product.', 'wp-ultimo'),
				'value'     => $object->has_module_limitation('sites'),
				'html_attr' => array(
					'v-model' => 'limit_sites'
				),
			),
		);

		if ($object->model !== 'product') {

			$fields['sites_overwrite'] = $this->override_notice($object->get_limitations(false)->exists('modules', 'sites'));

		} // end if;

		/*
		 * Sites not supported on this type
		 */
		$fields['site_not_allowed_note'] = array(
			'type'              => 'note',
			'desc'              => __('The product type selection does not support allowing for the creating of extra sites.', 'wp-ultimo'),
			'tooltip'           => '',
			'wrapper_html_attr' => array(
				'v-show'  => "get_state_value('product_type', 'none') === 'service' && limit_sites",
				'v-cloak' => '1',
			),
		);

		$fields['site_limit'] = array(
			'type'              => 'number',
			'min'               => 1,
			'title'             => __('Site Allowance', 'wp-ultimo'),
			'tooltip'           => __('This is the number of sites the customer will be able to create under this membership.', 'wp-ultimo'),
			'placeholder'       => 1,
			'wrapper_html_attr' => array(
				'v-show'  => "get_state_value('product_type', 'none') !== 'service' && limit_sites",
				'v-cloak' => '1',
			),
		);

		$fields['site_additional_charge'] = array(
			'type'              => 'toggle',
			'title'             => __('Charge for additional sites?', 'wp-ultimo'),
			'desc'              => __('Allow customers to create additional sites for an additional charge under this membership product. Please, be aware that only a few gateways support this feature.', 'wp-ultimo'),
			'value'             => false,
			'html_attr'         => array(
				'v-model' => 'site_additional_charge',
			),
			'wrapper_html_attr' => array(
				'v-show'  => "get_state_value('product_type', 'none') === 'plan' && limit_sites",
				'v-cloak' => '1',
			),
		);

		$fields['site_additional_charge_amount'] = array(
			'type'              => 'text',
			'title'             => __('Price per Additional Site', 'wp-ultimo'),
			'tooltip'           => __('This amount will be added to the current membership for each additional site registered.', 'wp-ultimo'),
			'placeholder'       => __('9.99', 'wp-ultimo'),
			'money'             => true,
			'value'             => 9.99,
			'wrapper_html_attr' => array(
				'v-show'  => "get_state_value('product_type', 'none') === 'plan' && site_additional_charge && limit_sites",
				'v-cloak' => '1',
			),
		);

		return apply_filters('wu_limitations_get_sites_fields', $fields, $object, $this);

	} // end get_sites_fields;

	/**
	 * Returns the HTML markup for the plugin selector list.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Base_Model $object The model being edited.
	 * @return string
	 */
	public function get_plugin_selection_list($object) {

		$all_plugins = $this->get_all_plugins();

		return wu_get_template_contents('limitations/plugin-selector', array(
			'plugins' => $all_plugins,
			'object'  => $object,
		));

	} // end get_plugin_selection_list;

	/**
	 * Returns the HTML markup for the plugin selector list.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Base_Model $object The model being edited.
	 * @return string
	 */
	public function get_theme_selection_list($object) {

		$all_themes = $this->get_all_themes();

		return wu_get_template_contents('limitations/theme-selector', array(
			'themes' => $all_themes,
			'object' => $object,
		));

	} // end get_theme_selection_list;

	/**
	 * Returns a list of all plugins available as options, excluding WP Ultimo.
	 *
	 * We also exclude a couple more.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function get_all_plugins() {

		$all_plugins = get_plugins();

		return array_filter($all_plugins, function($path) {

			return !in_array($path, $this->plugin_exclusion_list(), true);

		}, ARRAY_FILTER_USE_KEY);

	} // end get_all_plugins;

	/**
	 * Returns a list of all themes available as options, after filtering.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function get_all_themes() {

		$all_plugins = wp_get_themes();

		return array_filter($all_plugins, function($path) {

			return !in_array($path, $this->theme_exclusion_list(), true);

		}, ARRAY_FILTER_USE_KEY);

	} // end get_all_themes;

	/**
	 * Returns the exclusion list for plugins.
	 *
	 * We don't want people forcing WP Ultimo to be deactivated, do we?
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function plugin_exclusion_list() {

		$exclusion_list = array(
			'wp-ultimo-20/wp-ultimo.php', // Slug during dev
			'wp-ultimo/wp-ultimo.php',
			'user-switching/user-switching.php',
		);

		return apply_filters('wu_limitations_plugin_exclusion_list', $exclusion_list);

	} // end plugin_exclusion_list;

	/**
	 * Returns the exclusion list for themes.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function theme_exclusion_list() {

		$exclusion_list = array();

		return apply_filters('wu_limitations_theme_exclusion_list', $exclusion_list);

	} // end theme_exclusion_list;

} // end class Limitation_Manager;
