<?php
/**
 * HTML field view.
 *
 * @since 2.0.0
 */
?>

<div class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php echo $field->get_wrapper_html_attributes(); ?>>

  <div class="wu-block wu-w-full">

    <?php

    /**
     * Adds the partial title template.
     * @since 2.0.0
     */
    wu_get_template('checkout/fields/partials/field-title', array(
      'field' => $field,
    ));

    /**
     * Adds the partial description template.
     * @since 2.0.0
     */
    wu_get_template('checkout/fields/partials/field-description', array(
      'field' => $field,
    ));

    ?>

    <div class="wu-block wu-w-full wu-mt-4">

      <?php echo $field->content; ?>

    </div>

    <?php

    /**
     * Adds the partial title template.
     * @since 2.0.0
     */
    wu_get_template('checkout/fields/partials/field-errors', array(
      'field' => $field,
    ));

    ?>

  </div>

</div>
