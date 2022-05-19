<?php
/**
 * Contains deprecated functions.
 *
 * @package WP_Ultimo
 * @subpackage Deprecated
 * @since 2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/*
 * Classes
 */

/**
 * Deprecated: WU_Settings
 *
 * @deprecated 2.0.0
 */
class WU_Settings {

	/**
	 * Deprecated: Returns all the sections of settings.
	 *
	 * @deprecated 2.0.0
	 *
	 * @param bool $filters Deprecated argument.
	 * @return array
	 */
	public static function get_sections($filters = true) {

		_deprecated_function(__METHOD__, '2.0.0', 'WP_Ultimo()->settings->get_sections()');

		return WP_Ultimo()->settings->get_sections();

	} // end get_sections;

	/**
	 * Deprecated: Get all the settings from the plugin.
	 *
	 * @deprecated 2.0.0
	 *
	 * @return array Array containing all the settings.
	 */
	public static function get_settings() {

		_deprecated_function(__METHOD__, '2.0.0', 'wu_get_all_settings()');

		return wu_get_all_settings();

	} // end get_settings;

	/**
	 * Deprecated: Handles the saving of the settings after the save button is pressed.
	 *
	 * @deprecated 2.0.0
	 *
	 * @param boolean $deprecated Deprecated argument.
	 * @param boolean $reset If we need to reset the settings.
	 * @return bool
	 */
	public static function save_settings($deprecated = false, $reset = false) {

		_deprecated_function(__METHOD__, '2.0.0', 'WP_Ultimo()->settings->save_settings()');

		return WP_Ultimo()->settings->save_settings(array(), $reset);

	} // end save_settings;

	/**
	 * Deprecated: Get a specific settings from the plugin.
	 *
	 * @since  1.4.0 Now we can filter settings we get.
	 * @since  1.1.5 Let's we pass default responses, in case nothing is found.
	 * @deprecated 2.0.0
	 *
	 * @param  string $setting Settings name to return.
	 * @param  string $default Default value.
	 * @return string
	 */
	public static function get_setting($setting, $default = false) {

		_deprecated_function(__METHOD__, '2.0.0', 'wu_get_setting()');

		return wu_get_setting($setting, $default);

	} // end get_setting;

	/**
	 * Deprecated: Saves a specific setting into the database.
	 *
	 * @param string $setting Option key to save.
	 * @param mixed  $value   New value of the option.
	 * @return mixed
	 */
	public static function save_setting($setting, $value) {

		_deprecated_function(__METHOD__, '2.0.0', 'wu_save_setting()');

		return wu_save_setting($setting, $value);

	} // end save_setting;

	/**
	 * Deprecated: Returns the image being used as a logo.
	 *
	 * @since  1.7.0 Added setting option.
	 * @since  1.1.5 Return the default in case.
	 * @deprecated 2.0.0
	 *
	 * @param  string $size The size to retrieve the logo.
	 * @param  null   $logo Deprecated argument.
	 * @param  null   $setting_name Deprecated argument.
	 * @param  null   $fallback Deprecated argument.
	 * @return string
	 */
	public static function get_logo($size = 'full', $logo = null, $setting_name = null, $fallback = null) {

		_deprecated_function(__METHOD__, '2.0.0', 'wu_get_network_logo()');

		return wu_get_network_logo($size);

	} // end get_logo;

	/**
	 * Deprecated: Return the countries list.
	 *
	 * @since 1.5.4
	 * @return array
	 */
	public static function get_countries() {

		_deprecated_function(__METHOD__, '2.0.0', 'wu_get_countries()');

		return wu_get_countries();

	} // end get_countries;

} // end class WU_Settings;

/**
 * Deprecated: WU_Page
 *
 * @deprecated 2.0.0
 */
class WU_Page extends \WP_Ultimo\Admin_Pages\Base_Admin_Page {

	/**
	 * Holds the attributes.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * Deprecated: Creates the page with the necessary hooks
	 *
	 * @deprecated 2.0.0
	 * @since 1.8.2
	 *
	 * @param boolean $network If this is a network page.
	 * @param array   $atts The page attributes.
	 */
	public function __construct($network = true, $atts = array()) {

		_deprecated_function(__METHOD__, '2.0.0', 'wu_send_mail()');

		$this->attributes = wp_parse_args($atts, array(
			'badge_count'   => 0,
			'position'      => 10,
			'submenu_title' => false,
			'id'            => 'wp-ultimo-page',
			'type'          => 'menu',
			'parent'        => 'wp-ultimo',
			'capability'    => 'manage_network',
			'menu_icon'     => 'dashicons-menu',
			'title'         => __('Admin Page', 'wp-ultimo'),
			'menu_title'    => __('Admin Page', 'wp-ultimo'),
		));

		/*
		 * Sets the defaults.
		 */
		$this->position    = $this->attributes['position'];
		$this->badge_count = $this->attributes['badge_count'];
		$this->parent      = $this->attributes['parent'];
		$this->menu_icon   = $this->attributes['menu_icon'];
		$this->type        = $this->attributes['type'];
		$this->id          = $this->attributes['id'] . '-one';

		parent::__construct();

	} // end __construct;

	/**
	 * Returns the title of the page. Must be declared on the child classes.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return $this->attributes['title'];

	} // end get_title;

	/**
	 * Returns the title of menu for this page. Must be declared on the child classes.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return $this->attributes['menu_title'];

	} // end get_menu_title;

	/**
	 * Every child class should implement the output method to display the contents of the page.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function output() {} // end output;

} // end class WU_Page;

/**
 * Deprecated: WU_Site_Templates
 *
 * @deprecated 2.0.0
 */
class WU_Site_Templates {

	/**
	 * Returns the template preview URL.
	 *
	 * @since 2.0.0
	 *
	 * @param string $site_id The site ID.
	 * @return string
	 */
	public static function get_template_preview_url($site_id = '') {

		_deprecated_function(__METHOD__, '2.0.0', 'WP_Ultimo\UI\Template_Previewer::get_instance()->get_preview_url()');

		return \WP_Ultimo\UI\Template_Previewer::get_instance()->get_preview_url($site_id);

	} // end get_template_preview_url;

} // end class WU_Site_Templates;

/**
 * Deprecated: WU_Mail
 *
 * @deprecated 2.0.0
 */
class WU_Mail {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Deprecated: Send our mail using WordPress.
	 *
	 * @deprecated 2.0.0
	 *
	 * @param  string $to destinatary email.
	 * @param  string $subject Subject line.
	 * @param  string $content Body of the message.
	 * @param  bool   $html Body of the message.
	 * @param  array  $shortcodes The payload. Key => value.
	 * @param  array  $attachments Attachments.
	 * @param  array  $bcc Bcc.
	 * @return boolean
	 */
	public function send_mail($to, $subject, $content, $html = true, $shortcodes = array(), $attachments = array(), $bcc = '') {

		_deprecated_function(__METHOD__, '2.0.0', 'wu_send_mail()');

		$from = array(
			'name'  => wu_get_setting('from_name'),
			'email' => wu_get_setting('from_email'),
		);

		/*
		 * Constructs the backwards compatible array.
		 */
		$args = array(
			'payload'     => $shortcodes,
			'subject'     => $subject,
			'content'     => $content,
			'site_name'   => get_network_option(null, 'site_name'),
			'site_url'    => get_site_url(wu_get_main_site_id()),
			'attachments' => $attachments,
			'type'        => $html ? 'html' : 'plain',
			'bcc'         => $bcc
		);

		return wu_send_mail($from, $to, $args);

	} // end send_mail;

	/**
	 * Deprecated: Send an email template registered in our framework.
	 *
	 * @deprecated 2.0.0
	 *
	 * @param string $slug The slug identifying the template to be sent.
	 * @param string $to   Recipient's email address.
	 * @param  array  $shortcodes The payload. Key => value.
	 * @param  array  $attachments Attachments.
	 * @return void
	 */
	public function send_template($slug, $to, $shortcodes, $attachments = array()) {

		_deprecated_function(__METHOD__, '2.0.0');

	} // end send_template;

	/**
	 * Deprecated: Register template of a certain email.
	 *
	 * @deprecated 2.0.0
	 *
	 * @param  string $slug Indentifier of this template.
	 * @param  string $args Array with the arguments.
	 * @return void
	 */
	public function register_template($slug, $args) {

		_deprecated_function(__METHOD__, '2.0.0');

	} // end register_template;

} // end class WU_Mail;

/**
 * Deprecated: Returns the WU_Mail instance.
 *
 * @deprecated 2.0.0
 * @since 2.0.0
 * @return WU_Mail
 */
function WU_Mail() { // phpcs:ignore

	_deprecated_function(__METHOD__, '2.0.0');

	return WU_Mail::get_instance();

} // end WU_Mail;

/**
 * Deprecated: WU_Plans class.
 */
class WU_Plans {

	/**
	 * Deprecated.
	 *
	 * Here to prevent fatal errors.
	 *
	 * @since 2.0.0
	 * @return \WU_Plans
	 */
	public static function get_instance() {

		return new self;

	} // end get_instance;

	/**
	 * Deprecated: WP_Plans::get_plans().
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public static function get_plans() {

		_deprecated_function(__METHOD__, '2.0.0', 'wu_get_plans()');

		$plans = wu_get_plans();

		return $plans;

	} // end get_plans;

	/**
	 * Deprecated: WU_Plans::get_most_popular_plan()
	 *
	 * Returns any plan to avoid problems.
	 * This method should not be used.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Product
	 */
	public static function get_most_popular_plan() {

		_deprecated_function(__METHOD__, '2.0.0');

		$plans = wu_get_plans();

		return $plans ? $plans[0] : false;

	} // end get_most_popular_plan;

} // end class WU_Plans;

/**
 * Deprecated: WU_Multi_Network
 *
 * This class was used to add support to multi-network environments.
 * This is no longer necessary as it is natively supported by BerlinDB.
 * That being the case, we're adding this here because it contained some
 * helper static methods that other plugins might be using, so we're deprecating them
 * generically.
 *
 * @deprecated 2.0.0
 */
class WU_Multi_Network {

	/**
	 * Catch-all for all static methods to deprecate.
	 *
	 * @since 2.0.0
	 *
	 * @param string $method_name Method being called.
	 * @param array  $args The arguments passed to that method.
	 * @return bool
	 */
	public static function __callStatic($method_name, $args) {

		_deprecated_function(__CLASS__ . "::$method_name()", '2.0.0');

		return false;

	} // end __callStatic;

} // end class WU_Multi_Network;

/**
 * Deprecated: WU_Help_Pointers
 *
 * There is plans to re-add something like this in the future.
 * For now, this is deprecated and declaring it have no side-effects.
 * This class is here to prevent fatal errors when plugin developers
 * used it on their WP Ultimo extensions.
 *
 * @deprecated 2.0.0
 */
class WU_Help_Pointers {

	/**
	 * Deprecated constructor.
	 *
	 * @since 2.0.0
	 * @param array $pntrs The pointers to add.
	 */
	public function __construct($pntrs = array()) {

		_deprecated_function(__CLASS__, '2.0.0');

	} // end __construct;

} // end class WU_Help_Pointers;

/**
 * Deprecated: WU_Util
 *
 * This class is being explicitly replaced by public apis
 * in the form of simple functions that are available at the global scope.
 *
 * @deprecated 2.0.0
 */
class WU_Util {

	/**
	 * Deprecated: is_login_page()
	 *
	 * @deprecated 2.0.0
	 * @return boolean
	 */
	public static function is_login_page() {

		_deprecated_function(__METHOD__, '2.0.0');

		return false;

	} // end is_login_page;

	/**
	 * Deprecated: format_megabytes
	 *
	 * @deprecated 2.0.0
	 *
	 * @param int  $size The size in Megabytes. Size format uses bytes, instead.
	 * @param null $after_suffix Deprecated argument.
	 * @return string
	 */
	public static function format_megabytes($size, $after_suffix = null) {

		_deprecated_function(__METHOD__, '2.0.0', 'size_format()');

		return size_format($size * MB_IN_BYTES);

	} // end format_megabytes;

	/**
	 * Deprecated: to_float
	 *
	 * @deprecated 2.0.0
	 *
	 * @param string $num Numeric string to convert to float. E.g. $500.00, 5.000,00, etc.
	 * @return float
	 */
	public static function to_float($num) {

		_deprecated_function(__METHOD__, '2.0.0', 'wu_to_float()');

		return wu_to_float($num);

	} // end to_float;

	/**
	 * Deprecated: tooltip
	 *
	 * @deprecated 2.0.0
	 *
	 * @param string $text The tooltip text.
	 * @param string $icon The tooltip icon.
	 * @return string
	 */
	public static function tooltip($text, $icon = 'dashicons-editor-help') {

		_deprecated_function(__METHOD__, '2.0.0', 'wu_tooltip()');

		return wu_tooltip($text, $icon);

	} // end tooltip;

	/**
	 * Deprecated: wp_die
	 *
	 * @deprecated 2.0.0
	 *
	 * @param string  $message The message to display on the error page.
	 * @param string  $title The title of the error page.
	 * @param boolean $redirect Deprecated argument.
	 * @param integer $time Deprecated argument.
	 * @param array   $args Arguments to pass down to wp_die.
	 * @return void
	 */
	public static function wp_die($message, $title, $redirect = false, $time = 5000, $args = array()) {

		_deprecated_function(__METHOD__, '2.0.0', 'wp_die()');

		wp_die($message, $title, $args);

	} // end wp_die;

	/**
	 * Deprecated: display_alert
	 *
	 * @deprecated 2.0.0
	 *
	 * @param string  $title Deprecated argument.
	 * @param string  $message Deprecated argument.
	 * @param string  $type Deprecated argument.
	 * @param boolean $arguments Deprecated argument.
	 * @return void
	 */
	public static function display_alert($title, $message, $type = 'success', $arguments = false) {

		_deprecated_function(__METHOD__, '2.0.0');

	} // end display_alert;

	/**
	 * Deprecated: registers_today
	 *
	 * @deprecated 2.0.0
	 * @return int
	 */
	public static function registers_today() {

		_deprecated_function(__METHOD__, '2.0.0', 'wu_get_customers() w/ count = true');

		$signups = wu_get_customers(array(
			'count'      => true,
			'date_query' => array(
				'column'    => 'date_registered',
				'after'     => 'today',
				'inclusive' => true,
			),
		));

		return $signups;

	} // end registers_today;

	/**
	 * Deprecated: users_on_trial
	 *
	 * @deprecated 1.5.3
	 * @return int
	 */
	public static function users_on_trial() {

		_deprecated_function(__METHOD__, '1.5.3');

		return 0;

	} // end users_on_trial;

	/**
	 * Deprecated: array_filter_key
	 *
	 * This deprecated method returns the original array passed, with
	 * no processing.
	 *
	 * @deprecated 2.0.0
	 *
	 * @param array        $array Array to filter.
	 * @param string|array $callback Deprecated argument.
	 * @return array
	 */
	public static function array_filter_key(array $array, $callback) {

		_deprecated_function(__METHOD__, '2.0.0');

		return $array;

	} // end array_filter_key;

	/**
	 * Deprecated: generate_csv
	 *
	 * @deprecated 2.0.0
	 *
	 * @param string $file_name Full path to file, including extension.
	 * @param array  $data Data to save. First column being the headers.
	 * @return void
	 */
	public static function generate_csv($file_name, $data = array()) {

		_deprecated_function(__METHOD__, '2.0.0', 'wu_generate_csv()');

		wu_generate_csv($file_name, $data);

	} // end generate_csv;

	/**
	 * Deprecated: color
	 *
	 * @deprecated 2.0.0
	 *
	 * @param string $hex Hex code of the color.
	 * @return \WP_Ultimo\Dependencies\Mexitek\PHPColors\Color
	 */
	public static function color($hex) {

		_deprecated_function(__METHOD__, '2.0.0', 'wu_color()');

		return wu_color($hex);

	} // end color;

} // end class WU_Util;

/**
 * Deprecated: WU_Logger
 *
 * @deprecated 2.0.0
 */
class WU_Logger {

	/**
	 * Catch-all for all static methods to deprecate.
	 *
	 * The add and clear methods have their own public apis, so we
	 * use them instead.
	 *
	 * @deprecated 2.0.0
	 *
	 * @param string $method_name Method being called.
	 * @param array  $args The arguments passed to that method.
	 * @return bool
	 */
	public static function __callStatic($method_name, $args) {

		$alternative = "\\WP_Ultimo\\Logger::$method_name";

		if ($method_name === 'add') {

			$alternative = 'wu_log_add';

		} elseif ($method_name === 'clear') {

			$alternative = 'wu_log_clear';

		} // end if;

		_deprecated_function(__CLASS__ . "::$method_name()", '2.0.0', "$alternative()");

		return call_user_func_array($alternative, $args);

	} // end __callStatic;

} // end class WU_Logger;

/**
 * Deprecated: WU_Links
 *
 * @deprecated 2.0.0
 */
class WU_Links {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Deprecated: get_link
	 *
	 * @deprecated 2.0.0
	 *
	 * @param string $slug Slug of the link.
	 * @return string
	 */
	public function get_link($slug) {

		_deprecated_function(__METHOD__, '2.0.0', 'wu_get_documentation_url()');

		return wu_get_documentation_url($slug);

	} // end get_link;

} // end class WU_Links;

/*
 * Models
 */

/**
 * Deprecated: WU_Site
 *
 * @deprecated 2.0.0
 */
class WU_Site extends \WP_Ultimo\Models\Site {

	/**
	 * Override te constructor due to this being a native table.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $object Object containing the parameters.
	 */
	public function __construct($object = null) {

		_deprecated_function(__CLASS__, '2.0.0', '\WP_Ultimo\Models\Site');

		if (is_numeric($object)) {

			$object = wu_get_site($object);

		} // end if;

		if ($object) {

			$object = get_object_vars($object);

		} // end if;

		parent::__construct($object);

	} // end __construct;

} // end class WU_Site;

/**
 * Deprecated: WU_Site_Template
 *
 * @deprecated 2.0.0
 */
class WU_Site_Template extends \WP_Ultimo\Models\Site {

	/**
	 * Override te constructor due to this being a native table.
	 *
	 * @deprecated 2.0.0
	 *
	 * @param mixed $object Object containing the parameters.
	 */
	public function __construct($object = null) {

		_deprecated_function(__CLASS__, '2.0.0', '\WP_Ultimo\Models\Site');

		if (is_numeric($object)) {

			$object = wu_get_site($object);

		} // end if;

		if ($object) {

			$object = get_object_vars($object);

		} // end if;

		parent::__construct($object);

	} // end __construct;

} // end class WU_Site_Template;

/**
 * Deprecated: WU_Site_Template
 *
 * @deprecated 2.0.0
 */
class WU_Site_Owner {

	/**
	 * Deprecated
	 *
	 * @deprecated 2.0.0
	 */
	public function __construct() {

		_deprecated_function(__CLASS__, '2.0.0');

	} // end __construct;

} // end class WU_Site_Owner;

/**
 * Deprecated Trait.
 *
 * @since 2.0.0
 */
trait WU_Deprecated_Model {

	/**
	 * Generic method to bypass the protected status of the new models.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key The key to set.
	 * @param mixed  $value The value to set.
	 */
	public function __set($key, $value) {

		if (method_exists($this, "set_$key")) {

			call_user_func(array($this, "set_$key"), $value);

		} // end if;

		$this->{$key} = $value;

		$this->after_set($key, $value);

	} // end __set;

	/**
	 * Generic get method to bypass the protected status of the new models.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key The key to set.
	 */
	public function __get($key) {

		if (method_exists($this, "get_$key")) {

			return call_user_func(array($this, "get_$key"));

		} // end if;

		return false;

	} // end __get;

	/**
	 * Generic method to bypass the protected status of the new models.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key The key to set.
	 * @param mixed  $value The value to set.
	 */
	public function after_set($key, $value) {} // end after_set;

} // end trait WU_Deprecated_Model;

/**
 * Deprecated: WU_Coupon
 *
 * @deprecated 2.0.0
 */
class WU_Coupon extends \WP_Ultimo\Models\Discount_Code {

	use WU_Deprecated_Model;

	/**
	 * Override te constructor due to this being a native table.
	 *
	 * @deprecated 2.0.0
	 *
	 * @param mixed $object Object containing the parameters.
	 */
	public function __construct($object = null) {

		_deprecated_function(__CLASS__, '2.0.0', '\WP_Ultimo\Models\Discount_Code');

		if (is_numeric($object)) {

			$object = wu_get_discount_code($object);

		} // end if;

		if ($object) {

			$object = get_object_vars($object);

		} // end if;

		if (is_array($object)) {

			foreach ($object as $att => $value) {

				$this->{$att} = $value;

			} // end foreach;

		} // end if;

		parent::__construct($object);

	} // end __construct;

	/**
	 * Generic method to bypass the protected status of the new models.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key The key to set.
	 * @param mixed  $value The value to set.
	 */
	public function after_set($key, $value) {

		if ($key === 'title') {

			$this->set_code($value);

			$this->set_name($value);

			$this->set_description($value);

		} // end if;

	} // end after_set;

} // end class WU_Coupon;

/**
 * Deprecated: WU_Plan
 *
 * @deprecated 2.0.0
 */
class WU_Plan extends \WP_Ultimo\Models\Product {

	use WU_Deprecated_Model;

	/**
	 * Override te constructor due to this being a native table.
	 *
	 * @deprecated 2.0.0
	 *
	 * @param mixed $object Object containing the parameters.
	 */
	public function __construct($object = null) {

		_deprecated_function(__CLASS__, '2.0.0', '\WP_Ultimo\Models\Product');

		if (is_numeric($object)) {

			$object = wu_get_product($object);

		} // end if;

		if ($object) {

			$object = get_object_vars($object);

		} // end if;

		if (is_array($object)) {

			foreach ($object as $att => $value) {

				$this->{$att} = $value;

			} // end foreach;

		} // end if;

		$this->set_slug(uniqid());

		parent::__construct($object);

	} // end __construct;

	/**
	 * Generic get method to bypass the protected status of the new models.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key The key to set.
	 */
	public function __get($key) {

		if ($key === 'price_1') {

			return $this->get_amount();

		} // end if;

		if (method_exists($this, "get_$key")) {

			return call_user_func(array($this, "get_$key"));

		} // end if;

		return $this->get_meta('wpu_' . $key, false);

	} // end __get;

	/**
	 * Generic method to bypass the protected status of the new models.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key The key to set.
	 * @param mixed  $value The value to set.
	 */
	public function after_set($key, $value) {

		if ($key === 'price_1') {

			$this->set_amount($value);

		} // end if;

	} // end after_set;

} // end class WU_Plan;

/**
 * Deprecated: WU_Subscription
 *
 * @deprecated 2.0.0
 */
class WU_Subscription extends \WP_Ultimo\Models\Membership {

	use WU_Deprecated_Model;

	/**
	 * Override te constructor due to this being a native table.
	 *
	 * @deprecated 2.0.0
	 *
	 * @param mixed $object Object containing the parameters.
	 */
	public function __construct($object = null) {

		_deprecated_function(__CLASS__, '2.0.0', '\WP_Ultimo\Models\Membership');

		if (is_numeric($object)) {

			$object = wu_get_membership($object);

		} // end if;

		if ($object) {

			$object = get_object_vars($object);

		} // end if;

		parent::__construct($object);

	} // end __construct;

	/**
	 * Generic method to bypass the protected status of the new models.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key The key to set.
	 * @param mixed  $value The value to set.
	 */
	public function after_set($key, $value) {} // end after_set;

	/**
	 * Deprecated: Add a valid coupon code to the subscription.
	 *
	 * @deprecated 2.0.0
	 * @param null $coupon_code Deprecated Argument.
	 */
	public function apply_coupon_code($coupon_code = null) {

		_deprecated_function(__METHOD__, '2.0.0');

	} // end apply_coupon_code;

	/**
	 * Deprecated: Add a valid coupon code to the subscription.
	 *
	 * @deprecated 2.0.0
	 */
	public function get_price_after_coupon_code() {

		_deprecated_function(__METHOD__, '2.0.0');

	} // end get_price_after_coupon_code;

} // end class WU_Subscription;

/**
 * Deprecated: WU_Signup
 */
class WU_Signup extends \WP_Ultimo\Checkout\Legacy_Checkout {

	/**
	 * Override te constructor due to this being a native table.
	 *
	 * @deprecated 2.0.0
	 */
	public function __construct() {

		_deprecated_function(__CLASS__, '2.0.0', '\WP_Ultimo\Checkout\Legacy_Checkout');

	} // end __construct;

} // end class WU_Signup;

/**
 * Deprecated: WU_Gateway
 *
 * @since 2.0.0
 */
abstract class WU_Gateway {

} // end class WU_Gateway;

/**
 * Deprecated: WU_Site_Hooks
 *
 * @todo implement this for the legacy checkout.
 * @since 2.0.0
 */
class WU_Site_Hooks {

	/**
	 * Deprecated: Return all the templates available for use in Blog Creation
	 *
	 * @deprecated 2.0.0
	 *
	 * @since  1.1.3 Templates now use site names instead of path.
	 * @since  1.5.4 Optimized version to reduce query count.
	 *
	 * @param bool $include_wp If we want to include a default WordPress site.
	 * @return array Array containing all the available templates.
	 */
	public static function get_available_templates($include_wp = true) {

		_deprecated_function(__CLASS__, '2.0.0');

		return array();

	} // end get_available_templates;

	/**
	 * Deprecated: Duplicates our template site in the creation of the new user site.
	 *
	 * @deprecated 2.0.0
	 *
	 * @param  integer $site_to_duplicate ID of site template.
	 * @param  string  $title                   Site Title.
	 * @param  string  $domain                  Domain of the new site, as selected.
	 * @param  string  $email                   Admin email of the user.
	 * @param  string  $site_domain             The site domain.
	 * @param  bool    $copy_files              If we need to copy files over.
	 *
	 * @return integer Site ID of the new site.
	 */
	public static function duplicate_site($site_to_duplicate, $title, $domain, $email, $site_domain = false, $copy_files = '') {

		global $current_site;

		_deprecated_function(__CLASS__, '2.0.0', '\WP_Ultimo\Helpers\Site_Duplicator::duplicate_site()');

		$arguments = array(
			'email'      => $email,
			'path'       => $domain,
			'copy_files' => $copy_files,
			'domain'     => $site_domain ? $site_domain : $current_site->domain,
		);

		return \WP_Ultimo\Helpers\Site_Duplicator::duplicate_site($site_to_duplicate, $title, $arguments);

	} // end duplicate_site;

	/**
	 * Deprecated: Returns the preview URL to a given site id.
	 *
	 * @deprecated 2.0.0
	 *
	 * @param string $site_id The site to get the preview image.
	 * @return string
	 */
	public static function get_template_preview_url($site_id = '') {

		_deprecated_function(__CLASS__, '2.0.0', '\WP_Ultimo\Models\Site::get_featured_image()');

		$site = wu_get_site($site_id);

		if (!$site) {

			return '';

		} // end if;

		return $site->get_featured_image();

	} // end get_template_preview_url;

} // end class WU_Site_Hooks;

/**
 * Deprecated: WU_Transactions
 *
 * @deprecated 2.0.0
 */
class WU_Transactions {

	/**
	 * Deprecated: Returns the current time from the network.
	 *
	 * @deprecated 2.0.0
	 *
	 * @param string $type Either mysql or timestamp.
	 * @return string
	 */
	public static function get_current_time($type = 'mysql') {

		_deprecated_function(__CLASS__, '2.0.0', 'wu_date()');

		$date = wu_date();

		return $type === 'mysql' ? $date->format('Y-m-d H:i:s') : $date->format('U');

	} // end get_current_time;

} // end class WU_Transactions;

/*
 * Functions
 */

/**
 * Deprecated: Returns a coupon code object based on the code.
 *
 * @deprecated 2.0.0
 * @see wu_get_discount_code_by_code()
 *
 * @param string  $coupon_code Coupon code.
 * @param boolean $return_invalid If we should return the coupon even if it is no longer valid.
 * @return \WP_Ultimo\Models\Discount_Code|false
 */
function wu_get_coupon($coupon_code, $return_invalid = false) {

	_deprecated_function(__FUNCTION__, '2.0.0', 'wu_get_discount_code_by_code()');

	$discount_code = wu_get_discount_code_by_code($coupon_code);

	if (!$discount_code) {

		return false;

	} // end if;

	if (!$return_invalid && !$discount_code->is_valid()) {

		return false;

	} // end if;

	return new WU_Coupon($discount_code);

} // end wu_get_coupon;

/**
 * Deprecated: Returns a plan based on the id passed
 *
 * @deprecated 2.0.0
 *
 * @param integer $plan_id The plan id to get.
 * @return \WP_Ultimo\Models\Product|false
 */
function wu_get_plan($plan_id) {

	_deprecated_function(__FUNCTION__, '2.0.0', 'wu_get_product()');

	$plan = wu_get_product($plan_id);

	if (!$plan) {

		return false;

	} // end if;

	return new WU_Plan($plan);

} // end wu_get_plan;

/**
 * Deprecated: Gets a plan by its slug.
 *
 * @since 1.9.0
 * @deprecated 2.0.0
 *
 * @param string $plan_slug The plan slug.
 * @return \WP_Ultimo\Models\Product|false
 */
function wu_get_plan_by_slug($plan_slug) {

	_deprecated_function(__FUNCTION__, '2.0.0', 'wu_get_product_by_slug()');

	return wu_get_product_by_slug($plan_slug);

} // end wu_get_plan_by_slug;

/**
 * Deprecated: Returns a subscription object based on the user.
 *
 * This method is returning the first result of a global search for
 * memberships with this user_id. This needs to be changed on your code as soon as possible,
 * to make use of the current methods to search memberships based on the customer.
 *
 * @deprecated 2.0.0
 *
 * @param  int $user_id User id to get subscription from.
 * @return \WP_Ultimo\Models\Membership|false
 */
function wu_get_subscription($user_id) {

	_deprecated_function(__FUNCTION__, '2.0.0', 'wu_get_membership_by()');

	return wu_get_membership_by('user_id', $user_id);

} // end wu_get_subscription;

/**
 * Deprecated: Returns a subscription object based on the integration key.
 *
 * @deprecated 2.0.0
 *
 * @param string $integration_key The gateway subscription key/id.
 * @return \WP_Ultimo\Models\Membership|false
 */
function wu_get_subscription_by_integration_key($integration_key) {

	_deprecated_function(__FUNCTION__, '2.0.0', 'wu_get_membership_by()');

	return wu_get_membership_by('gateway_subscription_id', $integration_key);

} // end wu_get_subscription_by_integration_key;

/**
 * Deprecated: Return a subscription object based on the current user.
 *
 * @since 1.7.3
 * @deprecated 2.0.0
 *
 * @return \WP_Ultimo\Models\Membership|false
 */
function wu_get_current_subscription() {

	$user_id = get_current_user_id();

	_deprecated_function(__FUNCTION__, '2.0.0', 'wu_get_membership_by()');

	return wu_get_membership_by('user_id', $user_id);

} // end wu_get_current_subscription;

/**
 * Deprecated: Checks if the current user is an active subscriber.
 *
 * New APIs will be added to check membership status.
 * Do not use this methods, as they rely on the user id.
 *
 * @since 1.6.2
 * @deprecated 2.0.0
 *
 * @param integer $user_id The user ID.
 * @return boolean
 */
function wu_is_active_subscriber($user_id = false) {

	_deprecated_function(__FUNCTION__, '2.0.0');

	if ($user_id === false) {

		$membership = wu_get_current_site()->get_membership();

	} else {

		$membership = wu_get_membership_by('user_id', get_current_user_id());

	} // end if;

	return $membership && $membership->is_active();

}  // end wu_is_active_subscriber;

/**
 * Deprecated: Checks if a given user is a customer of a given plan.
 *
 * @since 1.6.2
 * @deprecated 2.0.0
 *
 * @param integer $user_id The user Id.
 * @param integer $plan_id The plan Id.
 * @return boolean
 */
function wu_has_plan($user_id, $plan_id) {

	_deprecated_function(__FUNCTION__, '2.0.0');

	/*
	 * This function is frequently used by custom snippets
	 * developed by Ultimo users, and as such, they might
	 * get loaded and run before Ultimo is set up and the APIs
	 * are loaded. In that case, we just return false,
	 * to prevent a fatal error.
	 */
	if (function_exists('wu_get_membership_by') === false) {

		return false;

	} // end if;

	$membership = wu_get_membership_by('user_id', $user_id);

	return $membership && absint($membership->get_plan_id()) === absint($plan_id);

} // end wu_has_plan;

/**
 * Deprecated: Returns the gateway being used by the current user at the moment.
 *
 * @since  1.1.0
 * @deprecated 1.9.0
 *
 * @return object Gateway class
 */
function wu_get_active_gateway() {

	_deprecated_function(__FUNCTION__, '2.0.0', 'wu_get_active_gateways()');

	$active_gateways = wu_get_active_gateways();

	return reset($active_gateways);

} // end wu_get_active_gateway;

/**
 * Deprecated: Generates the price description.
 *
 * @since 1.7.0
 * @deprecated 2.0.0
 *
 * @param float   $price Deprecated Argument.
 * @param int     $interval Deprecated Argument.
 * @param boolean $extended Deprecated Argument.
 * @return string
 */
function wu_get_interval_string($price = null, $interval = null, $extended = null) {

	_deprecated_function(__FUNCTION__, '2.0.0', '\WP_Ultimo\Models\Product::get_price_description()');

	return '';

} // end wu_get_interval_string;

/**
 * Deprecated: get_wu_currencies.
 *
 * This was badly named.
 *
 * @since 2.0.0
 * @return array
 */
function get_wu_currencies() { // phpcs:ignore

	_deprecated_function(__FUNCTION__, '2.0.0', 'wu_get_currencies()');

	return wu_get_currencies();

} // end get_wu_currencies;

/**
 * Deprecated: get_wu_currency_symbol.
 *
 * This was badly named.
 *
 * @since 2.0.0
 * @param string $currency Currency code.
 * @return string
 */
function get_wu_currency_symbol($currency = '') { // phpcs:ignore

	_deprecated_function(__FUNCTION__, '2.0.0', 'wu_get_currency_symbol()');

	return wu_get_currency_symbol($currency);

} // end get_wu_currency_symbol;
