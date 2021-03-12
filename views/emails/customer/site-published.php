<?php
/**
 * Site Published Email Template - Customer
 *
 * @since 2.0.0
 */
?>
<p><?php printf(__('Hey %s,', 'wp-ultimo'), '{{customer_name}}'); ?></p>

<p><?php printf(__('We have great news! The site <b>%1$s</b> (%2$s) was created successfully and is ready!', 'wp-ultimo'), '{{site_title}}', '<a href="{{site_url}}" style="text-decoration: none;" rel="nofollow">{{site_url}}</a>'); ?></p>

<h2><b><?php _e('Your Site', 'wp-ultimo'); ?></b></h2>

<table cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse;">
  <tbody>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Title', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fff; border: 1px solid #eee; border: 1px solid #eee;">
        {{site_title}}
      </td>
    </tr>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('URL', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fff; border: 1px solid #eee;">
        <a href="{{site_url}}" style="text-decoration: none;" rel="nofollow"><?php _e('Visit Site &rarr;', 'wp-ultimo'); ?></a>
      </td>
    </tr>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Admin Panel', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fff; border: 1px solid #eee;">
        <a href="{{site_admin_url}}" style="text-decoration: none;" rel="nofollow"><?php _e('Visit Admin Panel &rarr;', 'wp-ultimo'); ?></a>
      </td>
    </tr>
  </tbody>
</table>
