<?php
/**
 * Domain Mapping Created Email Template
 *
 * @since 2.0.0
 */
?>
<p><?php _e('Hey there', 'wp-ultimo'); ?></p>

<p><?php printf(__('A new domain, %2$s, was added to the site %3$s.', 'wp-ultimo'), '{{customer_name}}', '{{domain_domain}}', '{{site_title}}'); ?></p>

<h2><b><?php _e('Domain', 'wp-ultimo'); ?></b></h2>

<table cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse;">
  <tbody>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Domain', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fff; border: 1px solid #eee; border: 1px solid #eee;">
       {{domain_domain}}
      </td>
    </tr>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('ID', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">
        <a href="{{domain_admin_url}}" style="text-decoration: none;" rel="nofollow">{{domain_id}}</a>
      </td>
    </tr>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Domain Stage', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">
        <code>{{domain_stage}}</code>
      </td>
    </tr>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Active', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">
        <code>{{domain_active}}</code>
      </td>
    </tr>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Primary', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">
        <code>{{domain_primary}}</code>
      </td>
    </tr>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Secure', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee;">
        <code>{{domain_secure}}</code>
      </td>
    </tr>
    <tr>
      <td style="text-align: right; width: 160px; padding: 8px; background: #f9f9f9; border: 1px solid #eee;"><b><?php _e('Admin Panel', 'wp-ultimo'); ?></b></td>
      <td style="padding: 8px; background: #fff; border: 1px solid #eee;">
        <a href="{{domain_manage_url}}" style="text-decoration: none;" rel="nofollow"><?php _e('Go to Domain &rarr;', 'wp-ultimo'); ?></a>
      </td>
    </tr>
  </tbody>
</table>

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
