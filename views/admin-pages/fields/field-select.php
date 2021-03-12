<?php
/**
 * Select field view.
 *
 * @since 2.0.0
 */
?>
<li class="<?php echo esc_attr($field->wrapper_classes); ?>" <?php echo $field->get_wrapper_html_attributes(); ?>>

  <span class="wu-block wu-w-full">

    <?php if ($field->title) : ?>

      <h3 class="wu-my-1 wu-text-2xs wu-uppercase">

        <?php echo $field->title; ?>

        <?php if ($field->tooltip) : ?>

          <?php echo wu_tooltip($field->tooltip); ?>

        <?php endif; ?>

      </h3>

    <?php endif; ?>

    <select class="form-control wu-w-full wu-my-1" name="<?php echo esc_attr($field->id); ?><?php echo isset($field->html_attr['multiple']) && $field->html_attr['multiple'] ? '[]' : ''; ?>" <?php echo $field->get_html_attributes(); ?> placeholder="<?php echo $field->placeholder; ?>">

      <?php foreach ($field->options as $option_value => $option_label) : ?>

        <option <?php selected($option_value === $field->value || (is_array($field->value) && in_array($option_value, $field->value))); ?> value="<?php echo esc_attr($option_value); ?>">

          <?php echo $option_label; ?>

        </option>

      <?php endforeach; ?>

    </select>

  </span>

</li>
