<?php
/**
 * Graph countries view.
 *
 * @since 2.0.0
 */
?>
<?php if (!empty($sites)) : ?>

    <div class="wu-advanced-filters wu--mx-3 wu--mb-3 wu--mt-2">

    <table class="wp-list-table widefat fixed striped wu-border-t-0 wu-border-l-0 wu-border-r-0">

      <thead>
        <tr>
          <th class="wu-w-8/12"><?php _e('Site', 'wp-ultimo'); ?></th>
          <th><?php _e('Visits', 'wp-ultimo'); ?></th>
          <th class="wu-text-right"><?php _e('Actions', 'wp-ultimo'); ?></th>
        </tr>
      </thead>

      <tbody>

	      <?php foreach ($sites as $site_visits) : ?>

          <tr>
            <td class="wu-align-middle">
              <span class="wu-uppercase wu-text-xs wu-text-gray-700 wu-font-bold">
		            <?php echo $site_visits->site->get_title(); ?>
              </span>
              <span class="wu-text-xs wu-block">
                <a href="<?php echo $site_visits->site->get_active_site_url(); ?>">
		              <?php echo $site_visits->site->get_active_site_url(); ?>
                </a>
              </span>
            </td>
            <td class="wu-align-middle"><?php echo sprintf(__('%d visit(s)', 'wp-ultimo'), $site_visits->count); ?></td>
            <td class="wu-text-right wu-align-middle">
              <a href="<?php echo esc_attr(get_admin_url($site_visits->site->get_id())); ?>">  
		            <?php _e('View &rarr;', 'wp-ultimo'); ?>
              </a>
            </td>
          </tr>

        <?php endforeach; ?>

      </tbody>

    </table>

  </div>

<?php else : ?>

  <div class="wu-bg-gray-100 wu-p-4 wu-rounded">

    <?php _e('No visits registered in this period.', 'wp-ultimo'); ?>

  </div>

<?php endif; ?>
