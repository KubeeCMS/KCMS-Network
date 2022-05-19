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
$slug = 'signup_countries';
$headers = array(
  __('Country', 'wp-ultimo'),
  __('Customer Count', 'wp-ultimo'),
);

foreach ($countries as $country_code => $count) {

  $line = array(
    wu_get_country_name($country_code),
    $count,
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

<?php if (!empty($countries)) : ?>

    <div class="wu-advanced-filters wu--mx-3 wu--mb-3 wu-mt-3">

    <table class="wp-list-table widefat fixed striped wu-border-t-0 wu-border-l-0 wu-border-r-0">

      <thead>
        <tr>
          <th><?php _e('Country', 'wp-ultimo'); ?></th>
          <th class="wu-text-right"><?php _e('Customer Count', 'wp-ultimo'); ?></th>
        </tr>
      </thead>

      <tbody>

        <?php foreach ($countries as $country_code => $count) : ?>

          <tr>
            <td>
              <?php

                printf('<span class="wu-flag-icon wu-flag-icon-%s wu-w-5 wu-mr-1" %s></span>',
                  strtolower($country_code),
                  wu_tooltip_text(wu_get_country_name($country_code))
                );

              ?>
              <?php echo wu_get_country_name($country_code); ?>
            </td>
            <td class="wu-text-right"><?php echo $count; ?></td>
          </tr>

          <?php 
          
          $state_list = wu_get_states_of_customers($country_code);
          $_state_count = 0; 
          
          ?>

          <?php foreach ($state_list as $state => $state_count) : $_state_count = $_state_count + $state_count; ?>

            <tr>
              <td class="wu-text-xs">|&longrightarrow; <?php echo $state; ?></td>
              <td class="wu-text-right"><?php echo $state_count; ?></td>
            </tr>

          <?php endforeach; ?>

          <?php if ($state_list && $count - $_state_count >= 0) : ?>

            <tr>
              <td class="wu-text-xs">|&longrightarrow; <?php _e('Other', 'wp-ultimo') ?></td>
              <td class="wu-text-right"><?php echo $count - $_state_count; ?></td>
            </tr>

          <?php endif; ?>

        <?php endforeach; ?>

      </tbody>

    </table>

  </div>

<?php else : ?>

  <div class="wu-bg-gray-100 wu-p-4 wu-rounded wu-mt-6">

    <?php _e('No countries registered yet.', 'wp-ultimo'); ?>

  </div>

<?php endif; ?>
