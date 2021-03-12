<?php
/**
 * Code editor field view.
 *
 * @since 2.0.0
 */
?>
<li class="<?php echo esc_attr($field->wrapper_classes); ?>" <?php echo $field->get_wrapper_html_attributes(); ?>>

  <span class="wu-block wu-w-full">

    <h3 class="wu-my-1 wu-text-2xs wu-uppercase">

      <?php echo $field->title; ?>

      <?php if ($field->tooltip) : ?>

        <?php echo wu_tooltip($field->tooltip); ?>

      <?php endif; ?>

    </h3>

    <textarea id="field-<?php echo esc_attr($field->id); ?>" data-init="0"  data-code-editor="<?php echo esc_attr($field->lang); ?>" class="form-control wu-w-full wu-my-1 <?php echo esc_attr($field->classes); ?>" name="<?php echo esc_attr($field->id); ?>" placeholder="<?php echo esc_attr($field->placeholder); ?>" <?php echo $field->get_html_attributes(); ?>><?php echo esc_attr($field->value); ?></textarea>

  </span>

</li>
