<?php
/**
 * Total widget view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-styling">

  <ul class="md:wu-flex wu-my-0 wu-mx-0">

    <li class="wu-p-2 wu-w-full md:wu-w-full wu-relative">

      <div>

        <strong class="wu-text-gray-800 wu-text-2xl md:wu-text-xl">
          <?php echo $new_accounts; ?>
        </strong>

      </div>

      <div class="wu-text-sm wu-text-gray-600">
        <span class="wu-block"><?php _e('New Memberships', 'wp-ultimo'); ?></span>
      </div>

    </li>

  </ul>

  <div class="wu--mx-3 wu--mb-3 wu-mt-2">


  <table class="wp-list-table widefat fixed striped wu-border-t-1 wu-border-l-0 wu-border-r-0">

      <thead>
        <tr>
          <th><?php _e('Product Name', 'wp-ultimo'); ?></th>
          <th class="wu-text-right"><?php _e('New Memberships', 'wp-ultimo'); ?></th>
        </tr>
      </thead>

      <tbody>

        <?php if ($products) : ?>

          <?php foreach ($products as $product) : ?>

            <tr>
              <td>
                <?php echo $product->name; ?>
              </td>
              <td class="wu-text-right">
                <?php echo $product->count; ?>
              </td>
            </tr>

          <?php endforeach; ?>

        <?php else : ?>

            <tr>
              <td colspan="2">
                <?php _e('No Products found.', 'wp-ultimo'); ?>
              </td>
            </tr>
          
        <?php endif; ?>
        
      </tbody>

  </table>

  </div>

</div>
