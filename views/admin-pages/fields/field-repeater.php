<?php
/**
 * Repeater field view.
 *
 * @since 2.0.0
 */
?>
<?php if ($field->title) : ?>

  <li id="" class="<?php echo esc_attr($field->wrapper_classes); ?>" <?php echo $field->get_wrapper_html_attributes(); ?>>

    <div class="wu-w-full wu-block">

      <h3 class="wu-my-1 wu-text-2xs wu-uppercase">

        <?php echo $field->title; ?>

        <?php if ($field->tooltip) : ?>

          <?php echo wu_tooltip($field->tooltip); ?>

        <?php endif; ?>

      </h3>

      <?php if ($field->desc) : ?>

        <span class="">

          <?php echo $field->desc; ?>

        </span>

      <?php endif; ?>

    </div>

  </li>

<?php endif; ?>

<?php
  $fields = array();
  foreach ($field->fields as $key => $value) {
    $fields[$key.'[]'] = $field->fields[$key];
  }

  if (is_array($field->values)) {
    $position = 0;
    $field_len = count($field->values);
    foreach ($field->values as $key => $value) {
      $field_id = esc_attr($field->id);

      $field_id .= $position !== $field_len - 1 ? $key : '';
      $position++;
    ?>
      <li id="<?php echo esc_attr($field_id); ?>-line" class="field-repeater <?php echo esc_attr($field->wrapper_classes); ?>" <?php echo $field->get_wrapper_html_attributes(); ?>>

        <div class="wu-w-full <?php echo esc_attr($field->classes); ?>">
    <?php
      foreach ($value as $field_name => $field_value) {
        $fields[$field_name.'[]']['value'] = $field_value;
      }

      $form = new \WP_Ultimo\UI\Form($field->id, $fields, array(
        'views'                 => 'admin-pages/fields',
        'classes'               => 'wu-flex',
        'field_wrapper_classes' => 'wu-bg-transparent',
      ));

      $form->render();
    ?>
        </div>
      </li>
    <?php

    }
  } else {
  ?>
    <li id="<?php echo esc_attr($field->id); ?>-line" class="field-repeater <?php echo esc_attr($field->wrapper_classes); ?>" <?php echo $field->get_wrapper_html_attributes(); ?>>

      <div class="wu-w-full <?php echo esc_attr($field->classes); ?>">

        <?php
        /**
         * Instantiate the form for the order details.
         *
         * @since 2.0.0
         */
        $form = new \WP_Ultimo\UI\Form($field->id, $fields, array(
          'views'                 => 'admin-pages/fields',
          'classes'               => 'wu-flex',
          'field_wrapper_classes' => 'wu-bg-transparent',
        ));

        $form->render();

        ?>

      </div>

    </li>
  <?php
  }

?>

<li class="<?php echo esc_attr($field->wrapper_classes); ?>" <?php echo $field->get_wrapper_html_attributes(); ?>>

  <a class="button button-primary" href="#" v-on:click.prevent="duplicate_and_clean($event, '.field-repeater:last')">
      <?php _e('Add new Line', 'wp-ultimo'); ?>
  </a>

</li>
