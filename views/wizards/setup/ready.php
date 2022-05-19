<?php
/**
 * Ready view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-bg-white wu-p-4 wu--mx-6 wu-flex wu-content-center" style="height: 400px;">

  <div class="wu-self-center wu-text-center wu-w-full">

    <span class="dashicons dashicons-yes-alt wu-text-green-400 wu-w-auto wu-h-auto wu-text-5xl wu-mb-2"></span>

    <h1 class="wu-text-gray-800">
      <?php echo sprintf(__('We are ready, %s!', 'wp-ultimo'), isset($page->customer->first) ? $page->customer->first : __('my friend', 'wp-ultimo')); ?>
    </h1>

    <p class="wu-text-lg wu-text-gray-600 wu-my-4">
      <?php _e('We told you this was going to be a walk in the park!', 'wp-ultimo'); ?>
    </p>

    <p class="wu-text-lg wu-text-gray-600 wu-my-4">
      <?php _e('You now have everything you need in place to start building your Website as a Service business!', 'wp-ultimo'); ?>
    </p>

    <p class="wu-text-lg wu-text-gray-600 wu-my-4">
      <?php _e('Don\'t worry! We\'ll guide you through the first steps.', 'wp-ultimo'); ?>
    </p>

    <p>
      <a href="https://twitter.com/share" class="twitter-share-button" data-url="https://wpultimo.com" data-text="<?php echo esc_attr('I just created my own premium WordPress site network with #wpultimo'); ?>" data-via="WPUltimo" data-size="large">Tell the World!</a>

			<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
    </p>

  </div>

</div>

<!-- Submit Box -->
<div class="wu-bg-gray-100 wu--m-in wu-mt-4 wu-p-4 wu-overflow-hidden wu-border-t wu-border-solid wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300">

  <span class="wu-float-right">

    <a href="<?php echo esc_url(wu_network_admin_url('wp-ultimo-about')); ?>" class="button button-primary button-large">
     <?php _e('Finish!', 'wp-ultimo'); ?>
    </a>

  </span>

</div>
<!-- End Submit Box -->

