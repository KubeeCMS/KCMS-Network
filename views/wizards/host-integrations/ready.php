<?php
/**
 * Host integrations ready view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-bg-white wu-p-4 wu--mx-6 wu-flex wu-content-center" style="height: 250px;">

  <div class="wu-self-center wu-text-center wu-w-full">
    <span class="dashicons dashicons-yes-alt wu-text-green-400 wu-w-auto wu-h-auto wu-text-5xl wu-mb-2"></span>
    <h1>
      <?php _e('That\'s it! We are ready!', 'wp-ultimo'); ?>
    </h1>
    <p class="wu-text-lg wu-text-gray-600 wu-my-4">
      <?php printf(__('The integration with %s was correctly setup and is now ready! Now, every time a new domain is added to your platform, WP Ultimo will sync that with your application automatically.', 'wp-ultimo'), $integration->get_title()); ?>
    </p>
  </div>

</div>

<!-- Submit Box -->
<div class="wu-bg-gray-100 wu--m-in wu-mt-4 wu-p-4 wu-overflow-hidden wu-border-t wu-border-solid wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300">

  <span class="wu-float-right">

    <a href="<?php echo esc_url(wu_network_admin_url('wp-ultimo')); ?>" class="button button-primary button-large">
     <?php _e('Finish!', 'wp-ultimo'); ?>
    </a>

  </span>

</div>
<!-- End Submit Box -->

