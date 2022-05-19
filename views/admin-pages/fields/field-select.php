<?php
/**
 * Select field view.
 *
 * @since 2.0.0
 */
?>
<li class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php echo $field->get_wrapper_html_attributes(); ?>>

  <div class="wu-block wu-w-full">

    <?php

    /**
     * Adds the partial title template.
     * @since 2.0.0
     */
    wu_get_template('admin-pages/fields/partials/field-title', array(
      'field' => $field,
    ));

    ?>

    <select class="form-control wu-w-full wu-my-1" name="<?php echo esc_attr($field->id); ?><?php echo isset($field->html_attr['multiple']) && $field->html_attr['multiple'] ? '[]' : ''; ?>" <?php echo $field->get_html_attributes(); ?> placeholder="<?php echo $field->placeholder; ?>">

      <?php foreach ($field->options as $option_value => $option_label) : ?>

        <option <?php selected($option_value === $field->value || (is_array($field->value) && in_array($option_value, $field->value))); ?> value="<?php echo esc_attr($option_value); ?>">

          <?php echo $option_label; ?>

        </option>

      <?php endforeach; ?>

      <?php if ($field->options_template) : ?>

        <?php echo $field->options_template; ?>

      <?php endif; ?>

    </select>

    <?php

    /**
     * Adds the partial title template.
     * @since 2.0.0
     */
    wu_get_template('admin-pages/fields/partials/field-description', array(
      'field' => $field,
    ));

    ?>

  </div>

</li>
