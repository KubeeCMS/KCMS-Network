<?php
/**
 * HTML field view.
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

    <div class="wu-block wu-w-full wu-mt-4 <?php echo esc_attr($field->classes); ?>">

      <?php echo $field->content; ?>

    </div>

  </div>


</li>
