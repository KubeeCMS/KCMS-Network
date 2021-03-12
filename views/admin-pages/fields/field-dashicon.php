<?php
/**
 * Select Dashicon field view.
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

    <select class="wu_select_icon" name="<?php echo esc_attr($field->id); ?>">

        <option value=""><?php echo __('No Icon','wp-ultimo'); ?></option>

        <?php foreach (wu_get_icons_list() as $category_label => $category_array) : ?>

          <optgroup label="<?php echo $category_label; ?>">

            <?php foreach ($category_array as $option_key => $option_value) : ?>

              <option
                value="<?php echo esc_attr($option_value); ?>"
                <?php selected($field->value, $option_value); ?>
              >
                <?php echo $option_value; ?>
              </option>

            <?php endforeach; ?>

          </optgroup>

        <?php endforeach; ?>

    </select>

  </span>

</li>
