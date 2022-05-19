<?php
/**
 * Adds a validation rules that allows us to check if a given parameter is unique.
 *
 * @package WP_Ultimo
 * @subpackage Helpers/Validation_Rules
 * @since 2.0.0
 */

namespace WP_Ultimo\Helpers\Validation_Rules;

use WP_Ultimo\Dependencies\Rakit\Validation\Rule;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds a validation rules that allows us to check if a given parameter is unique.
 *
 * @since 2.0.0
 */
class Unique_Site extends Rule {

	/**
	 * Error message to be returned when this value has been used.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $message = '';

	/**
	 * Parameters that this rule accepts.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $fillableParams = array('self_id'); // phpcs:ignore

	/**
	 * Performs the actual check.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $value Value being checked.
	 * @return boolean
	 */
	public function check($value) : bool { // phpcs:ignore

		$this->requireParameters(array());

		$self_id = $this->parameter('self_id');

		$results = wpmu_validate_blog_signup($value, 'Test Title');

		$results = $this->revalidate_for_hyphens($results);

		if ($results['errors']->has_errors()) {

			$this->message = $results['errors']->get_error_message();

			return false;

		} // end if;

		return true;

	} // end check;

	/**
	 * Allows for hyphens to be used, since WordPress supports it.
	 *
	 * @since 2.0.0
	 *
	 * @param array $result_object The wpmu_validate_blog_signup result.
	 * @return array
	 */
	protected function revalidate_for_hyphens($result_object) {

		$errors = $result_object['errors'];

		$blogname_errors = $errors->get_error_messages('blogname');

		$message_to_ignore = __('Site names can only contain lowercase letters (a-z) and numbers.');

		$error_key = array_search($message_to_ignore, $blogname_errors, true);

		/**
		 * Check if we have an error for only letters and numbers
		 * if so, we remove it and re-validate with our custom rule
		 * which is the same, but also allows for hyphens.
		 */
		if (!empty($blogname_errors) && $error_key !== false) {

			unset($result_object['errors']->errors['blogname'][$error_key]);

			if (empty($result_object['errors']->errors['blogname'])) {

				unset($result_object['errors']->errors['blogname']);

			} // end if;

			if (preg_match('/[^a-z0-9-]+/', $result_object['blogname'])) {

				$result_object['errors']->add('blogname', __('Site names can only contain lowercase letters (a-z), numbers, and hyphens.', 'wp-ultimo'));

			} // end if;

		} // end if;

		return $result_object;

	} // end revalidate_for_hyphens;

} // end class Unique_Site;
