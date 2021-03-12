<?php
/**
 * Payment Received Email Template
 *
 * @since 2.0.0
 */
?>
<p><?php _e('Hey there', 'wp-ultimo'); ?></p>

<p><?php printf(__('We have great news! You received %1$s from %2$s (%3$s) for %4$s.', 'wp-ultimo'), '{{payment_total}}', '{{customer_name}}', '{{customer_user_email}}', '{{payment_product_names}}'); ?></p>

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
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Paid with', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">{{payment_gateway}}</td>
    </tr>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('ID', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">
        <a href="{{payment_manage_url}}" style="text-decoration: none;" rel="nofollow">{{payment_id}}</a>
      </td>
    </tr>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Reference Code', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">
        <a href="{{payment_manage_url}}" style="text-decoration: none;" rel="nofollow">{{payment_reference_code}}</a>
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
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Admin Panel', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fff; border: 1px solid #eee;">
        <a href="{{payment_manage_url}}" style="text-decoration: none;" rel="nofollow"><?php _e('Go to Payment &rarr;', 'wp-ultimo'); ?></a>
      </td>
    </tr>
  </tbody>
</table>

<h2><b><?php _e('Membership', 'wp-ultimo'); ?></b></h2>

<table cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse;">
  <tbody>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Amount', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fff; border: 1px solid #eee;">
        {{membership_description}}
      </td>
    </tr>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Initial Amount', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fff; border: 1px solid #eee;">
        {{membership_initial_amount}}
      </td>
    </tr>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('ID', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">
        <a href="{{membership_manage_url}}" style="text-decoration: none;" rel="nofollow">{{membership_id}}</a>
      </td>
    </tr>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Reference Code', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">
        <a href="{{membership_manage_url}}" style="text-decoration: none;" rel="nofollow">{{membership_reference_code}}</a>
      </td>
    </tr>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Expiration', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fff; border: 1px solid #eee;">{{membership_date_expiration}}</td>
    </tr>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Admin Panel', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fff; border: 1px solid #eee;">
        <a href="{{membership_manage_url}}" style="text-decoration: none;" rel="nofollow"><?php _e('Go to Membership &rarr;', 'wp-ultimo'); ?></a>
      </td>
    </tr>
  </tbody>
</table>

<h2><b><?php _e('Customer', 'wp-ultimo'); ?></b></h2>

<table cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse;">
  <tbody>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Customer', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fff; border: 1px solid #eee;">
        {{customer_avatar}}<br />
        {{customer_name}}
      </td>
    </tr>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Email Address', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fff; border: 1px solid #eee;">
        <a href="mailto:{{customer_user_email}}" style="text-decoration: none;" rel="nofollow">{{customer_user_email}}</a>
      </td>
    </tr>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('ID', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">
        <a href="{{customer_manage_url}}" style="text-decoration: none;" rel="nofollow">{{customer_id}}</a>
      </td>
    </tr>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Billing Address', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">{{customer_billing_address}}</td>
    </tr>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Admin Panel', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fff; border: 1px solid #eee;">
        <a href="{{customer_manage_url}}" style="text-decoration: none;" rel="nofollow"><?php _e('Go to Customer &rarr;', 'wp-ultimo'); ?></a>
      </td>
    </tr>
  </tbody>
</table>
