<?php
/**
 * Graph countries view.
 *
 * @since 2.0.0
 */
?>

<div class="wu-styling">

<div class="wu-widget-inset">

<?php

$data    = array();
$slug    = 'most_visited_sites';
$headers = array(
	__('Site', 'wp-ultimo'),
	__('Visits', 'wp-ultimo'),
);

foreach ($sites as $site_visits) {

	$site_line = $site_visits->site->get_title().' '.get_admin_url($site_visits->site->get_id());

	$line = array(
		$site_line,
		$site_visits->count,
	);

	$data[] = $line;

} // end foreach;

$page->render_csv_button(array(
	'headers' => $headers,
	'data'    => $data,
	'slug'    => $slug,
));

?>

</div>

</div>

<?php if (!empty($sites)) : ?>

    <div class="wu-advanced-filters wu--mx-3 wu--mb-3 wu-mt-3">

    <table class="wp-list-table widefat fixed striped wu-border-t-0 wu-border-l-0 wu-border-r-0">

      <thead>
        <tr>
          <th class="wu-w-8/12"><?php _e('Site', 'wp-ultimo'); ?></th>
          <th class="wu-text-right"><?php _e('Visits', 'wp-ultimo'); ?></th>
        </tr>
      </thead>

      <tbody>

	      <?php foreach ($sites as $site_visits) : ?>

          <tr>
            <td class="wu-align-middle">
              <span class="wu-uppercase wu-text-xs wu-text-gray-700 wu-font-bold">
		            <?php echo $site_visits->site->get_title(); ?>
              </span>
            
              <div class="sm:wu-flex">          
            
                <a title="<?php _e('Homepage', 'wp-ultimo'); ?>" href="<?php echo esc_attr(get_home_url($site_visits->site->get_id())); ?>" class="wu-no-underline wu-flex wu-items-center wu-text-xs wp-ui-text-highlight">
          
                  <span class="dashicons-wu-link1 wu-align-middle wu-mr-1"></span>
                  <?php _e('Homepage', 'wp-ultimo'); ?>

                </a>

                <a title="<?php _e('Dashboard', 'wp-ultimo'); ?>" href="<?php echo esc_attr(get_admin_url($site_visits->site->get_id())); ?>" class="wu-no-underline wu-flex wu-items-center wu-text-xs wp-ui-text-highlight sm:wu-mt-0 sm:wu-ml-6">
          
                  <span class="dashicons-wu-browser wu-align-middle wu-mr-1"></span>
                  <?php _e('Dashboard', 'wp-ultimo'); ?>

                </a>
          
              </div>
            </td>
            <td class="wu-align-middle wu-text-right">
              <?php echo sprintf(_n('%d visit', '%d visits', $site_visits->count, 'wp-ultimo'), $site_visits->count); ?>
            </td>
          </tr>

        <?php endforeach; ?>

      </tbody>

    </table>

  </div>

<?php else : ?>

  <div class="wu-bg-gray-100 wu-p-4 wu-rounded wu-mt-6">

    <?php _e('No visits registered in this period.', 'wp-ultimo'); ?>

  </div>

<?php endif; ?>
