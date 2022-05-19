<?php
/**
 * Broadcast List Table class.
 *
 * @package WP_Ultimo
 * @subpackage List_Table
 * @since 2.0.0
 */

namespace WP_Ultimo\List_Tables;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Broadcast List Table class.
 *
 * @since 2.0.0
 */
class Broadcast_List_Table extends Base_List_Table {

	/**
	 * Holds the query class for the object being listed.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = '\\WP_Ultimo\\Database\\Broadcasts\\Broadcast_Query';

	/**
	 * Initializes the table.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		parent::__construct(array(
			'singular' => __('Broadcast', 'wp-ultimo'),  // singular name of the listed records
			'plural'   => __('Broadcasts', 'wp-ultimo'), // plural name of the listed records
			'ajax'     => true,                          // does this table support ajax?
			'add_new'  => array(
				'url'     => wu_get_form_url('add_new_broadcast_message'),
				'classes' => 'wubox',
			),
		));

	} // end __construct;

	/**
	 * Overrides the checkbox column to disable the checkboxes on the email types.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Broadcast $item The broadcast object.
	 * @return string
	 */
	public function column_cb($item) {

		if ($item->get_type() === 'broadcast_email') {

			return '<input type="checkbox" disabled>';

		} // end if;

		return parent::column_cb($item);

	} // end column_cb;

	/**
	 * Returns the markup for the type column.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Broadcast $item The broadcast object.
	 * @return string
	 */
	public function column_type($item) {

		$type = $item->get_type();

		$class = 'wu-bg-gray-200';

		if ($type === 'broadcast_email') {

			$label = __('Email', 'wp-ultimo');

		}  // end if;

		if ($type === 'broadcast_notice') {

			$status = $item->get_notice_type();

			$label = __('Notice', 'wp-ultimo');

			if ($status === 'info') {

				$class = 'wu-bg-blue-200';

			} elseif ($status === 'success') {

				$class = 'wu-bg-green-200';

			} elseif ($status === 'warning') {

				$class = 'wu-bg-orange-200';

			} elseif ($status === 'error') {

				$class = 'wu-bg-red-200';

			} // end if;

		} // end if;

		return "<span class='wu-py-1 wu-px-2 $class wu-rounded-sm wu-text-gray-700 wu-text-xs wu-font-mono'>{$label}</span>";

	} // end column_type;

	/**
	 * Displays the name of the broadcast.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Broadcast $item The broadcast object.
	 * @return string
	 */
	public function column_the_content($item) {

		$title = sprintf('<strong class="wu-block wu-text-gray-700">%s</strong>', $item->get_title()); // phpcs:ignore

		$content = wp_trim_words(wp_strip_all_tags($item->get_content()), 7);

		$url_atts = array(
			'id' => $item->get_id(),
			'slug' => $item->get_slug(),
			'model' => 'broadcast'
		);

		$actions = array(
			'edit'   => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-broadcast', $url_atts), __('Edit', 'wp-ultimo')),
			'delete' => sprintf('<a title="%s" class="wubox" href="%s">%s</a>', __('Delete', 'wp-ultimo'), wu_get_form_url('delete_modal', $url_atts), __('Delete', 'wp-ultimo')),
		);

		return $title . $content . $this->row_actions($actions);

	} // end column_the_content;

	/**
	 * Displays the target customers of the broadcast.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Broadcast $item The broadcast object.
	 * @return string
	 */
	public function column_target_customers($item) {

		$targets = wu_get_broadcast_targets($item->get_id(), 'customers');

		$targets = array_filter(array_map('wu_get_customer', $targets));

		$targets_count = count($targets);

		$html = '<div class="wu-p-2 wu-mr-1 wu-flex wu-rounded wu-items-center wu-border wu-border-solid wu-border-gray-300 wu-bg-gray-100 wu-relative wu-overflow-hidden">';

		switch ($targets_count) {
			case 0:
				$not_found = __('No customer found', 'wp-ultimo');

				return "<div class='wu-p-2 wu-mr-1 wu-flex wu-rounded wu-items-center wu-border wu-border-solid wu-border-gray-300 wu-bg-gray-100 wu-relative wu-overflow-hidden'>
										<span class='dashicons dashicons-wu-block wu-text-gray-600 wu-px-1 wu-pr-3'>&nbsp;</span>
												<div class=''>
														<span class='wu-block wu-py-3 wu-text-gray-600 wu-text-2xs wu-font-bold wu-uppercase'>{$not_found}</span>
												</div>
										</div>";

			break;
			case 1:
				$customer = array_pop($targets);

				$url_atts = array(
					'id' => $customer->get_id(),
				);

				$customer_link = wu_network_admin_url('wp-ultimo-edit-customer', $url_atts);

				$avatar = get_avatar($customer->get_user_id(), 32, 'identicon', '', array(
					'force_display' => true,
					'class'         => 'wu-rounded-full wu-border-solid wu-border-1 wu-border-white hover:wu-border-gray-400',
				));

				$display_name = $customer->get_display_name();

				$id = $customer->get_id();

				$email = $customer->get_email_address();

				$html = "<a href='{$customer_link}' class='wu-p-2 wu-flex wu-flex-grow wu-bg-gray-100 wu-rounded wu-items-center wu-border wu-border-solid wu-border-gray-300'>
										{$avatar}
										<div class='wu-pl-2'>
												<strong class='wu-block'>{$display_name} <small class='wu-font-normal'>(#{$id})</small></strong>
												<small>{$email}</small>
										</div>
								</a>";

				return $html;
			break;
			default:
				foreach ($targets as $key => $target) {

					$customer = $target;

					$tooltip_name = $customer->get_display_name();

					$email = $customer->get_email_address();

					$avatar = get_avatar($email, 32, 'identicon', '', array(
						'class' => 'wu-rounded-full wu-border-solid wu-border-1 wu-border-white hover:wu-border-gray-400',
					));

					$url_atts = array(
						'id' => $customer->get_id(),
					);

					$customer_link = wu_network_admin_url('wp-ultimo-edit-customer', $url_atts);

					$html .= "<div class='wu-flex wu--mr-4'><a role='tooltip' aria-label='{$tooltip_name}' href='{$customer_link}'>{$avatar}</a></div>";

				} // end foreach;

				if ($targets_count < 7) {

					$modal_atts = array(
						'action'      => 'wu_modal_targets_display',
						'object_id'   => $item->get_id(),
						'width'       => '400',
						'height'      => '360',
						'target_type' => 'customers',
					);

					$html .= sprintf('<div class="wu-inline-block wu-mr-2">
										<a href="%s" title="%s" class="wubox"><span class="wu-ml-6 wu-uppercase wu-text-xs wu-font-bold"> %s %s</span></a>
										</div>', wu_get_form_url('view_broadcast_targets', $modal_atts), __('Targets', 'wp-ultimo'), $targets_count, __('Targets', 'wp-ultimo'));

					$html .= '</div>';

					return $html;

				} // end if;

				$modal_atts = array(
					'action'      => 'wu_modal_targets_display',
					'object_id'   => $item->get_id(),
					'width'       => '400',
					'height'      => '360',
					'target_type' => 'customers',
				);

				$html .= sprintf('<div class="wu-inline-block wu-ml-4">
								<a href="%s" title="%s" class="wubox"><span class="wu-pl-2 wu-uppercase wu-text-xs wu-font-bold"> %s %s</span></a>
								</div>', wu_get_form_url('view_broadcast_targets', $modal_atts), __('Targets', 'wp-ultimo'), $targets_count, __('Targets', 'wp-ultimo'));

				$html .= '</div>';

				return $html;

			break;

		} // end switch;

	} // end column_target_customers;

	/**
	 * Displays the target products of the broadcast.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Broadcast $item The broadcast object.
	 * @return string
	 */
	public function column_target_products($item) {

		$targets = wu_get_broadcast_targets($item->get_id(), 'products');

		$html = '';

		$products = array_filter(array_map('wu_get_product', $targets));

		$product_count = count($products);

		switch ($product_count) {
			case 0:
				$not_found = __('No product found', 'wp-ultimo');

				$html = "<div class='wu-p-2 wu-mr-1 wu-flex wu-rounded wu-items-center wu-border wu-border-solid wu-border-gray-300 wu-bg-gray-100 wu-relative wu-overflow-hidden'>
					<span class='dashicons dashicons-wu-block wu-text-gray-600 wu-px-1 wu-pr-3'>&nbsp;</span>
							<div class=''>
									<span class='wu-block wu-py-3 wu-text-gray-600 wu-text-2xs wu-font-bold wu-uppercase'>{$not_found}</span>
							</div>
					</div>";
				break;
			case 1:
				$product = array_pop($products);

				$image = $product->get_featured_image('thumbnail');

				if ($image) {

					$image = sprintf('<img class="wu-w-7 wu-h-7 wu-bg-gray-200 wu-rounded-full wu-text-gray-600 wu-flex wu-items-center wu-justify-center wu-border-solid wu-border-1 wu-border-white hover:wu-border-gray-400" src="%s">', esc_attr($image));

				} else {

					$image = '<div class="wu-w-7 wu-h-7 wu-bg-gray-200 wu-rounded-full wu-text-gray-600 wu-flex wu-items-center wu-justify-center wu-border-solid wu-border-1 wu-border-white">
					<span class="dashicons-wu-image"></span>
					</div>';

				} // end if;

				$name = $product->get_name();

				$id = $product->get_id();

				$plan_customers = wu_get_membership_customers($product->get_id());

				$customer_count = (int) 0;

				if ($plan_customers) {

					$customer_count = count($plan_customers);

				} // end if;

				$description = sprintf(__('%s customer(s) targeted.', 'wp-ultimo'), $customer_count);

				$url_atts = array(
					'id' => $product->get_id(),
				);

				$product_link = wu_network_admin_url('wp-ultimo-edit-product', $url_atts);

				$html = "<a href='{$product_link}' class='wu-p-2 wu-flex wu-flex-grow wu-bg-gray-100 wu-rounded wu-items-center wu-border wu-border-solid wu-border-gray-300'>
						{$image}
						<div class='wu-pl-2'>
								<strong class='wu-block'>{$name} <small class='wu-font-normal'>(#{$id})</small></strong>
								<small>{$description}</small>
						</div>
				</a>";
				break;

		} // end switch;

		if ($html) {

			return $html;

		} // end if;

		$html = '<div class="wu-p-2 wu-mr-1 wu-flex wu-rounded wu-items-center wu-border wu-border-solid wu-border-gray-300 wu-bg-gray-100 wu-relative wu-overflow-hidden">';

		foreach ($products as $product) {

			$url_atts = array(
				'id' => $product->get_id(),
			);

			$product_link = wu_network_admin_url('wp-ultimo-edit-product', $url_atts);

			$product_name = $product->get_name();

			$image = $product->get_featured_image('thumbnail');

			if ($image) {

				$image = sprintf('<img class="wu-w-7 wu-h-7 wu-bg-gray-200 wu-rounded-full wu-text-gray-600 wu-flex wu-items-center wu-justify-center wu-border-solid wu-border-1 wu-border-white hover:wu-border-gray-400" src="%s">', esc_attr($image));

			} else {

				$image = '<div class="wu-w-7 wu-h-7 wu-bg-gray-200 wu-rounded-full wu-text-gray-600 wu-flex wu-items-center wu-justify-center wu-border-solid wu-border-1 wu-border-white hover:wu-border-gray-400">
				<span class="dashicons-wu-image wu-p-1 wu-rounded-full"></span>
		</div>';

			} // end if;

			$html .= "<div class='wu-flex wu--mr-4'><a role='tooltip' aria-label='{$product_name}' href='{$product_link}'>{$image}</a></div>";

		} // end foreach;

		if ($product_count > 1 && $product_count < 5) {

			$modal_atts = array(
				'action'      => 'wu_modal_targets_display',
				'object_id'   => $item->get_id(),
				'width'       => '400',
				'height'      => '360',
				'target_type' => 'products',
			);

			$html .= sprintf('<div class="wu-inline-block wu-ml-4">
			<a href="%s" title="%s" class="wubox"><span class="wu-pl-2 wu-uppercase wu-text-xs wu-font-bold"> %s %s</span></a></div>', wu_get_form_url('view_broadcast_targets', $modal_atts), __('Targets', 'wp-ultimo'), $product_count, __('Targets', 'wp-ultimo'));

			$html .= '</div>';

			return $html;

		} // end if;

		$modal_atts = array(
			'action'      => 'wu_modal_targets_display',
			'object_id'   => $item->get_id(),
			'width'       => '400',
			'height'      => '360',
			'target_type' => 'products',
		);

		$html .= sprintf('<div class="wu-inline-block wu-ml-4"><a href="%s" title="%s" class="wubox"><span class="wu-pl-2 wu-uppercase wu-text-xs wu-font-bold"> %s %s</span></a></div>', wu_get_form_url('view_broadcast_targets', $modal_atts), __('Targets', 'wp-ultimo'), $product_count, __('Targets', 'wp-ultimo'));

		$html .= '</div>';

		return $html;

	} // end column_target_products;

	/**
	 * Returns the list of columns for this particular List Table.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_columns() {

		$columns = array(
			'cb'               => '<input type="checkbox" />',
			'type'             => __('Type', 'wp-ultimo'),
			'the_content'      => __('Content', 'wp-ultimo'),
			'target_customers' => __('Target Customers', 'wp-ultimo'),
			'target_products'  => __('Target Products', 'wp-ultimo'),
			'date_created'     => __('Date', 'wp-ultimo'),
			'id'               => __('ID', 'wp-ultimo'),
		);

		return $columns;

	} // end get_columns;

	/**
	 * Returns the filters for this page.
	 *
	 * @since 2.0.0
	 * @return boolean|array
	 */
	public function get_filters() {

		return array(
			'filters'      => array(
				'type'   => array(
					'label'   => __('Broadcast Type', 'wp-ultimo'),
					'options' => array(
						'broadcast_notice' => __('Email', 'wp-ultimo'),
						'broadcast_email'  => __('Notices', 'wp-ultimo'),
					),
				),
				'status' => array(
					'label'   => __('Notice Type', 'wp-ultimo'),
					'options' => array(
						'info'    => __('Info - Blue', 'wp-ultimo'),
						'success' => __('Success - Green', 'wp-ultimo'),
						'warning' => __('Warning - Yellow', 'wp-ultimo'),
						'error'   => __('Error - Red', 'wp-ultimo'),
					),
				),
			),
			'date_filters' => array(
				'date_created' => array(
					'label'   => __('Date', 'wp-ultimo'),
					'options' => $this->get_default_date_filter_options(),
				),
			),
		);

	} // end get_filters;

	/**
	 * Registers the necessary scripts and styles for this admin page.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts() {

		parent::register_scripts();

	} // end register_scripts;

	/**
	 * Returns the pre-selected filters on the filter bar.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_views() {

		return array(
			'all'      => array(
				'field' => 'status',
				'url'   => add_query_arg('type', 'all'),
				'label' => __('All Broadcasts', 'wp-ultimo'),
				'count' => 0,
			),
			'broadcast_email'      => array(
				'field' => 'type',
				'url'   => add_query_arg('type', 'broadcast_email'),
				'label' => __('Emails', 'wp-ultimo'),
				'count' => 0,
			),
			'broadcast_notice'      => array(
				'field' => 'type',
				'url'   => add_query_arg('type', 'broadcast_notice'),
				'label' => __('Notices', 'wp-ultimo'),
				'count' => 0,
			),
		);

	} // end get_views;

} // end class Broadcast_List_Table;
