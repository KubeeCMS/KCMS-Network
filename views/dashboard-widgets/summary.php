<?php
/**
 * Summary view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-styling">

  <ul class="md:wu-flex wu-m-0">

    <li class="wu-p-2 wu-w-full md:wu-w-4/12 wu-relative">

      <div>

        <strong class="wu-text-gray-800 wu-text-base">
          <?php echo $signups; ?>
        </strong>

      </div>

      <div class="wu-text-md wu-text-gray-600">
        <span class="wu-block"><?php _e('Signups today', 'wp-ultimo'); ?></span>
      </div>

    </li>

    <li class="wu-p-2 wu-w-full md:wu-w-4/12 wu-relative" <?php echo wu_tooltip_text(__('MRR stands for Monthly Recurring Revenue', 'wp-ultimo')); ?>>

      <div>

        <strong class="wu-text-gray-800 wu-text-base">
          <?php echo wu_format_currency($mrr); ?>
        </strong>

      </div>

      <div class="wu-text-md wu-text-gray-600">
        <span class="wu-block"><?php _e('MRR', 'wp-ultimo'); ?></span>
      </div>

    </li>

    <li class="wu-p-2 wu-w-full md:wu-w-4/12 wu-relative">

      <div>

        <strong class="wu-text-gray-800 wu-text-base">
          <?php echo wu_format_currency($gross_revenue); ?>
        </strong>

      </div>

      <div class="wu-text-md wu-text-gray-600">
        <span class="wu-block"><?php _e('Today\'s gross revenue', 'wp-ultimo'); ?></span>
      </div>

    </li>

  </ul>

</div>
