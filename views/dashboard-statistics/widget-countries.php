<?php
/**
 * Graph countries view.
 *
 * @since 2.0.0
 */
?>
<?php if (!empty($countries)) : ?>

    <div class="wu-advanced-filters wu--mx-3 wu--mb-3 wu--mt-2">

    <table class="wp-list-table widefat fixed striped wu-border-t-0 wu-border-l-0 wu-border-r-0">

      <thead>
        <tr>
          <th><?php _e('Country', 'wp-ultimo'); ?></th>
          <th><?php _e('Customer Count', 'wp-ultimo'); ?></th>
        </tr>
      </thead>

      <tbody>

        <?php foreach ($countries as $country_code => $count) : ?>

          <tr>
            <td>
              <?php printf('<img class="wu-align-text-bottom" src="https://www.countryflags.io/%s/flat/16.png" alt="Country Flag">', $country_code); ?>
              <?php echo wu_get_country_name($country_code); ?>
            </td>
            <td><?php echo $count; ?></td>
          </tr>

        <?php endforeach; ?>

      </tbody>

    </table>

  </div>

<?php else : ?>

  <div class="wu-bg-gray-100 wu-p-4 wu-rounded">

    <?php _e('No countries registered yet.', 'wp-ultimo'); ?>

  </div>

<?php endif; ?>
