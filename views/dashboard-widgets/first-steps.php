<?php
/**
 * First steps view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-styling odd:wu-styling" style="margin: -12px -12px;">

  <div class="wu-flex wu-p-4 wu-content-center wu-items-center">

    <div class="wu-w-full sm:wu-w-8/12">

      <span class="wu-block wu-my-1 wu-text-base wu-font-semibold wu-text-gray-700">
        <?php _e('Your network is taking shape!', 'wp-ultimo'); ?>
      </span>

      <span class="wu-block wu-my-1 wu-text-gray-600">
        <?php _e('Here are the next steps to keep you on that streak!', 'wp-ultimo'); ?>
      </span>

    </div>

    <div class="wu-w-4/12 wu-text-right wu-hidden sm:wu-inline-block">

      <span class="wu-inline-block wu-bg-green-100 wu-text-center wu-align-middle wu-p-2 wu-font-mono wu-px-3 wu-border wu-border-green-300 wu-text-green-700 wu-border-solid wu-rounded">
        <?php echo $percentage.'% '.__('done', 'wp-ultimo'); ?>
      </span>

    </div>

  </div>

  <ul class="wu-m-0 wu-p-0">

    <?php $index = 1; foreach ($steps as $step_slug => $step) : ?>

      <li
        class="sm:wu-flex wu-py-2 wu-px-4 wu-content-center wu-items-center wu-m-0 wu-border-solid wu-border-0 wu-border-t wu-border-gray-300 <?php echo $step['done'] ? 'wu-bg-white wu-opacity-75' : 'wu-bg-gray-100' ; ?>"
      >

        <div>
          <span class="wu-hidden sm:wu-inline-block wu-mr-4 wu-bg-white wu-text-center wu-align-middle wu-p-1 wu-font-mono wu-px-3 wu-border wu-border-gray-300 wu-border-solid wu-rounded">
            <?php echo $index; ?>
          </span>
        </div>

        <div class="wu-w-full sm:wu-w-1/2">

          <span class="wu-block wu-my-1 wu-font-semibold wu-text-gray-700">

            <span class="<?php echo $step['done'] ? 'wu-line-through' : '' ; ?>"><?php echo $step['title']; ?></span>

            <?php if ($step['done']) : ?>

              <span class="wu-text-green-600 dashicons dashicons-yes-alt"></span>

            <?php endif; ?>

          </span>

          <span class="wu-block wu-my-1 wu-text-gray-600 <?php echo $step['done'] ? 'wu-line-through' : '' ; ?>"><?php echo $step['desc']; ?></span>

        </div>

        <div class="wu-w-full sm:wu-w-1/2 wu-text-right">

          <div class="wu-block sm:wu-hidden wu-h-2">&nbsp;</div>

          <a href="<?php echo $step['action_link']; ?>" class="button wu-w-full sm:wu-w-auto wu-text-center">
            <?php echo $step['action_label']; ?>
          </a>

        </div>

      </li>

    <?php $index++; endforeach; ?>

  </ul>

  <?php if ($all_done) : ?>

    <div class="wu-p-4 wu-bg-gray-100 wu-border-solid wu-border-0 wu-border-t wu-text-right wu-border-gray-300">

      <button
        value="wp-ultimo-setup"
        checked="checked"
        class="button wu-text-center hide-postbox-tog"
        id="wp-ultimo-setup-hide"
      >
        <?php _e('Dismiss', 'wp-ultimo'); ?>
      </button>

    </div>

  <?php endif; ?>

</div>
