<?php
/**
 * Targets
 *
 * @since 2.0.0
 */
?>
<div class="<?php echo esc_attr($wrapper_class); ?>">

  <?php if ($targets) : ?>

    <ul class="wu-widget-list">

      <?php foreach ($targets as $target_key => $target) : ?>

        <li class="wu-p-2 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-200 wu-border-solid">

          <a title="<?php echo $target['display_name']; ?>" href='<?php echo $target['link'] ?>' class='<?php echo $modal_class; ?> wu-table-card wu-text-gray-700 wu-p-2 wu-flex wu-flex-grow wu-rounded wu-items-center wu-border wu-border-solid wu-border-gray-300 wu-no-underline'>

            <div class="wu-flex wu-relative wu-h-8 wu-w-8 wu-rounded-full wu-ring-2 wu-ring-white wu-bg-gray-300 wu-items-center wu-justify-center">

              <?php echo $target['avatar']; ?>

            </div>

          <div class='wu-pl-2'>

            <strong class='wu-block'><?php echo $target['display_name']; ?><small class='wu-font-normal'> (#<?php echo $target['id']; ?>)</small></strong>

            <small><?php echo $target['description']; ?></small>

          </div>

          </a>

        </li>

      <?php endforeach; ?>

    </ul>

    <?php else : ?>

    <ul class="wu-widget-list">

      <li class="wu-p-2 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-400 wu-border-solid">

        <div class="wu-p-2 wu-mr-1 wu-flex wu-rounded wu-items-center wu-border wu-border-solid wu-border-gray-300 wu-bg-white wu-relative wu-overflow-hidden">

          <span class='dashicons dashicons-wu-block wu-text-gray-600 wu-px-1 wu-pr-3'>&nbsp;</span>

          <div class=''>

            <span class='wu-block wu-py-3 wu-text-gray-600 wu-text-2xs wu-font-bold wu-uppercase'>

              <?php echo esc_html(__('No Targets', 'wp-ultimo')); ?>

            </span>

          </div>

        </div>

      </li>

    </ul>

    <?php endif; ?>

</div>
