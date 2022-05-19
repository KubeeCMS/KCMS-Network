<?php
/**
 * Site Template Placeholders
 *
 * Replaces the content of templates with placeholders.
 *
 * @package WP_Ultimo
 * @subpackage Site_Templates
 * @since 2.0.0
 */

namespace WP_Ultimo\Site_Templates;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Replaces the content of templates with placeholders.
 *
 * @since 2.0.0
 */
class Template_Placeholders {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Keeps a copy of the placeholders as saved.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $placeholders_as_saved = array();

	/**
	 * Keeps an array of placeholder => value.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $placeholders = array();

	/**
	 * Holds the placeholder tags.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $placeholder_keys = array();

	/**
	 * Holds the placeholder values.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $placeholder_values = array();

	/**
	 * Loads the placeholders and adds the hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		$this->load_placeholders();

		add_action('wp_ultimo_admin_pages', array($this, 'add_template_placeholders_admin_page'));

		add_action('wp_ajax_wu_get_placeholders', array($this, 'serve_placeholders_via_ajax'));

		add_action('wp_ajax_wu_save_placeholders', array($this, 'save_placeholders'));

		add_filter('the_content', array($this, 'placeholder_replacer'));

		add_filter('the_title', array($this, 'placeholder_replacer'));

	} // end init;

	/**
	 * Loads the placeholders to keep them "cached".
	 *
	 * @since 2.0.0
	 * @return void
	 */
	protected function load_placeholders() {

		$placeholders = wu_get_option('template_placeholders', array(
			'placeholders' => array(),
		));

		$this->placeholders_as_saved = $placeholders;

		$placeholders = $placeholders['placeholders'];

		$tags   = array_column($placeholders, 'placeholder');
		$values = array_column($placeholders, 'content');

		$tags   = array_map(array($this, 'add_curly_braces'), $tags);
		$values = array_map('nl2br', $values);

		$this->placeholder_keys   = $tags;
		$this->placeholder_values = $values;
		$this->placeholders       = array_combine($this->placeholder_keys, $this->placeholder_values);

		/*
		 * Filter everything.
		 */
		$this->placeholder_keys   = array_filter($this->placeholder_keys);
		$this->placeholder_values = array_filter($this->placeholder_values);
		$this->placeholders       = array_filter($this->placeholders);

	} // end load_placeholders;

	/**
	 * Adds curly braces to the placeholders.
	 *
	 * @since 2.0.0
	 *
	 * @param string $tag The placeholder string.
	 * @return string
	 */
	protected function add_curly_braces($tag) {

		return "{{{$tag}}}";

	} // end add_curly_braces;

	/**
	 * Replace the contents with the placeholders.
	 *
	 * @since 2.0.0
	 *
	 * @param string $content The content of the post.
	 * @return string
	 */
	public function placeholder_replacer($content) {

		return str_replace($this->placeholder_keys, $this->placeholder_values, $content);

	} // end placeholder_replacer;

	/**
	 * Serve placeholders via ajax.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function serve_placeholders_via_ajax() {

		wp_send_json_success($this->placeholders_as_saved);

	} // end serve_placeholders_via_ajax;

	/**
	 * Save the placeholders.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function save_placeholders() {

		if (!check_ajax_referer('wu_edit_placeholders_editing')) {

			wp_send_json(array(
				'code'    => 'not-enough-permissions',
				'message' => __('You don\'t have permission to alter placeholders.', 'wp-ultimo')
			));

		} // end if;

		$data = json_decode(file_get_contents('php://input'), true);

		$placeholders = isset($data['placeholders']) ? $data['placeholders'] : array();

		wu_save_option('template_placeholders', array(
			'placeholders' => $placeholders,
		));

		wp_send_json(array(
			'code'    => 'success',
			'message' => __('Placeholders successfully updated!', 'wp-ultimo'),
		));

	} // end save_placeholders;

	/**
	 * Adds the template placeholders admin page.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_template_placeholders_admin_page() {

		new \WP_Ultimo\Admin_Pages\Placeholders_Admin_Page;

	} // end add_template_placeholders_admin_page;

} // end class Template_Placeholders;
