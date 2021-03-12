<?php
/**
 * Total widget view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-styling">

  <div class="wu-widget-inset">

    <table class="wp-list-table widefat fixed striped wu-border-none">

        <thead>
          <tr>
            <th><?php _e('Tax', 'wp-ultimo'); ?></th>
            <th><?php _e('Rate', 'wp-ultimo'); ?></th>
            <th><?php _e('Orders', 'wp-ultimo'); ?></th>
            <th><?php _e('Tax Total', 'wp-ultimo'); ?></th>
          </tr>
        </thead>

        <tbody>

          <?php if ($taxes_by_rate) : ?>

            <?php foreach ($taxes_by_rate as $tax_line) : ?>

              <tr>
                <td>
                  <?php echo wu_get_isset($tax_line, 'title', 'No Name'); ?>
                </td>
                <td>
                  <?php echo $tax_line['tax_rate']; ?>%
                </td>
                <td>
                  <?php echo $tax_line['order_count']; ?>
                </td>
                <td>
                  <?php echo wu_format_currency($tax_line['tax_total']); ?>
                </td>
              </tr>

            <?php endforeach; ?>

          <?php else : ?>

              <tr>
                <td colspan="4">
                  <?php _e('No Taxes found.', 'wp-ultimo'); ?>
                </td>
              </tr>
            
          <?php endif; ?>
          
        </tbody>

    </table>

  </div>

</div>
