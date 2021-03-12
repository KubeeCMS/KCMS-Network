<?php
/**
 * Panel field view.
 *
 * @since 2.0.0
 */
?>
<li class="<?php echo esc_attr($field->wrapper_classes); ?>" data-wu-app="true" data-state='{"edit":false}'>

  <div class="wu-block" v-show="!edit">

    <h3 class="wu-my-1 wu-text-2xs wu-uppercase">

      <?php echo $field->title; ?>

      <?php if ($field->tooltip) : ?>

        <?php echo wu_tooltip($field->tooltip); ?>

      <?php endif; ?>

    </h3>

  </div>

    <div class="wu-block" v-show="!edit">
      <a href="#" class="wu-p-2 wu--m-2 wp-ui-text-highlight wu-rounded-full wu-h-14 wu-w-14" v-on:click="open($event)" data-field="<?php echo esc_attr($field_slug); ?>">
        <?php echo wu_tooltip(__('Edit'), 'dashicons-arrow-right-alt2'); ?>
      </a>
    </div>

    <div v-cloak class="wu-block wu-w-full" v-show="edit">

    </div>

</li>
