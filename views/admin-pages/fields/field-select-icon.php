<?php
/**
 * Select icon field view.
 *
 * @since 2.0.0
 */
?>
<li class="<?php echo esc_attr($field->wrapper_classes); ?>" <?php echo $field->get_wrapper_html_attributes(); ?>>

  <div class="wu-block wu-w-full">

    <h3 class="wu-my-1 wu-text-2xs wu-uppercase">

      <?php echo $field->title; ?>

      <?php if ($field->tooltip) : ?>

        <?php echo wu_tooltip($field->tooltip); ?>

      <?php endif; ?>

    </h3>

    <?php if ($field->desc) : ?>

      <?php echo $field->desc; ?>

    <?php endif; ?>

    <div class="wu-flex wu-flex-wrap wu--mx-2 wu-mt-2">

      <?php foreach ($field->options as $option_value => $option) : ?>

        <div class="wu-w-1/3 wu-p-2 wu-box-border wu-flex <?php echo esc_attr($field->classes); ?>" style="height: 110px;">

          <label class="wu-w-full wu-relative wu-rounded wu-p-4 wu-border-solid wu-border wu-flex wu-items-center wu-justify-center wu-bg-gray-100 wu-text-gray-500 wu-border-gray-300" v-bind:class="require('<?php echo esc_attr($field->id); ?>', '<?php echo esc_attr($option_value); ?>') ? 'wu-bg-gray-200 wu-text-gray-700 wu-border-gray-400 selected' : '' " for="<?php echo esc_attr($field->id.'-'.$option_value); ?>">

            <div class="wu-text-center">

              <span class="wu-block wu-text-2xl wu-mb-2 <?php echo esc_attr($option['icon']); ?>"></span>

              <input class="wu-w-0 wu-h-0 wu-hidden" id="<?php echo esc_attr($field->id.'-'.$option_value); ?>" type="radio" <?php checked($option_value, $field->value); ?> value="<?php echo esc_attr($option_value); ?>" name="<?php echo esc_attr($field->id); ?>" <?php echo $field->get_html_attributes(); ?>>

              <span class="wu-uppercase wu-text-2xs wu-font-semibold">

                <?php echo $option['title']; ?>

              </span>

            </div>

          </label>

        </div>

      <?php endforeach; ?>

    </div>

  </div>

</li>
