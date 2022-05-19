<?php
/**
 * Runcloud instructions view.
 *
 * @since 2.0.0
 */
?>
<h1>
<?php _e('Instructions', 'wp-ultimo'); ?></h1>

<p class="wu-text-lg wu-text-gray-600 wu-my-4 wu-mb-6">

  <?php _e('You’ll need to get your', 'wp-ultimo'); ?> <strong><?php _e('API Key', 'wp-ultimo'); ?></strong> <?php _e('and', 'wp-ultimo'); ?> <strong><?php _e('API Secret', 'wp-ultimo'); ?></strong>, <?php _e('as well as find the', 'wp-ultimo'); ?> <strong><?php _e('Server ID', 'wp-ultimo'); ?></strong> <?php _e('and', 'wp-ultimo'); ?> <strong><?php _e('APP ID', 'wp-ultimo'); ?></strong> <?php _e('for your WordPress application', 'wp-ultimo'); ?>.

</p>

<h3 class="wu-m-0 wu-py-4 wu-text-lg" id="step-1-getting-the-api-key-and-secret">
  <?php _e('Getting the API Key and API Secret', 'wp-ultimo'); ?>
</h3>

<p class="wu-text-sm">
  <?php _e('On your RunCloud admin panel, click the cog icon at the top-right corner to go to the settings page', 'wp-ultimo'); ?>.
</p>

<div class="">
  <img class="" src="https://downloads.intercomcdn.com/i/o/96714761/44a4c8aa07818dfa1d033ab1/Capto_Capture-2018-05-07_09-28-03_AM.png">
</div>

<p class="wu-text-center"><i><?php _e('Settings Page Link', 'wp-ultimo'); ?></i></p>

<p class="wu-text-sm"><?php _e('On the new page, click in the', 'wp-ultimo'); ?><b> <?php _e('API Key', 'wp-ultimo'); ?> </b> <?php _e('menu item on the left', 'wp-ultimo'); ?>.</p>

<div class="">
  <img class="wu-w-full" src="https://downloads.intercomcdn.com/i/o/96714765/4776de1d93681c6ac780f678/Screen-Shot-2018-05-07-at-09.30.43.png">
</div>
<p class="wu-text-center"><i><?php _e('Copy the API key and secret values', 'wp-ultimo'); ?></i></p>
<p class="wu-text-sm"> <?php _e('Copy the', 'wp-ultimo'); ?> <b> <?php _e('API Key and Secret values', 'wp-ultimo'); ?> </b>, <?php _e('we will need them in the next steps', 'wp-ultimo'); ?>. <b> <?php _e('YOU ALSO NEED TO CHANGE THE ENABLE API ACCESS TO “ENABLE”', 'wp-ultimo'); ?>, </b> <?php _e('otherwise RunCloud won’t accept WP Ultimo API calls', 'wp-ultimo'); ?>.</p>
<div class="">
  <img class="wu-w-full" src="https://downloads.intercomcdn.com/i/o/96714772/199efd0c89ab16ae7b678811/Screen-Shot-2018-05-07-at-09.32.43.png">
</div>
<p class="wu-text-center"><i><?php _e('On that same page, you also need to change the API access option to “Enable”', 'wp-ultimo'); ?></i></p>

  <h3 class="wu-m-0 wu-py-4 wu-text-lg" id="step-1-getting-the-api-key-and-secret">
  <?php _e('Getting the Server and App IDs', 'wp-ultimo'); ?>
</h3>
<p class="wu-text-sm"><?php _e('To find what are the server and app ids for your application, navigate to its manage page inside the RunCloud panel. Once you are there, you’ll be able to extract the values from the URL', 'wp-ultimo'); ?>.</p>
<div class=""><img class="wu-w-full" src="https://downloads.intercomcdn.com/i/o/96714775/8b97f2e047f86e449f663321/Screen-Shot-2018-05-07-at-09.35.30.png">
</div>
<div class=""><img class="wu-w-full" src="https://downloads.intercomcdn.com/i/o/96714784/2d25046a319547dd37da8490/Capto_Capture-2018-05-07_09-14-09_AM.png">
</div>
<p class="wu-text-center"><i><?php _e('Server and App ID values are on the URL', 'wp-ultimo'); ?></i></p>
<p class="wu-text-sm"><?php _e('Save the Server and APP id values as they will be necessary in the next step', 'wp-ultimo'); ?>.</p>
