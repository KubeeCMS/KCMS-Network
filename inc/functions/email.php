<?php
/**
 * Email Functions
 *
 * Public APIs to load and deal with WP Ultimo email.
 *
 * @author      Arindo Duque
 * @category    Admin
 * @package     WP_Ultimo/Email_Manager
 * @version     2.0.0
 */

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
 * Get all registered system emails.
 *
 * @since 2.0.0
 *
 * @return array With all system emails.
 */
function wu_get_all_system_emails() {

	return \WP_Ultimo\Models\Email::query(array(
		'type__in' => array('system_email'),
	));

} // end wu_get_all_system_emails;

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
function wu_send_mail($from = array(), $to, $args = array()) {

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
