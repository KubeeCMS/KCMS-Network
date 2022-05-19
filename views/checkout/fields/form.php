<?php
/**
 * Form fields view.
 *
 * @since 2.0.0
 */
?>
<?php if ($form->wrap_in_form_tag) : ?>

  <form id="<?php echo esc_attr($form_slug); ?>" method="<?php echo esc_attr($form->method); ?>" <?php echo $form->get_html_attributes(); ?>>

<?php else : ?>

  <<?php echo $form->wrap_tag; ?> class="<?php echo esc_attr(trim($form->classes ? $form->classes.' '.$step->classes.' wu-mt-2' : $step->classes.' wu-mt-2')); ?>" <?php echo $form->get_html_attributes(); ?>>

<?php endif; ?>

  <?php if ($form->title) : ?>

    <h3 class="wu-checkout-section-title"><?php echo $form->title; ?></h3>

  <?php endif; ?>

  <?php echo $rendered_fields; ?>

<?php if ($form->wrap_in_form_tag) : ?>

  </form>

<?php else : ?>

  </<?php echo $form->wrap_tag; ?>>

<?php endif; ?>
