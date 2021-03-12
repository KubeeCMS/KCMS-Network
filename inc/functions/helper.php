<?php
/**
 * General helper functions for WP Ultimo.
 *
 * @author      Arindo Duque
 * @category    Admin
 * @package     WP_Ultimo/Helper
 * @version     2.0.0
 */

/**
 * Returns the WP Ultimo version.
 *
 * @since 2.0.0
 * @return string
 */
function wu_get_version() {

	return WP_Ultimo()->version;

} // end wu_get_version;

/**
 * Wrapper to the network_admin_url function for WP Ultimo admin urls.
 *
 * @since 2.0.0
 *
 * @param string $path WP Ultimo page.
 * @param array  $query URL query parameters.
 * @return string
 */
function wu_network_admin_url($path, $query = array()) {

	$path = sprintf('admin.php?page=%s', $path);

	$url = network_admin_url($path);

	return add_query_arg($query, $url);

} // end wu_network_admin_url;

/**
 * Returns the current URL.
 *
 * @since 2.0.0
 * @return string
 */
function wu_get_current_url() {

	return (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

} // end wu_get_current_url;

/**
 * Shorthand to retrieving variables from $_GET, $_POST and $_REQUEST;
 *
 * @since 2.0.0
 *
 * @param string  $key Key to retrieve.
 * @param boolean $default Default value, when the variable is not available.
 * @return mixed
 */
function wu_request($key, $default = false) {

	return isset($_REQUEST[$key]) ? $_REQUEST[$key] : $default;

} // end wu_request;

/**
 * Checks if an array key value is set and returns it.
 *
 * If the key is not set, returns the $default parameter.
 * This function is a helper to serve as a shorthand for the tedious
 * and ugly $var = isset($array['key'])) ? $array['key'] : $default.
 * Using this, that same line becomes wu_get_isset($array, 'key', $default);
 *
 * @since 2.0.0
 *
 * @param array  $array Array to check key.
 * @param string $key Key to check.
 * @param mixed  $default Default value, if the key is not set.
 * @return mixed
 */
function wu_get_isset($array, $key, $default = false) {

	return isset($array[$key]) ? $array[$key] : $default;

} // end wu_get_isset;

/**
 * Converts a string (e.g. 'yes' or 'no' or '1' or '0') to a bool.
 *
 * @since 2.0.0
 *
 * @param string $string The string to convert.
 * @return bool
 */
function wu_string_to_bool($string) {

	return is_bool($string) ? $string : ('on' === strtolower($string) || 'yes' === strtolower($string) || 1 === $string || 'true' === strtolower($string) || '1' === $string);

} // end wu_string_to_bool;

/**
 * Converts formatted values back into floats.
 *
 * @since 2.0.0
 *
 * @param string $num Formatted number string.
 * @return float
 */
function wu_to_float($num) {

	$dot_pos   = strrpos($num, '.');
	$comma_pos = strrpos($num, ',');

	$sep = (($dot_pos > $comma_pos) && $dot_pos) ? $dot_pos :
	((($comma_pos > $dot_pos) && $comma_pos) ? $comma_pos : false);

	if (!$sep) {

		return floatval(preg_replace('/[^0-9]/', '', $num));

	} // end if;

	return floatval(
		preg_replace('/[^0-9]/', '', substr($num, 0, $sep)) . '.' .
		preg_replace('/[^0-9]/', '', substr($num, $sep + 1, strlen($num)))
	);

} // end wu_to_float;

/**
 * Adds a tooltip icon.
 *
 * @since 2.0.0
 *
 * @param string $tooltip Message to display.
 * @param string $icon Dashicon to display as the icon.
 * @return string
 */
function wu_tooltip($tooltip, $icon = 'dashicons-editor-help') {

	if (empty($tooltip)) {

		return '';

	} // end if;

	$markup = '<span class="wu-styling" role="tooltip" aria-label="%s">
		<span class="dashicons wu-text-xs wu-w-auto wu-h-auto wu-align-text-bottom %s"></span>
	</span>';

	return sprintf($markup, esc_attr($tooltip), esc_attr($icon));

} // end wu_tooltip;

/**
 * Adds a tooltip to a HTML element. Needs to be echo'ed.
 *
 * @since 2.0.0
 *
 * @param string $tooltip Message to display.
 * @return string
 */
function wu_tooltip_text($tooltip) {

	return sprintf('role="tooltip" aria-label="%s"', esc_attr($tooltip));

} // end wu_tooltip_text;

/**
 * Converts a slug to a name.
 *
 * This function turns discount_code into Discount Code, by removing _- and using ucwords.
 *
 * @since 2.0.0
 *
 * @param string $slug The slug to convert.
 * @return string
 */
function wu_slug_to_name($slug) {

	$slug = str_replace(array('-', '_'), ' ', $slug);

	return ucwords($slug);

} // end wu_slug_to_name;

/**
 * Converts a list of Model objects to a list of ID => $label_field
 *
 * @since 2.0.0
 *
 * @param array  $models The list of models to convert.
 * @param string $label_field The name of the field to use.
 * @return array
 */
function wu_models_to_options($models, $label_field = 'name') {

	$options_list = array();

	foreach ($models as $model) {

		$options_list[$model->get_id()] = call_user_func(array($model, "get_{$label_field}"));

	} // end foreach;

	return $options_list;

} // end wu_models_to_options;

/**
 * Returns the main site id for the network.
 *
 * @since 2.0.0
 * @return int
 */
function wu_get_main_site_id() {

	global $current_site;

	return $current_site->blog_id;

} // end wu_get_main_site_id;

/**
 * Generate CSV file
 *
 * @param  string $file_name File name.
 * @param  array  $data Content.
 * @return void
 */
function wu_generate_csv($file_name, $data = array()) {

	$fp = fopen('php://output', 'w');

	if ($fp && $data) {

		header('Content-Type: text/csv; charset=utf-8');

		header('Content-Disposition: attachment; filename="' . $file_name . '.csv"');

		header('Pragma: no-cache');

		header('Expires: 0');

		foreach ($data as $data_line) {

			if (is_array($data_line)) {

				fputcsv($fp, array_values($data_line));

			} elseif (is_object($data_line)) {

				fputcsv($fp, array_values(get_object_vars($data_line)));

			} // end if;

		} // end foreach;

	} // end if;

} // end wu_generate_csv;

/**
 * Returns the content.
 *
 * @since 2.0.0
 *
 * @param  string $slug The slug of the link to be returned.
 * @param  bool   $return_default If we should return a default value.
 * @return string
 */
function wu_get_documentation_url($slug, $return_default = true) {

	return \WP_Ultimo\Documentation::get_instance()->get_link($slug, $return_default);

} // end wu_get_documentation_url;

/**
 * Adds the license key to a given URL.
 *
 * @since 2.0.0
 *
 * @param string $url URL to attach the license key to.
 * @return string
 */
function wu_with_license_key($url) {

	$license_key = '';

	$license = \WP_Ultimo\License::get_instance();

	$license_key = $license->get_license_key();

	return add_query_arg('license_key', $license_key, $url);

} // end wu_with_license_key;

/**
 * Get the customers' IP address.
 *
 * @since 2.0.0
 * @return string
 */
function wu_get_ip() {

	$geolocation = \WP_Ultimo\Geolocation::geolocate_ip('', true);

	return apply_filters('wu_get_ip', $geolocation['ip']);

} // end wu_get_ip;

/**
 * Returns the URL for assets inside the assets folder.
 *
 * @since 2.0.0
 *
 * @param string $asset Asset file name with the extention.
 * @param string $assets_dir Assets sub-directory. Defaults to 'img'.
 * @param string $base_dir   Base dir. Defaults to 'assets'.
 * @return string
 */
function wu_get_asset($asset, $assets_dir = 'img', $base_dir = 'assets') {

	return WP_Ultimo()->helper->get_asset($asset, $assets_dir, $base_dir);

} // end wu_get_asset;

/**
 * Add a log entry to chosen file.
 *
 * @since 2.0.0
 *
 * @param string $handle Name of the log file to write to.
 * @param string $message Log message to write.
 * @return void
 */
function wu_log_add($handle, $message) {

	\WP_Ultimo\Logger::add($handle, $message);

} // end wu_log_add;

/**
 * Clear entries from chosen file.
 *
 * @since 2.0.0
 *
 * @param mixed $handle Name of the log file to clear.
 * @return void
 */
function wu_log_clear($handle) {

	\WP_Ultimo\Logger::clear($handle);

} // end wu_log_clear;

/**
 * Replaces dashes with underscores on strings.
 *
 * @since 2.0.0
 *
 * @param string $str String to replace dashes in.
 * @return string
 */
function wu_replace_dashes($str) {

	return str_replace('-', '_', $str);

} // end wu_replace_dashes;

/**
 * Get the initials for a string.
 *
 * E.g. Brazilian People will return BP.
 *
 * @since 2.0.0
 *
 * @param string  $string String to process.
 * @param integer $max_size Number of initials to return.
 * @return string
 */
function wu_get_initials($string, $max_size = 2) {

	$words = explode(' ', $string);

	$initials = '';

	for ($i = 0; $i < $max_size; $i++) {

		if (!isset($words[$i])) {

			break;

		} // end if;

		$initials .= substr($words[$i], 0, 1);

	} // end for;

	return strtoupper($initials);

} // end wu_get_initials;

/**
 * Array map implementation to deal with keys.
 *
 * @since 2.0.0
 *
 * @param callable $callable The callback to run.
 * @param array    $array The array to map the keys.
 * @return array
 */
function wu_array_map_keys($callable, $array) {

	$keys = array_keys($array);

	$keys = array_map($callable, $keys);

	return array_combine($keys, $array);

} // end wu_array_map_keys;

/**
 * Sorts the fields.
 *
 * @param array $a The first array containing a order key.
 * @param array $b The second array containing a order key.
 * @return int
 */
function wu_sort_by_order($a, $b) {

	$a['order'] = isset($a['order']) ? (int) $a['order'] : 50;

	$b['order'] = isset($b['order']) ? (int) $b['order'] : 50;

	return $a['order'] - $b['order'];

} // end wu_sort_by_order;

/**
 * Get a setting value, when te normal APIs are not available.
 *
 * Should only be used if we're running in sunrise.
 *
 * @since 2.0.0
 *
 * @param string $setting Setting to get.
 * @param mixed  $default Default value.
 * @return mixed
 */
function wu_get_setting_early($setting, $default = false) {

	if (did_action('wp_ultimo_load')) {

		_doing_it_wrong('wu_get_setting_early', __('Current APIs are already available. You should use wu_get_setting() instead.', 'wp-ultimo'), '2.0.0');

	} // end if;

	$settings_key = \WP_Ultimo\Settings::KEY;

	$settings = get_network_option(null, 'wp-ultimo_' . $settings_key);

	return wu_get_isset($settings, $setting, $default);

} // end wu_get_setting_early;

/**
 * Returns a list of valid selectable roles.
 *
 * @since 2.0.0
 * @param boolean $add_default_option Adds a new default option.
 * @return array
 */
function wu_get_roles_as_options($add_default_option = false) {

	if (!function_exists('get_editable_roles')) {

		require_once(ABSPATH . 'wp-admin/includes/user.php');

	} // end if;

	$roles = array();

	if ($add_default_option) {

		$roles['default'] = __('Use WP Ultimo default', 'wp-ultimo');

	} // end if;

	$editable_roles = get_editable_roles();

	foreach ($editable_roles as $role => $details) {

		$roles[esc_attr($role)] = translate_user_role($details['name']);

	} // end foreach;

	return $roles;

} // end wu_get_roles_as_options;

/**
 * Converts an array to a vue data-state parameter.
 *
 * @since 2.0.0
 *
 * @param array $state_array The array to convert.
 * @return string
 */
function wu_convert_to_state($state_array = array()) {

	$object = (object) $state_array; // Force object to prevent issues with Vue.

	return json_encode($object);

} // end wu_convert_to_state;

/**
 * Gets or creates a Session object.
 *
 * @since 2.0.0
 *
 * @param string $session_key The session key.
 * @return \WP_Ultimo\Session
 */
function wu_get_session($session_key) {

	return new \WP_Ultimo\Session($session_key);

} // end wu_get_session;

/**
 * Picks content to return depending on the environment.
 *
 * This is useful when creating layouts that will be used on the front-end as well as
 * the backend (admin panel). You can use this function to pick the content to return
 * according to the environment. Can be used both for HTML, but is must useful when
 * dealing with CSS classes.
 *
 * E.g. <?php echo wu_env_picker('wu-m-0', 'wu--mx-3 wu--my-2'); ?>
 * In the backend, this will return the classes 'wu--mx-3 wu--my-2',
 * while it will return wu-m-0 omn the frontend.
 *
 * Values can be anything, but will usually be strings.
 *
 * @since 2.0.0
 *
 * @param mixed $frontend_content Content to return on the frontend.
 * @param mixed $backend_content  Content to return on the backend.
 * @param bool  $is_admin You can manually pass the is_admin result, if need be.
 * @return mixed
 */
function wu_env_picker($frontend_content, $backend_content, $is_admin = null) {

	$is_admin = is_null($is_admin) ? is_admin() : $is_admin;

	return $is_admin ? $backend_content : $frontend_content;

} // end wu_env_picker;

/**
 * Returns the list of available icons. To add more icons you need use the filter
 * wu_icons_list, and new array using the Key as the optgroup label and the value
 * as the array with all the icons you want to make avaiable.
 *
 * Don't forget to add the css as well.
 *
 * @since 2.0.0
 *
 * @return array With all available icons.
 */
function wu_get_icons_list() {

	$all_icons = array();

	$all_icons['WP Ultimo Icons'] = array(
		'dashicons-wu-add_task',
		'dashicons-wu-address',
		'dashicons-wu-add-to-list',
		'dashicons-wu-add-user',
		'dashicons-wu-adjust',
		'dashicons-wu-air',
		'dashicons-wu-aircraft',
		'dashicons-wu-aircraft-landing',
		'dashicons-wu-aircraft-take-off',
		'dashicons-wu-align-bottom',
		'dashicons-wu-align-horizontal-middle',
		'dashicons-wu-align-left',
		'dashicons-wu-align-right',
		'dashicons-wu-align-top',
		'dashicons-wu-align-vertical-middle',
		'dashicons-wu-archive',
		'dashicons-wu-area-graph',
		'dashicons-wu-arrow-bold-down',
		'dashicons-wu-arrow-bold-left',
		'dashicons-wu-arrow-bold-right',
		'dashicons-wu-arrow-bold-up',
		'dashicons-wu-arrow-down',
		'dashicons-wu-arrow-left',
		'dashicons-wu-arrow-long-down',
		'dashicons-wu-arrow-long-left',
		'dashicons-wu-arrow-long-right',
		'dashicons-wu-arrow-long-up',
		'dashicons-wu-arrow-right',
		'dashicons-wu-arrow-up',
		'dashicons-wu-arrow-with-circle-down',
		'dashicons-wu-arrow-with-circle-left',
		'dashicons-wu-arrow-with-circle-right',
		'dashicons-wu-arrow-with-circle-up',
		'dashicons-wu-attachment',
		'dashicons-wu-awareness-ribbon',
		'dashicons-wu-back',
		'dashicons-wu-back-in-time',
		'dashicons-wu-bar-graph',
		'dashicons-wu-battery',
		'dashicons-wu-beamed-note',
		'dashicons-wu-bell',
		'dashicons-wu-blackboard',
		'dashicons-wu-block',
		'dashicons-wu-book',
		'dashicons-wu-bookmark',
		'dashicons-wu-bookmarks',
		'dashicons-wu-bowl',
		'dashicons-wu-box',
		'dashicons-wu-briefcase',
		'dashicons-wu-browser',
		'dashicons-wu-brush',
		'dashicons-wu-bucket',
		'dashicons-wu-cake',
		'dashicons-wu-calculator',
		'dashicons-wu-calendar',
		'dashicons-wu-camera',
		'dashicons-wu-ccw',
		'dashicons-wu-chat',
		'dashicons-wu-check',
		'dashicons-wu-checkbox-checked',
		'dashicons-wu-checkbox-unchecked',
		'dashicons-wu-chevron-down',
		'dashicons-wu-chevron-left',
		'dashicons-wu-chevron-right',
		'dashicons-wu-chevron-small-down',
		'dashicons-wu-chevron-small-left',
		'dashicons-wu-chevron-small-right',
		'dashicons-wu-chevron-small-up',
		'dashicons-wu-chevron-thin-down',
		'dashicons-wu-chevron-thin-left',
		'dashicons-wu-chevron-thin-right',
		'dashicons-wu-chevron-thin-up',
		'dashicons-wu-chevron-up',
		'dashicons-wu-chevron-with-circle-down',
		'dashicons-wu-chevron-with-circle-left',
		'dashicons-wu-chevron-with-circle-right',
		'dashicons-wu-chevron-with-circle-up',
		'dashicons-wu-circle',
		'dashicons-wu-circle-with-cross',
		'dashicons-wu-circle-with-minus',
		'dashicons-wu-circle-with-plus',
		'dashicons-wu-circular-graph',
		'dashicons-wu-clapperboard',
		'dashicons-wu-classic-computer',
		'dashicons-wu-clipboard',
		'dashicons-wu-clock',
		'dashicons-wu-cloud',
		'dashicons-wu-code',
		'dashicons-wu-cog',
		'dashicons-wu-coin-dollar',
		'dashicons-wu-coin-euro',
		'dashicons-wu-coin-pound',
		'dashicons-wu-coin-yen',
		'dashicons-wu-colours',
		'dashicons-wu-compass',
		'dashicons-wu-controller-fast-forward',
		'dashicons-wu-controller-jump-to-start',
		'dashicons-wu-controller-next',
		'dashicons-wu-controller-paus',
		'dashicons-wu-controller-play',
		'dashicons-wu-controller-record',
		'dashicons-wu-controller-stop',
		'dashicons-wu-controller-volume',
		'dashicons-wu-copy',
		'dashicons-wu-credit',
		'dashicons-wu-credit-card',
		'dashicons-wu-credit-card1',
		'dashicons-wu-cross',
		'dashicons-wu-cup',
		'dashicons-wu-cw',
		'dashicons-wu-cycle',
		'dashicons-wu-database',
		'dashicons-wu-dial-pad',
		'dashicons-wu-direction',
		'dashicons-wu-document',
		'dashicons-wu-document-landscape',
		'dashicons-wu-documents',
		'dashicons-wu-done',
		'dashicons-wu-done_all',
		'dashicons-wu-dot-single',
		'dashicons-wu-dots-three-horizontal',
		'dashicons-wu-dots-three-vertical',
		'dashicons-wu-dots-two-horizontal',
		'dashicons-wu-dots-two-vertical',
		'dashicons-wu-download',
		'dashicons-wu-drink',
		'dashicons-wu-drive',
		'dashicons-wu-drop',
		'dashicons-wu-edit',
		'dashicons-wu-email',
		'dashicons-wu-emoji-flirt',
		'dashicons-wu-emoji-happy',
		'dashicons-wu-emoji-neutral',
		'dashicons-wu-emoji-sad',
		'dashicons-wu-erase',
		'dashicons-wu-eraser',
		'dashicons-wu-export',
		'dashicons-wu-eye',
		'dashicons-wu-feather',
		'dashicons-wu-filter_1',
		'dashicons-wu-filter_2',
		'dashicons-wu-filter_3',
		'dashicons-wu-filter_4',
		'dashicons-wu-filter_5',
		'dashicons-wu-filter_6',
		'dashicons-wu-filter_7',
		'dashicons-wu-filter_8',
		'dashicons-wu-filter_9',
		'dashicons-wu-filter_9_plus',
		'dashicons-wu-flag',
		'dashicons-wu-flash',
		'dashicons-wu-flashlight',
		'dashicons-wu-flat-brush',
		'dashicons-wu-flow-branch',
		'dashicons-wu-flow-cascade',
		'dashicons-wu-flow-line',
		'dashicons-wu-flow-parallel',
		'dashicons-wu-flow-tree',
		'dashicons-wu-folder',
		'dashicons-wu-folder-images',
		'dashicons-wu-folder-music',
		'dashicons-wu-folder-video',
		'dashicons-wu-forward',
		'dashicons-wu-funnel',
		'dashicons-wu-game-controller',
		'dashicons-wu-gauge',
		'dashicons-wu-globe',
		'dashicons-wu-graduation-cap',
		'dashicons-wu-grid',
		'dashicons-wu-hair-cross',
		'dashicons-wu-hand',
		'dashicons-wu-hash',
		'dashicons-wu-hashtag',
		'dashicons-wu-heart',
		'dashicons-wu-heart-outlined',
		'dashicons-wu-help',
		'dashicons-wu-help-with-circle',
		'dashicons-wu-home',
		'dashicons-wu-hour-glass',
		'dashicons-wu-image',
		'dashicons-wu-image-inverted',
		'dashicons-wu-images',
		'dashicons-wu-inbox',
		'dashicons-wu-infinity',
		'dashicons-wu-info',
		'dashicons-wu-info-with-circle',
		'dashicons-wu-install',
		'dashicons-wu-key',
		'dashicons-wu-keyboard',
		'dashicons-wu-lab-flask',
		'dashicons-wu-landline',
		'dashicons-wu-language',
		'dashicons-wu-laptop',
		'dashicons-wu-layers',
		'dashicons-wu-leaf',
		'dashicons-wu-level-down',
		'dashicons-wu-level-up',
		'dashicons-wu-lifebuoy',
		'dashicons-wu-light-bulb',
		'dashicons-wu-light-down',
		'dashicons-wu-light-up',
		'dashicons-wu-line-graph',
		'dashicons-wu-link',
		'dashicons-wu-list',
		'dashicons-wu-location',
		'dashicons-wu-location-pin',
		'dashicons-wu-lock',
		'dashicons-wu-lock-open',
		'dashicons-wu-login',
		'dashicons-wu-log-out',
		'dashicons-wu-loop',
		'dashicons-wu-magnet',
		'dashicons-wu-magnifying-glass',
		'dashicons-wu-mail',
		'dashicons-wu-man',
		'dashicons-wu-map',
		'dashicons-wu-mask',
		'dashicons-wu-medal',
		'dashicons-wu-megaphone',
		'dashicons-wu-menu',
		'dashicons-wu-message',
		'dashicons-wu-mic',
		'dashicons-wu-minus',
		'dashicons-wu-mobile',
		'dashicons-wu-modern-mic',
		'dashicons-wu-moon',
		'dashicons-wu-mouse',
		'dashicons-wu-music',
		'dashicons-wu-new',
		'dashicons-wu-new-message',
		'dashicons-wu-news',
		'dashicons-wu-note',
		'dashicons-wu-notification',
		'dashicons-wu-number',
		'dashicons-wu-old-mobile',
		'dashicons-wu-old-phone',
		'dashicons-wu-open-book',
		'dashicons-wu-palette',
		'dashicons-wu-paper-plane',
		'dashicons-wu-pencil',
		'dashicons-wu-pencil2',
		'dashicons-wu-phone',
		'dashicons-wu-pie-chart',
		'dashicons-wu-pin',
		'dashicons-wu-plus',
		'dashicons-wu-popup',
		'dashicons-wu-power-cord',
		'dashicons-wu-power-plug',
		'dashicons-wu-price-ribbon',
		'dashicons-wu-price-tag',
		'dashicons-wu-print',
		'dashicons-wu-progress-empty',
		'dashicons-wu-progress-full',
		'dashicons-wu-progress-one',
		'dashicons-wu-progress-two',
		'dashicons-wu-publish',
		'dashicons-wu-qrcode',
		'dashicons-wu-quote',
		'dashicons-wu-radio',
		'dashicons-wu-remove-user',
		'dashicons-wu-reply',
		'dashicons-wu-reply-all',
		'dashicons-wu-resize-100',
		'dashicons-wu-resize-full-screen',
		'dashicons-wu-retweet',
		'dashicons-wu-rocket',
		'dashicons-wu-round-brush',
		'dashicons-wu-rss',
		'dashicons-wu-ruler',
		'dashicons-wu-save',
		'dashicons-wu-scissors',
		'dashicons-wu-select-arrows',
		'dashicons-wu-share',
		'dashicons-wu-shareable',
		'dashicons-wu-share-alternitive',
		'dashicons-wu-shield',
		'dashicons-wu-shop',
		'dashicons-wu-shopping-bag',
		'dashicons-wu-shopping-basket',
		'dashicons-wu-shopping-cart',
		'dashicons-wu-shuffle',
		'dashicons-wu-signal',
		'dashicons-wu-sound',
		'dashicons-wu-sound-mix',
		'dashicons-wu-sound-mute',
		'dashicons-wu-sports-club',
		'dashicons-wu-spreadsheet',
		'dashicons-wu-squared-cross',
		'dashicons-wu-squared-minus',
		'dashicons-wu-squared-plus',
		'dashicons-wu-star',
		'dashicons-wu-star-outlined',
		'dashicons-wu-stopwatch',
		'dashicons-wu-suitcase',
		'dashicons-wu-swap',
		'dashicons-wu-sweden',
		'dashicons-wu-switch',
		'dashicons-wu-tablet',
		'dashicons-wu-tag',
		'dashicons-wu-text',
		'dashicons-wu-text-document',
		'dashicons-wu-text-document-inverted',
		'dashicons-wu-thermometer',
		'dashicons-wu-thumbs-down',
		'dashicons-wu-thumbs-up',
		'dashicons-wu-thunder-cloud',
		'dashicons-wu-ticket',
		'dashicons-wu-ticket1',
		'dashicons-wu-time-slot',
		'dashicons-wu-toggle_on',
		'dashicons-wu-tools',
		'dashicons-wu-traffic-cone',
		'dashicons-wu-trash',
		'dashicons-wu-tree',
		'dashicons-wu-triangle-down',
		'dashicons-wu-triangle-left',
		'dashicons-wu-triangle-right',
		'dashicons-wu-triangle-up',
		'dashicons-wu-trophy',
		'dashicons-wu-tv',
		'dashicons-wu-typing',
		'dashicons-wu-uninstall',
		'dashicons-wu-unread',
		'dashicons-wu-untag',
		'dashicons-wu-upload',
		'dashicons-wu-upload-to-cloud',
		'dashicons-wu-user',
		'dashicons-wu-users',
		'dashicons-wu-v-card',
		'dashicons-wu-verified',
		'dashicons-wu-video',
		'dashicons-wu-vinyl',
		'dashicons-wu-voicemail',
		'dashicons-wu-wallet',
		'dashicons-wu-warning',
		'dashicons-wu-wp-ultimo'
	);

	$all_icons['Dashicons'] = array(
		'dashicons-before dashicons-admin-appearance',
		'dashicons-before dashicons-admin-collapse',
		'dashicons-before dashicons-admin-comments',
		'dashicons-before dashicons-admin-customizer',
		'dashicons-before dashicons-admin-generic',
		'dashicons-before dashicons-admin-home',
		'dashicons-before dashicons-admin-links',
		'dashicons-before dashicons-admin-media',
		'dashicons-before dashicons-admin-multisite',
		'dashicons-before dashicons-admin-network',
		'dashicons-before dashicons-admin-page',
		'dashicons-before dashicons-admin-plugins',
		'dashicons-before dashicons-admin-post',
		'dashicons-before dashicons-admin-settings',
		// 'dashicons-before dashicons-admin-site-alt',
		// 'dashicons-before dashicons-admin-site-alt2',
		// 'dashicons-before dashicons-admin-site-alt3',
		'dashicons-before dashicons-admin-site',
		'dashicons-before dashicons-admin-tools',
		'dashicons-before dashicons-admin-users',
		'dashicons-before dashicons-album',
		'dashicons-before dashicons-align-center',
		'dashicons-before dashicons-align-left',
		'dashicons-before dashicons-align-none',
		'dashicons-before dashicons-align-right',
		'dashicons-before dashicons-analytics',
		'dashicons-before dashicons-archive',
		'dashicons-before dashicons-arrow-down-alt',
		'dashicons-before dashicons-arrow-down-alt2',
		'dashicons-before dashicons-arrow-down',
		'dashicons-before dashicons-arrow-left-alt',
		'dashicons-before dashicons-arrow-left-alt2',
		'dashicons-before dashicons-arrow-left',
		'dashicons-before dashicons-arrow-right-alt',
		'dashicons-before dashicons-arrow-right-alt2',
		'dashicons-before dashicons-arrow-right',
		'dashicons-before dashicons-arrow-up-alt',
		'dashicons-before dashicons-arrow-up-alt2',
		'dashicons-before dashicons-arrow-up',
		'dashicons-before dashicons-art',
		'dashicons-before dashicons-awards',
		'dashicons-before dashicons-backup',
		'dashicons-before dashicons-book-alt',
		'dashicons-before dashicons-book',
		'dashicons-before dashicons-buddicons-activity',
		'dashicons-before dashicons-buddicons-bbpress-logo',
		'dashicons-before dashicons-buddicons-buddypress-logo',
		'dashicons-before dashicons-buddicons-community',
		'dashicons-before dashicons-buddicons-forums',
		'dashicons-before dashicons-buddicons-friends',
		'dashicons-before dashicons-buddicons-groups',
		'dashicons-before dashicons-buddicons-pm',
		'dashicons-before dashicons-buddicons-replies',
		'dashicons-before dashicons-buddicons-topics',
		'dashicons-before dashicons-buddicons-tracking',
		'dashicons-before dashicons-building',
		'dashicons-before dashicons-businessman',
		'dashicons-before dashicons-calendar-alt',
		'dashicons-before dashicons-calendar',
		'dashicons-before dashicons-camera',
		'dashicons-before dashicons-carrot',
		'dashicons-before dashicons-cart',
		'dashicons-before dashicons-category',
		'dashicons-before dashicons-chart-area',
		'dashicons-before dashicons-chart-bar',
		'dashicons-before dashicons-chart-line',
		'dashicons-before dashicons-chart-pie',
		'dashicons-before dashicons-clipboard',
		'dashicons-before dashicons-clock',
		'dashicons-before dashicons-cloud',
		'dashicons-before dashicons-controls-back',
		'dashicons-before dashicons-controls-forward',
		'dashicons-before dashicons-controls-pause',
		'dashicons-before dashicons-controls-play',
		'dashicons-before dashicons-controls-repeat',
		'dashicons-before dashicons-controls-skipback',
		'dashicons-before dashicons-controls-skipforward',
		'dashicons-before dashicons-controls-volumeoff',
		'dashicons-before dashicons-controls-volumeon',
		'dashicons-before dashicons-dashboard',
		'dashicons-before dashicons-desktop',
		'dashicons-before dashicons-dismiss',
		'dashicons-before dashicons-download',
		'dashicons-before dashicons-edit',
		'dashicons-before dashicons-editor-aligncenter',
		'dashicons-before dashicons-editor-alignleft',
		'dashicons-before dashicons-editor-alignright',
		'dashicons-before dashicons-editor-bold',
		'dashicons-before dashicons-editor-break',
		'dashicons-before dashicons-editor-code',
		'dashicons-before dashicons-editor-contract',
		'dashicons-before dashicons-editor-customchar',
		'dashicons-before dashicons-editor-expand',
		'dashicons-before dashicons-editor-help',
		'dashicons-before dashicons-editor-indent',
		'dashicons-before dashicons-editor-insertmore',
		'dashicons-before dashicons-editor-italic',
		'dashicons-before dashicons-editor-justify',
		'dashicons-before dashicons-editor-kitchensink',
		'dashicons-before dashicons-editor-ltr',
		'dashicons-before dashicons-editor-ol',
		'dashicons-before dashicons-editor-outdent',
		'dashicons-before dashicons-editor-paragraph',
		'dashicons-before dashicons-editor-paste-text',
		'dashicons-before dashicons-editor-paste-word',
		'dashicons-before dashicons-editor-quote',
		'dashicons-before dashicons-editor-removeformatting',
		'dashicons-before dashicons-editor-rtl',
		'dashicons-before dashicons-editor-spellcheck',
		'dashicons-before dashicons-editor-strikethrough',
		'dashicons-before dashicons-editor-table',
		'dashicons-before dashicons-editor-textcolor',
		'dashicons-before dashicons-editor-ul',
		'dashicons-before dashicons-editor-underline',
		'dashicons-before dashicons-editor-unlink',
		'dashicons-before dashicons-editor-video',
		'dashicons-before dashicons-email-alt',
		// 'dashicons-before dashicons-email-alt2',
		'dashicons-before dashicons-email',
		'dashicons-before dashicons-excerpt-view',
		'dashicons-before dashicons-external',
		'dashicons-before dashicons-facebook-alt',
		'dashicons-before dashicons-facebook',
		'dashicons-before dashicons-feedback',
		'dashicons-before dashicons-filter',
		'dashicons-before dashicons-flag',
		'dashicons-before dashicons-format-aside',
		'dashicons-before dashicons-format-audio',
		'dashicons-before dashicons-format-chat',
		'dashicons-before dashicons-format-gallery',
		'dashicons-before dashicons-format-image',
		'dashicons-before dashicons-format-quote',
		'dashicons-before dashicons-format-status',
		'dashicons-before dashicons-format-video',
		'dashicons-before dashicons-forms',
		'dashicons-before dashicons-googleplus',
		'dashicons-before dashicons-grid-view',
		'dashicons-before dashicons-groups',
		'dashicons-before dashicons-hammer',
		'dashicons-before dashicons-heart',
		'dashicons-before dashicons-hidden',
		'dashicons-before dashicons-id-alt',
		'dashicons-before dashicons-id',
		'dashicons-before dashicons-image-crop',
		'dashicons-before dashicons-image-filter',
		'dashicons-before dashicons-image-flip-horizontal',
		'dashicons-before dashicons-image-flip-vertical',
		'dashicons-before dashicons-image-rotate-left',
		'dashicons-before dashicons-image-rotate-right',
		'dashicons-before dashicons-image-rotate',
		'dashicons-before dashicons-images-alt',
		'dashicons-before dashicons-images-alt2',
		'dashicons-before dashicons-index-card',
		'dashicons-before dashicons-info',
		'dashicons-before dashicons-laptop',
		'dashicons-before dashicons-layout',
		'dashicons-before dashicons-leftright',
		'dashicons-before dashicons-lightbulb',
		'dashicons-before dashicons-list-view',
		'dashicons-before dashicons-location-alt',
		'dashicons-before dashicons-location',
		'dashicons-before dashicons-lock',
		'dashicons-before dashicons-marker',
		'dashicons-before dashicons-media-archive',
		'dashicons-before dashicons-media-audio',
		'dashicons-before dashicons-media-code',
		'dashicons-before dashicons-media-default',
		'dashicons-before dashicons-media-document',
		'dashicons-before dashicons-media-interactive',
		'dashicons-before dashicons-media-spreadsheet',
		'dashicons-before dashicons-media-text',
		'dashicons-before dashicons-media-video',
		'dashicons-before dashicons-megaphone',
		// 'dashicons-before dashicons-menu-alt',
		'dashicons-before dashicons-menu',
		'dashicons-before dashicons-microphone',
		'dashicons-before dashicons-migrate',
		'dashicons-before dashicons-minus',
		'dashicons-before dashicons-money',
		'dashicons-before dashicons-move',
		'dashicons-before dashicons-nametag',
		'dashicons-before dashicons-networking',
		'dashicons-before dashicons-no-alt',
		'dashicons-before dashicons-no',
		'dashicons-before dashicons-palmtree',
		'dashicons-before dashicons-paperclip',
		'dashicons-before dashicons-performance',
		'dashicons-before dashicons-phone',
		'dashicons-before dashicons-playlist-audio',
		'dashicons-before dashicons-playlist-video',
		'dashicons-before dashicons-plus-alt',
		'dashicons-before dashicons-plus-light',
		'dashicons-before dashicons-plus',
		'dashicons-before dashicons-portfolio',
		'dashicons-before dashicons-post-status',
		'dashicons-before dashicons-pressthis',
		'dashicons-before dashicons-products',
		'dashicons-before dashicons-randomize',
		'dashicons-before dashicons-redo',
		// 'dashicons-before dashicons-rest-api',
		'dashicons-before dashicons-rss',
		'dashicons-before dashicons-schedule',
		'dashicons-before dashicons-screenoptions',
		'dashicons-before dashicons-search',
		'dashicons-before dashicons-share-alt',
		'dashicons-before dashicons-share-alt2',
		'dashicons-before dashicons-share',
		'dashicons-before dashicons-shield-alt',
		'dashicons-before dashicons-shield',
		'dashicons-before dashicons-slides',
		'dashicons-before dashicons-smartphone',
		'dashicons-before dashicons-smiley',
		'dashicons-before dashicons-sort',
		'dashicons-before dashicons-sos',
		'dashicons-before dashicons-star-empty',
		'dashicons-before dashicons-star-filled',
		'dashicons-before dashicons-star-half',
		'dashicons-before dashicons-sticky',
		'dashicons-before dashicons-store',
		'dashicons-before dashicons-tablet',
		'dashicons-before dashicons-tag',
		'dashicons-before dashicons-tagcloud',
		'dashicons-before dashicons-testimonial',
		'dashicons-before dashicons-text',
		'dashicons-before dashicons-thumbs-down',
		'dashicons-before dashicons-thumbs-up',
		'dashicons-before dashicons-tickets-alt',
		'dashicons-before dashicons-tickets',
		// 'dashicons-before dashicons-tide',
		'dashicons-before dashicons-translation',
		'dashicons-before dashicons-trash',
		'dashicons-before dashicons-twitter',
		'dashicons-before dashicons-undo',
		'dashicons-before dashicons-universal-access-alt',
		'dashicons-before dashicons-universal-access',
		'dashicons-before dashicons-unlock',
		'dashicons-before dashicons-update',
		'dashicons-before dashicons-upload',
		'dashicons-before dashicons-vault',
		'dashicons-before dashicons-video-alt',
		'dashicons-before dashicons-video-alt2',
		'dashicons-before dashicons-video-alt3',
		'dashicons-before dashicons-visibility',
		'dashicons-before dashicons-warning',
		'dashicons-before dashicons-welcome-add-page',
		'dashicons-before dashicons-welcome-comments',
		'dashicons-before dashicons-welcome-learn-more',
		'dashicons-before dashicons-welcome-view-site',
		'dashicons-before dashicons-welcome-widgets-menus',
		'dashicons-before dashicons-welcome-write-blog',
		'dashicons-before dashicons-wordpress-alt',
		'dashicons-before dashicons-wordpress',
		'dashicons-before dashicons-yes-alt',
		'dashicons-before dashicons-yes',
	);

	return apply_filters('wu_icons_list', $all_icons);

} // end wu_get_icons_list;

/**
 * Tries to switch to a site to run the callback, before returning.
 *
 * @since 2.0.0
 *
 * @param array|string $callback Callable to run.
 * @param int          $site_id Site to switch to. Defaults to main site.
 * @return mixed
 */
function wu_switch_blog_and_run($callback, $site_id = false) {

	if (!$site_id) {

		$site_id = wu_get_main_site_id();

	} // end if;

	is_multisite() && switch_to_blog($site_id);

	$result = call_user_func($callback);

	is_multisite() && restore_current_blog();

	return $result;

} // end wu_switch_blog_and_run;

/**
 * Turns a multi-dimensional array into a flat array.
 *
 * @since 2.0.0
 *
 * @param array   $array The array to flatten.
 * @param boolean $indexes If we need to add the indexes as well.
 * @return array
 */
function wu_array_flatten($array, $indexes = false) {

	$return = array();

	array_walk_recursive($array, function($x, $index) use (&$return, $indexes) {

		if ($indexes) {

			$return[] = $index;

		} // end if;

		$return[] = $x;

	});

	return $return;

} // end wu_array_flatten;

/**
 * Checks if we are in debug mode.
 *
 * @since 2.0.0
 * @return bool
 */
function wu_is_debug() {

	return (defined('WP_DEBUG') && WP_DEBUG) || (defined('WP_ULTIMO_DEBUG') && WP_ULTIMO_DEBUG);

} // end wu_is_debug;

/**
 * Returns the PHP input (php://input) as JSON.
 *
 * @since 2.0.0
 * @return object
 */
function wu_get_input() {

	$body = @file_get_contents('php://input'); // phpcs:ignore

	return json_decode($body);

} // end wu_get_input;
