<?php
/**
 * Validates VAT code against EU servers.
 *
 * @package WP_Ultimo
 * @subpackage Tax
 * @since 2.0.0
 */

// phpcs:disable

namespace WP_Ultimo\Tax;

use \SoapClient;

/**
 * Validates a VAT against the EU site.
 *
 * @since 2.0.0
 */
class Vat_Validation {

	/**
	 * Site address
	 */
	const WSDL = 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

	/**
	 * Holds the client object.
	 *
	 * @since 2.0.0
	 * @var object
	 */
	private $_client = null;

	/**
	 * Options.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	private $_options = array(
		'debug' => false,
	);

	/**
	 * Result of the validation.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	private $_valid = false;

	/**
	 * Information
	 *
	 * @since 2.0.0
	 * @var array
	 */
	private $_data = array();

	/**
	 * Set up.
	 *
	 * @since 2.0.0
	 *
	 * @param array $options array of options for the validator.
	 */
	public function __construct($options = array()) {

		foreach ($options as $option => $value) {

			$this->_options[$option] = $value;

		} // end foreach;

		if (!class_exists('SoapClient')) {

			throw new \Exception('The Soap library has to be installed and enabled');

		} // end if;

		try {

			$this->_client = new SoapClient(self::WSDL, array('trace' => true));

		} catch (Exception $e) {

			$this->trace('Vat Translation Error', $e->getMessage());

		} // end try;

	} // end __construct;

	/**
	 * Check a VAT number.
	 *
	 * @since 2.0.0
	 *
	 * @param string  $country_code The ISO country code.
	 * @param integer $vat_number The VAT number.
	 * @return mixed
	 */
	public function check($country_code, $vat_number) {

		$rs = $this->_client->checkVat(array(
			'countryCode' => $country_code,
			'vatNumber'   => $vat_number
		));

		if ($this->isDebug()) {

			$this->trace('Web Service result', $this->_client->__getLastResponse());

		} // end if;

		if ($rs->valid) {

			$this->_valid = true;

			$name_arr = explode(' ', $rs->name, 2);

			if (count($name_arr) > 1) {

				list($denomination,$name) = $name_arr;

			} else {

				$denomination = $name_arr[0];

				$name = '';

			} // end if;

			$this->_data = array(
				'denomination' => $denomination,
				'name'         => $this->cleanUpString($name),
				'address'      => $this->cleanUpString($rs->address),
			);

			return true;

		} else {

			$this->_valid = false;

			$this->_data = array();

				return false;

		} // end if;

	} // end check;

	/**
	 * Gets the results of the validation.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function isValid() {

		return $this->_valid;

	} // end isValid;

	/**
	 * Get the denomination.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function getDenomination() {

		return $this->_data['denomination'];

	} // end getDenomination;

	/**
	 * Gets the name.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function getName() {

		return $this->_data['name'];

	} // end getName;

	/**
	 * Gets the address.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function getAddress() {

		return $this->_data['address'];

	} // end getAddress;

	/**
	 * Checks if we are in debug mode.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function isDebug() {

		return ($this->_options['debug'] === true);

	} // end isDebug;

	/**
	 * Prints debug message
	 *
	 * @since 2.0.0
	 *
	 * @param string $title The title of the return.
	 * @param string $body The body of the return.
	 * @return void
	 */
	private function trace($title, $body) {

		echo '<h2>TRACE: ' . $title . '</h2><pre>' . htmlentities($body) . '</pre>';

	} // end trace;

	/**
	 * Cleans up string result.
	 *
	 * @since 2.0.0
	 *
	 * @param string $string String to clean.
	 * @return string
	 */
	private function cleanUpString($string) {

		for ($i = 0;$i < 100;$i++) {

			$new_string = str_replace('  ', ' ', $string);

			if ($new_string === $string) {

				break;

			} else {

				$string = $new_string;

			} // end if;

		} // end for;

		$new_string = '';

		$words = explode(' ', $string);

		foreach ($words as $k => $w) {

			$new_string .= ucfirst(strtolower($w)) . ' ';

		} // end foreach;

		return $new_string;

	} // end cleanUpString;

} // end class Vat_Validation;
