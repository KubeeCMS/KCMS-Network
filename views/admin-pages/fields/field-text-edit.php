<?php
/**
 * Text edit field view.
 *
 * @since 2.0.0
 */
?>
<li class="<?php echo esc_attr($field->wrapper_classes); ?>" data-wu-app="<?php echo esc_attr($field->id); ?>" data-state='{"edit":false}'>

  <div class="wu-block" v-show="!edit">

    <h3 class="wu-my-1 wu-text-2xs wu-uppercase">

      <?php echo $field->title; ?>

      <?php if ($field->tooltip) : ?>

        <?php echo wu_tooltip($field->tooltip); ?>

      <?php endif; ?>

    </h3>

    <?php if ($field->type === 'date' || $field->date === true) : ?>

      <?php

        if (wu_validate_date($field->value)) {

          if ($field->display_value == false) {

            echo __('No date', 'wp-ultimo');

          } else {

            $date = $field->value;

            $time = strtotime($date, current_time('timestamp')); // phpcs:ignore

            $formatted_value = date_i18n(get_option('date_format'), $time);

            $placeholder = current_time('timestamp') > $time ? __('%s ago', 'wp-ultimo') : __('In %s', 'wp-ultimo'); // phpcs:ignore

            echo $formatted_value.sprintf('<br><small>%s</small>', sprintf($placeholder, human_time_diff($time)));

          } // end if;

        } else {

          _e('None', 'wp-ultimo');

        } // end if;

      ?>

    <?php else : ?>

      <span class="wu-my-1 wu-inline-block">

        <?php echo $field->display_value; ?>

      </span>

    <?php endif; ?>

  </div>

  <?php if ($field->edit) : ?>

    <div class="wu-block" v-show="!edit">
      <a href="#" class="wu-p-2 wu--m-2 wp-ui-text-highlight" v-on:click="open($event)" data-field="<?php echo esc_attr($field_slug); ?>">
        <?php echo wu_tooltip(__('Edit'), 'dashicons-edit'); ?>
      </a>
    </div>

    <div v-cloak class="wu-block wu-w-full" v-show="edit">

      <h3 class="wu-my-1 wu-text-2xs wu-uppercase">

        <?php echo $field->title; ?>

        <?php if ($field->tooltip) : ?>

          <?php echo wu_tooltip($field->tooltip); ?>

        <?php endif; ?>

      </h3>

      <input class="form-control wu-w-full wu-my-1" name="<?php echo esc_attr($field->id); ?>" type="text" placeholder="<?php echo esc_attr($field->placeholder); ?>" value="<?php echo esc_attr($field->value); ?>" <?php echo $field->get_html_attributes(); ?>>

    </div>

  <?php endif; ?>

  <?php if ($field->copy) : ?>

    <div class="wu-block" v-show="!edit">
      <a href="#" class="wu-p-2 wu--m-2" v-on:click="edit($event, '<?php echo esc_js($field_slug); ?>')" data-field="<?php echo esc_attr($field_slug); ?>">
        <?php echo wu_tooltip(__('Copy'), 'dashicons-admin-page'); ?>
      </a>
    </div>

  <?php endif; ?>

</li>
