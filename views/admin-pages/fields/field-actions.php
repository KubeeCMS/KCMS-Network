<?php
/**
 * Actions field view.
 *
 * @since 2.0.0
 */
?>
<li class="wu-bg-gray-100 <?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php echo $field->get_wrapper_html_attributes(); ?>>

		<?php foreach ($field->actions as $action_slug => $action) : ?>

			<span class="wu-flex wu-flex-wrap wu-content-center">

			<?php $action = new \WP_Ultimo\UI\Field($action_slug, $action); ?>

				<button class="button <?php echo esc_attr($action->classes); ?>" id="action_button" data-action="<?php echo $action->action; ?>" data-object="<?php echo $action->object_id; ?>" value="<?php echo wp_create_nonce($action->action); ?>" <?php echo $field->get_html_attributes(); ?> >

			<?php echo $action->title; ?>

			<?php if ($action->tooltip) : ?>

				<?php echo wu_tooltip($action->tooltip); ?>

					<?php endif; ?>

				</button>

				<span data-loading="wu_action_button_loading_<?php echo $action->object_id; ?>" id="wu_action_button_loading" class="wu-blinking-animation wu-text-gray-600 wu-my-1 wu-text-2xs wu-uppercase wu-font-semibold wu-text-center wu-self-center wu-px-4 wu-py wu-mt-1 hidden" >

			<?php echo $action->loading_text; ?>

				</span>

			</span>

		<?php endforeach; ?>

</li>
