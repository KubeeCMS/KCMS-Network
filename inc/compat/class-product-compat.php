<?php
/**
 * Products Compatibility Layer
 *
 * Handles product compatibility back-ports to WP Ultimo 1.X builds.
 *
 * @package WP_Ultimo
 * @subpackage Compat/Product_Compat
 * @since 2.0.0
 */

namespace WP_Ultimo\Compat;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles product compatibility back-ports to WP Ultimo 1.X builds.
 *
 * @since 2.0.0
 */
class Product_Compat {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		add_filter('wu_product_options_sections', array($this, 'add_legacy_section'), 100, 2);

		add_filter('update_post_metadata', array($this, 'check_update_plan'), 10, 5);

	} // end init;

	/**
	 * Saves meta data from old plugins on the new plugin.
	 *
	 * @since 2.0.0
	 *
	 * @param null   $null Short-circuit control.
	 * @param int    $object_id Object ID, in this case, of the Post object.
	 * @param string $meta_key The meta key being saved.
	 * @param mixed  $meta_value The meta value.
	 * @param mixed  $prev_value The previous value.
	 * @return null
	 */
	public function check_update_plan($null, $object_id, $meta_key, $meta_value, $prev_value) {
		/*
		 * Check if we are in the main site of the network.
		 */
		if (!is_main_site()) {

			return;

		} // end if;

		/*
		 * Check if we have a new entity with this ID.
		 */
		$migrated_product = wu_get_product($object_id);

		if (!$migrated_product) {

			return;

		} // end if;

		/*
		 * Prevent double prefixing.
		 */
		$meta_key = str_replace('wpu_', '', $meta_key);

		/*
		 * Save using the new meta table.
		 */
		$migrated_product->update_meta('wpu_' . $meta_key, maybe_serialize($meta_value));

		/**
		 * Explicitly returns null so we don't forget that
		 * returning anything else will prevent meta data from being saved.
		 */
		return null;

	} // end check_update_plan;

	/**
	 * Injects the compatibility panels to products Advanced Options.
	 *
	 * @since 2.0.0
	 *
	 * @param array                     $sections List of tabbed widget sections.
	 * @param \WP_Ultimo\Models\Product $object The model being edited.
	 * @return array
	 */
	public function add_legacy_section($sections, $object) {

		$sections['legacy_options_core'] = array(
			'title'  => __('Legacy Options', 'wp-ultimo'),
			'desc'   => __('Options used by old 1.X versions. ', 'wp-ultimo'),
			'icon'   => 'dashicons-wu-spreadsheet',
			'state'  => array(
				'legacy_options' => $object->is_migrated(),
			),
			'fields' => array(
				'legacy_options' => array(
					'type'      => 'toggle',
					'value'     => $object->is_migrated(),
					'title'     => __('Toggle Legacy Options', 'wp-ultimo'),
					'desc'      => __('Toggle this option to edit legacy options.', 'wp-ultimo'),
					'html_attr' => array(
						'v-model' => 'legacy_options',
					),
				),
				'featured_plan'       => array(
					'type'              => 'toggle',
					'value'             => $object->is_featured_plan(),
					'title'             => __('Featured Plan', 'wp-ultimo'),
					'desc'              => __('Toggle this option to mark this product as featured on the legacy pricing tables.', 'wp-ultimo'),
					'wrapper_html_attr' => array(
						'v-show' => 'legacy_options',
					),
				),
				'feature_list'   => array(
					'type'              => 'textarea',
					'title'             => __('Features List', 'wp-ultimo'),
					'placeholder'       => __('Feature 1', 'wp-ultimo') . PHP_EOL . __('Feature 2', 'wp-ultimo'),
					'tooltip'           => __('Add a feature per line. These will be shown on the pricing tables.', 'wp-ultimo'),
					'value'             => $object->get_feature_list(),
					'wrapper_html_attr' => array(
						'v-show' => 'legacy_options',
					),
					'html_attr'         => array(
						'rows' => 6,
					),
				),
			),
		);

		return $sections;

	} // end add_legacy_section;

} // end class Product_Compat;
