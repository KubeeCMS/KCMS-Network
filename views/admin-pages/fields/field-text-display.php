<?php
/**
 * Text display field view.
 *
 * @since 2.0.0
 */
?>
<li class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php echo $field->get_wrapper_html_attributes(); ?>>

  <div class="wu-block">

    <?php

    /**
     * Adds the partial title template.
     * @since 2.0.0
     */
    wu_get_template('admin-pages/fields/partials/field-title', array(
      'field' => $field,
    ));

    ?>

    <?php if ($field->type === 'date' || $field->date === true) : ?>

      <?php

        if (wu_validate_date($field->value)) {

          $date = $field->value;

          $time = strtotime(get_date_from_gmt($date));

          $formatted_value = date_i18n(get_option('date_format'), $time);

          $placeholder = wu_get_current_time('timestamp') > $time ? __('%s ago', 'wp-ultimo') : __('In %s', 'wp-ultimo'); // phpcs:ignore

          echo sprintf('<time datetime="%3$s">%1$s</time><br><small>%2$s</small>', $formatted_value, sprintf($placeholder, human_time_diff($time, wu_get_current_time('timestamp'))), get_date_from_gmt($date));

        } else {

          _e('None', 'wp-ultimo');

        } // end if;

      ?>

    <?php else : ?>

      <span class="wu-my-1 wu-inline-block">

        <span id="<?php echo $field->id; ?>_value"><?php echo $field->display_value; ?></span>

        <?php if ($field->copy) : ?>

          <a <?php echo wu_tooltip_text(__('Copy', 'wp-ultimo')); ?> class="wu-no-underline wp-ui-text-highlight wu-copy"  data-clipboard-action="copy" data-clipboard-target="#<?php echo $field->id; ?>_value">

            <span class="dashicons-wu-copy wu-align-middle"></span>

          </a>

        <?php endif; ?>

      </span>

    <?php endif; ?>

    <?php

    /**
     * Adds the partial title template.
     * @since 2.0.0
     */
    wu_get_template('admin-pages/fields/partials/field-description', array(
      'field' => $field,
    ));

    ?>

  </div>

</li>
