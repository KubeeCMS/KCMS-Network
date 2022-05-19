<?php
/**
 * WP Ultimo Tax Class.
 *
 * @package WP_Ultimo
 * @subpackage Tax
 * @since 2.0.0
 */

namespace WP_Ultimo\Tax;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo Tax Class.
 *
 * @since 2.0.0
 */
class Tax {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Adds hooks to be added at the original instantiations.
	 *
	 * @since 1.9.0
	 */
	public function init() {

		add_action('init', array($this, 'add_settings'));

		add_action('wu_page_wp-ultimo-settings_load', array($this, 'add_sidebar_widget'));

		if ($this->is_enabled()) {

			add_action('wp_ultimo_admin_pages', array($this, 'add_admin_page'));

			add_action('wp_ajax_wu_get_tax_rates', array($this, 'serve_taxes_rates_via_ajax'));

			add_action('wp_ajax_wu_save_tax_rates', array($this, 'save_taxes_rates'));

			add_action('wu_before_search_models', function() {

				$model = wu_request('model', 'state');

				$country = wu_request('country', 'not-present');

				if ($country === 'not-present') {

					return;

				} // end if;

				if ($model === 'state') {

					$results = wu_get_country_states($country, 'slug', 'name');

				} elseif ($model === 'city') {

					$states = explode(',', wu_request('state', ''));

					$results = wu_get_country_cities($country, $states, 'slug', 'name');

				} // end if;

				$query = wu_request('query', array(
					'search' => 'searching....',
				));

				$s = trim(wu_get_isset($query, 'search', 'searching...'), '*');

				$filtered = array();

				if (!empty($s)) {

					$filtered = \WP_Ultimo\Dependencies\Arrch\Arrch::find($results, array(
						'sort_key' => 'name',
						'where'    => array(
							array(array('slug', 'name'), '~', $s),
						),
					));

				} // end if;

				wp_send_json(array_values($filtered));

				exit;

			});

		} // end if;

	} // end init;

	/**
	 * Register tax settings.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_settings() {

		wu_register_settings_section('taxes', array(
			'title' => __('Taxes', 'wp-ultimo'),
			'desc'  => __('Taxes', 'wp-ultimo'),
			'icon'  => 'dashicons-wu-percent',
			'order' => 55,
		));

		wu_register_settings_field('taxes', 'enable_taxes', array(
			'title'   => __('Enable Taxes', 'wp-ultimo'),
			'desc'    => __('Enable this option to be able to collect sales taxes on your network payments.', 'wp-ultimo'),
			'type'    => 'toggle',
			'default' => 0,
		));

		wu_register_settings_field('taxes', 'inclusive_tax', array(
			'title'   => __('Inclusive Tax', 'wp-ultimo'),
			'desc'    => __('Enable this option if your prices include taxes. In that case, WP Ultimo will calculate the included tax instead of adding taxes to the price.', 'wp-ultimo'),
			'type'    => 'toggle',
			'default' => 0,
			'require' => array(
				'enable_taxes' => 1,
			),
		));

	} // end add_settings;

	/**
	 * Adds the sidebar widget.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_sidebar_widget() {

		wu_register_settings_side_panel('taxes', array(
			'title'  => __('Tax Rates', 'wp-ultimo'),
			'render' => array($this, 'render_taxes_side_panel'),
		));

	} // end add_sidebar_widget;

	/**
	 * Checks if this functionality is available and should be loaded.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_enabled() {

		$is_enabled = wu_get_setting('enable_taxes', false);

		return apply_filters('wu_enable_taxes', $is_enabled);

	} // end is_enabled;

	/**
	 * Adds the Tax Rate edit admin screen.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_admin_page() {

		new \WP_Ultimo\Admin_Pages\Tax_Rates_Admin_Page;

	} // end add_admin_page;

	/**
	 * Returns the Tax Rate Types available in the platform; Filterable
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_tax_rate_types() {

		return apply_filters('wu_get_tax_rate_types', array(
			'regular' => __('Regular', 'wp-ultimo')
		));

	} // end get_tax_rate_types;

	/**
	 * Returns the default elements of a tax rate.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_tax_rate_defaults() {

		$defaults = array(
			'id'         => uniqid(),
			'title'      => __('Tax Rate', 'wp-ultimo'),
			'country'    => '',
			'state'      => '',
			'city'       => '',
			'tax_type'   => 'percentage',
			'tax_amount' => 0,
			'priority'   => 10,
			'compound'   => false,
			'type'       => 'regular',
		);

		return apply_filters('wu_get_tax_rate_defaults', $defaults);

	} // end get_tax_rate_defaults;

	/**
	 * Returns the registered tax rates.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $fetch_state_options If true, sends the state options along-side the results.
	 * @return array
	 */
	public function get_tax_rates($fetch_state_options = false) {

		$tax_rates_categories = wu_get_option('tax_rates', array(
			'default' => array(
				'name'  => __('Default', 'wp-ultimo'),
				'rates' => array(),
			),
		));

		foreach ($tax_rates_categories as &$tax_rate_category) {

			$tax_rate_category['rates'] = array_map(function($rate) use ($fetch_state_options) {

				if ($fetch_state_options) {

					$rate['state_options'] = wu_get_country_states($rate['country'], 'slug', 'name');

				} // end if;

				$rate['tax_rate'] = is_numeric($rate['tax_rate']) ? $rate['tax_rate'] : 0;

				return wp_parse_args($rate, $this->get_tax_rate_defaults());

			}, $tax_rate_category['rates']);

		} // end foreach;

		return apply_filters('wu_get_tax_rates', $tax_rates_categories, $fetch_state_options);

	} // end get_tax_rates;

	/**
	 * Retrieves the tax rates to serve via ajax.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function serve_taxes_rates_via_ajax() {

		$tax_rates = array();

		if (current_user_can('read_tax_rates')) {

			$tax_rates = $this->get_tax_rates(true);

		} // end if;

		wp_send_json_success((object) $tax_rates);

	} // end serve_taxes_rates_via_ajax;

	/**
	 * Handles the saving of new tax rates.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function save_taxes_rates() {

		if (!check_ajax_referer('wu_tax_editing')) {

			wp_send_json(array(
				'code'    => 'not-enough-permissions',
				'message' => __('You don\'t have permission to alter tax rates', 'wp-ultimo')
			));

		} // end if;

		$data = json_decode(file_get_contents('php://input'), true);

		$tax_rates = isset($data['tax_rates']) ? $data['tax_rates'] : false;

		if (!$tax_rates) {

			wp_send_json(array(
				'code'    => 'tax-rates-not-found',
				'message' => __('No tax rates present in the request', 'wp-ultimo')
			));

		} // end if;

		$treated_tax_rates = array();

		foreach ($tax_rates as $tax_rate_slug => $tax_rate) {

			if (!isset($tax_rate['rates'])) {

				continue;

			} // end if;

			$tax_rate['rates'] = array_map(function($item) {

				unset($item['selected']);

				unset($item['state_options']);

				return $item;

			}, $tax_rate['rates']);

			$treated_tax_rates[strtolower(sanitize_title($tax_rate_slug))] = $tax_rate;

		} // end foreach;

		wu_save_option('tax_rates', $treated_tax_rates);

		wp_send_json(array(
			'code'         => 'success',
			'message'      => __('Tax Rates successfully updated!', 'wp-ultimo'),
			'tax_category' => strtolower(sanitize_title(wu_get_isset($data, 'tax_category', 'default'))),
		));

	} // end save_taxes_rates;

	/**
	 * Render the tax side panel.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_taxes_side_panel() { // phpcs:disable ?>

		<div id="wu-taxes-side-panel" class="wu-widget-inset">

			<div class="wu-p-4">

				<span class="wu-text-gray-700 wu-font-bold wu-uppercase wu-tracking-wide wu-text-xs">
					<?php _e('Manage Tax Rates', 'wp-ultimo'); ?>
				</span>

				<div class="wu-py-2">
					<img class="wu-w-full" alt="<?php esc_attr_e('Manage Tax Rates', 'wp-ultimo'); ?>" src="<?php echo wu_get_asset('sidebar/invoices.png'); ?>">
				</div>

				<p class="wu-text-gray-600 wu-p-0 wu-m-0">
					<?php _e('Add different tax rates depending on the country of your customers.', 'wp-ultimo'); ?>
				</p>

			</div>

			<div v-cloak v-show="enabled == 0" class="wu-mx-4 wu-p-2 wu-bg-blue-100 wu-text-blue-600 wu-rounded wu-mb-4">
				<?php _e('You need to activate tax support first.', 'wp-ultimo'); ?>
			</div>

			<div class="wu-p-4 wu-bg-gray-100 wu-border-solid wu-border-0 wu-border-t wu-border-gray-300">

				<span v-if="false" class="button wu-w-full wu-text-center">
					<?php _e('Manage Tax Rates &rarr;', 'wp-ultimo'); ?>
				</span>

				<div v-cloak>

					<a v-if="enabled" class="button wu-w-full wu-text-center" target="_blank" href="<?php echo wu_network_admin_url('wp-ultimo-tax-rates'); ?>">
						<?php _e('Manage Tax Rates &rarr;', 'wp-ultimo'); ?>
					</a>

					<button v-else disabled="disabled" class="button wu-w-full wu-text-center">
						<?php _e('Manage Tax Rates &rarr;', 'wp-ultimo'); ?>
					</button>

				</div>
			</div>

		</div>

		<script>
			(function($) {
				$(document).ready(function() {
					new Vue({
						el: "#wu-taxes-side-panel",
						data: {},
						computed: {
							enabled: function() {
								return <?php echo json_encode(wu_get_setting('enable_taxes')); ?>
							}
						}
					});
				});
			}(jQuery));
		</script>

	<?php // phpcs:enable

	} // end render_taxes_side_panel;

} // end class Tax;
