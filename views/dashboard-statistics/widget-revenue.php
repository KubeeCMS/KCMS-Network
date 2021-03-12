<?php
/**
 * Total widget view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-styling">

  <ul class="md:wu-flex wu-my-0 wu-mx-0">

    <li class="wu-p-2 wu-w-full md:wu-w-4/12 wu-relative" <?php echo wu_tooltip_text(__('MRR stands for Monthly Recurring Revenue', 'wp-ultimo')); ?>>

      <div>

        <strong class="wu-text-gray-800 wu-text-2xl">
          <?php echo wu_format_currency($mrr); ?>
        </strong>

      </div>

      <div class="wu-text-md wu-text-gray-600">
        <span class="wu-block"><?php _e('MRR', 'wp-ultimo'); ?></span>
      </div>

    </li>

    <li class="wu-p-2 wu-w-full md:wu-w-4/12 wu-relative">

      <div>

        <strong class="wu-text-gray-800 wu-text-2xl">
          <?php echo wu_format_currency($gross_revenue); ?>
        </strong>

      </div>

      <div class="wu-text-md wu-text-gray-600">
        <span class="wu-block"><?php _e('Gross Revenue', 'wp-ultimo'); ?></span>
      </div>

    </li>

    <li class="wu-p-2 wu-w-full md:wu-w-4/12 wu-relative">

      <div>

        <strong class="wu-text-gray-800 wu-text-2xl">
          <?php echo wu_format_currency($refunds); ?>
        </strong>

      </div>

      <div class="wu-text-sm wu-text-gray-600">
        <span class="wu-block"><?php _e('Refunded', 'wp-ultimo'); ?></span>
      </div>

    </li>

  </ul>

  <div class="wu--mx-3 wu--mb-3 wu-mt-2">

    <table class="wp-list-table widefat fixed striped wu-border-t-1 wu-border-l-0 wu-border-r-0">

        <thead>
          <tr>
            <th><?php _e('Product', 'wp-ultimo'); ?></th>
            <th><?php _e('New MRR', 'wp-ultimo'); ?></th>
            <th><?php _e('Refunds', 'wp-ultimo'); ?></th>
          </tr>
        </thead>

        <tbody>

          <?php if (wu_get_products()) : ?>
          
            <?php foreach (wu_get_products() as $product) : ?>

              <tr>
                <td>
                  <?php echo $product->get_name(); ?>
                </td>
                <td>--</td>
                <td>--</td>
              </tr>

            <?php endforeach; ?>

          <?php else : ?>

            <tr>
              <td colspan="3">
                <?php _e('No Products found.', 'wp-ultimo'); ?>
              </td>
            </tr>
          
          <?php endif; ?>
          
        </tbody>

    </table>

  </div>

</div>
