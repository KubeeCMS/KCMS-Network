<?php
/**
 * Multiselect field view.
 *
 * @since 2.0.0
 */
?>
<li 
  class="<?php echo esc_attr($field->wrapper_classes); ?>" 
  <?php echo $field->get_wrapper_html_attributes(); ?>
>

  <div class="wu-w-full">

    <h3 class="wu-my-1 wu-text-2xs wu-uppercase">

      <?php echo $field->title; ?>

      <?php if ($field->tooltip) : ?>

        <?php echo wu_tooltip($field->tooltip); ?>

      <?php endif; ?>

    </h3>

    <?php if ($field->desc) : ?>

      <p class="wu-my-1">

        <?php echo $field->desc; ?>

      </p>

    <?php endif; ?>

    <ul data-columns="<?php echo esc_attr($field->columns); ?>" class='items wu--mx-1 wu-overflow-hidden wu-multiselect-content wu-static wu-my-2'>

      <?php foreach ($field->options as $value => $option) : ?>

        <li class="item wu-box-border wu-m-0">

          <div class="wu-m-1 wu-bg-gray-100 wu-p-3 wu-m-0 wu-border-gray-300 wu-border-solid wu-border wu-rounded wu-items-center wu-flex wu-justify-between">

            <span class="wu-block">

              <span class="wu-my-1 wu-text-xs wu-font-bold wu-block">

                <?php echo $option['title']; ?>

              </span>

              <?php if (isset($option['desc']) && !empty($option['desc'])) : ?>

                <span class="wu-my-1 wu-inline-block wu-text-xs">

                  <?php echo $option['desc']; ?>

                </span>

              <?php endif; ?>

            </span>

            <span class="wu-block wu-ml-2">

              <div class="wu-toggle">

                <input <?php checked(in_array($value, (array) $field->value, true)); ?> value="<?php echo esc_attr($value); ?>" id="<?php echo esc_attr("{$field->id}_{$value}"); ?>" type="checkbox" name="<?php echo esc_attr("{$field->id}[]"); ?>" class="wu-tgl wu-tgl-ios" <?php echo $field->get_html_attributes(); ?>>

                <label for="<?php echo esc_attr("{$field->id}_{$value}"); ?>" class="wu-tgl-btn wp-ui-highlight"></label>

              </div>

            </span>

          </div>

        </li>

      <?php endforeach; ?>

    </ul>

  </div>

</li>
