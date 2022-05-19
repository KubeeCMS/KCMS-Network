<?php
/**
 * A trait to be included in entities to WP_Plans Class deprecated methods.
 *
 * @package WP_Ultimo
 * @subpackage Deprecated
 * @since 2.0.0
 */

namespace WP_Ultimo\Traits;

/**
 * WP_Ultimo_Plan_Deprecated trait.
 */
trait WP_Ultimo_Plan_Deprecated {

	/**
	 * Top deal equivalent.
	 *
	 * @since 2.0.0
	 * @var bool
	 */
	protected $featured_plan;

	/**
	 * Magic getter to provide backwards compatibility for plans.
	 *
	 * @since 2.0.0
	 *
	 * @throws \Exception Throws an exception when trying to get a key that is not available or back-compat.
	 * @param string $key Property to get.
	 * @return mixed
	 */
	public function __get($key) {

		$value = null;

		switch ($key) {

			case 'title':
				$value = $this->get_name();
				break;

			case 'id':
			case 'ID':
				$value = $this->get_id();
				break;

			case 'free':
				$value = $this->get_pricing_type() === 'free';
				break;

			case 'price_1':
			case 'price_3':
			case 'price_12':
				$value = 20;
				break;

			case 'top_deal':
				$value = $this->is_featured_plan();
				break;

			case 'feature_list':
				$value = $this->get_feature_list();
				break;

			case 'quotas':
				$value = array(
					// 'sites'  => 300,
					'upload' => 1024 * 1024 * 1024,
					'visits' => 300,
				);
				break;
			case 'post':
				$value = (object) array(
					'ID'         => $this->get_id(),
					'post_title' => $this->get_name(),
				);
				break;

			default:
				$value = $this->get_meta('wpu_' . $key, false, true);

		} // end switch;

		/**
		 * Let developers know that this is not going to be supported in the future.
		 *
		 * @since 2.0.0
		 */
		_doing_it_wrong($key, __('Product keys should not be accessed directly', 'wp-ultimo'), '2.0.0');

		return $value;

	} // end __get;

	/**
	 * Get the featured status for this product.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function is_featured_plan() {

		if ($this->featured_plan === null) {

			$this->featured_plan = $this->get_meta('featured_plan', false);

		} // end if;

		return (bool) $this->featured_plan;

	} // end is_featured_plan;

	/**
	 * Set the featured status for this product.
	 *
	 * @since 2.0.0
	 * @param array $featured_plan Feature list for pricing tables.
	 * @return void
	 */
	public function set_featured_plan($featured_plan) {

		$this->meta['featured_plan'] = $featured_plan;

	} // end set_featured_plan;

	/**
	 * Deprecated: Checks if a given plan is a contact us plan.
	 *
	 * @since 1.9.0
	 * @deprecated 2.0.0
	 * @return boolean
	 */
	public function is_contact_us() {

		_deprecated_function(__METHOD__, '2.0.0', 'get_pricing_type');

		return $this->get_pricing_type() === 'contact_us';

	} // end is_contact_us;

	/**
	 * Get the pricing table lines to be displayed on the pricing tables
	 *
	 * @since  1.4.0
	 * @return array
	 */
	public function get_pricing_table_lines() {

		$pricing_table_lines = array();

		/*
		 * Setup Fee
		 * @since 1.7.0
		 */
		if ($this->should_display_quota_on_pricing_tables('setup_fee', true)) {

			if ($this->get_pricing_type() === 'contact_us') {

				$pricing_table_lines[] = __('Contact Us to know more', 'wp-ultimo');

			} else {

				$pricing_table_lines[] = $this->has_setup_fee()
				? sprintf(__('Setup Fee: %s', 'wp-ultimo'), "<strong class='pricing-table-setupfee' data-value='" . $this->get_setup_fee() . "'>" . wu_format_currency($this->get_setup_fee()) . '</strong>')
				: __('No Setup Fee', 'wp-ultimo');

			} // end if;

		} // end if;

		/**
		 *
		 * Post Type Lines
		 * Gets the post type lines to be displayed on the pricing table options
		 */
		$post_types = get_post_types(array('public' => true), 'objects');
		$post_types = apply_filters('wu_get_post_types', $post_types);

		foreach ($post_types as $pt_slug => $post_type) {
			/*
			 * @since  1.1.3 Let users choose which post types to display on the pt
			 */
			if ($this->should_display_quota_on_pricing_tables($pt_slug)) {

				/**
				 * Get if disabled
				 */
				if ($this->is_post_type_disabled($pt_slug)) {

					// Translators: used as "No Posts" where a post type is disabled
					$pricing_table_lines[] = sprintf(__('No %s', 'wp-ultimo'), $post_type->labels->name);
					continue;

				} // end if;

				/**
				 * Get the values
			 *
				 * @var integer|string
				 */
				$value = $this->get_quota($pt_slug) == 0
				? __('Unlimited', 'wp-ultimo')
				: $this->get_quota($pt_slug);

				// Add Line
				$label = $value == 1 ? $post_type->labels->singular_name : $post_type->labels->name;

				$pricing_table_lines[] = sprintf('%s %s', $value, $label);

			} // end if;

		} // end foreach;

		/**
		 *
		 * Site, Disk Space and Trial
		 * Gets the Disk Space and Sites to be displayed on the pricing table options
		 */
		if (wu_get_setting('enable_multiple_sites') && $this->should_display_quota_on_pricing_tables('sites')) {

			$value = $this->get_quota('sites') == 0 ? __('Unlimited', 'wp-ultimo') : $this->get_quota('sites');

			// Add Line
			$pricing_table_lines[] = sprintf('<strong>%s %s</strong>', $value, _n('Site', 'Sites', $this->get_quota('sites'), 'wp-ultimo'));

		} // end if;

		/**
		 * Display DiskSpace
		 */
		if ($this->should_display_quota_on_pricing_tables('upload')) {

			$disk_space = size_format(absint($this->get_quota('disk_space')) * 1024 * 1024);

			// Add Line
			$pricing_table_lines[] = sprintf(__('%s <strong>Disk Space</strong>', 'wp-ultimo'), $disk_space);

		} // end if;

		/**
		 * Visits
		 *
		 * @since 1.6.0
		 */
		if ($this->should_display_quota_on_pricing_tables('visits')) {

			$value = $this->get_quota('visits') == 0 ? __('Unlimited', 'wp-ultimo') : number_format($this->get_quota('visits'));

			// Add Line
			$pricing_table_lines[] = sprintf('%s %s', $value, _n('Visit per month', 'Visits per month', $this->get_quota('visits'), 'wp-ultimo'));

		} // end if;

		/**
		 * Display Trial, if some
		 */
		$trial_days      = wu_get_setting('trial');
		$trial_days_plan = $this->get_trial_duration();

		if ($trial_days > 0 || $trial_days_plan) {

			$trial_days = $trial_days_plan ? $trial_days_plan : $trial_days;

			$pricing_table_lines[] = !$this->free ? sprintf(__('%s day <strong>Free Trial</strong>', 'wp-ultimo'), $trial_days) : '-';

		} // end if;

		/**
		 *
		 * Site, Disk Space and Trial
		 * Gets the Disk Space and Sites to be displayed on the pricing table options
		 */

		/** Loop custom lines */
		$custom_features = explode('<br />', nl2br($this->get_feature_list()));

		foreach ($custom_features as $custom_feature) {

			if (trim($custom_feature) == '') {

				continue;

			} // end if;

			$pricing_table_lines[] = sprintf('%s', trim($custom_feature));

		} // end foreach;

		/**
		 * Return Lines, filterable
		 */
		return apply_filters("wu_get_pricing_table_lines_$this->id", $pricing_table_lines, $this);

	} // end get_pricing_table_lines;

	/**
	 * Deprecated: A quota to get.
	 *
	 * @since 2.0.0
	 *
	 * @deprecated 2.0.0
	 * @param string $quota_name The quota name.
	 * @return mixed
	 */
	public function get_quota($quota_name) {

		if ($quota_name === 'visits') {

			$limit = (float) $this->get_limitations()->visits->get_limit();

		} elseif ($quota_name === 'disk_space') {

			$limit = (float) $this->get_limitations()->disk_space->get_limit();

		} elseif ($quota_name === 'sites') {

			$limit = (float) $this->get_limitations()->sites->get_limit();

		} else {

			$limit = (float) $this->get_limitations()->post_types->{$quota_name}->number;

		} // end if;

		return $limit;

	} // end get_quota;

	/**
	 * Returns wether or not we should display a given quota type in the Quotas and Limits widgets
	 *
	 * @since 1.5.4
	 * @param string $quota_type Post type to check.
	 * @param string $default Default value.
	 * @return bool
	 */
	public function should_display_quota_on_pricing_tables($quota_type, $default = false) {
		/*
		 * @since  1.3.3 Only Show elements allowed on the plan settings
		 */
		$elements = array();

		if (!$elements) {

			return true;

		} // end if;

		if (!isset( $elements[$quota_type] ) && $default) {

			return true;

		} // end if;

		return isset( $elements[$quota_type] ) && $elements[$quota_type];

	} // end should_display_quota_on_pricing_tables;

	/**
	 * Checks if this plan allows unlimited extra users
	 *
	 * @since 1.7.0
	 * @return boolean
	 */
	public function should_allow_unlimited_extra_users() {

		return apply_filters('wu_plan_should_allow_unlimited_extra_users', (bool) $this->unlimited_extra_users, $this);

	} // end should_allow_unlimited_extra_users;

	/**
	 * Returns wether or not we should display a given quota type in the Quotas and Limits widgets
	 *
	 * @since 1.5.4
	 * @param string $post_type The post type.
	 * @return bool
	 */
	public function is_post_type_disabled($post_type) {

		$elements = $this->disabled_post_types;

		if (!$elements) {

			return false;

		} // end if;

		return isset( $elements[$post_type] ) && $elements[$post_type];

	}  // end is_post_type_disabled;

	/**
	 * Returns the post_type quotas
	 *
	 * @since 1.7.0
	 * @return array
	 */
	public function get_post_type_quotas() {

		$quotas = $this->quotas;

		return array_filter($quotas, function($quota_name) {

			return !in_array($quota_name, array(
				'sites',
				'attachment',
				'upload',
				'users',
				'visits',
			), true);

		}, ARRAY_FILTER_USE_KEY);

	} // end get_post_type_quotas;

} // end trait WP_Ultimo_Plan_Deprecated;
