<?php
/**
 * Widget payload view.
 *
 * @since 2.0.0
 */
?>
<ul id="wu_payload" class="wu-widget-list wu-striped wu-m-0 wu--my-2 wu--mx-3">

  <li class="wu-p-4 wu-m-0" v-show="!loading" v-cloak>

    <pre id="wu_payload_content" v-html="payload" class="wu-overflow-auto wu-p-4 wu-m-0 wu-mt-2 wu-rounded wu-content-center wu-bg-gray-800 wu-text-white wu-font-mono wu-border wu-border-solid wu-border-gray-300 wu-max-h-screen wu-overflow-y-auto"><?php echo $payload; ?></pre>

  </li>

  <div v-show="loading" class="wu-block wu-p-4 wu-py-8 wu-mb-0 wu-bg-white wu-text-center wu-my-4 wu-rounded">

    <span class="wu-blinking-animation wu-text-gray-600 wu-my-1 wu-mb-0 wu-text-2xs wu-uppercase wu-font-semibold" >

      <?php echo $loading_text; ?>

    </span>

  </div>

</ul>

<div class="wu-bg-gray-200 wu-px-4 wu-py-3 wu--m-3 wu-mt-3">

  <textarea cols="100" rows="40" aria-hidden="true" class="screen-reader-text" id="hidden_textarea"><?php echo $payload; ?></textarea>

  <span>
    <button type="button" data-clipboard-action="copy" data-clipboard-target="#hidden_textarea" class="btn-clipboard button">
      <?php _e('Copy to the Clipboard', 'wp-ultimo'); ?>
    </button>
  </span>

</div>
