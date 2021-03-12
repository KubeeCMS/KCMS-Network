<?php
/**
 * Handles limitations to post types, uploads and more.
 *
 * @todo We need to move posts on downgrade.
 * @package WP_Ultimo
 * @subpackage Limits
 * @since 2.0.0
 */

namespace WP_Ultimo\Limits;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles limitations to post types, uploads and more.
 *
 * @since 2.0.0
 */
class Post_Type_Limits {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Runs on the first and only instantiation.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		/**
		 * Allow plugin developers to short-circuit the limitations.
		 *
		 * You can use this filter to run arbitrary code before any of the limits get initiated.
		 * If you filter returns any truthy value, the process will move on, if it returns any falsy value,
		 * the code will return and none of the hooks below will run.
		 *
		 * @since 1.7.0
		 * @param WU_Plan|false Current plan object
		 * @param integer User ID
		 */
		if (!apply_filters('wu_apply_plan_limits', wu_get_current_site()->has_limitations())) {

			return;

		} // end if;

		if (!wu_get_current_site()->has_module_limitation('post_types')) {

			return;

		} // end if;

		add_action('load-post-new.php', array($this, 'limit_posts'));

		add_filter('wp_handle_upload', array($this, 'limit_media'));

		add_filter('media_upload_tabs', array($this, 'limit_tabs'));

		add_action('current_screen', array($this, 'limit_restoring'), 10);

		add_filter('wp_insert_post_data', array($this, 'limit_draft_publishing'), 10, 2);

	} // end init;

	/**
	 * Prevents users from trashing posts and restoring them later to bypass the limitation.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function limit_restoring() {

		if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'untrash') {

			$this->limit_posts();

		} // end if;

	} // end limit_restoring;

	/**
	 * Limit the posts after the user reach his plan limits
	 *
	 * @since 1.0.0
	 * @since 1.5.4 Checks for blocked post types
	 */
	public function limit_posts() {

		if (is_main_site()) {

			return;

		} // end if;

		$screen = get_current_screen();

		if (!wu_get_current_site()->is_post_type_supported($screen->post_type)) {

			// translators: %s is the URL.
			wp_die(sprintf(__('Your plan does not support this post type.', 'wp-ultimo'), '#'), __('Limit Reached', 'wp-ultimo'), array('back_link' => true));

		} // end if;

		// Check if that is more than our limit
		if (wu_get_current_site()->is_post_above_limit($screen->post_type)) {

			// Display Errors Message
			// TODO: display a better error message
			// translators: %s is the URL
			wp_die(sprintf(__('You reached your plan\'s post limit.', 'wp-ultimo'), '#'), __('Limit Reached', 'wp-ultimo'), array('back_link' => true));

		} // end if;

	} // end limit_posts;

	/**
	 * Checks if the user is trying to publish a draft post.
	 *
	 * If that's the case, only allow him to do it if the post count is not above the quota.
	 *
	 * @since 1.7.0
	 * @param array $data Info being saved on posts.
	 * @param array $modified_data Data that is changing. We are interested in publish.
	 * @return array
	 */
	public function limit_draft_publishing($data, $modified_data) {

		if (get_post_status($modified_data['ID']) === 'publish') {

			return $data; // If the post is already published, no need to make changes

		} // end if;

		if (isset($data['post_status']) && $data['post_status'] !== 'publish') {

			return $data;

		} // end if;

		$post_type = isset($data['post_type']) ? $data['post_type'] : 'post';

		if (!wu_get_current_site()->is_post_type_supported($post_type) || wu_get_current_site()->is_post_above_limit($post_type)) {

			$data['post_status'] = 'draft';

		} // end if;

		return $data;

	} // end limit_draft_publishing;

	/**
	 * Limits uploads of items to the media library.
	 *
	 * @since 2.0.0
	 *
	 * @param array $file $_FILE array being passed.
	 * @return mixed
	 */
	public function limit_media($file) {

		$post_count = wp_count_posts('attachment');

		$post_count = $post_count->inherit;

		$quota = wu_get_current_site()->get_quota('attachment');

		// This bit is for the flash uploader
		if ($file['type'] === 'application/octet-stream' && isset($file['tmp_name'])) {

			$file_size = getimagesize($file['tmp_name']);

			if (isset($file_size['error']) && $file_size['error'] !== 0) {

				$file['error'] = "Unexpected Error: {$file_size['error']}";

				return $file;

			} else {

				$file['type'] = $file_size['mime'];

			} // end if;

		} // end if;

		if ($quota > 0 && $post_count >= $quota) {

			// translators: %d is the number of images allowed.
			$file['error'] = sprintf(__('You reached your media upload limit of %d images. Upgrade your account to unlock more media uploads.', 'wp-ultimo'), $quota, '#');

		} // end if;

		return $file;

	} // end limit_media;

	/**
	 * Remove the upload tabs if the quota is over.
	 *
	 * @since 2.0.0
	 *
	 * @param array $tabs Tabs of the media gallery upload modal.
	 * @return array
	 */
	public function limit_tabs($tabs) {

		$post_count = wp_count_posts('attachment');

		$post_count = $post_count->inherit;

		$quota = wu_get_current_site()->get_quota('attachment');

		if ($quota > 0 && $post_count > $quota) {

			unset($tabs['type']);

			unset($tabs['type_url']);

		} // end if;

		return $tabs;

	} // end limit_tabs;

} // end class Post_Type_Limits;
