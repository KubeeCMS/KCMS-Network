<?php
/**
 * Select field view.
 *
 * @since 2.0.0
 */
?>
<p class="<?php echo esc_attr($field->wrapper_classes); ?>">

  <label class="wu-block" for="field-<?php echo esc_attr($field->id); ?>">

    <?php echo $field->title; ?>

    <?php if ($field->required): ?>

      <span class="wu-text-red-500">*</span>

    <?php endif; ?>

    <?php echo wu_tooltip($field->tooltip); ?>

  </label>

  <select
    class="form-control wu-w-full wu-my-1 <?php echo esc_attr($field->classes); ?>"
    id="field-<?php echo esc_attr($field->id); ?>"
    name="<?php echo esc_attr($field->id); ?>"
    value="<?php echo esc_attr($field->value); ?>"
    <?php echo $field->get_html_attributes(); ?>
  >

  <?php if ($field->placeholder) : ?>

    <option <?php checked(!$field->value); ?> class="wu-opacity-75"><?php echo $field->placeholder; ?></option>

  <?php endif; ?>

  <?php foreach ($field->options as $key => $label) : ?>

    <option
      value="<?php echo esc_attr($key); ?>"
      <?php checked($key, $field->value); ?>
    >
      <?php echo $label; ?>
    </option>

  <?php endforeach; ?>

  </select>

  <span v-cloak class="wu-block wu-bg-red-100 wu-p-2" v-if="get_error('<?php echo esc_attr($field->id); ?>')" v-html="get_error('<?php echo esc_attr($field->id); ?>').message">
  </span>

</p>
