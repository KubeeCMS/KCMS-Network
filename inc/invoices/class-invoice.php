<?php
/**
 * Handles the generation of PDF Invoices.
 *
 * @package WP_Ultimo
 * @subpackage Invoices
 * @since 2.0.0
 */

namespace WP_Ultimo\Invoices;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles the generation of PDF Invoices.
 *
 * @since 2.0.0
 */
class Invoice {

	/**
	 * Keeps the settings key for the top-bar.
	 */
	const KEY = 'invoice_settings';

	/**
	 * Order object to generate the line items.
	 *
	 * @since 2.0.0
	 * @var \WP_Ultimo\Models\Payment
	 */
	protected $payment;

	/**
	 * The invoice attributes.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $attributes;

	/**
	 * Instance of the printer. For now, we use mPDF.
	 *
	 * @since 2.0.0
	 * @var \Mpdf\Mpdf
	 */
	protected $printer;

	/**
	 * Constructs the invoice object.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Checkout\Cart $payment The payment.
	 * @param array                    $atts Attributes to make available on template.
	 */
	public function __construct($payment, $atts = array()) {

		$this->set_payment($payment);

		$saved_atts = Invoice::get_settings();

		$atts = array_merge($saved_atts, $atts);

		$this->set_attributes($atts);

	} // end __construct;

	/**
	 * Magic getter for attributes.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key The attribute name to get.
	 * @return mixed
	 */
	public function __get($key) {

		return isset($this->attributes[$key]) ? $this->attributes[$key] : '';

	} // end __get;

	/**
	 * Setups the printer object. Uses mPdf.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private function pdf_setup() {

		$this->printer = new \Mpdf\Mpdf(array(
			'tempDir' => get_temp_dir(),
		));

		$this->printer->setDefaultFont($this->font);

		$this->printer->SetProtection(array('print'));

		$this->printer->SetTitle(__('Invoice', 'wp-ultimo'));

		$this->printer->SetAuthor($this->company_name);

		if (!$this->payment->is_payable()) {

			$this->printer->SetWatermarkText($this->paid_tag_text);

		} // end if;

		$this->printer->showWatermarkText = true;

		$this->printer->watermarkTextAlpha = 0.1;

		$this->printer->watermark_font = $this->font;

		$this->printer->SetDisplayMode('fullpage');

		if ($this->footer_message) {

			$this->printer->SetHTMLFooter($this->footer_message);

		} // end if;

	} // end pdf_setup;

	/**
	 * Saves the PDF file to the disk.
	 *
	 * @since 2.0.0
	 *
	 * @param boolean $file_name The name of the file. Should include the .pdf extension.
	 * @return void
	 */
	public function save_file($file_name) {

		$file_name = self::get_folder() . $file_name;

		$this->pdf($file_name);

	} // end save_file;

	/**
	 * Prints the PDF file to the browser.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function print_file() {

		$this->pdf();

	} // end print_file;

	/**
	 * Generates the HTML content of the Invoice template.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function render() {

		$atts = $this->attributes;

		$atts['payment'] = $this->payment;

		$atts['line_items'] = $this->payment->get_line_items();

		$atts['membership'] = $this->payment->get_membership();

		$atts['billing_address'] = $atts['membership'] ? $atts['membership']->get_billing_address()->to_array() : array();

		return wu_get_template_contents('invoice/template', $atts);

	} // end render;

	/**
	 * Handles the PDF generation.
	 *
	 * @since 2.0.0
	 *
	 * @param string|false $file_name The file name, to save. Empty or false to print to the browser.
	 * @return void
	 */
	protected function pdf($file_name = false) {

		wu_setup_memory_limit_trap();

		wu_try_unlimited_server_limits();

		$this->pdf_setup();

		$this->printer->WriteHTML($this->render());

		if ($file_name) {

			$this->printer->Output($file_name, \Mpdf\Output\Destination::FILE);

		} else {

			$this->printer->Output();

		} // end if;

	} // end pdf;

	/**
	 * Get the value of payment.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_payment() {

		return $this->payment;

	} // end get_payment;

	/**
	 * Set the value of payment.
	 *
	 * @since 2.0.0
	 * @param mixed $payment The Order object to add to the invoice.
	 * @return void
	 */
	public function set_payment($payment) {

		$this->payment = $payment;

	} // end set_payment;

	/**
	 * Get the value of attributes.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_attributes() {

		return $this->attributes;

	} // end get_attributes;

	/**
	 * Set the value of attributes.
	 *
	 * @since 2.0.0
	 * @param mixed $attributes The list of attributes to add to the invoice.
	 * @return void
	 */
	public function set_attributes($attributes) {

		$attributes = wp_parse_args($attributes, array(
			'company_name'    => wu_get_setting('company_name'),
			'company_address' => wu_get_setting('company_address'),
			'primary_color'   => '#675645',
			'font'            => 'DejaVuSansCondensed',
			'logo_url'        => wu_get_network_logo(),
			'use_custom_logo' => false,
			'custom_logo'     => false,
 			'footer_message'  => '',
			'paid_tag_text'   => __('Paid', 'wp-ultimo'),
		));

		$this->attributes = $attributes;

	} // end set_attributes;

	/**
	 * Generates the folder to keep invoices and returns the path.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public static function get_folder() {

		return wu_maybe_create_folder('wu-invoices');

	} // end get_folder;

	/**
	 * Returns the list of saved settings to customize the invoices..
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public static function get_settings() {

		return wu_get_option(Invoice::KEY, array());

	} // end get_settings;

	/**
	 * Save settings.
	 *
	 * @since 2.0.0
	 *
	 * @param array $settings_to_save List of settings to save.
	 * @return boolean
	 */
	public static function save_settings($settings_to_save) {

		$invoice = new self(false);

		$allowed_keys = array_keys($invoice->get_attributes());

		foreach ($settings_to_save as $setting_to_save => $value) {

			if (!in_array($setting_to_save, $allowed_keys, true)) {

				unset($settings_to_save[$setting_to_save]);

			} // end if;

		} // end foreach;

		return wu_save_option(Invoice::KEY, $settings_to_save);

	} // end save_settings;

} // end class Invoice;
