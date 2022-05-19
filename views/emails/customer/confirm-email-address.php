<?php
/**
 * Site Published Email Template - Customer
 *
 * @since 2.0.0
 */
?>
<p><?php printf(__('Hey %s,', 'wp-ultimo'), '{{customer_name}}'); ?></p>

<p><?php printf(__('Thanks for creating an account! You\'re only a step away from being ready.', 'wp-ultimo')); ?></p>

<p><?php printf(__('In order to complete the activation of your account, you need to confirm your email address by clicking on the link below.', 'wp-ultimo')); ?></p>

<p>
  <a href="{{verification_link}}" style="text-decoration: none;" rel="nofollow"><?php _e('Verify Email Address &rarr;', 'wp-ultimo'); ?></a>
  <br>
  <small><?php printf(__('or copy the link %s and paste it onto your browser', 'wp-ultimo'), '<code>{{verification_link}}</code>'); ?></small>
</p>
