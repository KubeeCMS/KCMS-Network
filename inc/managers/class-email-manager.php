<?php
/**
 * Email Manager
 *
 * Handles processes related to Emails.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Email_Manager
 * @since 2.0.0
 */

namespace WP_Ultimo\Managers;

use \WP_Ultimo\Managers\Base_Manager;
use \WP_Ultimo\Models\Email;
use \WP_Ultimo\Helpers\Sender;
use \WP_Ultimo\Models\Base_Model;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles processes related to emails.
 *
 * @since 2.0.0
 */
class Email_Manager extends Base_Manager {

	use \WP_Ultimo\Apis\Rest_Api, \WP_Ultimo\Apis\WP_CLI, \WP_Ultimo\Traits\Singleton;

	/**
	 * The manager slug.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $slug = 'email';

	/**
	 * The model class associated to this manager.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $model_class = '\\WP_Ultimo\\Models\\Email';

	/**
	 * All default system emails and their original content.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $registered_default_system_emails;

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		$this->enable_rest_api();

		$this->enable_wp_cli();

		$this->register_all_default_system_emails();

		/*
		 * Adds the Email fields
		 */
		add_action('wu_settings_emails', array($this, 'add_email_fields'));

		add_action('wu_event', array($this, 'send_system_email'), 10, 2);

		/*
		 * Registering a callback action to a schedule send.
		 */
		add_action('wu_send_schedule_system_email', array($this, 'send_schedule_system_email'), 10, 5);

		/*
		 * Create a log when a mail send fails.
		 */
		add_action('wp_mail_failed', array($this, 'log_mailer_failure'));

		add_action('wp_ajax_wu_get_event_payload_placeholders', array($this, 'get_event_placeholders'));

	} // end init;

	/**
	 * Send the email related to the current event.
	 *
	 * @param string $slug Slug of the event.
	 * @param array  $payload Payload of the event.
	 * @return void.
	 */
	public function send_system_email($slug, $payload) {

		$sender = new Sender();

		$all_emails = wu_get_emails(array(
			'event' => $slug,
		));

		$original_from = array(
			'name'  => wu_get_setting('from_name'),
			'email' => wu_get_setting('from_email'),
		);

		/*
		 * Loop through all the emails registered.
		 */
		foreach ($all_emails as $email) {

			if ($email->get_custom_sender()) {

				$from = array(
					'name'  => $email->get_custom_sender_name(),
					'email' => $email->get_custom_sender_email(),
				);

			} else {

				$from = $original_from;

			} // end if;

			/*
			 * Compiles the target list.
			 */
			$to = $email->get_target_list($payload);

			if (empty($to)) {

				wu_log_add('mailer', __('No targets found.', 'wp-ultimo'));

				return;

			} // end if;

			$args = array(
				'style'   => $email->get_style(),
				'content' => $email->get_content(),
				'subject' => get_network_option(null, 'site_name') . ' - ' . $email->get_title(),
				'payload' => $payload,
			);

			/*
			 * Add the invoice attachment, if need be.
			 */
			if (wu_get_isset($payload, 'payment_invoice_url') && wu_get_setting('attach_invoice_pdf', true)) {

				$file_name = 'invoice-' . $payload['payment_reference_code'] . '.pdf';

				$this->attach_file_by_url($payload['payment_invoice_url'], $file_name, $args['subject']);

			} // end if;

			$when_to_send = $email->get_when_to_send();

			if ($when_to_send) {

				$args['schedule'] = $when_to_send;

			} // end if;

			$sender->send_mail($from, $to, $args);

		} // end foreach;

	} // end send_system_email;

	/**
	 * Attach a file by a URL
	 *
	 * @since 2.0.0
	 *
	 * @param string $file_url The URL of the file to attach.
	 * @param string $file_name The name to save the file with.
	 * @param string $email_subject The email subject, to avoid attaching a file to the wrong email.
	 * @return void
	 */
	public function attach_file_by_url($file_url, $file_name, $email_subject = '') {

		add_action('phpmailer_init', function($mail) use ($file_url, $file_name, $email_subject) {

			if ($email_subject && $mail->Subject !== $email_subject) { // phpcs:ignore

				return;

			} // end if;

			$response = wp_remote_get($file_url, array(
				'timeout' => 50,
			));

			if (is_wp_error($response)) {

				return;

			} // end if;

			$file = wp_remote_retrieve_body($response);

			/*
			 * Use the default PHPMailer APIs to attach the file.
			 */
			$mail->addStringAttachment($file, $file_name);

		});

	} // end attach_file_by_url;

	/**
	 * Add all email fields.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_email_fields() {

		wu_register_settings_field('emails', 'sender_header', array(
			'title' => __('Sender Settings', 'wp-ultimo'),
			'desc'  => __('Change the settings of the email headers, like from and name.', 'wp-ultimo'),
			'type'  => 'header',
		));

		wu_register_settings_field('emails', 'from_name', array(
			'title'       => __('"From" Name', 'wp-ultimo'),
			'desc'        => __('How the sender name will appear in emails sent by WP Ultimo.', 'wp-ultimo'),
			'type'        => 'text',
			'placeholder' => get_network_option(null, 'site_name'),
			'default'     => get_network_option(null, 'site_name'),
			'html_attr'   => array(
				'v-model' => 'from_name',
			),
		));

		wu_register_settings_field('emails', 'from_email', array(
			'title'       => __('"From" E-mail', 'wp-ultimo'),
			'desc'        => __('How the sender email will appear in emails sent by WP Ultimo.', 'wp-ultimo'),
			'type'        => 'email',
			'placeholder' => get_network_option(null, 'admin_email'),
			'default'     => get_network_option(null, 'admin_email'),
			'html_attr'   => array(
				'v-model' => 'from_email',
			),
		));

		wu_register_settings_field('emails', 'template_header', array(
			'title' => __('Template Settings', 'wp-ultimo'),
			'desc'  => __('Change the settings of the email templates.', 'wp-ultimo'),
			'type'  => 'header',
		));

		wu_register_settings_field('emails', 'email_template_type', array(
			'title'     => __('Email Templates Style', 'wp-ultimo'),
			'desc'      => __('Choose if email body will be sent using the HTML template or in plain text.', 'wp-ultimo'),
			'type'      => 'select',
			'default'   => 'html',
			'options'   => array(
				'html'  => __('HTML Emails', 'wp-ultimo'),
				'plain' => __('Plain Emails', 'wp-ultimo'),
			),
			'html_attr' => array(
				'v-model' => 'emails_template',
			),
		));

		wu_register_settings_field('emails', 'expiring_header', array(
			'title' => __('Expiring Notification Settings', 'wp-ultimo'),
			'desc'  => __('Change the settings for the expiring notification (trials and subscriptions) emails.', 'wp-ultimo'),
			'type'  => 'header',
		));

		wu_register_settings_field('emails', 'expiring_days', array(
			'title'       => __('Days to Expire', 'wp-ultimo'),
			'desc'        => __('Select when we should send the notification email. If you select 3 days, for example, a notification email will be sent to every membership (or trial period) expiring in the next 3 days. Memberships are checked hourly.', 'wp-ultimo'),
			'type'        => 'number',
			'placeholder' => __('e.g. 3', 'wp-ultimo'),
			'html_attr'   => array(
				'v-model' => 'expiring_days',
			),
		));

	} // end add_email_fields;

	/**
	 * Register in the global variable all the default system emails.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args System email params.
	 * @return void.
	 */
	public function register_default_system_email($args) {

		$this->registered_default_system_emails[$args['slug']] = $args;

	} // end register_default_system_email;

	/**
	 * Create a system email.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args with the system email details to register.
	 * @return bool
	 */
	public function create_system_email($args) {

		if ($this->is_created($args['slug'])) {

			return;

		} // end if;

		$email_args = wp_parse_args($args, array(
			'event'              => '',
			'title'              => '',
			'content'            => '',
			'slug'               => '',
			'target'             => 'admin',
			'style'              => 'use_default',
			'send_copy_to_admin' => true,
			'active'             => true,
			'legacy'             => false,
			'date_registered'    => wu_get_current_time('mysql', true),
			'date_modified'      => wu_get_current_time('mysql', true),
			'status'             => 'publish'
		));

		$email = new Email($email_args);

		$saved = $email->save();

		return is_wp_error($saved) ? $saved : $email;

	} // end create_system_email;

	/**
	 * Register all default system emails.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function create_all_system_emails() {

		$system_emails = wu_get_default_system_emails();

		foreach ($system_emails as $email_key => $email_value) {

			$this->create_system_email($email_value);

		} // end foreach;

	} // end create_all_system_emails;

	/**
	 * Register all default system emails.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function register_all_default_system_emails() {
		/*
		 * Payment Successful - Admin
		 */
		$this->register_default_system_email(array(
			'event'   => 'payment_received',
			'slug'    => 'payment_received_admin',
			'target'  => 'admin',
			'title'   => __('You got a new payment!', 'wp-ultimo'),
			'content' => wu_get_template_contents('emails/admin/payment-received'),
		));

		/*
		 * Payment Successful - Customer
		 */
		$this->register_default_system_email(array(
			'event'   => 'payment_received',
			'slug'    => 'payment_received_customer',
			'target'  => 'customer',
			'title'   => __('We got your payment!', 'wp-ultimo'),
			'content' => wu_get_template_contents('emails/customer/payment-received'),
		));

		/*
		 * Site Published - Admin
		 */
		$this->register_default_system_email(array(
			'event'   => 'site_published',
			'target'  => 'admin',
			'slug'    => 'site_published_admin',
			'title'   => __('A new site was created on your Network!', 'wp-ultimo'),
			'content' => wu_get_template_contents('emails/admin/site-published'),
		));

		/*
		 * Site Published - Customer
		 */
		$this->register_default_system_email(array(
			'event'   => 'site_published',
			'target'  => 'customer',
			'slug'    => 'site_published_customer',
			'title'   => __('Your site is ready!', 'wp-ultimo'),
			'content' => wu_get_template_contents('emails/customer/site-published'),
		));

		/*
		 * Site Published - Customer
		 */
		$this->register_default_system_email(array(
			'event'   => 'confirm_email_address',
			'target'  => 'customer',
			'slug'    => 'confirm_email_address',
			'title'   => __('Confirm your email address!', 'wp-ultimo'),
			'content' => wu_get_template_contents('emails/customer/confirm-email-address'),
		));

		/*
		 * Domain Created - Admin
		 */
		$this->register_default_system_email(array(
			'event'   => 'domain_created',
			'target'  => 'admin',
			'slug'    => 'domain_created_admin',
			'title'   => __('A new domain was added to your Network!', 'wp-ultimo'),
			'content' => wu_get_template_contents('emails/admin/domain-created'),
		));

		do_action('wu_system_emails_after_register');

	} // end register_all_default_system_emails;

	/**
	 * Get a single or all default registered system emails.
	 *
	 * @since 2.0.0
	 *
	 * @param string $slug Default system email slug.
	 * @return array All default system emails.
	 */
	public function get_default_system_emails($slug = '') {

		if ($slug && isset($this->registered_default_system_emails[$slug])) {

			return $this->registered_default_system_emails[$slug];

		} // end if;

		return $this->registered_default_system_emails;

	} // end get_default_system_emails;

	/**
	 * Check if the system email already exists.
	 *
	 * @param mixed $slug Email slug to use as reference.
	 * @return Base_Model|false Return email object or false.
	 */
	public function is_created($slug) {

		return (bool) wu_get_email_by('slug', $slug);

	} // end is_created;

	/**
	 * Get the default template email.
	 *
	 * @since 2.0.0
	 *
	 * @param string $slug With the event slug.
	 * @return array With the email template.
	 */
	public function get_event_placeholders($slug = '') {

		$placeholders = array();

		if (wu_request('email_event')) {

			$slug = wu_request('email_event');

		} // end if;

		if ($slug) {

			$event = wu_get_event_type($slug);

			if ($event) {

				foreach (wu_maybe_lazy_load_payload($event['payload']) as $placeholder => $value) {

					$name = ucwords(str_replace('_', ' ', $placeholder));

					$placeholders[] = array(
						'name'        => $name,
						'placeholder' => $placeholder
					);

				} // end foreach;

			} // end if;

		} // end if;

		if (wu_request('email_event')) {

			wp_send_json($placeholders);

		} else {

			return $placeholders;

		} // end if;

	} // end get_event_placeholders;

	/**
	 * Sends a schedule email.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $to Email targets.
	 * @param string $subject Email subject.
	 * @param string $template Email content.
	 * @param array  $headers Email headers.
	 * @param array  $attachments Email attachments.
	 * @return mixed
	 */
	public function send_schedule_system_email($to, $subject, $template, $headers, $attachments) {

		$sender = new Sender();

		return $sender->send_mail($to, $subject, $template, $headers, $attachments);

	} // end send_schedule_system_email;

	/**
	 * Log failures on the WordPress mailer, just so we have a copy of the issues for debugging.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Error $error The error with the mailer.
	 * @return void.
	 */
	public function log_mailer_failure($error) {

		if (is_wp_error($error)) {

			wu_log_add('mailer-errors', $error->get_error_message());

		} // end if;

	} // end log_mailer_failure;

} // end class Email_Manager;
