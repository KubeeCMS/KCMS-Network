<?php
/**
 * Email Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Models\Email;

/**
 * Returns a email.
 *
 * @since 2.0.0
 *
 * @param int $email_id The id of the email. This is not the user ID.
 * @return \WP_Ultimo\Models\Email|false
 */
function wu_get_email($email_id) {

	return \WP_Ultimo\Models\Email::get_by_id($email_id);

} // end wu_get_email;

/**
 * Returns a single email defined by a particular column and value.
 *
 * @since 2.0.0
 *
 * @param string $column The column name.
 * @param mixed  $value The column value.
 * @return \WP_Ultimo\Models\Email|false
 */
function wu_get_email_by($column, $value) {

	return \WP_Ultimo\Models\Email::get_by($column, $value);

} // end wu_get_email_by;

/**
 * Queries emails.
 *
 * @since 2.0.0
 *
 * @param array $query Query arguments.
 * @return \WP_Ultimo\Models\Email[]
 */
function wu_get_emails($query = array()) {

	$query['type__in'] = array('system_email');

	if (wu_get_isset($query, 'event')) {

		$query['meta_query'] = array(
			'event_query' => array(
				'key'   => 'wu_system_email_event',
				'value' => wu_get_isset($query, 'event'),
			),
		);

	} // end if;

	return \WP_Ultimo\Models\Email::query($query);

} // end wu_get_emails;

/**
 * Get all saved system email.
 *
 * @since 2.0.0
 *
 * @return array With all system emails.
 */
function wu_get_all_system_emails() {

	return \WP_Ultimo\Models\Email::query(array(
		'status__in' => array('draft', 'publish'),
		'type__in'   => array('system_email'),
	));

} // end wu_get_all_system_emails;

/**
 * Get a single or all default registered system emails.
 *
 * @since 2.0.0
 *
 * @param string $slug Default system email slug.
 * @return array All default system emails.
 */
function wu_get_default_system_emails($slug = '') {

	return \WP_Ultimo\Managers\Email_Manager::get_instance()->get_default_system_emails($slug);

} // end wu_get_default_system_emails;

/**
 * Create a single default system email.
 *
 * @since 2.0.0
 *
 * @param string $slug Default system email slug to be create.
 * @return array
 */
function wu_create_default_system_email($slug) {

	$args = wu_get_default_system_emails($slug);

	return \WP_Ultimo\Managers\Email_Manager::get_instance()->create_system_email($args);

} // end wu_create_default_system_email;

/**
 * Send an email to one or more users.
 *
 * @since 2.0.0
 *
 * @param array $from From whom will be send this mail.
 * @param mixed $to   To who this email is.
 * @param array $args With content, subject and other arguments, has shortcodes, mail type.
 * @return array
 */
function wu_send_mail($from = array(), $to = array(), $args = array()) {

	$sender = new \WP_Ultimo\Helpers\Sender;

	return $sender->send_mail($from, $to, $args);

} // end wu_send_mail;

/**
 * Returns email-like strings.
 *
 * E.g.: Robert Smith <robert@rs.org>
 *
 * @since 2.0.0
 *
 * @param string       $email The email address.
 * @param false|string $name The customer/user display name.
 * @return string
 */
function wu_format_email_string($email, $name = false) {

	return $name ? sprintf('%s <%s>', $name, $email) : $email;

} // end wu_format_email_string;

/**
 * Creates a new email.
 *
 * Check the wp_parse_args below to see what parameters are necessary.
 *
 * @since 2.0.0
 *
 * @param array $email_data Email attributes.
 * @return \WP_Error|\WP_Ultimo\Models\Email
 */
function wu_create_email($email_data) {

	$email_data = wp_parse_args($email_data, array(
		'type'          => 'system_email',
		'event'         => 'Laborum consectetur',
		'title'         => 'Lorem Ipsum',
		'slug'          => 'lorem-ipsum',
		'target'        => 'admin',
		'date_created'  => wu_get_current_time('mysql', true),
		'date_modified' => wu_get_current_time('mysql', true),
	));

	$email = new Email($email_data);

	$saved = $email->save();

	return is_wp_error($saved) ? $saved : $email;

} // end wu_create_email;
