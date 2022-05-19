<?php
/**
 * Small header field view.
 *
 * @since 2.0.0
 */
?>
<li class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php echo $field->get_wrapper_html_attributes(); ?>>

  <div class="wu-block">

    <?php

    /**
     * Adds the partial title template.
     * @since 2.0.0
     */
    wu_get_template('admin-pages/fields/partials/field-title', array(
      'field' => $field,
    ));

    ?>

    <?php if ($field->desc) : ?>

      <span class="wu-my-1 wu-inline-block wu-text-xs"><?php echo $field->desc; ?></span>

    <?php endif; ?>

  </div>

</li>
