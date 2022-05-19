<?php
/**
 * Submit field view.
 *
 * @since 2.0.0
 */
?>
<li class="<?php echo esc_attr(trim($field->wrapper_classes).(strpos($field->wrapper_classes, '-bg-') === false ? ' wu-bg-gray-200' : '')); ?>" <?php echo $field->get_wrapper_html_attributes(); ?>>

  <button id="<?php echo esc_attr($field->id); ?>" type="submit" name="submit_button" value="<?php echo esc_attr($field->id); ?>" <?php echo $field->get_html_attributes(); ?> class="<?php echo esc_attr(trim($field->classes)); ?>">

    <?php echo $field->title; ?>

  </button>

</li>
