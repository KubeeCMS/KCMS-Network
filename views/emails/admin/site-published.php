<?php
/**
 * Site Published Email Template - Admin
 *
 * @since 2.0.0
 */
?>
<p><?php _e('Hey there', 'wp-ultimo'); ?></p>

<p><?php printf(__('A new website, <b>%1$s</b> (%2$s), was created successfully on your network!', 'wp-ultimo'), '{{site_title}}', '<a href="{{site_url}}" style="text-decoration: none;" rel="nofollow">{{site_url}}</a>'); ?></p>

<h2><b><?php _e('Site', 'wp-ultimo'); ?></b></h2>

<table cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse;">
  <tbody>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Title', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fff; border: 1px solid #eee; border: 1px solid #eee;">
        {{site_title}}
      </td>
    </tr>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('ID', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">
        <a href="{{site_admin_url}}" style="text-decoration: none;" rel="nofollow">{{site_id}}</a>
      </td>
    </tr>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Site URL', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fff; border: 1px solid #eee;">
        <a href="{{site_url}}" style="text-decoration: none;" rel="nofollow"><?php _e('Visit Site &rarr;', 'wp-ultimo'); ?></a>
      </td>
    </tr>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Site Admin Panel', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fff; border: 1px solid #eee;">
        <a href="{{site_admin_url}}" style="text-decoration: none;" rel="nofollow"><?php _e('Visit Admin Panel &rarr;', 'wp-ultimo'); ?></a>
      </td>
    </tr>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Admin Panel', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fff; border: 1px solid #eee;">
        <a href="{{site_manage_url}}" style="text-decoration: none;" rel="nofollow"><?php _e('Go to Site Management &rarr;', 'wp-ultimo'); ?></a>
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

