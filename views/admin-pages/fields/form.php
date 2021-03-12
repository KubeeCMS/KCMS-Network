<?php
/**
 * Form view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-styling">
    
  <?php echo $form->before; ?>

  <?php if ($form->wrap_in_form_tag) : ?>

    <form 
      id="<?php echo esc_attr($form_slug); ?>" 
      action="<?php echo esc_attr($form->action); ?>"
      method="<?php echo esc_attr($form->method); ?>"
      <?php echo $form->get_html_attributes(); ?>
    >

  <?php endif; ?>

  <ul id="wp-ultimo-form-<?php echo esc_attr($form->id); ?>" class="<?php echo esc_attr($form->classes); ?>" <?php echo $form->get_html_attributes(); ?>>

    <?php echo $rendered_fields; ?>

  </ul>

  <?php if ($form->wrap_in_form_tag) : ?>

  </form>

  <?php endif; ?>

  <?php echo $form->after; ?>

</div>
