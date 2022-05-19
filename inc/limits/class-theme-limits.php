<?php
/**
 * Handles limitations to post types, uploads and more.
 *
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
class Theme_Limits {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * List of themes that are not available.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $themes_not_available = array();

	/**
	 * Keep a cache of the results as the check is costly.
	 *
	 * @since 2.0.0
	 * @var null|false|string
	 */
	protected $forced_theme = null;

	/**
	 * Runs on the first and only instantiation.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		add_action('wu_sunrise_loaded', array($this, 'load_limitations'));

	} // end init;

	/**
	 * Apply limitations if they are available.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function load_limitations() {

		if (wu_get_current_site()->has_limitations()) {

			add_filter('stylesheet', array($this, 'force_active_theme'));

			add_filter('template', array($this, 'force_active_theme'));

			add_filter('allowed_themes', array($this, 'add_extra_available_themes'));

			add_filter('site_allowed_themes', array($this, 'add_extra_available_themes'));

			add_filter('wp_prepare_themes_for_js', array($this, 'maybe_remove_activate_button'));

			add_action('admin_enqueue_scripts', array($this, 'hacky_remove_activate_button'));

			add_action('admin_footer-themes.php', array($this, 'modify_backbone_template'));

			add_action('customize_changeset_save_data', array($this, 'prevent_theme_activation_on_customizer'), 99, 2);

		} // end if;

	} // end load_limitations;

	/**
	 * Prevents sub-site admins from switching to locked themes inside the customizer.
	 *
	 * @since 2.0.10
	 *
	 * @param array $data The changeset array being saved.
	 * @param array $context The context array with tons of info about the current customizer session.
	 * @return array|void
	 */
	public function prevent_theme_activation_on_customizer($data, $context) {

		if (wu_get_current_site()->has_limitations() === false) {

			return $data;

		} // end if;

		$pending_theme_switch = $context['manager']->is_theme_active() === false;

		if ($pending_theme_switch === false) {

			return $data;

		} // end if;

		$new_theme = $context['manager']->theme()->stylesheet;

		$theme_limitations = wu_get_current_site()->get_limitations()->themes;

		if ($theme_limitations->allowed($new_theme, 'not_available')) {

			$response = array(
				'code'    => 'not-available',
				'message' => __('This theme is not available on your current plan.', 'wp-ultimo'),
			);

			wp_send_json($response, 'not-available');

			exit;

		} // end if;

		return $data;

	} // end prevent_theme_activation_on_customizer;

	/**
	 * Removes the activate button from not available themes.
	 *
	 * This uses a very hack-y approach due to a bug on WordPress
	 * Core. The problem is that the WP code assumes that no one
	 * with the capability of activating themes would be unable
	 * to activate a theme (in cases of external factors for example).
	 *
	 * @todo Send patch to WordPress core.
	 * @since 2.0.0
	 * @return void
	 */
	public function hacky_remove_activate_button() {

		global $pagenow;

		if (!function_exists('wu_generate_upgrade_to_unlock_url')) {

			return;

		} // end if;

		if ($pagenow !== 'themes.php') {

			return;

		} // end if;

		add_wubox();

		$upgrade_button = wu_generate_upgrade_to_unlock_button(__('Upgrade to unlock', 'wp-ultimo'), array(
			'module'  => 'themes',
			'type'    => 'EXTENSION',
			'classes' => 'button',
		));

		wp_localize_script('theme', 'wu_theme_settings', array(
			'themes_not_available' => $this->themes_not_available,
			'replacement_message'  => $upgrade_button,
		));

	} // end hacky_remove_activate_button;

	/**
	 * Modifies the default WordPress theme page template.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function modify_backbone_template() { // phpcs:disable ?>

		<script type="text/javascript">
		
			let content = jQuery("#tmpl-theme").html();

			content = content.replace(new RegExp('(<a class="button activate".*<\/a>)', 'g'), '<# if ( !wu_theme_settings.themes_not_available.includes(data.id) ) { #>$1<# } else { #> {{{ wu_theme_settings.replacement_message.replace("EXTENSION", data.id) }}} <# } #>');

			jQuery("#tmpl-theme").html(content);
		
		</script>

		<?php // phpcs:enable

	} // end modify_backbone_template;

	/**
	 * Checks if a theme needs to have the activate button removed.
	 *
	 * @since 2.0.0
	 *
	 * @param array $themes The list of themes available.
	 * @return array
	 */
	public function maybe_remove_activate_button($themes) {

		if (is_main_site()) {

			return $themes;

		} // end if;

		$theme_limitations = wu_get_current_site()->get_limitations()->themes;

		foreach ($themes as $stylesheet => &$data) {

			$data['notAvailable'] = false;

			if ($theme_limitations->allowed($stylesheet, 'not_available')) {

				$data['actions']['activate'] = '';

				/*
				 * Hack solution due to core WP
				 * not allowing us to filter out the button.
				 */
				$data['notAvailable'] = true;

				/*
				 * Adds to the not available list
				 * for our hack-y solution.
				 */
				$this->themes_not_available[] = $stylesheet;

			} // end if;

		} // end foreach;

		return $themes;

	} // end maybe_remove_activate_button;

	/**
	 * Force the activation of one particularly selected theme.
	 *
	 * @since 2.0.0
	 *
	 * @param string $stylesheet The default theme being used.
	 * @return string
	 */
	public function force_active_theme($stylesheet) {

		if (is_main_site()) {

			return $stylesheet;

		} // end if;

		/*
		 * Checks if we have run this check before as it is costly.
		 */
		if ($this->forced_theme === null) {

			$this->forced_theme = wu_get_current_site()->get_limitations()->themes->get_forced_active_theme();

		} // end if;

		/*
		 * If any valid value is present, return it.
		 */
		if ($this->forced_theme) {

			return $this->forced_theme;

		} // end if;

		return $stylesheet;

	} // end force_active_theme;

	/**
	 * Deactivates the plugins that people are not allowed to use.
	 *
	 * @since 2.0.0
	 *
	 * @param array $themes Array with the plugins activated.
	 * @return array
	 */
	public function add_extra_available_themes($themes) {
		/*
		 * Bail on network admin =)
		 */
		if (is_network_admin()) {

			return $themes;

		} // end if;

		$theme_limitations = wu_get_current_site()->get_limitations()->themes;

		$_themes = $theme_limitations->get_all_themes();

		foreach ($_themes as $theme_stylesheet) {

			$should_appear = $theme_limitations->allowed($theme_stylesheet, 'visible');

			if (!$should_appear && isset($themes[$theme_stylesheet])) {

				unset($themes[$theme_stylesheet]);

			} elseif ($should_appear && !isset($themes[$theme_stylesheet])) {

				$themes[] = $theme_stylesheet;

			} // end if;

		} // end foreach;

		return $themes;

	} // end add_extra_available_themes;

} // end class Theme_Limits;
