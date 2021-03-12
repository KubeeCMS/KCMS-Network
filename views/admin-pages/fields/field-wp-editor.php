<?php
/**
 * WP editor field view.
 *
 * @since 2.0.0
 */
?>
<li class="<?php echo esc_attr($field->wrapper_classes); ?>" <?php echo $field->get_wrapper_html_attributes(); ?>>

	<span class="wu-block wu-w-full">

		<label for="<?php echo esc_attr($field->id); ?>">

			<h3 class="wu-my-1 wu-text-2xs wu-uppercase">

				<?php echo $field->title; ?>

				<?php if ($field->tooltip) : ?>

				<?php echo wu_tooltip($field->tooltip); ?>

				<?php endif; ?>

			</h3>

		</label>

		<div>

			<wp-editor 
				name="<?php echo esc_attr($field->id); ?>"
				id="<?php echo esc_attr($field->id); ?>"
				value="<?php echo esc_html($field->value); ?>"
				<?php echo $field->get_html_attributes(); ?>
			/>

		</div>

		<div>

			<p class="description wu-block wu-mt-1" id="<?php echo $field->id; ?>-desc">

				<?php echo $field->desc; ?>

			</p>

		</div>

	</span>

</li>

