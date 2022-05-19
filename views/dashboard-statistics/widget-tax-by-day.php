<?php
/**
 * Total widget view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-styling">

  <div class="wu-widget-inset">

  <?php

  $data = array();
  $slug = 'taxes_by_day';
	$headers = array(
		__('Day', 'wp-ultimo'),
		__('Orders', 'wp-ultimo'),
		__('Total Sales', 'wp-ultimo'),
		__('Tax Total', 'wp-ultimo'),
		__('Net Profit', 'wp-ultimo'),
	);

	foreach ($taxes_by_day as $day => $tax_line) {

		$line = array(
			date_i18n(get_option('date_format'), strtotime($day)),
			$tax_line['order_count'],
			wu_format_currency($tax_line['total']),
			wu_format_currency($tax_line['tax_total']),
			wu_format_currency($tax_line['net_profit'])
		);

    $data[] = $line;

	} // end foreach;

	$page->render_csv_button(array(
		'headers' => $headers,
		'data'    => $data,
		'slug'    => $slug
	));

	?>

    <table class="wp-list-table widefat fixed striped wu-border-none">

        <thead>
          <tr>
            <th class="wu-w-1/3"><?php _e('Day', 'wp-ultimo'); ?></th>
            <th><?php _e('Orders', 'wp-ultimo'); ?></th>
            <th><?php _e('Total Sales', 'wp-ultimo'); ?></th>
            <th><?php _e('Tax Total', 'wp-ultimo'); ?></th>
            <th><?php _e('Net Profit', 'wp-ultimo'); ?></th>
          </tr>
        </thead>

        <tbody>

          <?php if ($taxes_by_day) : ?>

            <?php foreach ($taxes_by_day as $day => $tax_line) : ?>

              <tr>
                <td>
                  <?php echo date_i18n(get_option('date_format'), strtotime($day)); ?>
                </td>
                <td>
                  <?php echo $tax_line['order_count']; ?>
                </td>
                <td>
                  <?php echo wu_format_currency($tax_line['total']); ?>
                </td>
                <td>
                  <?php echo wu_format_currency($tax_line['tax_total']); ?>
                </td>
                <td>
                  <?php echo wu_format_currency($tax_line['net_profit']); ?>
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
