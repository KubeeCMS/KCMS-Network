<?php
/**
 * Handles Beaver Builder Widget Support.
 *
 * @package WP_Ultimo\Builders
 * @subpackage Beaver_Builder
 * @since 2.0.0
 */

namespace WP_Ultimo\Builders\Beaver_Builder;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles Beaver Builder Widget Support.
 *
 * @since 2.0.0
 */
class Beaver_Builder_Widget_Manager {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Runs when Beaver_Builder element support is first loaded.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		add_action('wu_element_loaded', array($this, 'handle_element'));

	} // end init;

	/**
	 * Gets called when a new element is registered
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\UI\Base_Element $element The element being registered.
	 * @return void
	 */
	public function handle_element($element) {

		add_action('fl_builder_register_extensions', function() use ($element) {

			$this->register_beaver_module($element);

		});

	} // end handle_element;

	/**
	 * Registers the Beaver Builder Module.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\UI\Base_Element $element The element being registered.
	 * @return void
	 */
	public function register_beaver_module($element) {

		$settings = Beaver_Builder_Base_Module::format_settings($element->fields());

		\FLBuilderModel::$modules[$element->id]                  = new Builders\Beaver_Builder\Beaver_Builder_Base_Module($element->id, $element->get_title());
		\FLBuilderModel::$modules[$element->id]->form            = $settings;
		\FLBuilderModel::$modules[$element->id]->partial_refresh = true;

		add_action('fl_builder_before_render_module', function($module) use ($element) {

			if ($module->settings->type === $element->id) {
				$module->slug                = $element->id;
				$module->name                = $element->get_title();
				$module->settings->class     = '';
				$module->settings->id        = '';
				$module->settings->animation = array(
					'style' => ''
				);
			} // end if;

		} );

		add_filter('fl_ajax_render_new_module', function($result) use ($element) {

			if ($result['type'] === $element->id) {

				$module           = \FLBuilderModel::$modules[$element->id];
				$module->node     = $result['nodeId'];
				$module->parent   = $result['parentId'];
				$module->settings = (object) array(
					'type'      => $element->id,
					'id'        => '',
					'class'     => '',
					'animation' => array(
						'style' => ''
					),
				);

				$module_content = $element->display_template(null);

				ob_start();

				\FLBuilder::render_module_attributes($module);

				$module_attributes = ob_get_clean();

				$display_template = "
					<div $module_attributes>
						<div class='fl-module-content fl-node-content'>
							$module_content
						</div>
					</div>
				";

				$result['layout']['partial']    = true;
				$result['layout']['nodeId']     = $result['nodeId'];
				$result['layout']['nodeType']   = 'module';
				$result['layout']['moduleType'] = $element->id;
				$result['layout']['html']       = $display_template;
				$result['layout']['js']         = 'FLBuilder._renderLayoutComplete();';

			} // end if;

			return $result;

		});

		add_filter('fl_builder_render_module_content', function($content, $module) use ($element) {

			if ($module->settings->type === $element->id) {

				$content = $element->display_template(null);

			} // end if;

			return $content;

		}, 10, 2);

	} // end register_beaver_module;

} // end class Beaver_Builder_Widget_Manager;
