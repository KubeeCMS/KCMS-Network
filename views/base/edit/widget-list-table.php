<?php
/**
 * List table widget view.
 *
 * @since 2.0.0
 */
?>
<?php echo $before; ?>

<?php if ($page->edit) : ?>

  <div class="wu-advanced-filters wu-widget-list-table wu--m-3 wu--mt-1 wu--mb-3">

    <?php $table->prepare_items(); ?>

    <!-- <form id="posts-filter" method="post"> -->

      <input type="hidden" name="page" value="<?php echo $page->get_id(); ?>">

      <?php $table->display(); ?>

    <!-- </form> -->

  </div>

<?php else : ?>

  <div class="wu-p-12 wu-h-12 wu--mt-1 wu--mx-3 wu--mb-3 wu-bg-gray-100 wu-text-gray-500 wu-text-xs wu-text-center">
    <span class="dashicons dashicons-warning wu-h-8 wu-w-8 wu-mx-auto wu-text-center wu-text-4xl wu-block"></span>
    <span class="wu-block wu-text-sm">
        <?php printf(__('%s will show up here once this item is saved.', 'wp-ultimo'), $title); ?>
    </span>
  </div>

<?php endif; ?>

<?php echo $after; ?>
