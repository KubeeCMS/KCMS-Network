<?php
/**
 * Groupd field view.
 *
 * @since 2.0.0
 */
?>
<li class="<?php echo esc_attr($field->wrapper_classes); ?>" <?php echo $field->get_wrapper_html_attributes(); ?>>

  <div class="wu-block wu-w-full <?php echo esc_attr($field->classes); ?>">

  	<?php if ($field->title) : ?>

      <h3 class="wu-my-1 wu-text-2xs wu-uppercase">

		<?php echo $field->title; ?>

		<?php if ($field->tooltip) : ?>

			<?php echo wu_tooltip($field->tooltip); ?>

      <?php endif; ?>

      </h3>

    <?php endif; ?>

    <?php

    /**
     * Instantiate the form for the order details.
     *
     * @since 2.0.0
     */
    $form = new \WP_Ultimo\UI\Form($field->id, $field->fields, array(
		'views'                 => 'admin-pages/fields',
		'classes'               => 'wu-flex',
		'field_wrapper_classes' => 'wu-bg-transparent',
    ));

    $form->render();

    ?>

    <?php if ($field->desc) : ?>

      <span class="wu-mt-2 wu-block wu-bg-gray-100 wu-rounded wu-border-solid wu-border-gray-400 wu-border-t wu-border-l wu-border-b wu-border-r wu-text-xs wu-py-2 wu-p-2">

        <?php echo $field->desc; ?>

      </span>

		<?php endif; ?>

  </div>

</li>
