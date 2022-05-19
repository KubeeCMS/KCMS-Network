<?php
/**
 * Note field view.
 *
 * @since 2.0.0
 */
?>
<li class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php echo $field->get_wrapper_html_attributes(); ?>>

  <?php

  /**
   * Adds the partial title template.
   * @since 2.0.0
   */
  wu_get_template('admin-pages/fields/partials/field-title', array(
    'field' => $field,
  ));

  ?>

  <div class="<?php echo esc_attr('wu-my-0 '.$field->classes); ?>">

    <?php echo $field->desc; ?>
    
  </div>

</li>
