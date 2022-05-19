<?php
/**
 * Takes screenshots from websites.
 *
 * @package WP_Ultimo
 * @subpackage Helper
 * @since 2.0.0
 */

namespace WP_Ultimo\Helpers;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Takes screenshots from websites.
 *
 * @since 2.0.0
 */
class Screenshot {

	/**
	 * Returns the api link for the screenshot.
	 *
	 * @since 2.0.0
	 *
	 * @param string $domain Original site domain.
	 * @return string
	 */
	public static function api_url($domain) {

		$domain = esc_url($domain);

		$url = wu_with_license_key(sprintf('https://api.wpultimo.com/screenshot?url=%s', $domain));

		return $url . '&ext=.png';

	} // end api_url;

	/**
	 * Takes in a URL and creates it as an attachment.
	 *
	 * @since 2.0.0
	 *
	 * @param string $url Image URL to download.
	 * @return string|false
	 */
	public static function take_screenshot($url) {

		$url = self::api_url($url);

		return self::save_image_from_url($url);

	} // end take_screenshot;

	/**
	 * Downloads the image from the URL.
	 *
	 * @since 2.0.0
	 *
	 * @param string $url Image URL to download.
	 * @return int|false
	 */
	public static function save_image_from_url($url) {

		// translators: %s is the API URL.
		$log_prefix = sprintf(__('Downloading image from "%s":'), $url) . ' ';

		$response = wp_remote_get($url, array(
			'timeout' => 50,
		));

		if (wp_remote_retrieve_response_code($response) !== 200) {

			wu_log_add('screenshot-generator', $log_prefix . wp_remote_retrieve_response_message($response));

			return false;

		} // end if;

		if (is_wp_error($response)) {

			wu_log_add('screenshot-generator', $log_prefix . $response);

			return false;

		} // end if;

		/*
		 * Check if the results contain a PNG header.
		 */
		if (strpos($response['body'], "\x89\x50\x4e\x47\x0d\x0a\x1a\x0a") !== 0) {

			wu_log_add('screenshot-generator', $log_prefix . __('Result is not a PNG file.', 'wp-ultimo'));

			return false;

		} // end if;

		$upload = wp_upload_bits('screenshot-' . gmdate('Y-m-d-H-i-s') . '.png', null, $response['body']);

		if (!empty($upload['error'])) {

			wu_log_add('screenshot-generator', $log_prefix . json_encode($upload['error']));

			return false;

		} // end if;

		$file_path        = $upload['file'];
		$file_name        = basename($file_path);
		$file_type        = wp_check_filetype($file_name, null);
		$attachment_title = sanitize_file_name(pathinfo($file_name, PATHINFO_FILENAME));
		$wp_upload_dir    = wp_upload_dir();

		$post_info = array(
			'guid'           => $wp_upload_dir['url'] . '/' . $file_name,
			'post_mime_type' => $file_type['type'],
			'post_title'     => $attachment_title,
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		// Create the attachment
		$attach_id = wp_insert_attachment($post_info, $file_path);

		// Include image.php
		require_once(ABSPATH . 'wp-admin/includes/image.php');

		// Define attachment metadata
		$attach_data = wp_generate_attachment_metadata($attach_id, $file_path);

		// Assign metadata to attachment
		wp_update_attachment_metadata($attach_id, $attach_data);

		wu_log_add('screenshot-generator', $log_prefix . __('Success!', 'wp-ultimo'));

		return $attach_id;

	} // end save_image_from_url;

} // end class Screenshot;
