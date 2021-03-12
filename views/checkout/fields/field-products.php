<?php
/**
 * Products field view.
 *
 * @since 2.0.0
 */
?>
<p class="<?php echo esc_attr($field->wrapper_classes); ?>">

  <label class="wu-block" for="field-<?php echo esc_attr($field->id); ?>">

    <?php echo $field->title; ?>

  </label>

  <?php foreach (wu_get_plans() as $option) : ?>

    <label class="wu-block" for="field-<?php echo esc_attr($field->id); ?>-<?php echo esc_attr($option->get_id()); ?>">

      <input id="field-products-<?php echo esc_attr($option->get_id()); ?>" type="checkbox" name="products[]" value="<?php echo esc_attr($option->get_id()); ?>" <?php echo $field->get_html_attributes(); ?> <?php checked($field->value == $option->get_id()); ?> v-model="products">

      <?php echo $option->get_name(); ?>

    </label>

  <?php endforeach; ?>

  <span v-cloak class="wu-block wu-bg-red-100 wu-p-2" v-if="get_error('<?php echo esc_attr($field->id); ?>')" v-html="get_error('<?php echo esc_attr($field->id); ?>').message">
  </span>

  <!-- <input class="form-control wu-w-full wu-my-1 <?php echo esc_attr($field->classes); ?>" id="field-<?php echo esc_attr($field->id); ?>" name="<?php echo esc_attr($field->id); ?>" type="<?php echo esc_attr($field->type); ?>" placeholder="<?php echo esc_attr($field->placeholder); ?>" value="<?php echo esc_attr($field->value); ?>" <?php echo $field->get_html_attributes(); ?>>

  <span v-cloak class="wu-block wu-bg-red-100 wu-p-2" v-if="get_error('<?php echo esc_attr($field->id); ?>')" v-html="get_error('<?php echo esc_attr($field->id); ?>').message">
  </span> -->

</p>
