<?php
/**
 * Payment Received Email Template
 *
 * @since 2.0.0
 */
?>
<p><?php printf(__('Hey %s,', 'wp-ultimo'), '{{customer_name}}'); ?></p>

<p><?php printf(__('We have great news! We successfully processed your payment of %1$s for %2$s.', 'wp-ultimo'), '{{payment_total}}', '{{payment_product_names}}'); ?></p>

<p><a href="{{payment_invoice_url}}" style="text-decoration: none;" rel="nofollow"><?php _e('Download Invoice', 'wp-ultimo'); ?></a></p>

<h2><b><?php _e('Payment', 'wp-ultimo'); ?></b></h2>

<table cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse;">
  <tbody>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Products', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fff; border: 1px solid #eee; border: 1px solid #eee;">
        {{payment_product_names}}
      </td>
    </tr>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Subtotal', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fff; border: 1px solid #eee;">
        {{payment_subtotal}}
      </td>
    </tr>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Tax', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fff; border: 1px solid #eee;">
        {{payment_tax_total}}
      </td>
    </tr>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Total', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fff; border: 1px solid #eee;">
        {{payment_total}}
      </td>
    </tr>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Processed at', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fff; border: 1px solid #eee;">{{payment_date_created}}</td>
    </tr>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9;"><b><?php _e('Invoice', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">
        <a href="{{payment_invoice_url}}" style="text-decoration: none;" rel="nofollow">
          <?php _e('Download PDF', 'wp-ultimo'); ?>
        </a>
      </td>
    </tr>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Type', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fff; border: 1px solid #eee;">Initial Payment</td>
    </tr>
  </tbody>
</table>
