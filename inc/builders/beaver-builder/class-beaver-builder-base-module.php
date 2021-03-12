<?php
/**
 * Base class to add new Beaver modules.
 *
 * @package WP_Ultimo\UI\Builders
 * @subpackage Base_Element
 * @since 2.0.0
 */

namespace WP_Ultimo\Builders\Beaver_Builder;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Base class to add new Beaver Builder modules.
 *
 * @since 2.0.0
 */
class Beaver_Builder_Base_Module extends \FLBuilderModule {

	/**
	 * Constructor
	 *
	 * @param string $slug slug of the module.
	 * @param string $name name of the module.
	 * @since 2.0.0
	 */
	public function __construct($slug = null, $name = null) {

		$this->name     = $name;
		$this->slug     = $slug;
		$this->category = __( 'Basic', 'wp-ultimo' );
		$this->group    = __( 'WP Ultimo', 'wp-ultimo' );
		$this->dir      = plugin_dir_path( __FILE__ );

	} // end __construct;

	/**
	 * Update settings data before it is saved.
	 *
	 * @param object $settings A settings object that is going to be saved.
	 * @return object
	 * @since 2.0.0
	 */
	public function update($settings) {

		$settings->margin_top_responsive    = '';
		$settings->margin_top               = '';
		$settings->margin_bottom_responsive = '';
		$settings->margin_bottom            = '';
		$settings->margin_left_responsive   = '';
		$settings->margin_left              = '';
		$settings->margin_right_responsive  = '';
		$settings->margin_right             = '';
		$settings->animation                = '';

		return $settings;

	} // end update;

	/**
	 * Format fields to be used by Beaver Builder.
	 *
	 * @param object $fields Fields to set up the module settings.
	 * @return object
	 * @since 2.0.0
	 */
	public static function format_settings($fields) {

		$settings = array(
			'tab' => array(
				'title'    => __('Settings', 'wp-ultimo'),
				'sections' => array(),
			),
		);

		$section = '';

		foreach ($fields as $field_slug => $field) {

			if ('header' === $field['type']) {

				$section = $field_slug;

				$settings['tab']['sections'][$section] = array(
					'title'  => $field['title'],
					'fields' => array()
				);

			} elseif ('toggle' === $field['type']) {

				$settings['tab']['sections'][$section]['fields'][$field_slug] = array(
					'type'    => 'select',
					'label'   => $field['title'],
					'default' => 0,
					'options' => array(
						1 => __('Yes', 'wp-ultimo'),
						0 => __('No', 'wp-ultimo'),
					),
				);

			} // end if;

		} // end foreach;

		return $settings;

	} // end format_settings;

} // end class Beaver_Builder_Base_Module;
