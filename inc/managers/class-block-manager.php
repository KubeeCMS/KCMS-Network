<?php
/**
 * Block Manager
 *
 * Manages the registering of gutenberg blocks.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Block
 * @since 2.0.0
 */

namespace WP_Ultimo\Managers;

use WP_Ultimo\Managers\Base_Manager;
use WP_Ultimo\Logger;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles the ajax form registering, rendering, and permissions checking.
 *
 * @since 2.0.0
 */
class Block_Manager extends Base_Manager {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		add_filter('block_categories', array($this, 'add_wp_ultimo_block_category'), 1, 2);

	} // end init;

	/**
	 * Adds wp-ultimo as a Block category on Gutenberg.
	 *
	 * @since 2.0.0
	 *
	 * @param array    $categories List of categories.
	 * @param \WP_Post $post Post being edited.
	 * @return array
	 */
	public function add_wp_ultimo_block_category($categories, $post) {

		return array_merge($categories, array(
			array(
				'slug'  => 'wp-ultimo',
				'title' => __('WP Ultimo', 'wp-ultimo'),
			),
		));

	} // end add_wp_ultimo_block_category;

} // end class Block_Manager;
