<?php
/**
 * Base class that new host providers integrations must extend.
 *
 * @package WP_Ultimo
 * @subpackage Integrations/Host_Providers
 * @since 2.0.0
 */

namespace WP_Ultimo\Integrations\Host_Providers;

use WP_Ultimo\Helpers\WP_Config;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * This base class should be extended to implement new host integrations for SSL and domains.
 */
abstract class Base_Host_Provider {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Holds the id of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $id;

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $title;

	/**
	 * Link to the tutorial teaching how to make this integration work.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $tutorial_link = '';

	/**
	 * Array containing the features this integration supports.
	 *
	 * @var array
	 * @since 2.0.0
	 */
	protected $supports = array();

	/**
	 * Constants that need to be present on wp-config.php for this integration to work.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $constants = array();

	/**
	 * Constants that are optional on wp-config.php.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $optional_constants = array();

	/**
	 * Runs on singleton instantiation.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		add_filter('wu_domain_manager_get_integrations', array($this, 'self_register'));

		add_action('init', array($this, 'add_to_integration_list'));

	} // end init;

	/**
	 * Singletons can't be cloned!
	 *
	 * @since 2.0.0
	 * @return void
	 */
	final private function __clone() {} // end __clone;

	/**
	 * Loads the hooks and dependencies, but only if the hosting is enabled via is_enabled().
	 *
	 * @since 2.0.0
	 */
	final public function __construct() {

		if ($this->detect() && !$this->is_enabled()) {
			/*
			 * Adds an admin notice telling the admin that they should probably enable this integration.
			 */
			return $this->alert_provider_detected();

		} // end if;

		/*
		 * Only add hooks if the integration is enabled and correctly setup.
		 */
		if ($this->is_enabled()) {
			/*
			 * Checks if everything was correctly setup.
			 */
			if (!$this->is_setup()) {
				/*
				 * Adds an admin notice telling the admin that the provider is not correctly setup.
				 */
				return $this->alert_provider_not_setup();

			} // end if;

			/*
			 * Load the dependencies.
			 */
			$this->load_dependencies();

			/*
			 * Initialize the hooks.
			 */
			$this->register_hooks();

		} // end if;

	} // end __construct;

	/**
	 * Let the class register itself on the manager, allowing us to access the integrations later via the slug.
	 *
	 * @since 2.0.0
	 *
	 * @param array $integrations List of integrations added so far.
	 * @return array
	 */
	final public function self_register($integrations) {

		$integrations[$this->get_id()] = get_called_class();

		return $integrations;

	} // end self_register;

	/**
	 * Get the list of enabled host integrations.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function get_enabled_list() {

		return get_network_option(null, 'wu_host_integrations_enabled', array());

	} // end get_enabled_list;

	/**
	 * Check if this integration is enabled.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	final public function is_enabled() {

		$list = $this->get_enabled_list();

		return wu_get_isset($list, $this->get_id(), false);

	} // end is_enabled;

	/**
	 * Enables this integration.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function enable() {

		$list = $this->get_enabled_list();

		$list[$this->get_id()] = true;

		return update_network_option(null, 'wu_host_integrations_enabled', $list);

	} // end enable;

	/**
	 * Disables this integration.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function disable() {

		$list = $this->get_enabled_list();

		$list[$this->get_id()] = false;

		return update_network_option(null, 'wu_host_integrations_enabled', $list);

	} // end disable;

	/**
	 * Adds the host to the list of integrations.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_to_integration_list() {

		$slug = $this->get_id();

		$html = $this->is_enabled() ? sprintf('<span class="wu-self-center wu-text-green-800 wu-mr-4"><span class="dashicons-wu-check"></span> %s</span>', __('Activated', 'wp-ultimo')) : '';

		$url = wu_network_admin_url('wp-ultimo-hosting-integration-wizard', array(
			'integration' => $slug,
		));

		$html .= sprintf('<a href="%s" class="button-primary">%s</a>', $url, __('Configuration', 'wp-ultimo'));

		$title = sprintf(__('%s Integration', 'wp-ultimo'), $this->get_title());

		$title .= sprintf(
			"<span class='wu-normal-case wu-block wu-text-xs wu-font-normal wu-mt-1'>%s</span>",
			__('Go to the setup wizard to setup this integration.', 'wp-ultimo')
		);

		wu_register_settings_field('integrations', "integration_{$slug}", array(
			'type'  => 'note',
			'title' => $title,
			'desc'  => $html,
		));

	} // end add_to_integration_list;

	/**
	 * Adds an admin notice telling the admin that they should probably enable this integration.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function alert_provider_detected() {

		// translators: %1$s will be replaced with the integration title. E.g. RunCloud
		$message = sprintf(__('It looks like you are using %1$s as your hosting provider, yet the %1$s integration module is not active. In order for the domain mapping integration to work with %1$s, you might want to activate that module.', 'wp-ultimo'), $this->get_title());

		$slug = $this->get_id();

		$actions = array(
			'activate' => array(
				// translators: %s is the integration name.
				'title' => sprintf(__('Activate %s', 'wp-ultimo'), $this->get_title()),
				'url'   => wu_network_admin_url('wp-ultimo-hosting-integration-wizard', array(
					'integration' => $slug,
				)),
			)
		);

		WP_Ultimo()->notices->add($message, 'info', 'network-admin', "should-enable-{$slug}-integration", $actions);

	} // end alert_provider_detected;

	/**
	 * Adds an admin notice telling the admin that the provider is not correctly setup.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function alert_provider_not_setup() {

		// translators: %1$s will be replaced with the integration title. E.g. RunCloud.
		$message = sprintf(__('It looks like you are using %1$s as your hosting provider, yet the %1$s integration module was not properly setup. In order for the domain mapping integration to work with %1$s, you need to configure that module.', 'wp-ultimo'), $this->get_title());

		$slug = $this->get_id();

		$actions = array(
			'activate' => array(
				// translators: %s is the integration name
				'title' => sprintf(__('Setup %s', 'wp-ultimo'), $this->get_title()),
				'url'   => wu_network_admin_url('wp-ultimo-hosting-integration-wizard', array(
					'integration' => $slug,
					'tab'         => 'config',
				)),
			)
		);

		WP_Ultimo()->notices->add($message, 'warning', 'network-admin', "should-setup-{$slug}-integration", $actions);

	} // end alert_provider_not_setup;

	/**
	 * Get Fields for the integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_fields() {

		return array();

	} // end get_fields;

	/**
	 * Returns the integration id.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_id() {

		return $this->id;

	} // end get_id;

	/**
	 * Returns the integration title.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_title() {

		return $this->title;

	} // end get_title;

	/**
	 * Checks if a feature is supported, like auto-ssl for example.
	 *
	 * @since 2.0.0
	 * @param string $feature Feature to check.
	 * @return boolean
	 */
	public function supports($feature) {

		return apply_filters('wu_hosting_support_supports', in_array($feature, $this->supports, true), $this);

	} // end supports;

	/**
	 * Initializes the hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_hooks() {
		/*
		 * Hooks the event that is triggered when a new domain is added.
		 */
		add_action('wu_add_domain', array($this, 'on_add_domain'), 10, 2);

		/*
		 * Hooks the vent that is triggered when a domain is deleted.
		 */
		add_action('wu_remove_domain', array($this, 'on_remove_domain'), 10, 2);

	} // end register_hooks;

	/**
	 * Can be used to load dependencies.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function load_dependencies() {} // end load_dependencies;

	/**
	 * Picks up on tips that a given host provider is being used.
	 *
	 * We use this to suggest that the user should activate an integration module.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	abstract public function detect();

	/**
	 * Checks if the integration is correctly setup after enabled.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_setup() {

		$all_set = true;

		foreach ($this->constants as $constant) {

			$all_set = $all_set && defined($constant) && constant($constant);

			/*
			 * If any constant fail, bail.
			 */
			if ($all_set === false) {

				return false;

			} // end if;

		} // end foreach;

		return $all_set;

	} // end is_setup;

	/**
	 * Returns a list of all constants, optional or not.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_all_constants() {

		return array_merge($this->constants, $this->optional_constants);

	} // end get_all_constants;

	/**
	 * Adds the constants with their respective values into the wp-config.php.
	 *
	 * @since 2.0.0
	 *
	 * @param array $constant_values Key => Value of the necessary constants.
	 * @return void
	 */
	public function setup_constants($constant_values) {
		/*
		 * Important: This step is crucial, as it makes sure we clean up undesired constants.
		 * Removing this can allow insertion of arbitrary constants onto the wp-config.pp file
		 * and that should NEVER happen.
		 *
		 * Note that this is also performed on the get_constants_string.
		 */
		$values = shortcode_atts(array_flip($this->get_all_constants()), $constant_values);

		foreach ($values as $constant => $value) {

			WP_Config::get_instance()->inject_wp_config_constant($constant, $value);

		} // end foreach;

	} // end setup_constants;

	/**
	 * Generates a define string for manual insertion on-to wp-config.php.
	 *
	 * This is useful when the user is not willing to let WP Ultimo inject the code,
	 * Or when the wp-config.php is not writable.
	 *
	 * @since 2.0.0
	 *
	 * @param array $constant_values Key => Value of the necessary constants.
	 * @return string
	 */
	public function get_constants_string($constant_values) {
		/*
		 * Initializes the array with an opening comment.
		 */
		$content = array(
			sprintf('// WP Ultimo - Domain Mapping - %s', $this->get_title()),
		);

		/*
		 * Important: This step is crucial, as it makes sure we clean up undesired constants.
		 * Removing this can allow insertion of arbitrary constants onto the wp-config.pp file
		 * and that should NEVER happen.
		 */
		$constant_values = shortcode_atts(array_flip($this->get_all_constants()), $constant_values);

		/*
		 * Adds the constants, one by one.
		 */
		foreach ($constant_values as $constant => $value) {

			$content[] = sprintf("define( '%s', '%s' );", $constant, $value);

		} // end foreach;

		/*
		 * Adds the final line.
		 */
		$content[] = sprintf('// WP Ultimo - Domain Mapping - %s - End', $this->get_title());

		return implode(PHP_EOL, $content);

	} // end get_constants_string;

	/**
	 * Returns the explainer lines for the integration.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_explainer_lines() {

		$explainer_lines = array(
			'will'     => array(
				// translators: %s is the name of the integration e.g. RunCloud
				'send_domains' => sprintf(__('Send API calls to %s servers with domain names added to this network', 'wp-ultimo'), $this->get_title()),
			),
			'will_not' => array(),
		);

		if ($this->supports('autossl')) {

			// translators: %s is the name of the integration e.g. RunCloud
			$explainer_lines['will'][] = sprintf(__('Fetch and install a SSL certificate on %s platform after the domain is added.', 'wp-ultimo'), $this->get_title());

		} else {

			// translators: %s is the name of the integration e.g. RunCloud
			$explainer_lines['will_not'][] = sprintf(__('Fetch and install a SSL certificate on %s platform after the domain is added. This needs to be done manually.', 'wp-ultimo'), $this->get_title());

		} // end if;

		return $explainer_lines;

	} // end get_explainer_lines;

	/**
	 * This method gets called when a new domain is mapped.
	 *
	 * @since 2.0.0
	 * @param string $domain The domain name being mapped.
	 * @param int    $site_id ID of the site that is receiving that mapping.
	 * @return void
	 */
	abstract public function on_add_domain($domain, $site_id);

	/**
	 * This method gets called when a mapped domain is removed.
	 *
	 * @since 2.0.0
	 * @param string $domain The domain name being removed.
	 * @param int    $site_id ID of the site that is receiving that mapping.
	 * @return void
	 */
	abstract public function on_remove_domain($domain, $site_id);

	/**
	 * This method gets called when a new subdomain is being added.
	 *
	 * This happens every time a new site is added to a network running on subdomain mode.
	 *
	 * @since 2.0.0
	 * @param string $subdomain The subdomain being added to the network.
	 * @param int    $site_id ID of the site that is receiving that mapping.
	 * @return void
	 */
	abstract public function on_add_subdomain($subdomain, $site_id);

	/**
	 * This method gets called when a new subdomain is being removed.
	 *
	 * This happens every time a new site is removed to a network running on subdomain mode.
	 *
	 * @since 2.0.0
	 * @param string $subdomain The subdomain being removed to the network.
	 * @param int    $site_id ID of the site that is receiving that mapping.
	 * @return void
	 */
	abstract public function on_remove_subdomain($subdomain, $site_id);

	/**
	 * Tests the connection with the API.
	 *
	 * Needs to be implemented by integrations.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_connection() {

		wp_send_json_success(array());

	} // end test_connection;

	/**
	 * Returns the description of this integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return __('No description provided.', 'wp-ultimo');

	} // end get_description;

	/**
	 * Returns the logo for the integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_logo() {

		return '';

	} // end get_logo;

} // end class Base_Host_Provider;
