<?php
/**
 * Link field view.
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

    <a class="form-control <?php echo esc_attr($field->classes); ?>" type="<?php echo esc_attr($field->type); ?>" <?php echo $field->get_html_attributes(); ?>>
        <?php echo $field->display_value; ?>
    </a>

  </span>

</li>
