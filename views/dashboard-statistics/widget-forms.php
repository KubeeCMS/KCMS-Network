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

$data = array();
$slug = 'signup_forms';
$headers = array(
  __('Checkout Form', 'wp-ultimo'),
  __('Signups', 'wp-ultimo'),
);

foreach ($forms as $form) {

  $line = array(
    $form->signup_form,
    $form->count,
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

<?php if (!empty($forms)) : ?>

    <div class="wu-advanced-filters wu--mx-3 wu--mb-3 wu-mt-3">

    <table class="wp-list-table widefat fixed striped wu-border-t-0 wu-border-l-0 wu-border-r-0">

      <thead>
        <tr>
          <th><?php _e('Checkout Form', 'wp-ultimo'); ?></th>
          <th class="wu-text-right"><?php _e('Signups', 'wp-ultimo'); ?></th>
        </tr>
      </thead>

      <tbody>

        <?php foreach ($forms as $form) : ?>

          <tr>
            <td>
              <?php echo $form->signup_form; ?>
              <?php if ($form->signup_form === 'by-admin') : ?>
                <?php echo wu_tooltip(__('Customers created via the admin panel, by super admins.', 'wp-ultimo')); ?>
              <?php endif;?>
            </td>
            <td class="wu-text-right"><?php echo $form->count; ?></td>
          </tr>

        <?php endforeach; ?>

      </tbody>

    </table>

  </div>

<?php else : ?>

  <div class="wu-bg-gray-100 wu-p-4 wu-rounded wu-mt-6">

    <?php _e('No data yet.', 'wp-ultimo'); ?>

  </div>

<?php endif; ?>
